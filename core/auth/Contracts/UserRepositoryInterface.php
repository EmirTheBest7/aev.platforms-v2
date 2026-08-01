<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

use Core\Auth\DTO\AuthenticatedUser;

/**
 * Contract for user persistence.
 *
 * Services depend on this interface, never on the concrete repository,
 * so the storage engine can be swapped and the module can be unit tested
 * with a fake/in-memory implementation.
 */
interface UserRepositoryInterface
{
    public function existsByEmail(string $email): bool;

    public function existsByUsername(string $username): bool;

    /**
     * Creates a new user record and returns the generated user id.
     */
    public function create(string $email, string $username, string $passwordHash): int;

    /**
     * Fetches raw credential data needed to attempt a login.
     * Returns null if no active user matches the email.
     *
     * @return array{id:int, email:string, username:string, password_hash:string, is_locked:bool}|null
     */
    public function findCredentialsByEmail(string $email): ?array;

    /**
     * Builds the public-facing AuthenticatedUser DTO for a given user id.
     */
    public function findAuthenticatedUserById(int $userId): ?AuthenticatedUser;

    public function updatePasswordHash(int $userId, string $newHash): void;

    public function markLastLogin(int $userId): void;

    public function lockAccount(int $userId, int $untilTimestamp): void;

    public function unlockAccount(int $userId): void;
}
