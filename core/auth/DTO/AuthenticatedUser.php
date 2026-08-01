<?php

declare(strict_types=1);

namespace Core\Auth\DTO;

use Core\Auth\Value\Permission;
use Core\Auth\Value\Role;
use DateTimeImmutable;

/**
 * Safe, public representation of an authenticated user.
 *
 * This DTO NEVER carries the password hash. It is what services return to
 * callers (API layer, session storage, other apps) after successful auth.
 *
 * Roles/permissions default to empty arrays today because ALIEV.IO has no
 * RBAC engine yet — but the shape is stable so future integration does not
 * break existing consumers of this DTO.
 *
 * @param array<int, Role> $roles
 * @param array<int, Permission> $permissions
 */
final readonly class AuthenticatedUser
{
    /**
     * @param array<int, Role> $roles
     * @param array<int, Permission> $permissions
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $username,
        public ?DateTimeImmutable $lastLoginAt,
        public DateTimeImmutable $createdAt,
        public array $roles = [],
        public array $permissions = [],
    ) {
    }

    public function hasRole(Role|string $role): bool
    {
        foreach ($this->roles as $existing) {
            if ($existing->equals($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(Permission|string $permission): bool
    {
        foreach ($this->permissions as $existing) {
            if ($existing->equals($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convenience helper. Relies purely on role assignment ("admin" role),
     * not a hardcoded user id — stays valid once real RBAC is wired in.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'last_login_at' => $this->lastLoginAt?->format(DATE_ATOM),
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'roles' => array_map(static fn (Role $r): string => $r->key, $this->roles),
            'permissions' => array_map(static fn (Permission $p): string => $p->key, $this->permissions),
        ];
    }
}
