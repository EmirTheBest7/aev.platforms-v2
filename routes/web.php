<?php

declare(strict_types=1);

use Core\Routing\Request;
use Core\Routing\Router;

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

    $router->get('/careers', static fn(Request $r) => $make['careers']()->index($r));
    $router->get('/careers/team', static fn(Request $r) => $make['careers']()->team($r));
    $router->get('/careers/{slug}', static fn(Request $r, array $p) => $make['careers']()->show($r, $p));

    $router->get('/home/auth', static fn(Request $r) => $make['auth']()->show($r));
    $router->post('/home/auth/login', static fn(Request $r) => $make['auth']()->login($r));
    $router->post('/home/auth/register', static fn(Request $r) => $make['auth']()->register($r));
    $router->post('/home/auth/logout', static fn(Request $r) => $make['auth']()->logout($r));
    $router->get('/home/auth/reset', static fn(Request $r) => $make['auth']()->reset($r));

    $router->get('/downloads', static fn(Request $r) => $make['downloads']()->index($r));

    // Retained `_api` (the static UI/Docs bundle under /home/_api/ is served by Apache; these are its PHP endpoints).
    $router->get('/home/_api/', static fn(Request $r) => $make['terminal']()->root($r));
    $router->get('/home/_api/hello', static fn(Request $r) => $make['terminal']()->hello($r));
    $router->get('/home/_api/info', static fn(Request $r) => $make['terminal']()->info($r));
    $router->get('/home/_api/me', static fn(Request $r) => $make['terminal']()->me($r));
    $router->get('/home/_api/updates', static fn(Request $r) => $make['terminal']()->updates($r));
    $router->get('/home/_api/csrf', static fn(Request $r) => $make['terminal']()->csrf($r));
    $router->get('/home/_api/tools/domain', static fn(Request $r) => $make['terminal']()->domain($r));
    $router->post('/home/_api/valentine/yes', static fn(Request $r) => $make['terminal']()->valentineYes($r));

    $router->get('/api/prices', static fn(Request $r) => $make['prices']()->prices($r));


    $router->get('/widgets/clock', static fn(Request $r) => $make['widgets']()->clock($r));
    $router->get('/widgets/calculator', static fn(Request $r) => $make['widgets']()->calculator($r));

    (require __DIR__ . '/legacy.php')($router);
};
