<?php

declare(strict_types=1);

use App\Controllers\ContactController;
use App\Controllers\PageController;
use App\Http\Router;

/**
 * Public routes. Legacy URL handling lives in routes/legacy.php and is
 * documented in docs/URL-MIGRATION.md / docs/ROUTES.md.
 *
 * @return callable(Router, PageController, callable(): ContactController): void
 */
return static function (Router $router, PageController $pages, callable $contact): void {
    $router->get('/', $pages->home(...));

    $router->get('/contact', static fn($request) => $contact()->show($request));
    $router->post('/contact', static fn($request) => $contact()->submit($request));

    (require __DIR__ . '/legacy.php')($router);
};
