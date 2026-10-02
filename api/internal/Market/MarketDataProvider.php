<?php

declare(strict_types=1);

namespace Api\Internal\Market;

/**
 * A source of USD spot prices. The ticker UI never talks to a provider directly;
 * `PriceService` asks providers in order, so replacing or adding one never touches the UI.
 */
interface MarketDataProvider
{
    /** Short stable name used in config and logs (e.g. "coingecko"). */
    public function name(): string;

    /**
     * @param array<string, string> $assets display symbol => provider-specific asset id
     * @return array<string, float> display symbol => USD price (only symbols that were obtained)
     * @throws ProviderException when the provider cannot answer at all
     */
    public function fetch(array $assets): array;
}
