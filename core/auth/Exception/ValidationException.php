<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

/**
 * Thrown when input fails validation rules (registration or login).
 *
 * Carries a structured field => [messages] map so API layers can return
 * precise 422 responses without re-parsing a flat message string.
 */
final class ValidationException extends AuthException
{
    /**
     * @param array<string, array<int, string>> $errors field => list of error messages
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Validation failed.',
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
