<?php

declare(strict_types=1);

namespace Core\Auth\Repository;

use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\DTO\AuthenticatedUser;
use DateTimeImmutable;
use PDO;

/**
 * Concrete MySQL/MariaDB implementation of user persistence.
 *
 * All SQL for the `users` table lives here and nowhere else in the module.
 * Every query uses prepared statements — no string interpolation of input.
 *
 * Expected schema: see database/reference/auth_schema.sql
 */
final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
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

        /** @var array{id:int, email:string, username:string, password_hash:string, is_locked:int}|false $row */
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'email' => (string) $row['email'],
            'username' => (string) $row['username'],
            'password_hash' => (string) $row['password_hash'],
            'is_locked' => (bool) $row['is_locked'],
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
        $stmt = $this->pdo->prepare('UPDATE users SET locked_until = FROM_UNIXTIME(:until) WHERE id = :id');
        $stmt->execute(['until' => $untilTimestamp, 'id' => $userId]);
    }

    public function unlockAccount(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
