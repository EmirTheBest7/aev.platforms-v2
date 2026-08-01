<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

/**
 * Contract for cryptographically secure token generation.
 *
 * Used for CSRF tokens today; usable for password-reset or remember-me
 * tokens later without changing the calling code.
 */
interface TokenGeneratorInterface
{
    /**
     * Returns a secure random token as a hex string.
     */
    public function generate(int $lengthBytes): string;
}
