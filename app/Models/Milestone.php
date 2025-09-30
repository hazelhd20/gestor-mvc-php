<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class Milestone
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
            CREATE TABLE IF NOT EXISTS milestones (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id INT UNSIGNED NOT NULL,
                title VARCHAR(180) NOT NULL,
                description TEXT NULL,
                due_date DATE NULL,
                status ENUM('pendiente','en_progreso','en_revision','aprobado') NOT NULL DEFAULT 'pendiente',
                position INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_milestones_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                INDEX idx_milestones_project (project_id),
                INDEX idx_milestones_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS milestones (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT NULL,
                due_date TEXT NULL,
                status TEXT NOT NULL DEFAULT 'pendiente',
                position INTEGER NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function create(array $attributes): array
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO milestones (project_id, title, description, due_date, status, position, created_at, updated_at)
             VALUES (:project_id, :title, :description, :due_date, :status, :position, :created_at, :updated_at)'
        );

        $statement->bindValue(':project_id', $attributes['project_id'], PDO::PARAM_INT);
        $statement->bindValue(':title', $attributes['title']);
        $statement->bindValue(':description', $attributes['description'] ?? null);
        $statement->bindValue(':due_date', $attributes['due_date'] ?? null);
        $statement->bindValue(':status', $attributes['status'] ?? 'pendiente');
        $statement->bindValue(':position', $attributes['position'] ?? null);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible crear el hito.');
        }

        $id = (int) $this->db->lastInsertId();
        return $this->find($id) ?? [];
    }

    public function updateStatus(int $milestoneId, string $status): void
    {
        $allowed = ['pendiente', 'en_progreso', 'en_revision', 'aprobado'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Estado de hito no valido.');
        }

        $statement = $this->db->prepare('UPDATE milestones SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':status', $status);
        $statement->bindValue(':updated_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->bindValue(':id', $milestoneId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM milestones WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $milestone = $statement->fetch(PDO::FETCH_ASSOC);
        return $milestone ?: null;
    }

    public function forProject(int $projectId): array
    {
        $sql = <<<SQL
        SELECT m.*,
            COALESCE((SELECT COUNT(*) FROM deliverables d WHERE d.milestone_id = m.id), 0) AS deliverables_count,
            COALESCE((SELECT COUNT(*) FROM feedback f WHERE f.milestone_id = m.id), 0) AS feedback_count,
            (SELECT MAX(created_at) FROM deliverables d WHERE d.milestone_id = m.id) AS last_submission_at
        FROM milestones m
        WHERE m.project_id = :project_id
        ORDER BY COALESCE(m.position, m.created_at) ASC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function forUser(array $user): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);
        if ($userId === 0) {
            return [];
        }

        $column = $role === 'director' ? 'p.director_id' : 'p.student_id';

        $sql = <<<SQL
        SELECT m.*, p.title AS project_title, p.due_date AS project_due_date
        FROM milestones m
        INNER JOIN projects p ON p.id = m.project_id
        WHERE $column = :id
        ORDER BY COALESCE(m.due_date, m.created_at) ASC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
