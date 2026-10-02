<?php

declare(strict_types=1);

namespace Api\Terminal;

/**
 * Backs the terminal's `domain` tool: "does this name have DNS records?". Only a DNS query for a validated
 * host name is made — no connection to the target, and the records themselves are never returned.
 */
final class DomainLookup
{
    /** @var \Closure(string): bool */
    private readonly \Closure $resolver;

    /** @param (\Closure(string): bool)|null $resolver replaceable in tests */
    public function __construct(?\Closure $resolver = null)
    {
        $this->resolver = $resolver ?? static function (string $name): bool {
            $records = @dns_get_record($name . '.', DNS_A | DNS_AAAA | DNS_NS | DNS_MX);

            return is_array($records) && $records !== [];
        };
    }

    /** Lower-cased host name when `$input` is a plausible public domain (letters, digits, hyphens, dots), else null. */
    public static function normalize(string $input): ?string
    {
        $name = strtolower(trim($input));
        if ($name === '' || strlen($name) > 253 || !str_contains($name, '.')) {
            return null;
        }
        $labels = explode('.', $name);
        foreach ($labels as $label) {
            if (preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)$/D', $label) !== 1) {
                return null;
            }
        }
        $tld = end($labels);

        return preg_match('/^([a-z]{2,24}|xn--[a-z0-9-]{2,59})$/D', $tld) === 1 ? $name : null;
    }

    public function hasRecords(string $name): bool
    {
        return ($this->resolver)($name);
    }
}
