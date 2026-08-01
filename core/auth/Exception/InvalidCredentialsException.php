<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

/**
 * Thrown when login credentials do not match a known, active account.
 *
 * Intentionally generic ("email or password is incorrect") — never reveals
 * whether the email exists, to avoid user enumeration.
 *
 * Suggested API mapping: HTTP 401 Unauthorized.
 */
final class InvalidCredentialsException extends AuthException
{
    public function __construct(string $message = 'Email or password is incorrect.')
    {
        parent::__construct($message);
    }
}
