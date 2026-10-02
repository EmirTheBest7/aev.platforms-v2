<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Exact-match router with explicit redirect and "gone" tables for legacy URLs.
 * Handlers are `callable(Request): Response`.
 */
final class Router
{
    /** @var array<string, array<string, callable(Request): Response>> */
    private array $routes = [];

    /** @var array<string, array{0: string, 1: int}> */
    private array $redirects = [];

    /** @var array<string, true> */
    private array $gone = [];

    /** @param callable(Request): Response $handler */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
        $this->routes['HEAD'][$path] = $handler;
    }

    /** @param callable(Request): Response $handler */
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /** @param array<string, array{0: string, 1?: int}> $map old path => [new path, status] */
    public function redirects(array $map): void
    {
        foreach ($map as $from => $target) {
            $this->redirects[Request::normalizePath($from)] = [$target[0], $target[1] ?? 301];
        }
    }

    /** @param list<string> $paths path prefixes answered with 410 Gone */
    public function gone(array $paths): void
    {
        foreach ($paths as $path) {
            $this->gone[Request::normalizePath($path)] = true;
        }
    }

    public function dispatch(Request $request): Response
    {
        $path = $request->path;

        if (isset($this->redirects[$path])) {
            [$to, $status] = $this->redirects[$path];

            return Response::redirect($to, $status);
        }

        foreach (array_keys($this->gone) as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                throw new HttpException(410);
            }
        }

        $handler = $this->routes[$request->method][$path] ?? null;
        if ($handler !== null) {
            return $handler($request);
        }

        foreach ($this->routes as $method => $paths) {
            if ($method !== $request->method && isset($paths[$path])) {
                throw new HttpException(405);
            }
        }

        throw new HttpException(404);
    }

    /** @return list<string> */
    public function allowedMethods(string $path): array
    {
        $methods = [];
        foreach ($this->routes as $method => $paths) {
            if (isset($paths[$path])) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
