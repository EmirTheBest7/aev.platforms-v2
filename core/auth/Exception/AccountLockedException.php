<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

/**
 * Thrown when a login attempt is blocked by brute-force protection.
 *
 * Suggested API mapping: HTTP 429 Too Many Requests.
 */
final class AccountLockedException extends AuthException
{
    public function __construct(
        private readonly int $retryAfterSeconds,
        string $message = 'Too many failed login attempts. Please try again later.',
    ) {
        parent::__construct($message);
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
