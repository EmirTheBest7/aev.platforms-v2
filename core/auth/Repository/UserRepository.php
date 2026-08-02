<?php

declare(strict_types=1);

namespace Core\Auth\Repository;

use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\Database\DatabaseDriver;
use Core\Auth\DTO\AuthenticatedUser;
use DateTimeImmutable;
use PDO;

/**
 * User persistence, supporting both MySQL/MariaDB and PostgreSQL.
 *
 * All SQL for the `users` table lives here and nowhere else in the module.
 * Every query uses prepared statements — no string interpolation of input.
 *
 * The vast majority of queries here are standard ANSI SQL and work
 * identically on both engines. A small number of spots genuinely differ
 * between MySQL and PostgreSQL (auto-increment retrieval, timestamp-from-
 * unix-time conversion) — those branch on DatabaseDriver::fromPdo($pdo).
 *
 * Expected schema: see database/reference/auth_schema.sql (MySQL) or
 * database/reference/auth_schema.postgres.sql (PostgreSQL).
 */
final class UserRepository implements UserRepositoryInterface
{
    private readonly DatabaseDriver $driver;

    public function __construct(private readonly PDO $pdo)
    {
        $this->driver = DatabaseDriver::fromPdo($pdo);
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetchColumn();
    }

    public function existsByUsername(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(string $email, string $username, string $passwordHash): int
    {
        // PostgreSQL: lastInsertId() requires either a sequence name or a
        // RETURNING clause; RETURNING is the portable, driver-agnostic
        // approach and avoids depending on PostgreSQL's default sequence
        // naming convention (users_id_seq).
        if ($this->driver === DatabaseDriver::PostgreSQL) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (email, username, password_hash, created_at)
                 VALUES (:email, :username, :password_hash, NOW())
                 RETURNING id',
            );

            $stmt->execute([
                'email' => $email,
                'username' => $username,
                'password_hash' => $passwordHash,
            ]);

            return (int) $stmt->fetchColumn();
        }

        // MySQL/MariaDB: lastInsertId() works directly off the connection.
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, username, password_hash, created_at)
             VALUES (:email, :username, :password_hash, NOW())',
        );

        $stmt->execute([
            'email' => $email,
            'username' => $username,
            'password_hash' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findCredentialsByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, username, password_hash,
                    locked_until IS NOT NULL AND locked_until > NOW() AS is_locked
             FROM users
             WHERE email = :email
             LIMIT 1',
        );
        $stmt->execute(['email' => $email]);

        /** @var array{id:int, email:string, username:string, password_hash:string, is_locked:mixed}|false $row */
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'email' => (string) $row['email'],
            'username' => (string) $row['username'],
            'password_hash' => (string) $row['password_hash'],
            // MySQL returns 0/1 (int) for the computed boolean expression;
            // PostgreSQL returns a native boolean (true/false, or the
            // strings 't'/'f' depending on fetch mode). Casting via
            // filter_var handles both representations uniformly instead of
            // a plain (bool) cast, which would treat the string "f" as true.
            'is_locked' => filter_var($row['is_locked'], FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function findAuthenticatedUserById(int $userId): ?AuthenticatedUser
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, username, last_login_at, created_at
             FROM users
             WHERE id = :id
             LIMIT 1',
        );
        $stmt->execute(['id' => $userId]);

        /** @var array{id:int, email:string, username:string, last_login_at:?string, created_at:string}|false $row */
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        // Roles/permissions intentionally empty: no RBAC engine yet.
        // Wire in a RoleRepository/PermissionRepository lookup here later.
        return new AuthenticatedUser(
            id: (int) $row['id'],
            email: (string) $row['email'],
            username: (string) $row['username'],
            lastLoginAt: $row['last_login_at'] !== null
                ? new DateTimeImmutable($row['last_login_at'])
                : null,
            createdAt: new DateTimeImmutable($row['created_at']),
            roles: [],
            permissions: [],
        );
    }

    public function updatePasswordHash(int $userId, string $newHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $newHash, 'id' => $userId]);
    }

    public function markLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function lockAccount(int $userId, int $untilTimestamp): void
    {
        // FROM_UNIXTIME() is MySQL-only; PostgreSQL's equivalent is
        // TO_TIMESTAMP(). Both take a unix epoch integer and return a
        // native timestamp value.
        $expression = $this->driver === DatabaseDriver::PostgreSQL
            ? 'TO_TIMESTAMP(:until)'
            : 'FROM_UNIXTIME(:until)';

        $stmt = $this->pdo->prepare("UPDATE users SET locked_until = {$expression} WHERE id = :id");
        $stmt->execute(['until' => $untilTimestamp, 'id' => $userId]);
    }

    public function unlockAccount(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
