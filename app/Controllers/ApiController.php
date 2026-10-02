<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\Market\PriceService;

/** Same-origin JSON endpoints used by the page scripts. */
final class ApiController
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
