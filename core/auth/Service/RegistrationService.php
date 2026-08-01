<?php

declare(strict_types=1);

namespace Core\Auth\Service;

use Core\Auth\Contracts\AuditLoggerInterface;
use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\DTO\AuthenticatedUser;
use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\DuplicateAccountException;
use Core\Auth\Exception\ValidationException;
use Core\Auth\Security\PasswordHasher;
use Core\Auth\Validation\RegistrationValidator;

/**
 * Orchestrates the registration flow:
 * validate -> check duplicates -> hash password -> persist -> audit.
 */
final class RegistrationService
{
    public function __construct(
        private readonly RegistrationValidator $validator,
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasher $hasher,
        private readonly AuditLoggerInterface $auditLogger,
    ) {
    }

    /**
     * @throws ValidationException if input fails validation rules.
     * @throws DuplicateAccountException if the email or username is taken.
     */
    public function register(RegistrationData $data, string $ipAddress, ?string $userAgent = null): AuthenticatedUser
    {
        $this->validator->validate($data);

        if ($this->users->existsByEmail($data->email)) {
            $this->auditLogger->log('registration.failed', null, $ipAddress, $userAgent, [
                'reason' => 'duplicate_email',
            ]);

            throw DuplicateAccountException::forEmail($data->email);
        }

        if ($this->users->existsByUsername($data->username)) {
            $this->auditLogger->log('registration.failed', null, $ipAddress, $userAgent, [
                'reason' => 'duplicate_username',
            ]);

            throw DuplicateAccountException::forUsername($data->username);
        }

        $passwordHash = $this->hasher->hash($data->password);

        $userId = $this->users->create($data->email, $data->username, $passwordHash);

        $this->auditLogger->log('registration.success', $userId, $ipAddress, $userAgent);

        $user = $this->users->findAuthenticatedUserById($userId);

        // create() succeeded immediately prior, so the row is guaranteed
        // to exist; this null-check exists only to satisfy strict typing.
        if ($user === null) {
            throw new \RuntimeException('Failed to load newly created user.');
        }

        return $user;
    }
}
