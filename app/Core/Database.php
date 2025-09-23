<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = Config::get('db.driver', 'sqlite');

        try {
            if ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    Config::get('db.mysql.host'),
                    Config::get('db.mysql.port'),
                    Config::get('db.mysql.database'),
                    Config::get('db.mysql.charset', 'utf8mb4')
                );

                self::$connection = new PDO(
                    $dsn,
                    Config::get('db.mysql.username'),
                    Config::get('db.mysql.password'),
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } else {
                $database = Config::get('db.sqlite.database');
                if (!$database) {
                    throw new RuntimeException('SQLite database path is not configured.');
                }

                $directory = dirname($database);
                if (!is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                self::$connection = new PDO(
                    'sqlite:' . $database,
                    options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                self::$connection->exec('PRAGMA foreign_keys = ON');
            }
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage(), 0, $exception);
        }

        return self::$connection;
    }
}
