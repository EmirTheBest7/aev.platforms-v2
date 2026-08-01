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
    private function __construct(
        string $message,
        private readonly string $field,
        private readonly string $value,
    ) {
        parent::__construct($message);
    }

    public static function forEmail(string $email): self
    {
        return new self(
            sprintf('An account with the email "%s" already exists.', $email),
            'email',
            $email,
        );
    }

    public static function forUsername(string $username): self
    {
        return new self(
            sprintf('An account with the username "%s" already exists.', $username),
            'username',
            $username,
        );
    }

    /**
     * Which field caused the conflict: "email" or "username".
     * Useful for API layers building a structured 409 response.
     */
    public function field(): string
    {
        return $this->field;
    }

    /**
     * The conflicting value itself.
     */
    public function value(): string
    {
        return $this->value;
    }
}
