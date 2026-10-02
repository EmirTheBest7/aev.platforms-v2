<?php

declare(strict_types=1);

namespace Core\Routing;

/**
 * Exact-match router with an explicit redirect table for legacy URLs, plus single-segment
 * `{name}` parameters (`/careers/{slug}`); exact routes always win over parameterised ones.
 * Handlers are `callable(Request, array<string, string>): Response` (the parameter array is empty for exact routes).
 */
final class Router
{
    /** @var array<string, array<string, callable(Request, array<string, string>): Response>> */
    private array $routes = [];

    /** @var array<string, list<array{0: string, 1: callable(Request, array<string, string>): Response}>> method => [regex, handler] */
    private array $patterns = [];

    /** @var array<string, array{0: string, 1: int}> */
    private array $redirects = [];

    /** @param callable(Request, array<string, string>): Response $handler */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
        $this->add('HEAD', $path, $handler);
    }

    /** @param callable(Request, array<string, string>): Response $handler */
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /** @param callable(Request, array<string, string>): Response $handler */
    private function add(string $method, string $path, callable $handler): void
    {
        if (!str_contains($path, '{')) {
            $this->routes[$method][$path] = $handler;

            return;
        }
        $regex = preg_replace('#\\\{([a-z]+)\\\}#', '(?P<$1>[^/]+)', preg_quote($path, '#'));
        $this->patterns[$method][] = ['#^' . $regex . '$#', $handler];
    }

    /** @return array<string, string>|null parameters when `$path` matches a parameterised route of `$method` */
    private function match(string $method, string $path, ?callable &$handler): ?array
    {
        foreach ($this->patterns[$method] ?? [] as [$regex, $candidate]) {
            if (preg_match($regex, $path, $m) === 1) {
                $handler = $candidate;

                return array_filter($m, is_string(...), ARRAY_FILTER_USE_KEY);
            }
        }

        return null;
    }

    /** @param array<string, array{0: string, 1?: int}> $map old path => [new path, status] */
    public function redirects(array $map): void
    {
        foreach ($map as $from => $target) {
            $this->redirects[Request::normalizePath($from)] = [$target[0], $target[1] ?? 301];
        }
    }

    public function dispatch(Request $request): Response
    {
        $path = $request->path;

        if (isset($this->redirects[$path])) {
            [$to, $status] = $this->redirects[$path];

            return Response::redirect($to, $status);
        }

        $handler = $this->routes[$request->method][$path] ?? null;
        if ($handler !== null) {
            return $handler($request, []);
        }

        $handler = null;
        $params = $this->match($request->method, $path, $handler);
        if ($params !== null && $handler !== null) {
            return $handler($request, $params);
        }

        if ($this->allowedMethods($path) !== []) {
            throw new HttpException(405);
        }

        throw new HttpException(404);
    }

    /** @return list<string> */
    public function allowedMethods(string $path): array
    {
        $methods = [];
        $unused = null;
        foreach (array_unique([...array_keys($this->routes), ...array_keys($this->patterns)]) as $method) {
            if (isset($this->routes[$method][$path]) || $this->match($method, $path, $unused) !== null) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
