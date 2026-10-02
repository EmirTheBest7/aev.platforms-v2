<?php

declare(strict_types=1);

namespace Core\Helpers;

/**
 * Renders plain-PHP templates inside a layout. Templates get an `$e` escaper; every dynamic value must pass through it.
 *
 * Template names: `layouts/shell`, `partials/rail`, `errors/error` live in the shared root (resources/views);
 * `home::home`, `careers::show`, `auth::index` live in the view root registered for that page area
 * (website/<page>/views, core/auth/web/views).
 */
final class View
{
    /** @var array<string, mixed> variables of the page being rendered, inherited by its partials */
    private array $shared = [];

    /**
     * @param array<string, string> $roots area => directory; `shared` is the default root
     */
    public function __construct(
        private readonly array $roots,
        private readonly string $appUrl,
        private readonly string $publicDirectory = '',
    ) {}

    /**
     * Cache-busting URL for a first-party file under public/ (`/build/css/x.css` → `…?v=<mtime>`).
     * Assets are served with a long Cache-Control, so the URL must change with the file.
     */
    public function asset(string $path): string
    {
        $mtime = $this->publicDirectory !== '' ? @filemtime($this->publicDirectory . $path) : false;

        return $path . '?v=' . ($mtime !== false ? $mtime : 1);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta title, description, path, noindex, bodyClass
     */
    public function render(string $template, array $data = [], array $meta = [], string $layout = 'base'): string
    {
        $this->shared = $data;
        $content = $this->capture($template, $data);

        return $this->capture('layouts/' . $layout, $data + [
            'content' => $content,
            'meta' => $meta + ['title' => 'ΛΞV | Digital studio', 'description' => '', 'path' => '/', 'noindex' => false, 'bodyClass' => ''],
            'appUrl' => $this->appUrl,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = []): string
    {
        return $this->capture($template, $data + $this->shared);
    }

    /** @param array<string, mixed> $data */
    private function capture(string $template, array $data): string
    {
        if (preg_match('#^(?:([a-z]+)::)?([a-z0-9_]+(?:/[a-z0-9_-]+)*)$#iD', $template, $parts) !== 1 || str_contains($template, '..')) {
            throw new \InvalidArgumentException('Invalid template name.');
        }
        $directory = $this->roots[$parts[1] !== '' ? $parts[1] : 'shared'] ?? null;
        if ($directory === null) {
            throw new \InvalidArgumentException('Unknown template area.');
        }
        $file = $directory . '/' . $parts[2] . '.php';
        $e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $view = $this;

        return (static function () use ($file, $data, $e, $view): string {
            extract($data, EXTR_SKIP);
            $level = ob_get_level();
            ob_start();
            try {
                require $file;

                return (string) ob_get_clean();
            } catch (\Throwable $error) {
                while (ob_get_level() > $level) {
                    ob_end_clean(); // never leak a half-rendered template's buffer
                }
                throw $error;
            }
        })();
    }
}
