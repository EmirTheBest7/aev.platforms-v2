<?php

declare(strict_types=1);

namespace Core\Auth\Validation;

use Core\Auth\Config\AuthConfig;
use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\ValidationException;

/**
 * Validates registration input shape and rules (NOT uniqueness — that is
 * a persistence concern handled by RegistrationService + UserRepository).
 */
final class RegistrationValidator
{
    public function __construct(private readonly AuthConfig $config)
    {
    }

    /**
     * @throws ValidationException if any field fails validation.
     */
    public function validate(RegistrationData $data): void
    {
        $errors = [];

        $this->validateEmail($data->email, $errors);
        $this->validateUsername($data->username, $errors);
        $this->validatePassword($data->password, $data->passwordConfirmation, $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateEmail(string $email, array &$errors): void
    {
        if (trim($email) === '') {
            $errors['email'][] = 'Email is required.';

            return;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'][] = 'Email format is invalid.';
        }

        if (mb_strlen($email) > 254) {
            $errors['email'][] = 'Email is too long.';
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateUsername(string $username, array &$errors): void
    {
        if (trim($username) === '') {
            $errors['username'][] = 'Username is required.';

            return;
        }

        $length = mb_strlen($username);

        if ($length < $this->config->usernameMinLength || $length > $this->config->usernameMaxLength) {
            $errors['username'][] = sprintf(
                'Username must be between %d and %d characters.',
                $this->config->usernameMinLength,
                $this->config->usernameMaxLength,
            );
        }

        if (preg_match($this->config->usernamePattern, $username) !== 1) {
            $errors['username'][] = 'Username may only contain letters, numbers, dots, underscores, and hyphens.';
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validatePassword(string $password, string $confirmation, array &$errors): void
    {
        if ($password === '') {
            $errors['password'][] = 'Password is required.';

            return;
        }

        $length = mb_strlen($password);

        if ($length < $this->config->passwordMinLength) {
            $errors['password'][] = sprintf(
                'Password must be at least %d characters.',
                $this->config->passwordMinLength,
            );
        }

        if ($length > $this->config->passwordMaxLength) {
            $errors['password'][] = sprintf(
                'Password must be at most %d characters.',
                $this->config->passwordMaxLength,
            );
        }

        if (preg_match('/[A-Z]/', $password) !== 1) {
            $errors['password'][] = 'Password must contain at least one uppercase letter.';
        }

        if (preg_match('/[a-z]/', $password) !== 1) {
            $errors['password'][] = 'Password must contain at least one lowercase letter.';
        }

        if (preg_match('/[0-9]/', $password) !== 1) {
            $errors['password'][] = 'Password must contain at least one digit.';
        }

        if (!hash_equals($confirmation, $password)) {
            $errors['password_confirmation'][] = 'Password confirmation does not match.';
        }
    }
}
