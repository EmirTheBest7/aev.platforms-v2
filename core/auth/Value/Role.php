<?php

declare(strict_types=1);

namespace Core\Auth\Value;

/**
 * Immutable value object representing a role assigned to a user.
 *
 * No RBAC engine exists yet in ALIEV.IO — this VO exists so
 * AuthenticatedUser has a stable, typed shape ready for when roles are
 * actually assigned/loaded from the database.
 */
final readonly class Role
{
    public function __construct(
        public string $key,          // stable machine identifier, e.g. "admin"
        public ?string $label = null, // human readable label, e.g. "Administrator"
    ) {
    }

    public function equals(Role|string $other): bool
    {
        $otherKey = $other instanceof Role ? $other->key : $other;

        return $this->key === $otherKey;
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
