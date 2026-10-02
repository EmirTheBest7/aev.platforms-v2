<?php

declare(strict_types=1);

namespace App\Services\Market;

use App\Support\Logger;

/**
 * Cached, provider-agnostic price snapshot for the ticker.
 *
 * - Visitors never trigger upstream calls directly: the snapshot is refreshed at most once per
 *   `ttl` seconds (single-flight via a lock file), so upstream load is independent of traffic.
 * - Providers are tried in order; each only needs to supply the symbols still missing.
 * - On failure the last known value is kept and reported as `stale`; beyond `maxStale` — or for
 *   an asset with no verified source (e.g. AEVT) — the state is `unavailable` and `usd` is null.
 *   Nothing is ever invented.
 */
final class PriceService
{
    private const FAILURE_BACKOFF = 30;

    /**
     * @param list<MarketDataProvider> $providers in priority order
     * @param array<string, array<string, string>> $symbols display symbol => [provider name => asset id]; empty = no source
     * @param (\Closure(): int)|null $clock
     */
    public function __construct(
        private readonly string $cacheFile,
        private readonly array $providers,
        private readonly array $symbols,
        private readonly int $ttl,
        private readonly int $maxStale,
        private readonly Logger $logger,
        private readonly ?\Closure $clock = null,
    ) {}

    /**
     * @return array{updated_at: string|null, stale: bool, prices: array<string, array{usd: float|null, state: string}>}
     */
    public function snapshot(): array
    {
        $now = $this->now();
        $cache = $this->read();

        if (($cache['refresh_after'] ?? 0) <= $now) {
            $cache = $this->refresh($cache);
        }

        return $this->present($cache, $this->now());
    }

    /**
     * @param array<string, mixed> $cache
     * @return array<string, mixed>
     */
    private function refresh(array $cache): array
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return $cache;
        }

        $lock = @fopen($this->cacheFile . '.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock !== false) {
                fclose($lock);
            }

            return $cache; // another request is refreshing; serve what we have
        }

        try {
            $cache = $this->read(); // may have been refreshed while we waited
            $now = $this->now();
            if (($cache['refresh_after'] ?? 0) > $now) {
                return $cache;
            }

            $obtained = [];
            foreach ($this->providers as $provider) {
                $wanted = [];
                foreach ($this->symbols as $symbol => $ids) {
                    if (!isset($obtained[$symbol]) && isset($ids[$provider->name()])) {
                        $wanted[$symbol] = $ids[$provider->name()];
                    }
                }
                if ($wanted === []) {
                    continue;
                }
                try {
                    $obtained += $provider->fetch($wanted);
                } catch (ProviderException $e) {
                    $this->logger->warning('market.provider_failed', ['provider' => $provider->name(), 'reason' => $e->getMessage()]);
                }
            }

            $entries = is_array($cache['entries'] ?? null) ? $cache['entries'] : [];
            foreach ($obtained as $symbol => $usd) {
                $entries[$symbol] = ['usd' => $usd, 'at' => $now];
            }

            $cache = [
                'entries' => $entries,
                'fetched_at' => $obtained !== [] ? $now : ($cache['fetched_at'] ?? null),
                'refresh_after' => $now + ($obtained !== [] ? $this->ttl : self::FAILURE_BACKOFF),
            ];
            $this->write($cache);

            return $cache;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @param array<string, mixed> $cache
     * @return array{updated_at: string|null, stale: bool, prices: array<string, array{usd: float|null, state: string}>}
     */
    private function present(array $cache, int $now): array
    {
        $entries = is_array($cache['entries'] ?? null) ? $cache['entries'] : [];
        $prices = [];
        $anyStale = false;

        foreach (array_keys($this->symbols) as $symbol) {
            $entry = $entries[$symbol] ?? null;
            $hasSource = $this->symbols[$symbol] !== [];
            $age = is_array($entry) && isset($entry['at']) ? $now - (int) $entry['at'] : null;

            if (!$hasSource || $age === null || $age > $this->maxStale || !isset($entry['usd'])) {
                $prices[$symbol] = ['usd' => null, 'state' => 'unavailable'];
                continue;
            }

            $state = $age > $this->ttl * 3 ? 'stale' : 'ok';
            $anyStale = $anyStale || $state === 'stale';
            $prices[$symbol] = ['usd' => (float) $entry['usd'], 'state' => $state];
        }

        $fetchedAt = $cache['fetched_at'] ?? null;

        return [
            'updated_at' => is_int($fetchedAt) ? gmdate('c', $fetchedAt) : null,
            'stale' => $anyStale,
            'prices' => $prices,
        ];
    }

    /** @return array<string, mixed> */
    private function read(): array
    {
        $raw = @file_get_contents($this->cacheFile);
        $data = $raw !== false ? json_decode($raw, true) : null;

        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $cache */
    private function write(array $cache): void
    {
        $tmp = $this->cacheFile . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, json_encode($cache), LOCK_EX) !== false) {
            @rename($tmp, $this->cacheFile);
        }
    }

    private function now(): int
    {
        return $this->clock !== null ? ($this->clock)() : time();
    }
}
