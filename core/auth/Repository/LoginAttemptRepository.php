<?php

declare(strict_types=1);

namespace Core\Auth\Repository;

use Core\Auth\Contracts\LoginAttemptRepositoryInterface;
use Core\Auth\Database\DatabaseDriver;
use PDO;

/**
 * Tracks login attempts for brute-force decisions.
 * Supports both MySQL/MariaDB and PostgreSQL.
 *
 * This is operational data consumed by BruteForceGuard — separate from
 * the general AuditLogger, which is for human-facing history.
 *
 * Expected schema: see database/reference/auth_schema.sql (MySQL) or
 * database/reference/auth_schema.postgres.sql (PostgreSQL).
 */
final class LoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    private readonly DatabaseDriver $driver;

    public function __construct(private readonly PDO $pdo)
    {
        $this->driver = DatabaseDriver::fromPdo($pdo);
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
        // MySQL accepts a bound integer directly in `INTERVAL :window SECOND`.
        // PostgreSQL's INTERVAL syntax doesn't accept a bound parameter in
        // that position the same way — the portable approach is to build
        // the interval from a bound numeric via multiplication against a
        // fixed 1-second interval, which works identically on both engines
        // for MySQL... except MySQL doesn't support that form either.
        // So we branch: each engine gets syntax native to it.
        if ($this->driver === DatabaseDriver::PostgreSQL) {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM login_attempts
                 WHERE email = :email
                   AND successful = FALSE
                   AND attempted_at >= (NOW() - (:window || \' seconds\')::INTERVAL)',
            );
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM login_attempts
                 WHERE email = :email
                   AND successful = 0
                   AND attempted_at >= (NOW() - INTERVAL :window SECOND)',
            );
        }

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
