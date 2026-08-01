<?php

declare(strict_types=1);

namespace Core\Auth\Value;

/**
 * Immutable value object representing a single permission granted to a user.
 *
 * Same rationale as Role: no permission engine exists yet, but the shape
 * is stable and ready for future integration (e.g. "posts.delete").
 */
final readonly class Permission
{
    public function __construct(
        public string $key,           // e.g. "posts.delete"
        public ?string $label = null, // human readable label
    ) {
    }

    public function equals(Permission|string $other): bool
    {
        $otherKey = $other instanceof Permission ? $other->key : $other;

        return $this->key === $otherKey;
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
