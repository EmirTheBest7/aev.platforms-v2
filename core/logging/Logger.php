<?php

declare(strict_types=1);

namespace Core\Logging;

/**
 * Minimal structured (JSON-lines) logger. Sensitive keys are redacted before
 * anything is written, so callers can pass whole context arrays safely.
 */
final class Logger
{
    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
    private const SENSITIVE = '/pass|secret|token|authorization|cookie|api[_-]?key|csrf|session|email|message/i';

    public function __construct(
        private readonly string $channel,   // 'file' | 'stderr' | 'null'
        private readonly string $directory,
        private readonly string $minLevel = 'info',
    ) {}

    /** @param array<string, mixed> $context */
    public function log(string $level, string $message, array $context = []): void
    {
        if ((self::LEVELS[$level] ?? 1) < (self::LEVELS[$this->minLevel] ?? 1) || $this->channel === 'null') {
            return;
        }

        $line = json_encode([
            'time' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => $this->redact($context),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) . "\n";

        if ($this->channel === 'stderr') {
            file_put_contents('php://stderr', $line);

            return;
        }

        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0750, true);
        }
        @file_put_contents($this->directory . '/app-' . gmdate('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function redact(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE, $key) === 1) {
                $out[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $out[$key] = $this->redact($value);
            } elseif ($value instanceof \Throwable) {
                $out[$key] = $value::class . ': ' . $this->scrub($value->getMessage());
            } else {
                $out[$key] = is_string($value) ? $this->scrub($value) : $value;
            }
        }

        return $out;
    }

    /** Removes bot-token-shaped strings and bearer values from free text. */
    private function scrub(string $text): string
    {
        $text = (string) preg_replace('/\b\d{6,}:[A-Za-z0-9_-]{20,}\b/', '[redacted-token]', $text);

        return (string) preg_replace('/(bot)[A-Za-z0-9:_-]{20,}/i', '$1[redacted]', $text);
    }
}
