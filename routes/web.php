<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Router;

/**
 * Public routes. Controllers are resolved lazily through the factories passed in by
 * Application. Legacy URL handling lives in routes/legacy.php (docs/URL-MIGRATION.md).
 *
 * @return callable(Router, array<string, callable>): void
 */
return static function (Router $router, array $make): void {
    $router->get('/', static fn(Request $r) => $make['home']()->home($r));

    $router->get('/contact', static fn(Request $r) => $make['contact']()->show($r));
    $router->post('/contact', static fn(Request $r) => $make['contact']()->submit($r));

    $router->post('/hire', static fn(Request $r) => $make['hire']()->submit($r));

    $router->get('/api/prices', static fn(Request $r) => $make['api']()->prices($r));


    $router->get('/widgets/clock', static fn(Request $r) => $make['widgets']()->clock($r));
    $router->get('/widgets/calculator', static fn(Request $r) => $make['widgets']()->calculator($r));

    (require __DIR__ . '/legacy.php')($router);
};
