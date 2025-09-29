<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

class Comment
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
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS comments (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    submission_id INT UNSIGNED NOT NULL,
                    user_id INT UNSIGNED NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL);
        } else {
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    submission_id INTEGER NOT NULL REFERENCES submissions(id) ON DELETE CASCADE,
                    user_id INTEGER NOT NULL,
                    message TEXT NOT NULL,
                    created_at TEXT NOT NULL
                );
            SQL);
        }
    }

    public function create(array $attributes): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO comments (submission_id, user_id, message, created_at)
             VALUES (:submission_id, :user_id, :message, :created_at)'
        );
        $statement->bindValue(':submission_id', $attributes['submission_id'], PDO::PARAM_INT);
        $statement->bindValue(':user_id', $attributes['user_id'], PDO::PARAM_INT);
        $statement->bindValue(':message', $attributes['message']);
        $statement->bindValue(':created_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->execute();

        return $this->find((int) $this->db->lastInsertId());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM comments WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $comment = $statement->fetch(PDO::FETCH_ASSOC);
        return $comment ?: null;
    }

    public function allForSubmission(int $submissionId): array
    {
        $statement = $this->db->prepare('SELECT * FROM comments WHERE submission_id = :submission ORDER BY created_at ASC');
        $statement->bindValue(':submission', $submissionId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}