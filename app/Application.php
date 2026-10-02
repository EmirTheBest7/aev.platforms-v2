<?php

declare(strict_types=1);

namespace App;

use App\Controllers\ApiController;
use App\Controllers\CareersController;
use App\Controllers\ContactController;
use App\Controllers\ErrorController;
use App\Controllers\HireController;
use App\Controllers\HomeController;
use App\Controllers\WidgetController;
use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Models\JobRepository;
use App\Security\ClientIp;
use App\Security\FormGuard;
use App\Security\RateLimiter;
use App\Security\SecurityHeaders;
use App\Security\Signer;
use App\Services\Http\CurlHttpClient;
use App\Services\Http\HttpClient;
use App\Services\LeadStore;
use App\Services\Market\CoinbaseProvider;
use App\Services\Market\CoinGeckoProvider;
use App\Services\Market\MarketDataProvider;
use App\Services\Market\PriceService;
use App\Services\Notifier\LogNotifier;
use App\Services\Notifier\Notifier;
use App\Services\Notifier\TelegramNotifier;
use App\Support\AppCatalog;
use App\Support\Config;
use App\Support\Env;
use App\Support\Logger;
use App\Support\View;
use App\Validation\ContactValidator;
use App\Validation\HireValidator;
use Core\Auth\Config\AuthConfig;
use Core\Auth\Database\PdoConnection;
use Core\Auth\Security\CsrfProtection;
use Core\Auth\Security\TokenGenerator;
use Core\Auth\Session\SessionManager;

/**
 * Composition root: reads configuration, wires services explicitly (no magic container),
 * and turns a Request into a Response with security headers and safe error handling applied.
 *
 * `$overrides` lets tests inject collaborators: notifier, http.
 */
final class Application
{
    /** URL prefix whose trailing slash is part of its identity (retained static _api bundle). */
    private const SLASH_PREFIX = '/home/_api/';

    public readonly Config $config;
    public readonly Logger $logger;
    private readonly Router $router;
    private readonly View $view;
    private readonly ErrorController $errors;
    private readonly SecurityHeaders $headers;
    private readonly ClientIp $clientIp;
    private readonly string $storage;
    private readonly bool $secureUrl;
    private ?FormGuard $guard = null;
    private ?AuthConfig $authConfig = null;
    private ?SessionManager $session = null;
    private ?CsrfProtection $csrf = null;
    private ?\PDO $pdo = null;
    private ?HttpClient $http = null;

    /** @param array{notifier?: Notifier, http?: HttpClient, pdo?: \PDO} $overrides */
    public function __construct(
        private readonly string $root,
        ?Config $config = null,
        ?string $storage = null,
        private readonly array $overrides = [],
    ) {
        $this->config = $config ?? Config::fromDirectory($root . '/config');
        $this->storage = $storage ?? $root . '/storage';

        $this->logger = new Logger(
            $this->config->string('app.log_channel', 'file'),
            $this->storage . '/logs',
            $this->config->string('app.log_level', 'info'),
        );

        $this->view = new View($root . '/app/Views', $this->config->string('app.url'), $root . '/public');
        $this->errors = new ErrorController($this->view);
        $this->clientIp = new ClientIp($this->config->get('security.trusted_proxies', []) ?: []);

        $this->secureUrl = str_starts_with($this->config->string('app.url'), 'https://');
        $production = $this->config->string('app.env') === 'production';
        $this->headers = new SecurityHeaders(hsts: $this->secureUrl && $production, upgradeInsecure: $this->secureUrl && $production);

        $this->router = new Router();
        $this->registerRoutes();
    }

    public static function boot(string $root): self
    {
        Env::load($root . '/.env');

        return new self($root);
    }

    public function handle(Request $request): Response
    {
        try {
            $canonicalPath = $this->canonicalPath($request->path);
            $response = $this->httpsRedirect($request->withPath($canonicalPath))
                ?? $this->router->dispatch($request->withPath($canonicalPath));

            // Serve one URL per page: /contact/ -> /contact (single hop, only when a real page matched,
            // so legacy redirects such as /page/main/ -> / are never chained).
            if ($canonicalPath !== $request->path && $response->status < 300) {
                $query = $request->server['QUERY_STRING'] ?? '';
                $response = Response::redirect($canonicalPath . ($query !== '' ? '?' . $query : ''), 301);
            }
        } catch (HttpException $e) {
            $response = $request->wantsJson() ? $this->errors->renderJson($e->status) : $this->errors->render($e->status, $request->path);
            if ($e->status === 405) {
                $response = $response->withHeader('Allow', implode(', ', $this->router->allowedMethods($request->path)));
            }
        } catch (\Throwable $e) {
            $this->logger->error('request.unhandled', ['exception' => $e, 'path' => $request->path, 'at' => basename($e->getFile()) . ':' . $e->getLine()]);
            $response = match (true) {
                $this->config->bool('app.debug') => new Response("Unhandled exception (debug mode)\n\n" . $e, 500, 'text/plain; charset=UTF-8'),
                $request->wantsJson() => $this->errors->renderJson(500),
                default => $this->errors->render(500, $request->path),
            };
        }

        if ($response->header('Cache-Control') === null) {
            $response = $response->withHeader('Cache-Control', 'no-cache');
        }

        return $this->headers->apply($response);
    }

