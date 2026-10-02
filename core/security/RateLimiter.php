<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * Fixed-window rate limiter persisted as small files (no database or Redis
 * needed on plain PHP hosting). Keys are hashed, so no IP address is stored.
 */
final class RateLimiter
{
    public function __construct(private readonly string $directory) {}

    /**
     * Records a hit and returns true while the caller is still within the limit.
     */
    public function hit(string $bucket, string $subject, int $maxAttempts, int $windowSeconds): bool
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0750, true) && !is_dir($this->directory)) {
            return true; // fail open: never lock real users out because storage is broken
        }

        $file = $this->directory . '/' . hash('sha256', $bucket . '|' . $subject) . '.json';
        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            return true;
        }

        try {
            flock($handle, LOCK_EX);
            $raw = stream_get_contents($handle);
            $state = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            $now = time();

            if (!is_array($state) || !isset($state['start'], $state['count']) || $now - (int) $state['start'] >= $windowSeconds) {
                $state = ['start' => $now, 'count' => 0];
            }
            $state['count'] = (int) $state['count'] + 1;

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) json_encode($state));
            fflush($handle);

            return $state['count'] <= $maxAttempts;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
