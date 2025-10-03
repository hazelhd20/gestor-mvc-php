<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

class PasswordReset
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                token_hash VARCHAR(128) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_password_resets_user (user_id),
                UNIQUE KEY unique_password_resets_token (token_hash),
                CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token_hash TEXT NOT NULL UNIQUE,
                expires_at TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $delete = $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $delete->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $delete->execute();

        $statement = $this->db->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, :expires_at, :created_at)');
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':token_hash', $hash);
        $statement->bindValue(':expires_at', $expiresAt);
        $statement->bindValue(':created_at', $now);
        $statement->execute();

        return $token;
    }

    public function findValidByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $sql = 'SELECT pr.*, u.email, u.full_name FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token_hash = :hash AND pr.expires_at > :now LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':hash', $hash);
        $statement->bindValue(':now', $now);
        $statement->execute();

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public function invalidateByToken(string $token): void
    {
        $hash = hash('sha256', $token);
        $statement = $this->db->prepare('DELETE FROM password_resets WHERE token_hash = :hash');
        $statement->bindValue(':hash', $hash);
        $statement->execute();
    }

    public function deleteByUser(int $userId): void
    {
        $statement = $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
    }
}
