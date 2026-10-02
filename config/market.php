<?php

declare(strict_types=1);

use Core\Helpers\Env;

/**
 * Ticker market data. Symbol order is the legacy marquee order.
 * Each symbol maps provider name => provider asset id. An empty map means NO verified source:
 * the ticker shows an honest "unavailable" state for it (never an invented price).
 *
 * AEVT: the TON token described in the AEVT repository; no contract address, listing or price
 *       source exists anywhere in the legacy tree, its history or that repository → no source.
 * AEVD: the legacy code displayed the USDC price under the AEVD label. That historical mapping
 *       is preserved while MARKET_AEVD_FOLLOWS_USDC=true (default); the owner must confirm it.
 */
$aevdFollowsUsdc = Env::bool('MARKET_AEVD_FOLLOWS_USDC', true);

return [
    'ttl' => Env::int('MARKET_TTL_SECONDS', 60),
    'max_stale' => Env::int('MARKET_MAX_STALE_SECONDS', 21600),
    'providers' => array_values(array_filter(array_map('trim', explode(',', Env::get('MARKET_PROVIDERS', 'coingecko,coinbase') ?? '')))),
    'coingecko_key' => Env::get('COINGECKO_API_KEY', ''),
    'symbols' => [
        'BTC' => ['coingecko' => 'bitcoin', 'coinbase' => 'BTC'],
        'ETH' => ['coingecko' => 'ethereum', 'coinbase' => 'ETH'],
        'AEVT' => [],
        'AEVD' => $aevdFollowsUsdc ? ['coingecko' => 'usd-coin', 'coinbase' => 'USDC'] : [],
        'USDT' => ['coingecko' => 'tether', 'coinbase' => 'USDT'],
        'SOL' => ['coingecko' => 'solana', 'coinbase' => 'SOL'],
        'TON' => ['coingecko' => 'the-open-network', 'coinbase' => 'TON'],
        'DOT' => ['coingecko' => 'polkadot', 'coinbase' => 'DOT'],
        'SUI' => ['coingecko' => 'sui', 'coinbase' => 'SUI'],
        'APT' => ['coingecko' => 'aptos', 'coinbase' => 'APT'],
    ],
];
