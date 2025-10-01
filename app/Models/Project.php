<?php

namespace App\Models;

use App\Core\Database;
use DateInterval;
use DateTimeImmutable;
use PDO;
use RuntimeException;

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
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS projects (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(180) NOT NULL,
                description TEXT NULL,
                student_id INT UNSIGNED NOT NULL,
                director_id INT UNSIGNED NOT NULL,
                status ENUM('planificado','en_progreso','en_riesgo','completado') NOT NULL DEFAULT 'planificado',
                due_date DATE NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_projects_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_projects_director FOREIGN KEY (director_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_projects_student (student_id),
                INDEX idx_projects_director (director_id),
                INDEX idx_projects_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT NULL,
                student_id INTEGER NOT NULL,
                director_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT 'planificado',
                due_date TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY(director_id) REFERENCES users(id) ON DELETE CASCADE
            );
            SQL;
        }

        $this->db->exec($sql);
    }

    public function create(array $attributes): array
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO projects (title, description, student_id, director_id, status, due_date, created_at, updated_at)
             VALUES (:title, :description, :student_id, :director_id, :status, :due_date, :created_at, :updated_at)'
        );

        $statement->bindValue(':title', $attributes['title']);
        $statement->bindValue(':description', $attributes['description'] ?? null);
        $statement->bindValue(':student_id', $attributes['student_id'], PDO::PARAM_INT);
        $statement->bindValue(':director_id', $attributes['director_id'], PDO::PARAM_INT);
        $statement->bindValue(':status', $attributes['status'] ?? 'planificado');
        $statement->bindValue(':due_date', $attributes['due_date'] ?? null);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible registrar el proyecto.');
        }

        $id = (int) $this->db->lastInsertId();

        return $this->find($id) ?? [];
    }

    public function studentHasActiveProject(int $studentId, ?int $excludeProjectId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM projects WHERE student_id = :student_id AND status IN ('planificado','en_progreso','en_riesgo')";
        if ($excludeProjectId !== null) {
            $sql .= " AND id != :exclude_id";
        }

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        if ($excludeProjectId !== null) {
            $statement->bindValue(':exclude_id', $excludeProjectId, PDO::PARAM_INT);
        }
        $statement->execute();

        $count = (int) ($statement->fetchColumn() ?: 0);
        return $count > 0;
    }

    public function updateStatus(int $projectId, string $status): void
    {
        $allowed = ['planificado', 'en_progreso', 'en_riesgo', 'completado'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Estado de proyecto no valido.');
        }

        $statement = $this->db->prepare('UPDATE projects SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':status', $status);
        $statement->bindValue(':updated_at', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $statement->bindValue(':id', $projectId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function allForUser(array $user): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);

        if ($userId === 0) {
            return [];
        }

        $query = $role === 'director'
            ? 'WHERE p.director_id = :id'
            : 'WHERE p.student_id = :id';

        $sql = <<<SQL
        SELECT
            p.*,
            s.full_name AS student_name,
            s.email AS student_email,
            d.full_name AS director_name,
            d.email AS director_email,
            COALESCE((SELECT COUNT(*) FROM milestones m WHERE m.project_id = p.id), 0) AS milestones_total,
            COALESCE((SELECT COUNT(*) FROM milestones m WHERE m.project_id = p.id AND m.status = 'aprobado'), 0) AS milestones_done
        FROM projects p
        INNER JOIN users s ON s.id = p.student_id
        INNER JOIN users d ON d.id = p.director_id
        $query
        ORDER BY COALESCE(p.due_date, p.created_at) ASC
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $sql = <<<SQL
        SELECT
            p.*,
            s.full_name AS student_name,
            s.email AS student_email,
            d.full_name AS director_name,
            d.email AS director_email
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

    public function statsForUser(array $user): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);

        if ($userId === 0) {
            return ['total' => 0, 'active' => 0, 'completed' => 0, 'due_soon' => 0];
        }

        $column = $role === 'director' ? 'director_id' : 'student_id';

        $statement = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE $column = :id");
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        $total = (int) ($statement->fetchColumn() ?: 0);

        $statement = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE $column = :id AND status IN ('planificado','en_progreso','en_riesgo')");
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        $active = (int) ($statement->fetchColumn() ?: 0);

        $statement = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE $column = :id AND status = 'completado'");
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        $completed = (int) ($statement->fetchColumn() ?: 0);

        $soonDate = (new DateTimeImmutable('now'))->add(new DateInterval('P7D'))->format('Y-m-d');
        $today = (new DateTimeImmutable('now'))->format('Y-m-d');

        $sqlDueSoon = "SELECT COUNT(*) FROM projects WHERE $column = :id AND status IN ('planificado','en_progreso','en_riesgo') AND due_date IS NOT NULL AND due_date BETWEEN :today AND :soon";
        $statement = $this->db->prepare($sqlDueSoon);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':today', $today);
        $statement->bindValue(':soon', $soonDate);
        $statement->execute();
        $dueSoon = (int) ($statement->fetchColumn() ?: 0);

        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed,
            'due_soon' => $dueSoon,
        ];
    }

    public function upcomingMilestones(array $user, int $limit = 5): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);
        if ($userId === 0) {
            return [];
        }

        $column = $role === 'director' ? 'p.director_id' : 'p.student_id';

        $sql = <<<SQL
        SELECT m.*, p.title AS project_title
        FROM milestones m
        INNER JOIN projects p ON p.id = m.project_id
        WHERE $column = :id AND m.status IN ('pendiente','en_progreso','en_revision')
        ORDER BY COALESCE(m.due_date, m.created_at) ASC
        LIMIT :limit
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function boardColumns(array $user): array
    {
        $role = $user['role'] ?? 'estudiante';
        $userId = (int) ($user['id'] ?? 0);
        if ($userId === 0) {
            return [];
        }

        $column = $role === 'director' ? 'p.director_id' : 'p.student_id';

        $sql = <<<SQL
        SELECT
            m.id, m.title, m.status, m.due_date, m.project_id,
            m.position, m.updated_at,
            p.title AS project_title
        FROM milestones m
        INNER JOIN projects p ON p.id = m.project_id
        WHERE $column = :id
        ORDER BY m.status, COALESCE(m.position, m.created_at)
        SQL;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();

        $milestones = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $columns = [
            'pendiente' => [],
            'en_progreso' => [],
            'en_revision' => [],
            'aprobado' => [],
        ];

        foreach ($milestones as $milestone) {
            $status = $milestone['status'];
            if (!isset($columns[$status])) {
                $columns[$status] = [];
            }
            $columns[$status][] = $milestone;
        }

        return $columns;
    }
}
