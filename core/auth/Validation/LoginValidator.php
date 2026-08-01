<?php

declare(strict_types=1);

namespace Core\Auth\Validation;

use Core\Auth\DTO\LoginData;
use Core\Auth\Exception\ValidationException;

/**
 * Validates login input shape only (non-empty, well-formed email).
 *
 * Whether the credentials are actually CORRECT is a separate concern,
 * handled by LoginService + InvalidCredentialsException — validation
 * failures here mean "malformed request", not "wrong password".
 */
final class LoginValidator
{
    /**
     * @throws ValidationException if input is malformed.
     */
    public function validate(LoginData $data): void
    {
        $errors = [];

        if (trim($data->email) === '') {
            $errors['email'][] = 'Email is required.';
        } elseif (filter_var($data->email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'][] = 'Email format is invalid.';
        }

        if ($data->password === '') {
            $errors['password'][] = 'Password is required.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}
