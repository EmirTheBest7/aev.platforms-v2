<?php

declare(strict_types=1);

namespace Api\Terminal;

use Core\Auth\DTO\AuthenticatedUser;
use Core\Logging\Logger;
use Core\Routing\Request;
use Core\Routing\Response;
use Core\Security\FormGuard;
use Core\Services\Notifier\Notifier;

/**
 * The retained `_api` allow-list (/home/_api/…) that backs the static terminal/Docs bundle.
 *
 * Every endpoint is listed explicitly in routes/web.php — nothing is dispatched from the URL. There is no CORS:
 * only this site's own pages call these. State-changing endpoints require the session CSRF token and are rate limited.
 */
final class ApiController
{
    public function __construct(
        private readonly FormGuard $guard,
        private readonly Notifier $notifier,
        private readonly DomainLookup $domains,
        private readonly Logger $logger,
        /** @var \Closure(): ?AuthenticatedUser */
        private readonly \Closure $currentUser,
        private readonly string $appUrl,
    ) {}

    /** /home/_api/ opens the UI, as the original did when no function was named. */
    public function root(Request $request): Response
    {
        return Response::redirect('/home/_api/UI/', 302);
    }

    public function hello(Request $request): Response
    {
        return $this->json(['ok' => true, 'message' => 'Hello']);
    }

    public function info(Request $request): Response
    {
        return $this->json(['ok' => true, 'name' => 'ΛΞV', 'url' => $this->appUrl, 'logo' => $this->appUrl . '/build/images/brand/ALIEV.svg']);
    }

    /** Who the terminal prompt should name. */
    public function me(Request $request): Response
    {
        $user = ($this->currentUser)();

        return $this->json($user === null ? ['authenticated' => false] : ['authenticated' => true, 'username' => $user->username]);
    }

    public function updates(Request $request): Response
    {
        if (($this->currentUser)() === null) {
            return $this->json(['ok' => false, 'error' => 401, 'message' => 'Sign in required.'], 401);
        }

        return $this->json(['ok' => true, 'updates' => []]);
    }

    /** CSRF token for the static pages of the bundle (they have no server-side template to carry one). */
    public function csrf(Request $request): Response
    {
        return $this->json(['token' => $this->guard->tokens()['csrf']]);
    }

    /** Terminal `domain` tool: ?name=example.com → {"domain":…,"hasRecords":bool}. */
    public function domain(Request $request): Response
    {
        $ip = $this->guard->clientAddress($request);
        $this->guard->requireWithinLimit('api-domain', $ip, $this->guard->pseudonym($ip), 20, 200);

        $name = DomainLookup::normalize($request->query['name'] ?? '');
        if ($name === null) {
            return $this->json(['ok' => false, 'error' => 422, 'message' => 'Enter a domain name such as example.com.'], 422);
        }

        return $this->json(['ok' => true, 'domain' => $name, 'hasRecords' => $this->domains->hasRecords($name)]);
    }

    /**
     * The Valentine page's "yes" button. The original sent a Telegram message straight from the browser with the bot
     * token in the page script; now the browser only posts here and the (environment-configured) notifier sends a
     * fixed message. No client-supplied text ever reaches the message.
     */
    public function valentineYes(Request $request): Response
    {
        $this->guard->requireCsrf($request, 'api-valentine');
        $ip = $this->guard->clientAddress($request);
        $who = $this->guard->pseudonym($ip);
        $this->guard->requireWithinLimit('api-valentine', $ip, $who, 3, 5);

        $delivered = $this->notifier->send('She said yes! 💌');
        $this->logger->info('api.valentine', ['who' => $who, 'delivered' => $delivered]);

        return $this->json(['ok' => true]);
    }

    /** @param array<string, mixed> $data */
    private function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status)->withHeader('Cache-Control', 'no-store')->withHeader('X-Robots-Tag', 'noindex');
    }
}
