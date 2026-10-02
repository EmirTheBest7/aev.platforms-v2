<?php

declare(strict_types=1);

namespace Api\Internal\Market;

use Core\Services\Http\HttpClient;
use Core\Services\Http\HttpClientException;

/**
 * CoinGecko "Demo / Keyless" API (docs.coingecko.com, verified 2026-10-02): `GET /simple/price`,
 * server-side only (browser calls fail CORS), prices cached upstream ~60 s. An optional Demo key
 * (`x-cg-demo-api-key` header) raises the limits; without it limits are per shared IP.
 * Terms-of-service/attribution rules could not be read (page bot-blocked) — see docs/LICENSES.md.
 */
final class CoinGeckoProvider implements MarketDataProvider
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $apiKey = '',
        private readonly string $baseUrl = 'https://api.coingecko.com/api/v3',
        private readonly int $timeoutSeconds = 4,
    ) {}

    public function name(): string
    {
        return 'coingecko';
    }

    public function fetch(array $assets): array
    {
        if ($assets === []) {
            return [];
        }

        $url = rtrim($this->baseUrl, '/') . '/simple/price?vs_currencies=usd&ids=' . rawurlencode(implode(',', array_values($assets)));
        $headers = ['Accept' => 'application/json'];
        if ($this->apiKey !== '') {
            $headers['x-cg-demo-api-key'] = $this->apiKey;
        }

        try {
            $response = $this->http->get($url, $headers, $this->timeoutSeconds);
        } catch (HttpClientException $e) {
            throw new ProviderException('coingecko unreachable', 0, $e);
        }

        if ($response['status'] !== 200) {
            throw new ProviderException('coingecko http ' . $response['status']);
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new ProviderException('coingecko returned invalid JSON');
        }

        $prices = [];
        foreach ($assets as $symbol => $id) {
            $usd = $data[$id]['usd'] ?? null;
            if ((is_int($usd) || is_float($usd)) && $usd > 0) {
                $prices[$symbol] = (float) $usd;
            }
        }

        if ($prices === []) {
            throw new ProviderException('coingecko returned no usable prices');
        }

        return $prices;
    }
}
