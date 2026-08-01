<?php

declare(strict_types=1);

namespace Core\Auth\Service;

use Core\Auth\Contracts\AuditLoggerInterface;
use Core\Auth\Contracts\SessionHandlerInterface;
use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\DTO\AuthenticatedUser;
use Core\Auth\DTO\LoginData;
use Core\Auth\Exception\AccountLockedException;
use Core\Auth\Exception\InvalidCredentialsException;
use Core\Auth\Exception\ValidationException;
use Core\Auth\Security\BruteForceGuard;
use Core\Auth\Security\PasswordHasher;
use Core\Auth\Validation\LoginValidator;

/**
 * Orchestrates the login flow:
 * validate -> lock check -> verify password -> brute-force bookkeeping
 * -> session creation -> last-login tracking -> audit.
 */
final class LoginService
{
    public function __construct(
        private readonly LoginValidator $validator,
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasher $hasher,
        private readonly BruteForceGuard $bruteForceGuard,
        private readonly SessionHandlerInterface $session,
        private readonly AuditLoggerInterface $auditLogger,
    ) {
    }

    /**
     * @throws ValidationException if input is malformed.
     * @throws AccountLockedException if the account is currently locked.
     * @throws InvalidCredentialsException if email/password do not match.
     */
    public function login(LoginData $data): AuthenticatedUser
    {
        $this->validator->validate($data);

        $credentials = $this->users->findCredentialsByEmail($data->email);

        // Generic failure path for "email not found" — deliberately
        // indistinguishable from "wrong password" to prevent enumeration.
        if ($credentials === null) {
            $this->auditLogger->log('login.failed', null, $data->ipAddress, $data->userAgent, [
                'reason' => 'unknown_email',
            ]);

            throw new InvalidCredentialsException();
        }

        try {
            $this->bruteForceGuard->assertNotLocked($credentials['is_locked']);
        } catch (AccountLockedException $e) {
            $this->auditLogger->log('login.locked', $credentials['id'], $data->ipAddress, $data->userAgent);

            throw $e;
        }

        if (!$this->hasher->verify($data->password, $credentials['password_hash'])) {
            $this->bruteForceGuard->registerFailure(
                $credentials['id'],
                $data->email,
                $data->ipAddress,
                $data->userAgent,
            );

            $this->auditLogger->log('login.failed', $credentials['id'], $data->ipAddress, $data->userAgent, [
                'reason' => 'invalid_password',
            ]);

            throw new InvalidCredentialsException();
        }

        // Password correct: opportunistically upgrade the hash if the
        // configured Argon2id cost parameters have since changed.
        if ($this->hasher->needsRehash($credentials['password_hash'])) {
            $newHash = $this->hasher->hash($data->password);
            $this->users->updatePasswordHash($credentials['id'], $newHash);
            $this->auditLogger->log('password.rehashed', $credentials['id'], $data->ipAddress, $data->userAgent);
        }

        $this->bruteForceGuard->registerSuccess(
            $credentials['id'],
            $data->email,
            $data->ipAddress,
            $data->userAgent,
        );

        $this->users->markLastLogin($credentials['id']);

        $user = $this->users->findAuthenticatedUserById($credentials['id']);

        if ($user === null) {
            throw new \RuntimeException('Failed to load authenticated user after login.');
        }

        $this->session->login($user);

        $this->auditLogger->log('login.success', $credentials['id'], $data->ipAddress, $data->userAgent);

        return $user;
    }
}
