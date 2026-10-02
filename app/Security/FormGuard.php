<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\HttpException;
use App\Http\Request;
use App\Support\Logger;
use Core\Auth\Contracts\SessionHandlerInterface;
use Core\Auth\Security\CsrfProtection;

/**
 * The shared protections for every public POST form:
 * body-size limit → session + CSRF → honeypot → signed form-age window → rate limit.
 *
 * `check()` either throws an HttpException (413/403/429), or returns how the caller should proceed.
 */
final class FormGuard
{
    public const OK = 'ok';
    public const HONEYPOT = 'honeypot';   // a bot filled the hidden field: pretend success, store nothing
    public const TOO_FAST = 'timing';     // submitted too quickly / too stale / forged timestamp

    private const MAX_BODY_BYTES = 16384;
    private const MIN_FILL_SECONDS = 3;
    private const MAX_FORM_AGE_SECONDS = 7200;

    public function __construct(
        private readonly SessionHandlerInterface $session,
        private readonly CsrfProtection $csrf,
        private readonly Signer $signer,
        private readonly RateLimiter $limiter,
        private readonly ClientIp $clientIp,
        private readonly Logger $logger,
    ) {}

    /**
     * Starts the session and returns the hidden-field values a form must render.
     *
     * @return array{csrf: string, ts: string}
     */
    public function tokens(): array
    {
        $this->session->start();

        return ['csrf' => $this->csrf->getToken(), 'ts' => $this->signer->sign((string) time())];
    }

    /**
     * @param string $bucket     rate-limit bucket name (e.g. "contact", "hire")
     * @param string $csrfField  name of the CSRF field in the body; the `X-CSRF-Token` header is also accepted
     * @return array{outcome: string, who: string}
     */
    public function check(Request $request, string $bucket, int $perTenMinutes = 3, int $perDay = 10, string $csrfField = '_csrf'): array
    {
        $this->requireCsrf($request, $bucket, $csrfField);

        $ip = $this->clientIp->resolve($request);
        $who = $this->signer->pseudonym($ip);

        if ($request->input('website') !== '') {
            $this->logger->info($bucket . '.honeypot', ['who' => $who]);

            return ['outcome' => self::HONEYPOT, 'who' => $who];
        }

        $rendered = $this->signer->verify($request->input('_ts'));
        $age = $rendered !== null && ctype_digit($rendered) ? time() - (int) $rendered : -1;
        if ($age < self::MIN_FILL_SECONDS || $age > self::MAX_FORM_AGE_SECONDS) {
            return ['outcome' => self::TOO_FAST, 'who' => $who];
        }

        $this->requireWithinLimit($bucket, $ip, $who, $perTenMinutes, $perDay);

        return ['outcome' => self::OK, 'who' => $who];
    }

    /**
     * Size limit + session + CSRF for a state-changing request (HttpException 413 / 403).
     * Used directly by script endpoints that carry the token in a header.
     */
    public function requireCsrf(Request $request, string $bucket, string $csrfField = '_csrf', int $maxBytes = self::MAX_BODY_BYTES): void
    {
        if ($request->contentLength() > $maxBytes) {
            throw new HttpException(413);
        }

        $this->session->start();

        $token = $request->input($csrfField);
        if ($token === '') {
            $token = $request->header('X-CSRF-Token') ?? '';
        }
        if (!$this->csrf->validate($token)) {
            $this->logger->warning($bucket . '.csrf_rejected');
            throw new HttpException(403);
        }
    }

    /** Two-window rate limit per client IP (HttpException 429). */
    public function requireWithinLimit(string $bucket, string $ip, string $who, int $perTenMinutes, int $perDay): void
    {
        if (!$this->limiter->hit($bucket . ':short', $ip, $perTenMinutes, 600) || !$this->limiter->hit($bucket . ':day', $ip, $perDay, 86400)) {
            $this->logger->warning($bucket . '.rate_limited', ['who' => $who]);
            throw new HttpException(429);
        }
    }

    public function clientAddress(Request $request): string
    {
        return $this->clientIp->resolve($request);
    }

    public function pseudonym(string $ip): string
    {
        return $this->signer->pseudonym($ip);
    }

    public function rotate(): void
    {
        $this->csrf->rotateToken();
    }
}
