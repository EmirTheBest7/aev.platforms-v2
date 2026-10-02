<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, string> $server
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $post = [],
        public readonly array $server = [],
    ) {}

    public static function fromGlobals(): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';

        /** @var array<string, string> $server */
        $server = array_filter($_SERVER, is_string(...));

        return new self(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), $path, $_GET, $_POST, $server);
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . ltrim((string) preg_replace('#/{2,}#', '/', $path), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function withPath(string $path): self
    {
        return new self($this->method, $path, $this->query, $this->post, $this->server);
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return $this->server[$key] ?? null;
    }

    public function contentLength(): int
    {
        return (int) ($this->server['CONTENT_LENGTH'] ?? 0);
    }

    public function input(string $key): string
    {
        $value = $this->post[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /** @return list<string> */
    public function inputList(string $key): array
    {
        $value = $this->post[$key] ?? [];

        return is_array($value) ? array_values(array_filter($value, is_string(...))) : [];
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') !== '' && strtolower($this->server['HTTPS']) !== 'off';
    }
}
