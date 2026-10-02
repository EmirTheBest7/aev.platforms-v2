<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Routing\Request;

/**
 * Resolves the client address. X-Forwarded-For is honoured only when the
 * immediate peer is a configured trusted proxy; otherwise it is ignored
 * (a spoofed header must never let an attacker dodge rate limits).
 */
final class ClientIp
{
    /** @param list<string> $trustedProxies IPs or CIDR ranges */
    public function __construct(private readonly array $trustedProxies = []) {}

    public function resolve(Request $request): string
    {
        $peer = $request->server['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!$this->isTrusted($peer)) {
            return $peer;
        }

        $forwarded = $request->header('X-Forwarded-For');
        if ($forwarded === null) {
            return $peer;
        }

        // Walk right-to-left, skipping our own proxies; the first untrusted hop is the client.
        foreach (array_reverse(array_map('trim', explode(',', $forwarded))) as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP) === false) {
                return $peer;
            }
            if (!$this->isTrusted($candidate)) {
                return $candidate;
            }
        }

        return $peer;
    }

    public function isTrusted(string $ip): bool
    {
        foreach ($this->trustedProxies as $rule) {
            if (self::matches($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    private static function matches(string $ip, string $rule): bool
    {
        if (!str_contains($rule, '/')) {
            return $ip === $rule;
        }
        [$subnet, $bits] = explode('/', $rule, 2);
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin) || !ctype_digit($bits)) {
            return false;
        }
        $bits = (int) $bits;
        $bytes = intdiv($bits, 8);
        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }
        $rest = $bits % 8;
        if ($rest === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $rest)) & 0xFF;

        return (ord($ipBin[$bytes]) & $mask) === (ord($subnetBin[$bytes]) & $mask);
    }
}
