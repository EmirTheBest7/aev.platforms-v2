<?php

declare(strict_types=1);

namespace Core\Auth\Repository;

use Core\Auth\Contracts\LoginAttemptRepositoryInterface;
use PDO;

/**
 * Concrete MySQL/MariaDB implementation for tracking login attempts.
 *
 * This is operational data consumed by BruteForceGuard — separate from
 * the general AuditLogger, which is for human-facing history.
 *
 * Expected schema: see database/reference/auth_schema.sql
 */
final class LoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function recordAttempt(
        string $email,
        string $ipAddress,
        ?string $userAgent,
        bool $successful,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address, user_agent, successful, attempted_at)
             VALUES (:email, :ip, :agent, :successful, NOW())',
        );

        $stmt->execute([
            'email' => $email,
            'ip' => $ipAddress,
            'agent' => $userAgent,
            'successful' => $successful ? 1 : 0,
        ]);
    }

    public function countRecentFailures(string $email, int $windowSeconds): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = :email
               AND successful = 0
               AND attempted_at >= (NOW() - INTERVAL :window SECOND)',
        );

        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->bindValue('window', $windowSeconds, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function clearFailures(string $email): void
    {
        // We don't delete history (audit value); instead we rely on
        // countRecentFailures' time window to naturally "forget" failures
        // after a successful login is recorded. Nothing to clear explicitly.
        //
        // If a stricter reset-on-success policy is desired later, this is
        // the place to implement it (e.g. an explicit `reset_at` marker).
    }
}
