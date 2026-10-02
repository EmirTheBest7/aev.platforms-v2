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
