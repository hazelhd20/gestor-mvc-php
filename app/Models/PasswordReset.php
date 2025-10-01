<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

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
                token_hash VARCHAR(255) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_password_resets_user_id (user_id),
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
        $this->deleteByUserId($userId);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');
        $createdAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, :expires_at, :created_at)');
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':token_hash', $tokenHash);
        $statement->bindValue(':expires_at', $expiresAt);
        $statement->bindValue(':created_at', $createdAt);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible generar el token de recuperacion.');
        }

        return $token;
    }

    public function findValidToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $statement = $this->db->prepare('SELECT * FROM password_resets WHERE token_hash = :token_hash LIMIT 1');
        $statement->bindValue(':token_hash', $tokenHash);
        $statement->execute();

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            return null;
        }

        $expiresAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $record['expires_at']);
        $now = new DateTimeImmutable('now');

        if (!$expiresAt || $expiresAt < $now) {
            $this->deleteToken((int) $record['id']);
            return null;
        }

        return $record;
    }

    public function deleteToken(int $tokenId): void
    {
        $statement = $this->db->prepare('DELETE FROM password_resets WHERE id = :id');
        $statement->bindValue(':id', $tokenId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function deleteByUserId(int $userId): void
    {
        $statement = $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
    }
}
