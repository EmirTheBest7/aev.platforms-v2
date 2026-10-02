<?php

declare(strict_types=1);

namespace Core\Auth\DTO;

/**
 * Raw registration input, captured immutably before validation.
 *
 * This is a dumb data carrier — RegistrationValidator is responsible for
 * deciding whether its contents are actually valid.
 */
final readonly class RegistrationData
{
    public function __construct(
        public string $email,
        public string $username,
        public string $password,
        public string $passwordConfirmation,
        public ?string $displayName = null,   // "Your Full Name" on the legacy form (optional, <= 80 chars)
        public ?string $referralCode = null,  // stored as entered after validation; no behaviour attached
    ) {
    }
}
