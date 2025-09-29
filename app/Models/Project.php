<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

class Project
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
                CREATE TABLE IF NOT EXISTS projects (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(160) NOT NULL,
                    description TEXT NULL,
                    status ENUM('planeacion','en_progreso','en_revision','finalizado') NOT NULL DEFAULT 'planeacion',
                    student_id INT UNSIGNED NOT NULL,
                    director_id INT UNSIGNED NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL);
        } else {
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS projects (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT NULL,
                    status TEXT NOT NULL DEFAULT 'planeacion',
                    student_id INTEGER NOT NULL,
                    director_id INTEGER NOT NULL,
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
            'INSERT INTO projects (title, description, status, student_id, director_id, created_at, updated_at)
             VALUES (:title, :description, :status, :student_id, :director_id, :created_at, :updated_at)'
        );

        $statement->bindValue(':title', $attributes['title']);
        $statement->bindValue(':description', $attributes['description'] ?? null);
        $statement->bindValue(':status', $attributes['status'] ?? 'planeacion');
        $statement->bindValue(':student_id', $attributes['student_id'], PDO::PARAM_INT);
        $statement->bindValue(':director_id', $attributes['director_id'], PDO::PARAM_INT);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);
        $statement->execute();

        return $this->find((int) $this->db->lastInsertId());
    }

    public function updateStatus(int $projectId, string $status): void
    {
        $statement = $this->db->prepare('UPDATE projects SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':status', $status);
        $statement->bindValue(':updated_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->bindValue(':id', $projectId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $project = $statement->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    public function findForUser(int $projectId, array $user): ?array
    {
        $query = $user['role'] === 'director'
            ? 'SELECT * FROM projects WHERE id = :id AND director_id = :user LIMIT 1'
            : 'SELECT * FROM projects WHERE id = :id AND student_id = :user LIMIT 1';

        $statement = $this->db->prepare($query);
        $statement->bindValue(':id', $projectId, PDO::PARAM_INT);
        $statement->bindValue(':user', $user['id'], PDO::PARAM_INT);
        $statement->execute();

        $project = $statement->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    public function allForUser(array $user): array
    {
        $statement = $user['role'] === 'director'
            ? $this->db->prepare('SELECT * FROM projects WHERE director_id = :user ORDER BY created_at DESC')
            : $this->db->prepare('SELECT * FROM projects WHERE student_id = :user ORDER BY created_at DESC');

        $statement->bindValue(':user', $user['id'], PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}