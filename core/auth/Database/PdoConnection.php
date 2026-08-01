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
 */
final class PdoConnection
{
    private static ?PDO $instance = null;

    public function __construct(
        private readonly string $dsn,
        private readonly string $username,
        private readonly string $password,
        private readonly array $options = [],
    ) {
    }

    public function connect(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        try {
            self::$instance = new PDO(
                $this->dsn,
                $this->username,
                $this->password,
                $this->options + $defaultOptions,
            );
        } catch (PDOException $e) {
            // Never leak DSN/credentials details to the caller.
            throw new RuntimeException('Database connection failed.', previous: $e);
        }

        return self::$instance;
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
}
