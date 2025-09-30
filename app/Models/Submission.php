<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

class Submission
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
                CREATE TABLE IF NOT EXISTS submissions (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    milestone_id INT UNSIGNED NOT NULL,
                    user_id INT UNSIGNED NOT NULL,
                    notes TEXT NULL,
                    attachment_path VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL);
        } else {
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS submissions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    milestone_id INTEGER NOT NULL REFERENCES milestones(id) ON DELETE CASCADE,
                    user_id INTEGER NOT NULL,
                    notes TEXT NULL,
                    attachment_path TEXT NULL,
                    created_at TEXT NOT NULL
                );
            SQL);
        }
    }

    public function create(array $attributes): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO submissions (milestone_id, user_id, notes, attachment_path, created_at)
             VALUES (:milestone_id, :user_id, :notes, :attachment_path, :created_at)'
        );
        $statement->bindValue(':milestone_id', $attributes['milestone_id'], PDO::PARAM_INT);
        $statement->bindValue(':user_id', $attributes['user_id'], PDO::PARAM_INT);
        $statement->bindValue(':notes', $attributes['notes'] ?? null);
        $statement->bindValue(':attachment_path', $attributes['attachment_path'] ?? null);
        $statement->bindValue(':created_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->execute();

        return $this->find((int) $this->db->lastInsertId());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM submissions WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $submission = $statement->fetch(PDO::FETCH_ASSOC);
        return $submission ?: null;
    }

    public function latestForMilestone(int $milestoneId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM submissions WHERE milestone_id = :milestone ORDER BY created_at DESC LIMIT 1');
        $statement->bindValue(':milestone', $milestoneId, PDO::PARAM_INT);
        $statement->execute();

        $submission = $statement->fetch(PDO::FETCH_ASSOC);
        return $submission ?: null;
    }

    public function allForMilestone(int $milestoneId): array
    {
        $statement = $this->db->prepare('SELECT * FROM submissions WHERE milestone_id = :milestone ORDER BY created_at DESC');
        $statement->bindValue(':milestone', $milestoneId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}