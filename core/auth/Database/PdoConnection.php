<?php

declare(strict_types=1);

namespace Core\Auth\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Single source of PDO connections for the auth module.
 *
 * No other class in core/auth should call `new PDO(...)` directly — this
 * guarantees consistent error mode, fetch mode, and charset everywhere,
 * and gives us one place to swap connection strategy later (e.g. pooling).
 *
 * Supports both MySQL/MariaDB and PostgreSQL via separate factory methods.
 * The repositories that consume the resulting PDO (UserRepository,
 * LoginAttemptRepository) branch their SQL dialect off of
 * DatabaseDriver::fromPdo($pdo) rather than assuming one engine.
 */
final class PdoConnection
{
    /**
     * Per-instance cache (NOT static/shared) so a process that opens both
     * a MySQL and a PostgreSQL connection — e.g. during a migration, or in
     * tests — never accidentally hands back the wrong engine's connection.
     * Each `new PdoConnection(...)` you construct gets its own single
     * cached PDO, reused across repeated connect() calls on that instance.
     */
    private ?PDO $instance = null;

    public function __construct(
        private readonly string $dsn,
        private readonly string $username,
        private readonly string $password,
        private readonly array $options = [],
    ) {
    }

    public function connect(): PDO
    {
        if ($this->instance instanceof PDO) {
            return $this->instance;
        }

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        try {
            $this->instance = new PDO(
                $this->dsn,
                $this->username,
                $this->password,
                $this->options + $defaultOptions,
            );
        } catch (PDOException $e) {
            // Never leak DSN/credentials details to the caller.
            throw new RuntimeException('Database connection failed.', previous: $e);
        }

        return $this->instance;
    }

    /**
     * Convenience factory for MySQL/MariaDB using discrete parameters.
     */
    public static function forMysql(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port = 3306,
        string $charset = 'utf8mb4',
    ): self {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset,
        );

        return new self($dsn, $username, $password);
    }

    /**
     * Convenience factory for PostgreSQL using discrete parameters.
     */
    public static function forPostgres(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port = 5432,
    ): self {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $host,
            $port,
            $database,
        );

        return new self($dsn, $username, $password);
    }
}
