<?php

declare(strict_types=1);

namespace App\Security;

use App\Http\Response;

/**
 * Applies the site-wide security header policy (documented in docs/SECURITY.md).
 * The CSP forbids inline script/style: all behaviour and styling ships as
 * same-origin files, which is what makes this policy strict and simple.
 */
final class SecurityHeaders
{
    public function __construct(
        private readonly bool $hsts,
        private readonly bool $upgradeInsecure,
    ) {}

    public function apply(Response $response): Response
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "manifest-src 'self'",
            "worker-src 'self'",
            "object-src 'none'",
            "base-uri 'none'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];
        if ($this->upgradeInsecure) {
            $csp[] = 'upgrade-insecure-requests';
        }

        $headers = [
            'Content-Security-Policy' => implode('; ', $csp),
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-Frame-Options' => 'DENY',
        ];
        if ($this->hsts) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if ($response->header($name) === null) {
                $response = $response->withHeader($name, $value);
            }
        }

        return $response;
    }
}
