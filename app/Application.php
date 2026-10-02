<?php

declare(strict_types=1);

namespace App;

use App\Controllers\ContactController;
use App\Controllers\ErrorController;
use App\Controllers\PageController;
use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Security\ClientIp;
use App\Security\RateLimiter;
use App\Security\SecurityHeaders;
use App\Security\Signer;
use App\Services\LeadStore;
use App\Services\Notifier\LogNotifier;
use App\Services\Notifier\Notifier;
use App\Services\Notifier\TelegramNotifier;
use App\Support\Config;
use App\Support\Env;
use App\Support\Logger;
use App\Support\View;
use App\Validation\ContactValidator;
use Core\Auth\Config\AuthConfig;
use Core\Auth\Security\CsrfProtection;
use Core\Auth\Security\TokenGenerator;
use Core\Auth\Session\SessionManager;

/**
 * Composition root: reads configuration, wires services explicitly (no magic
 * container), and turns a Request into a Response with security headers and
 * safe error handling applied.
 */
final class Application
{
    public readonly Config $config;
    public readonly Logger $logger;
    private readonly Router $router;
    private readonly ErrorController $errors;
    private readonly SecurityHeaders $headers;
    private readonly ClientIp $clientIp;
    private readonly string $storage;

    public function __construct(
        private readonly string $root,
        ?Config $config = null,
        ?string $storage = null,
        private readonly ?Notifier $notifierOverride = null,
    ) {
        $this->config = $config ?? Config::fromDirectory($root . '/config');
        $this->storage = $storage ?? $root . '/storage';

        $this->logger = new Logger(
            $this->config->string('app.log_channel', 'file'),
            $this->storage . '/logs',
            $this->config->string('app.log_level', 'info'),
        );

        $view = new View($root . '/app/Views', $this->config->string('app.url'));
        $this->errors = new ErrorController($view);
        $this->clientIp = new ClientIp($this->config->get('security.trusted_proxies', []) ?: []);

        $secureUrl = str_starts_with($this->config->string('app.url'), 'https://');
        $this->headers = new SecurityHeaders(
            hsts: $secureUrl && $this->config->string('app.env') === 'production',
            upgradeInsecure: $secureUrl && $this->config->string('app.env') === 'production',
        );

        $this->router = new Router();
        $this->registerRoutes($view, $secureUrl);
    }

    public static function boot(string $root): self
    {
        Env::load($root . '/.env');

        return new self($root);
    }

    public function handle(Request $request): Response
    {
        try {
            $canonicalPath = Request::normalizePath($request->path);
            $response = $this->httpsRedirect($request->withPath($canonicalPath))
                ?? $this->router->dispatch($request->withPath($canonicalPath));

            // Serve one URL per page: /contact/ -> /contact (single hop, only when a real page matched,
            // so legacy redirects such as /page/main/ -> / are never chained).
            if ($canonicalPath !== $request->path && $response->status < 300) {
                $query = $request->server['QUERY_STRING'] ?? '';
                $response = Response::redirect($canonicalPath . ($query !== '' ? '?' . $query : ''), 301);
            }
        } catch (HttpException $e) {
            $response = $this->errors->render($e->status, $request->path);
            if ($e->status === 405) {
                $response = $response->withHeader('Allow', implode(', ', $this->router->allowedMethods($request->path)));
            }
        } catch (\Throwable $e) {
            $this->logger->error('request.unhandled', ['exception' => $e, 'path' => $request->path, 'at' => basename($e->getFile()) . ':' . $e->getLine()]);
            $response = $this->config->bool('app.debug')
                ? new Response("Unhandled exception (debug mode)\n\n" . $e, 500, 'text/plain; charset=UTF-8')
                : $this->errors->render(500, $request->path);
        }

        if ($response->header('Cache-Control') === null) {
            $response = $response->withHeader('Cache-Control', 'no-cache');
        }

        return $this->headers->apply($response);
    }

    private function httpsRedirect(Request $request): ?Response
    {
        if (!$this->config->bool('security.force_https')) {
            return null;
        }
        $peerTrusted = $this->clientIp->isTrusted($request->server['REMOTE_ADDR'] ?? '');
        $secure = $request->isSecure() || ($peerTrusted && strtolower($request->header('X-Forwarded-Proto') ?? '') === 'https');
        if ($secure) {
            return null;
        }

        return Response::redirect(rtrim($this->config->string('app.url'), '/') . $request->path, 301);
    }

    private function registerRoutes(View $view, bool $secureUrl): void
    {
        $pages = new PageController($view);
        $contact = fn(): ContactController => $this->contactController($view, $secureUrl);

        $routes = require $this->root . '/routes/web.php';
        $routes($this->router, $pages, $contact);
    }

    private function contactController(View $view, bool $secureUrl): ContactController
    {
        $auth = new AuthConfig(
            sessionName: 'aliev_session',
            sessionLifetimeSeconds: 3600,
            sessionCookieSecure: $secureUrl,
        );
        $session = new SessionManager($auth);

        return new ContactController(
            $view,
            $session,
            new CsrfProtection($auth, new TokenGenerator()),
            new Signer($this->config->string('app.key')),
            new RateLimiter($this->storage . '/ratelimit'),
            $this->clientIp,
            new ContactValidator(),
            new LeadStore($this->storage . '/leads'),
            $this->notifier(),
            $this->logger,
        );
    }

    private function notifier(): Notifier
    {
        return $this->notifierOverride ?? match ($this->config->string('notify.driver', 'log')) {
            'telegram' => new TelegramNotifier(
                $this->config->string('notify.telegram.token'),
                $this->config->string('notify.telegram.chat_id'),
                max(1, $this->config->int('notify.timeout', 4)),
                $this->logger,
            ),
            default => new LogNotifier($this->logger),
        };
    }
}
