<?php

declare(strict_types=1);

namespace Core\Auth\Security;

use Core\Auth\Contracts\TokenGeneratorInterface;

/**
 * Generates cryptographically secure random tokens using random_bytes().
 */
final class TokenGenerator implements TokenGeneratorInterface
{
    public function generate(int $lengthBytes): string
    {
        if ($lengthBytes < 1) {
            throw new \InvalidArgumentException('Token length must be at least 1 byte.');
        }

        return bin2hex(random_bytes($lengthBytes));
    }
}
