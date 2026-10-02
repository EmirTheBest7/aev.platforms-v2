<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Renders plain-PHP templates from app/Views inside a layout. Templates get an
 * `$e` escaper; every dynamic value must pass through it.
 */
final class View
{
    public function __construct(
        private readonly string $directory,
        private readonly string $appUrl,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta title, description, path, noindex, bodyClass
     */
    public function render(string $template, array $data = [], array $meta = [], string $layout = 'base'): string
    {
        $content = $this->capture($template, $data);

        return $this->capture('layouts/' . $layout, [
            'content' => $content,
            'meta' => $meta + ['title' => 'ΛΞV | Digital studio', 'description' => '', 'path' => '/', 'noindex' => false, 'bodyClass' => ''],
            'appUrl' => $this->appUrl,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = []): string
    {
        return $this->capture($template, $data);
    }

    /** @param array<string, mixed> $data */
    private function capture(string $template, array $data): string
    {
        if (preg_match('#^[a-z0-9_/-]+$#i', $template) !== 1) {
            throw new \InvalidArgumentException('Invalid template name.');
        }
        $file = $this->directory . '/' . $template . '.php';
        $e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $view = $this;

        return (static function () use ($file, $data, $e, $view): string {
            extract($data, EXTR_SKIP);
            ob_start();
            require $file;

            return (string) ob_get_clean();
        })();
    }
}
