<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

/**
 * Thrown during registration when the email or username is already taken.
 *
 * Suggested API mapping: HTTP 409 Conflict.
 */
final class DuplicateAccountException extends AuthException
{
    private function __construct(string $message, private readonly string $field)
    {
        parent::__construct($message);
    }

    public static function forEmail(string $email): self
    {
        return new self("An account with this email already exists.", 'email');
    }

    public static function forUsername(string $username): self
    {
        return new self("An account with this username already exists.", 'username');
    }

    public function field(): string
    {
        return $this->field;
    }
}
