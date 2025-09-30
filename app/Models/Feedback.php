<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class Feedback
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
            CREATE TABLE IF NOT EXISTS feedback (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                milestone_id INT UNSIGNED NOT NULL,
                author_id INT UNSIGNED NOT NULL,
                recipient_id INT UNSIGNED NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_feedback_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
                CONSTRAINT fk_feedback_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_feedback_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_feedback_milestone (milestone_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS feedback (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                milestone_id INTEGER NOT NULL,
                author_id INTEGER NOT NULL,
                recipient_id INTEGER NULL,
                content TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY(milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
                FOREIGN KEY(author_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY(recipient_id) REFERENCES users(id) ON DELETE SET NULL
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function create(array $attributes): array
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO feedback (milestone_id, author_id, recipient_id, content, created_at)
             VALUES (:milestone_id, :author_id, :recipient_id, :content, :created_at)'
        );

        $statement->bindValue(':milestone_id', $attributes['milestone_id'], PDO::PARAM_INT);
        $statement->bindValue(':author_id', $attributes['author_id'], PDO::PARAM_INT);
        $statement->bindValue(':recipient_id', $attributes['recipient_id'] ?? null, PDO::PARAM_INT);
        $statement->bindValue(':content', $attributes['content']);
        $statement->bindValue(':created_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible registrar el comentario.');
        }

        $id = (int) $this->db->lastInsertId();
        return $this->find($id) ?? [];
    }

    public function find(int $id): ?array
    {
        $sql = <<<SQL
        SELECT f.*, a.full_name AS author_name, a.role AS author_role,
               r.full_name AS recipient_name
        FROM feedback f
        INNER JOIN users a ON a.id = f.author_id
        LEFT JOIN users r ON r.id = f.recipient_id
        WHERE f.id = :id
        LIMIT 1
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $feedback = $statement->fetch(PDO::FETCH_ASSOC);
        return $feedback ?: null;
    }

    public function forMilestone(int $milestoneId): array
    {
        $sql = <<<SQL
        SELECT f.*, a.full_name AS author_name, a.role AS author_role,
               r.full_name AS recipient_name
        FROM feedback f
        INNER JOIN users a ON a.id = f.author_id
        LEFT JOIN users r ON r.id = f.recipient_id
        WHERE f.milestone_id = :milestone_id
        ORDER BY f.created_at DESC
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
        SELECT f.*, a.full_name AS author_name, a.role AS author_role,
               r.full_name AS recipient_name
        FROM feedback f
        INNER JOIN users a ON a.id = f.author_id
        LEFT JOIN users r ON r.id = f.recipient_id
        WHERE f.milestone_id IN ($placeholders)
        ORDER BY f.created_at DESC
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

    public function recentForUser(array $user, int $limit = 5): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);
        if ($userId === 0) {
            return [];
        }

        $column = $role === 'director' ? 'p.director_id' : 'p.student_id';

        $sql = <<<SQL
        SELECT f.*, a.full_name AS author_name, m.title AS milestone_title, p.title AS project_title
        FROM feedback f
        INNER JOIN milestones m ON m.id = f.milestone_id
        INNER JOIN projects p ON p.id = m.project_id
        INNER JOIN users a ON a.id = f.author_id
        WHERE $column = :id
        ORDER BY f.created_at DESC
        LIMIT :limit
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
