<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Immutable, dot-addressable configuration built from the arrays returned by
 * `config/*.php` (file name = first key segment, e.g. `app.url`).
 */
final class Config
{
    /** @param array<string, mixed> $items */
    public function __construct(private readonly array $items) {}

    public static function fromDirectory(string $dir): self
    {
        $items = [];
        foreach (glob(rtrim($dir, '/') . '/*.php') ?: [] as $file) {
            $items[basename($file, '.php')] = require $file;
        }

        return new self($items);
    }

    /**
     * Returns a copy with `$overrides` deep-merged over the current items (arrays merge, scalars replace).
     *
     * @param array<string, mixed> $overrides
     */
    public function with(array $overrides): self
    {
        return new self(self::merge($this->items, $overrides));
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private static function merge(array $base, array $extra): array
    {
        foreach ($extra as $key => $value) {
            $base[$key] = is_array($value) && isset($base[$key]) && is_array($base[$key]) && !array_is_list($value)
                ? self::merge($base[$key], $value)
                : $value;
        }

        return $base;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $node = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return $default;
            }
            $node = $node[$segment];
        }

        return $node;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}
