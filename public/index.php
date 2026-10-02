<?php

declare(strict_types=1);

use Core\Application;
use Core\Routing\Request;

$root = dirname(__DIR__);

$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Dependencies are not installed. Run: composer install\n");
}
require $autoload;

ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

$app = Application::boot($root);
date_default_timezone_set($app->config->string('app.timezone', 'UTC'));

$request = Request::fromGlobals();
$app->handle($request)->send($request->method !== 'HEAD');
