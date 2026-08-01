<?php

declare(strict_types=1);

namespace Core\Auth\Security;

use Core\Auth\Config\AuthConfig;

/**
 * Wraps password_hash()/password_verify() with Argon2id as the algorithm.
 *
 * This is the single place that decides "how we hash passwords" — nothing
 * else in the module calls password_hash()/password_verify() directly.
 */
final class PasswordHasher
{
    public function __construct(private readonly AuthConfig $config)
    {
    }

    public function hash(string $plainPassword): string
    {
        $hash = password_hash(
            $plainPassword,
            PASSWORD_ARGON2ID,
            $this->config->argon2Options(),
        );

        if ($hash === false) {
            // password_hash() only returns false on catastrophic failure
            // (e.g. misconfigured environment) — never on bad input.
            throw new \RuntimeException('Password hashing failed.');
        }

        return $hash;
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    /**
     * Returns true if the stored hash was created with older/weaker
     * parameters and should be re-hashed with current settings.
     *
     * Call this after a successful verify() and, if true, re-hash the
     * plaintext password (still in memory at that point) and persist it.
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash(
            $hash,
            PASSWORD_ARGON2ID,
            $this->config->argon2Options(),
        );
    }
}
