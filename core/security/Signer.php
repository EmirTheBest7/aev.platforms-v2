<?php

declare(strict_types=1);

namespace Core\Security;

/** HMAC signer used for stateless tokens (form timestamps) and IP pseudonymisation. */
final class Signer
{
    public function __construct(private readonly string $key)
    {
        if (strlen($key) < 32) {
            throw new \LogicException('APP_KEY must be at least 32 characters.');
        }
    }

    public function sign(string $payload): string
    {
        return $payload . '.' . hash_hmac('sha256', $payload, $this->key);
    }

    /** Returns the payload when the signature is valid, otherwise null. */
    public function verify(string $token): ?string
    {
        $pos = strrpos($token, '.');
        if ($pos === false) {
            return null;
        }
        $payload = substr($token, 0, $pos);

        return hash_equals($this->sign($payload), $token) ? $payload : null;
    }

    /** One-way, keyed pseudonym — lets us correlate abuse without storing addresses. */
    public function pseudonym(string $value): string
    {
        return substr(hash_hmac('sha256', 'pseudonym|' . $value, $this->key), 0, 16);
    }
}
