<?php

declare(strict_types=1);

use App\Http\Router;

/**
 * Legacy (aev.platforms-master) public URLs. Single-hop 301s only, and only
 * for destinations that exist; removed products answer 410 Gone.
 * Extended as pages are migrated — keep in sync with docs/URL-MIGRATION.md.
 */
return static function (Router $router): void {
    $router->redirects([
        '/page/main' => ['/'],
        '/page/contact' => ['/contact'],
    ]);

    $router->gone([
        '/home',            // social-platform experiment: auth, timeline, messenger, wallet, studio, _api …
        '/page/maps',
        '/page/empty',
        '/page/design_store',
        '/page/material',
        '/page/universal',
        '/page/qirimcz',
    ]);
};
