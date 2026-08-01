<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

/**
 * Contract for recording and querying individual login attempts.
 *
 * This is operational data consumed by BruteForceGuard to make real-time
 * lock/unlock decisions — distinct from AuditLoggerInterface, which is a
 * general historical security log for humans.
 */
interface LoginAttemptRepositoryInterface
{
    public function recordAttempt(
        string $email,
        string $ipAddress,
        ?string $userAgent,
        bool $successful,
    ): void;

    /**
     * Counts failed attempts for the given email within the last
     * $windowSeconds seconds, used to decide whether to lock the account.
     */
    public function countRecentFailures(string $email, int $windowSeconds): int;

    /**
     * Clears failure history for an email, typically called after a
     * successful login.
     */
    public function clearFailures(string $email): void;
}
