<?php

declare(strict_types=1);

/**
 * Applies database/migrations/*.sql in order, once each (tracked in `schema_migrations`).
 * With SEED_DEV_DATA=true (never in production) it also applies database/seeds/*.sql — fictional development data.
 * Usage:  php scripts/migrate.php          (reads DB_* from the environment or .env)
 * Exit codes: 0 ok · 1 failure.  Statements are split on ";" at end of line (no ";" inside literals).
 */

use Core\Helpers\Env;
use Core\Database\PdoConnection;

require dirname(__DIR__) . '/vendor/autoload.php';
Env::load(dirname(__DIR__) . '/.env');

$host = Env::get('DB_HOST', '');
if ($host === '' || Env::get('DB_DATABASE', '') === '') {
    fwrite(STDERR, "DB_HOST / DB_DATABASE are not configured.\n");
    exit(1);
}

$pdo = null;
$lastError = null;
for ($attempt = 1; $attempt <= 30; $attempt++) { // the database container may still be starting
    try {
        $pdo = PdoConnection::forMysql(
            $host,
            (string) Env::get('DB_DATABASE'),
            (string) Env::get('DB_USERNAME', ''),
            (string) Env::get('DB_PASSWORD', ''),
            Env::int('DB_PORT', 3306),
        )->connect();
        break;
    } catch (Throwable $e) {
        $lastError = $e->getMessage();
        sleep(1);
    }
}
if ($pdo === null) {
    fwrite(STDERR, 'Could not connect to the database: ' . ($lastError ?? 'unknown error') . "\n");
    exit(1);
}

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) NOT NULL PRIMARY KEY, applied_at DATETIME NOT NULL) ENGINE=InnoDB');
$done = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);

$files = glob(dirname(__DIR__) . '/database/migrations/*.sql') ?: [];
sort($files);
$applied = 0;

foreach ($files as $file) {
    $version = basename($file, '.sql');
    if (in_array($version, $done, true)) {
        continue;
    }
    $statements = preg_split('/;\s*\n/', (string) file_get_contents($file)) ?: [];
    try {
        foreach ($statements as $statement) {
            $statement = trim((string) preg_replace('/^\s*--.*$/m', '', $statement));
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
        $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (:v, NOW())')->execute(['v' => $version]);
    } catch (Throwable $e) {
        fwrite(STDERR, "Migration {$version} failed: " . $e->getMessage() . "\n");
        exit(1);
    }
    echo "applied {$version}\n";
    ++$applied;
}

echo $applied === 0 ? "database is up to date\n" : "done ({$applied} applied)\n";

if (Env::bool('SEED_DEV_DATA', false) && Env::get('APP_ENV', 'production') !== 'production') {
    $seeds = glob(dirname(__DIR__) . '/database/seeds/*.sql') ?: [];
    sort($seeds);
    foreach ($seeds as $file) {
        try {
            foreach (preg_split('/;\s*\n/', (string) file_get_contents($file)) ?: [] as $statement) {
                $statement = trim((string) preg_replace('/^\s*--.*$/m', '', $statement));
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }
        } catch (Throwable $e) {
            fwrite(STDERR, 'Seed ' . basename($file) . ' failed: ' . $e->getMessage() . "\n");
            exit(1);
        }
        echo 'seeded ' . basename($file) . "\n";
    }
}