    /**
     * Application routes have no trailing slash (`/x/` → `/x`). The retained static bundle under
     * /home/_api/ keeps it: its relative asset paths only resolve with the slash (docs/ARCHITECTURE.md §6).
     */
    private function canonicalPath(string $path): string
    {
        $normalized = Request::normalizePath($path);
        $collapsed = '/' . ltrim((string) preg_replace('#/{2,}#', '/', $path), '/');

        return str_starts_with($collapsed, self::SLASH_PREFIX) ? $collapsed : $normalized;
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

    private function registerRoutes(): void
    {
        $routes = require $this->root . '/routes/web.php';
        $routes($this->router, [
            'home' => fn(): HomeController => new HomeController($this->view, $this->config, new AppCatalog($this->config), $this->guard()),
            'contact' => fn(): ContactController => new ContactController($this->view, $this->guard(), new ContactValidator(), new LeadStore($this->storage . '/leads'), $this->notifier(), $this->logger, $this->config->string('integrations.mapbox_token'), $this->config->string('integrations.destinations.email')),
            'hire' => fn(): HireController => new HireController($this->guard(), new HireValidator(), new LeadStore($this->storage . '/leads'), $this->notifier(), $this->logger),
            'api' => fn(): ApiController => new ApiController($this->priceService()),
            'widgets' => fn(): WidgetController => new WidgetController($this->view),
            'careers' => fn(): CareersController => new CareersController($this->view, new JobRepository($this->pdo()), $this->logger, $this->config->string('integrations.destinations.email')),
        ]);
    }

    private function guard(): FormGuard
    {
        if ($this->guard === null) {
            $this->guard = new FormGuard(
                $this->session(),
                $this->csrf(),
                new Signer($this->config->string('app.key')),
                new RateLimiter($this->storage . '/ratelimit'),
                $this->clientIp,
                $this->logger,
            );
        }

        return $this->guard;
    }

    /** The one session manager of the site: forms, auth and the API all share it (never build a second). */
    private function session(): SessionManager
    {
        return $this->session ??= new SessionManager($this->authConfig());
    }

    /** The one CSRF implementation of the site (core/auth). */
    private function csrf(): CsrfProtection
    {
        return $this->csrf ??= new CsrfProtection($this->authConfig(), new TokenGenerator());
    }

    private function authConfig(): AuthConfig
    {
        return $this->authConfig ??= new AuthConfig(sessionName: 'aliev_session', sessionLifetimeSeconds: 3600, sessionCookieSecure: $this->secureUrl);
    }

    /**
     * The one database connection (lazy: pages that do not need the database never open it).
     * A connection failure surfaces as PDOException; controllers log it and answer with a generic 503.
     */
    private function pdo(): \PDO
    {
        return $this->pdo ??= $this->overrides['pdo'] ?? PdoConnection::forMysql(
            $this->config->string('database.host'),
            $this->config->string('database.database'),
            $this->config->string('database.username'),
            $this->config->string('database.password'),
            $this->config->int('database.port', 3306),
        )->connect();
    }

    private function http(): HttpClient
    {
        return $this->http ??= $this->overrides['http'] ?? new CurlHttpClient();
    }

    private function notifier(): Notifier
    {
        return $this->overrides['notifier'] ?? match ($this->config->string('notify.driver', 'log')) {
            'telegram' => new TelegramNotifier(
                $this->config->string('notify.telegram.token'),
                $this->config->string('notify.telegram.chat_id'),
                max(1, $this->config->int('notify.timeout', 4)),
                $this->logger,
            ),
            default => new LogNotifier($this->logger),
        };
    }

    private function priceService(): PriceService
    {
        /** @var array<string, array<string, string>> $symbols */
        $symbols = (array) $this->config->get('market.symbols', []);
        $providers = [];
        foreach ((array) $this->config->get('market.providers', []) as $name) {
            $provider = $this->provider((string) $name);
            if ($provider !== null) {
                $providers[] = $provider;
            }
        }

        return new PriceService(
            $this->storage . '/cache/prices.json',
            $providers,
            $symbols,
            max(10, $this->config->int('market.ttl', 60)),
            max(60, $this->config->int('market.max_stale', 21600)),
            $this->logger,
        );
    }

    private function provider(string $name): ?MarketDataProvider
    {
        return match ($name) {
            'coingecko' => new CoinGeckoProvider($this->http(), $this->config->string('market.coingecko_key')),
            'coinbase' => new CoinbaseProvider($this->http()),
            default => null,
        };
    }
}
