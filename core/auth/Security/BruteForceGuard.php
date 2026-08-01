<?php

declare(strict_types=1);

namespace Core\Auth\Security;

use Core\Auth\Config\AuthConfig;
use Core\Auth\Contracts\LoginAttemptRepositoryInterface;
use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\Exception\AccountLockedException;

/**
 * Encapsulates brute-force protection policy.
 *
 * Reads recent failure history from LoginAttemptRepositoryInterface and
 * decides whether an account should be locked, using thresholds from
 * AuthConfig. Locking itself is persisted via UserRepositoryInterface
 * so `is_locked` can be checked cheaply on every login attempt.
 */
final class BruteForceGuard
{
    public function __construct(
        private readonly AuthConfig $config,
        private readonly LoginAttemptRepositoryInterface $attempts,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Call before attempting password verification.
     *
     * @throws AccountLockedException if the account is currently locked.
     */
    public function assertNotLocked(bool $isLockedFlag): void
    {
        if ($isLockedFlag) {
            throw new AccountLockedException($this->config->lockoutDurationSeconds);
        }
    }

    /**
     * Call after a failed password verification. Records the failure and
     * locks the account if the threshold within the configured window is
     * exceeded.
     */
    public function registerFailure(int $userId, string $email, string $ipAddress, ?string $userAgent): void
    {
        $this->attempts->recordAttempt($email, $ipAddress, $userAgent, successful: false);

        $recentFailures = $this->attempts->countRecentFailures(
            $email,
            $this->config->bruteForceWindowSeconds,
        );

        if ($recentFailures >= $this->config->maxFailedAttempts) {
            $this->users->lockAccount(
                $userId,
                time() + $this->config->lockoutDurationSeconds,
            );
        }
    }

    /**
     * Call after a successful login to reset the account's lock state.
     */
    public function registerSuccess(int $userId, string $email, string $ipAddress, ?string $userAgent): void
    {
        $this->attempts->recordAttempt($email, $ipAddress, $userAgent, successful: true);
        $this->attempts->clearFailures($email);
        $this->users->unlockAccount($userId);
    }
}
