<?php

declare(strict_types=1);

namespace Api\Internal;

use Api\Internal\Market\PriceService;
use Core\Routing\Request;
use Core\Routing\Response;

/** The internal JSON API used by the site's own pages: the ticker feed (GET /api/prices). */
final class PricesController
{
    public function __construct(private readonly PriceService $prices) {}

    /**
     * Ticker snapshot. Never errors because an upstream is down: the body always states, per symbol,
     * whether the price is `ok`, `stale` (last known) or `unavailable`.
     */
    public function prices(Request $request): Response
    {
        return Response::json($this->prices->snapshot())
            ->withHeader('Cache-Control', 'public, max-age=30')
            ->withHeader('X-Robots-Tag', 'noindex');
    }
}
