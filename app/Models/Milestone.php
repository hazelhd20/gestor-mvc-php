<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

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
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS milestones (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    project_id INT UNSIGNED NOT NULL,
                    title VARCHAR(160) NOT NULL,
                    description TEXT NULL,
                    due_date DATE NULL,
                    status ENUM('pendiente','en_progreso','en_revision','completado') NOT NULL DEFAULT 'pendiente',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL);
        } else {
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS milestones (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                    title TEXT NOT NULL,
                    description TEXT NULL,
                    due_date TEXT NULL,
                    status TEXT NOT NULL DEFAULT 'pendiente',
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                );
            SQL);
        }
    }

    public function create(array $attributes): array
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO milestones (project_id, title, description, due_date, status, created_at, updated_at)
             VALUES (:project_id, :title, :description, :due_date, :status, :created_at, :updated_at)'
        );
        $statement->bindValue(':project_id', $attributes['project_id'], PDO::PARAM_INT);
        $statement->bindValue(':title', $attributes['title']);
        $statement->bindValue(':description', $attributes['description'] ?? null);
        $statement->bindValue(':due_date', $attributes['due_date'] ?? null);
        $statement->bindValue(':status', $attributes['status'] ?? 'pendiente');
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);
        $statement->execute();

        return $this->find((int) $this->db->lastInsertId());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM milestones WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $milestone = $statement->fetch(PDO::FETCH_ASSOC);
        return $milestone ?: null;
    }

    public function allForProject(int $projectId): array
    {
        $statement = $this->db->prepare('SELECT * FROM milestones WHERE project_id = :project ORDER BY due_date IS NULL, due_date');
        $statement->bindValue(':project', $projectId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $milestoneId, string $status): void
    {
        $statement = $this->db->prepare('UPDATE milestones SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':status', $status);
        $statement->bindValue(':updated_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->bindValue(':id', $milestoneId, PDO::PARAM_INT);
        $statement->execute();
    }
}