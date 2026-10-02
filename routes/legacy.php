<?php

declare(strict_types=1);

use App\Http\Router;

/**
 * Legacy (aev.platforms-master) public URLs. Single-hop 301s only, and only
 * for destinations that exist. Nothing is declared permanently gone (410) during the revival.
 * Extended as pages are migrated — keep in sync with docs/URL-MIGRATION.md.
 */
return static function (Router $router): void {
    $router->redirects([
        '/page/main' => ['/'],
        '/page/contact' => ['/contact'],
    ]);

    // Intentionally NO 410 (Gone) entries. The legacy platform (home/*), the terminal/tools, the experiments
    // under page/* and the event pages are being preserved and revived (see docs/PROJECT-VISION.md and
    // docs/HISTORICAL-FEATURES.md). Until a path is rebuilt it answers an honest 404; as each one returns
    // it gets a real route (or a 301 to its new location) here and in docs/URL-MIGRATION.md.
};
