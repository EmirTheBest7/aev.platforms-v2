<?php

declare(strict_types=1);

namespace Core\Auth\DTO;

/**
 * Raw login input, captured immutably before validation.
 *
 * ipAddress / userAgent are carried through so LoginService can pass them
 * to BruteForceGuard, LoginAttemptRepository, and AuditLogger without
 * reaching into superglobals deep inside the module.
 */
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $ipAddress,
        public ?string $userAgent = null,
    ) {
    }
}
