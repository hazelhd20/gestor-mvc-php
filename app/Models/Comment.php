<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use PDOException;

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
                    submission_id INT UNSIGNED NULL,
                    milestone_id INT UNSIGNED NULL,
                    user_id INT UNSIGNED NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
                    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL);

            $hasMilestoneColumn = $this->db->query("SHOW COLUMNS FROM comments LIKE 'milestone_id'")->fetch(PDO::FETCH_ASSOC);
            if (!$hasMilestoneColumn) {
                $this->db->exec('ALTER TABLE comments ADD COLUMN milestone_id INT UNSIGNED NULL AFTER submission_id');
            }

            $submissionColumn = $this->db->query("SHOW COLUMNS FROM comments LIKE 'submission_id'")->fetch(PDO::FETCH_ASSOC);
            if ($submissionColumn && ($submissionColumn['Null'] ?? '') !== 'YES') {
                $this->db->exec('ALTER TABLE comments MODIFY submission_id INT UNSIGNED NULL');
            }
        } else {
            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    submission_id INTEGER NULL REFERENCES submissions(id) ON DELETE CASCADE,
                    milestone_id INTEGER NULL REFERENCES milestones(id) ON DELETE CASCADE,
                    user_id INTEGER NOT NULL,
                    message TEXT NOT NULL,
                    created_at TEXT NOT NULL
                );
            SQL);

            $columns = $this->db->query("PRAGMA table_info('comments')")->fetchAll(PDO::FETCH_ASSOC);
            $hasMilestoneColumn = false;
            $submissionNotNull = false;

            foreach ($columns as $column) {
                if (($column['name'] ?? '') === 'milestone_id') {
                    $hasMilestoneColumn = true;
                }
                if (($column['name'] ?? '') === 'submission_id' && (int) ($column['notnull'] ?? 0) === 1) {
                    $submissionNotNull = true;
                }
            }

            if (!$hasMilestoneColumn || $submissionNotNull) {
                $this->migrateSqliteTable();
            }
        }
    }

    private function migrateSqliteTable(): void
    {
        try {
            $this->db->exec('BEGIN TRANSACTION');

            $this->db->exec(<<<SQL
                CREATE TABLE IF NOT EXISTS comments_migration (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    submission_id INTEGER NULL REFERENCES submissions(id) ON DELETE CASCADE,
                    milestone_id INTEGER NULL REFERENCES milestones(id) ON DELETE CASCADE,
                    user_id INTEGER NOT NULL,
                    message TEXT NOT NULL,
                    created_at TEXT NOT NULL
                );
            SQL);

            $this->db->exec(<<<SQL
                INSERT INTO comments_migration (id, submission_id, milestone_id, user_id, message, created_at)
                SELECT id, submission_id, NULL AS milestone_id, user_id, message, created_at FROM comments;
            SQL);

            $this->db->exec('DROP TABLE comments');
            $this->db->exec('ALTER TABLE comments_migration RENAME TO comments');

            $this->db->exec('COMMIT');
        } catch (PDOException $exception) {
            $this->db->exec('ROLLBACK');
            throw $exception;
        }
    }

    public function create(array $attributes): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO comments (submission_id, milestone_id, user_id, message, created_at)'
            . ' VALUES (:submission_id, :milestone_id, :user_id, :message, :created_at)'
        );

        if (array_key_exists('submission_id', $attributes) && $attributes['submission_id'] !== null) {
            $statement->bindValue(':submission_id', $attributes['submission_id'], PDO::PARAM_INT);
        } else {
            $statement->bindValue(':submission_id', null, PDO::PARAM_NULL);
        }

        if (array_key_exists('milestone_id', $attributes) && $attributes['milestone_id'] !== null) {
            $statement->bindValue(':milestone_id', $attributes['milestone_id'], PDO::PARAM_INT);
        } else {
            $statement->bindValue(':milestone_id', null, PDO::PARAM_NULL);
        }

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

    public function allForMilestone(int $milestoneId): array
    {
        $statement = $this->db->prepare('SELECT * FROM comments WHERE milestone_id = :milestone ORDER BY created_at ASC');
        $statement->bindValue(':milestone', $milestoneId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
