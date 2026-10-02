<?php

declare(strict_types=1);

namespace Api\Internal\Market;

use Core\Services\Http\HttpClient;
use Core\Services\Http\HttpClientException;

/**
 * Coinbase Data API — prices: `GET /v2/prices/{BASE}-USD/spot` (docs.cdp.coinbase.com
 * "Data API - Prices", scope "N/A": public, no authentication). One request per asset, so it
 * is the fallback behind CoinGecko. Rate limits for this endpoint could not be read.
 */
final class CoinbaseProvider implements MarketDataProvider
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl = 'https://api.coinbase.com',
        private readonly int $timeoutSeconds = 3,
    ) {}

    public function name(): string
    {
        return 'coinbase';
    }

    public function fetch(array $assets): array
    {
        $prices = [];
        $failures = 0;

        foreach ($assets as $symbol => $base) {
            if (preg_match('/^[A-Z0-9]{2,10}$/', $base) !== 1) {
                continue;
            }
            try {
                $response = $this->http->get(rtrim($this->baseUrl, '/') . '/v2/prices/' . $base . '-USD/spot', ['Accept' => 'application/json'], $this->timeoutSeconds);
            } catch (HttpClientException) {
                ++$failures;
                continue;
            }
            $amount = $response['status'] === 200 ? (json_decode($response['body'], true)['data']['amount'] ?? null) : null;
            if (is_string($amount) && is_numeric($amount) && (float) $amount > 0) {
                $prices[$symbol] = (float) $amount;
            } else {
                ++$failures;
            }
        }

        if ($prices === [] && $assets !== []) {
            throw new ProviderException('coinbase returned no usable prices (' . $failures . ' failed)');
        }

        return $prices;
    }
}
