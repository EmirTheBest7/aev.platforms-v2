<?php

declare(strict_types=1);

namespace Core\Auth\Config;

/**
 * Central, immutable configuration for the authentication module.
 *
 * Every tunable security/behavioral value lives here so the rest of the
 * module (and future callers in api/v2/auth/) never hardcodes magic numbers.
 */
final class AuthConfig
{
    public function __construct(
        // --- Password hashing (Argon2id) ---
        public readonly int $passwordMinLength = 10,
        public readonly int $passwordMaxLength = 128,
        public readonly int $argon2Memory = 65536,   // KiB (64 MB)
        public readonly int $argon2Time = 4,          // iterations
        public readonly int $argon2Threads = 2,

        // --- Username rules ---
        public readonly int $usernameMinLength = 3,
        public readonly int $usernameMaxLength = 32,
        public readonly string $usernamePattern = '/^[a-zA-Z0-9_.\-]+$/',

        // --- Brute force protection ---
        public readonly int $maxFailedAttempts = 5,
        public readonly int $bruteForceWindowSeconds = 900,   // 15 minutes
        public readonly int $lockoutDurationSeconds = 900,    // 15 minutes

        // --- Session ---
        public readonly string $sessionName = 'aliev_session',
        public readonly int $sessionLifetimeSeconds = 3600,   // 1 hour
        public readonly bool $sessionCookieSecure = true,
        public readonly bool $sessionCookieHttpOnly = true,
        public readonly string $sessionCookieSameSite = 'Lax',

        // --- CSRF ---
        public readonly string $csrfSessionKey = '_csrf_token',
        public readonly int $csrfTokenLength = 32, // bytes, before hex encoding

        // --- Tokens (generic secure tokens: CSRF, future reset/remember-me) ---
        public readonly int $tokenLength = 32, // bytes, before hex encoding
    ) {
    }

    /**
     * Options array as expected by password_hash() for PASSWORD_ARGON2ID.
     *
     * @return array{memory_cost:int,time_cost:int,threads:int}
     */
    public function argon2Options(): array
    {
        return [
            'memory_cost' => $this->argon2Memory,
            'time_cost' => $this->argon2Time,
            'threads' => $this->argon2Threads,
        ];
    }
}
