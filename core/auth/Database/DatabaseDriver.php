<?php

declare(strict_types=1);

namespace Core\Auth\Database;

use PDO;

/**
 * Identifies which SQL dialect a given PDO connection speaks.
 *
 * Repositories use this to branch the handful of genuinely
 * engine-specific SQL fragments (timestamp arithmetic, RETURNING clauses,
 * etc.) without needing a separate driver parameter threaded through every
 * constructor — the PDO connection already knows what it is.
 */
enum DatabaseDriver: string
{
    case MySQL = 'mysql';
    case PostgreSQL = 'pgsql';

    public static function fromPdo(PDO $pdo): self
    {
        /** @var string $driverName */
        $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return self::from($driverName);
    }
}
