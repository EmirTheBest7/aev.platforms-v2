<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Models\JobRepository;

/**
 * Legacy (aev.platforms-master) public URLs. Single-hop 301s only, and only
 * for destinations that exist. Nothing is declared permanently gone (410) during the revival.
 * Extended as pages are migrated — keep in sync with docs/URL-MIGRATION.md.
 */
return static function (Router $router): void {
    $router->redirects([
        '/page/main' => ['/'],
        '/page/contact' => ['/contact'],
        '/page/careers' => ['/careers'],
        '/page/careers/list' => ['/careers'],
        '/page/careers/team' => ['/careers/team'],
    ]);

    // Original job URL: /page/careers/desc/?job_url=<slug>  →  /careers/<slug> (only for well-formed slugs)
    $router->get('/page/careers/desc', static function (Request $request): Response {
        $slug = $request->query['job_url'] ?? '';

        return Response::redirect(is_string($slug) && preg_match(JobRepository::SLUG_PATTERN, $slug) === 1 ? '/careers/' . $slug : '/careers', 301);
    });

    // Intentionally NO 410 (Gone) entries. The legacy platform (home/*), the terminal/tools, the experiments
    // under page/* and the event pages are being preserved and revived (see docs/PROJECT-VISION.md and
    // docs/HISTORICAL-FEATURES.md). Until a path is rebuilt it answers an honest 404; as each one returns
    // it gets a real route (or a 301 to its new location) here and in docs/URL-MIGRATION.md.
};
