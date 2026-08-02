<?php

declare(strict_types=1);

/**
 * Bootstrap for apps/account/* test pages.
 *
 * FOR TESTING/DEVELOPMENT PURPOSES ONLY.
 * ---------------------------------------------------------------------
 * This wires core/auth/ to a database connection using plain constants
 * below. In a real application this configuration would come from
 * environment variables (.env, getenv(), etc.) — it's hardcoded here
 * only to keep this test app dependency-free and easy to run locally.
 * Do NOT deploy this file as-is to a public-facing server.
 * ---------------------------------------------------------------------
 */

// ---------------------------------------------------------------------
// 1. Autoload core/auth/ classes.
//    Replace with Composer's vendor/autoload.php once Composer is set up.
// ---------------------------------------------------------------------
require_once __DIR__ . '/../../core/autoload.php';

use Core\Auth\AuthFacade;
use Core\Auth\Config\AuthConfig;
use Core\Auth\Database\PdoConnection;

// ---------------------------------------------------------------------
// 2. Database configuration.
//    Toggle DB_DRIVER between 'mysql' and 'pgsql' to test either engine.
// ---------------------------------------------------------------------
const DB_DRIVER   = 'mysql'; // 'mysql' or 'pgsql'
const DB_HOST     = '127.0.0.1';
const DB_PORT     = null;    // null = use each driver's default port
const DB_DATABASE = 'aliev_io';
const DB_USERNAME = 'aliev_user';
const DB_PASSWORD = 'secret';

try {
    $connection = match (DB_DRIVER) {
        'pgsql' => PdoConnection::forPostgres(
            host: DB_HOST,
            database: DB_DATABASE,
            username: DB_USERNAME,
            password: DB_PASSWORD,
            port: DB_PORT ?? 5432,
        ),
        default => PdoConnection::forMysql(
            host: DB_HOST,
            database: DB_DATABASE,
            username: DB_USERNAME,
            password: DB_PASSWORD,
            port: DB_PORT ?? 3306,
        ),
    };

    $pdo = $connection->connect();
} catch (\Throwable $e) {
    http_response_code(500);
    exit(
        '<p style="font-family: sans-serif; color: #b91c1c;">'
        . 'Database connection failed. Check the constants at the top of '
        . '<code>apps/account/_bootstrap.php</code> and make sure the '
        . 'schema from <code>database/reference/</code> has been loaded.'
        . '<br><br><strong>Detail:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES)
        . '</p>'
    );
}

// ---------------------------------------------------------------------
// 3. Auth configuration.
//    sessionCookieSecure is disabled here for local HTTP testing only —
//    never disable this in staging/production (see docs/AUTH_MODULE.md §12).
// ---------------------------------------------------------------------
$authConfig = new AuthConfig(
    sessionCookieSecure: false,
);

$auth = AuthFacade::create($pdo, $authConfig);

// Start the session immediately so CSRF tokens and auth state are
// available to every page that includes this bootstrap.
$auth->session()->start();
