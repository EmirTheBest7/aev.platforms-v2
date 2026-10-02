<?php

declare(strict_types=1);

namespace Core\Routing;

final class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    /** @var array<string, string> CSP directive => full value, replacing the default for that directive */
    private array $cspOverrides = [];

    public function __construct(
        private readonly string $body = '',
        public readonly int $status = 200,
        string $contentType = 'text/html; charset=UTF-8',
    ) {
        $this->headers['Content-Type'] = $contentType;
    }

    /** @param array<string, mixed> $data */
    public static function json(array $data, int $status = 200): self
    {
        return new self((string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR), $status, 'application/json; charset=UTF-8');
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

    /**
     * Narrowly widens the Content-Security-Policy for this response only. Every use must be
     * documented in docs/SECURITY.md.
     *
     * @param array<string, string> $directives e.g. ['frame-src' => "'self' https://www.intergram.xyz"]
     */
    public function withCsp(array $directives): self
    {
        $clone = clone $this;
        $clone->cspOverrides = $directives + $this->cspOverrides;

        return $clone;
    }

    /** @return array<string, string> */
    public function cspOverrides(): array
    {
        return $this->cspOverrides;
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
