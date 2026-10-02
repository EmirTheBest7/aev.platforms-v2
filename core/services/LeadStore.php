<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Append-only JSON-lines store for contact requests (storage/leads/). Plain files
 * keep deployment portable; swap for a database repository later without
 * changing callers. Files are created 0600 and the directory 0750.
 */
final class LeadStore
{
    public function __construct(private readonly string $directory) {}

    /** @param array<string, mixed> $record */
    public function append(array $record): bool
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0750, true) && !is_dir($this->directory)) {
            return false;
        }

        $file = $this->directory . '/leads-' . gmdate('Y-m') . '.jsonl';
        $isNew = !file_exists($file);
        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";

        if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
            return false;
        }
        if ($isNew) {
            @chmod($file, 0600);
        }

        return true;
    }

    /** Collision-resistant, non-sequential, client-independent reference (e.g. AEV-7K3QH9XM2P). */
    public static function newReference(): string
    {
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ'; // Crockford base32, no I L O U
        $out = '';
        foreach (str_split(random_bytes(10)) as $byte) {
            $out .= $alphabet[ord($byte) & 31];
        }

        return 'AEV-' . $out;
    }
}
