<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class Deliverable
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
            CREATE TABLE IF NOT EXISTS deliverables (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                milestone_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type VARCHAR(120) NULL,
                file_size BIGINT NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_deliverables_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
                CONSTRAINT fk_deliverables_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_deliverables_milestone (milestone_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS deliverables (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                milestone_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                file_path TEXT NOT NULL,
                original_name TEXT NOT NULL,
                mime_type TEXT NULL,
                file_size INTEGER NULL,
                notes TEXT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY(milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function create(array $attributes): array
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO deliverables (milestone_id, user_id, file_path, original_name, mime_type, file_size, notes, created_at)
             VALUES (:milestone_id, :user_id, :file_path, :original_name, :mime_type, :file_size, :notes, :created_at)'
        );

        $statement->bindValue(':milestone_id', $attributes['milestone_id'], PDO::PARAM_INT);
        $statement->bindValue(':user_id', $attributes['user_id'], PDO::PARAM_INT);
        $statement->bindValue(':file_path', $attributes['file_path']);
        $statement->bindValue(':original_name', $attributes['original_name']);
        $statement->bindValue(':mime_type', $attributes['mime_type'] ?? null);
        $statement->bindValue(':file_size', $attributes['file_size'] ?? null);
        $statement->bindValue(':notes', $attributes['notes'] ?? null);
        $statement->bindValue(':created_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible registrar el entregable.');
        }

        $id = (int) $this->db->lastInsertId();
        return $this->find($id) ?? [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM deliverables WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $deliverable = $statement->fetch(PDO::FETCH_ASSOC);
        return $deliverable ?: null;
    }

    public function forMilestone(int $milestoneId): array
    {
        $sql = <<<SQL
        SELECT d.*, u.full_name AS author_name, u.role AS author_role
        FROM deliverables d
        INNER JOIN users u ON u.id = d.user_id
        WHERE d.milestone_id = :milestone_id
        ORDER BY d.created_at DESC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':milestone_id', $milestoneId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function forMilestones(array $milestoneIds): array
    {
        if ($milestoneIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($milestoneIds), '?'));
        $sql = <<<SQL
        SELECT d.*, u.full_name AS author_name, u.role AS author_role
        FROM deliverables d
        INNER JOIN users u ON u.id = d.user_id
        WHERE d.milestone_id IN ($placeholders)
        ORDER BY d.created_at DESC
        SQL;

        $statement = $this->db->prepare($sql);
        foreach ($milestoneIds as $index => $id) {
            $statement->bindValue($index + 1, (int) $id, PDO::PARAM_INT);
        }
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $grouped = [];
        foreach ($rows as $row) {
            $milestoneId = (int) $row['milestone_id'];
            if (!isset($grouped[$milestoneId])) {
                $grouped[$milestoneId] = [];
            }
            $grouped[$milestoneId][] = $row;
        }

        return $grouped;
    }
}
