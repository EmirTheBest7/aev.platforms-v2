<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(
        private readonly string $body = '',
        public readonly int $status = 200,
        string $contentType = 'text/html; charset=UTF-8',
    ) {
        $this->headers['Content-Type'] = $contentType;
    }

    public static function redirect(string $location, int $status = 303): self
    {
        $response = new self('', $status);
        $response->headers['Location'] = $location;

        return $response;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function send(bool $withBody = true): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . str_replace(["\r", "\n"], '', $value), true);
        }
        if ($withBody) {
            echo $this->body;
        }
    }
}
