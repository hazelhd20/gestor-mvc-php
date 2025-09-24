<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class Project
{
    private PDO $db;

    private const STATUSES = ['planeacion', 'en_progreso', 'en_revision', 'finalizado'];

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureTable();
    }

    public static function statusCatalog(): array
    {
        return [
            'planeacion' => [
                'label' => 'Planeacion',
                'description' => 'Definicion de objetivos y alcance inicial.',
            ],
            'en_progreso' => [
                'label' => 'En progreso',
                'description' => 'Actividades y entregables en desarrollo.',
            ],
            'en_revision' => [
                'label' => 'En revision',
                'description' => 'Entregables enviados para comentarios.',
            ],
            'finalizado' => [
                'label' => 'Finalizado',
                'description' => 'Proyecto concluido y aprobado.',
            ],
        ];
    }

    private function ensureTable(): void
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS projects (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(160) NOT NULL,
                description TEXT NULL,
                status ENUM('planeacion','en_progreso','en_revision','finalizado') NOT NULL DEFAULT 'planeacion',
                due_date DATE NULL,
                director_id INT UNSIGNED NOT NULL,
                student_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_projects_director FOREIGN KEY (director_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_projects_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT NULL,
                status TEXT NOT NULL DEFAULT 'planeacion',
                due_date TEXT NULL,
                director_id INTEGER NOT NULL,
                student_id INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY (director_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function create(array $attributes): array
    {
        $status = $attributes['status'] ?? 'planeacion';
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'planeacion';
        }

        $description = trim((string) ($attributes['description'] ?? ''));
        $description = $description === '' ? null : $description;

        $dueDate = trim((string) ($attributes['due_date'] ?? ''));
        $dueDate = $dueDate === '' ? null : $dueDate;

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO projects (title, description, status, due_date, director_id, student_id, created_at, updated_at)
             VALUES (:title, :description, :status, :due_date, :director_id, :student_id, :created_at, :updated_at)'
        );

        $statement->bindValue(':title', $attributes['title']);
        $statement->bindValue(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $status);
        $statement->bindValue(':due_date', $dueDate, $dueDate === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':director_id', $attributes['director_id'], PDO::PARAM_INT);
        $statement->bindValue(':student_id', $attributes['student_id'], PDO::PARAM_INT);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible crear el proyecto.');
        }

        $id = (int) $this->db->lastInsertId();

        $project = $this->findWithRelations($id);
        if (!$project) {
            throw new RuntimeException('No fue posible recuperar el proyecto recien creado.');
        }

        return $project;
    }

    public function findWithRelations(int $id): ?array
    {
        $sql = <<<SQL
        SELECT p.*,
               s.full_name AS student_name,
               s.email AS student_email,
               s.matricula AS student_matricula,
               d.full_name AS director_name,
               d.email AS director_email,
               d.department AS director_department
        FROM projects p
        INNER JOIN users s ON s.id = p.student_id
        INNER JOIN users d ON d.id = p.director_id
        WHERE p.id = :id
        LIMIT 1
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $project = $statement->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    public function allForDirector(int $directorId): array
    {
        $sql = <<<SQL
        SELECT p.*, s.full_name AS student_name, s.email AS student_email
        FROM projects p
        INNER JOIN users s ON s.id = p.student_id
        WHERE p.director_id = :director
        ORDER BY p.created_at DESC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':director', $directorId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForStudent(int $studentId): array
    {
        $sql = <<<SQL
        SELECT p.*, d.full_name AS director_name, d.email AS director_email
        FROM projects p
        INNER JOIN users d ON d.id = p.director_id
        WHERE p.student_id = :student
        ORDER BY p.created_at DESC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':student', $studentId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}


