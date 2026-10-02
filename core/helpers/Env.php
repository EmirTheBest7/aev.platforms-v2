<?php

declare(strict_types=1);

namespace Core\Helpers;

/**
 * Reads configuration from the process environment, optionally seeded from a
 * local `.env` file. Real environment variables always win over the file, so
 * production secrets injected by the platform cannot be shadowed.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) !== 1) {
                continue;
            }
            self::$values[$key] = self::parseValue($value);
        }
    }

    public static function set(string $key, string $value): void
    {
        self::$values[$key] = $value;
    }

    public static function reset(): void
    {
        self::$values = [];
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $real = getenv($key);
        if ($real !== false && $real !== '') {
            return $real;
        }

        $value = self::$values[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        return $value === null ? $default : in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return $value !== null && preg_match('/^-?\d+$/', $value) === 1 ? (int) $value : $default;
    }

    private static function parseValue(string $raw): string
    {
        $raw = trim($raw);
        if ($raw !== '' && ($raw[0] === '"' || $raw[0] === "'")) {
            $quote = $raw[0];
            $end = strpos($raw, $quote, 1);

            return $end === false ? substr($raw, 1) : substr($raw, 1, $end - 1);
        }

        // Unquoted: strip trailing inline comment ("value   # note").
        return trim((string) preg_replace('/\s+#.*$/', '', $raw));
    }
}
