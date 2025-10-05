<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class Notification
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
            CREATE TABLE IF NOT EXISTS notifications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                type VARCHAR(100) NULL,
                title VARCHAR(200) NOT NULL,
                body TEXT NULL,
                action_url VARCHAR(255) NULL,
                data JSON NULL,
                read_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_notifications_user_read (user_id, read_at),
                CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
            $this->db->exec($sql);
            return;
        }

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            type TEXT NULL,
            title TEXT NOT NULL,
            body TEXT NULL,
            action_url TEXT NULL,
            data TEXT NULL,
            read_at TEXT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        SQL;
        $this->db->exec($sql);
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications (user_id, read_at)');
    }

    public function create(array $attributes): array
    {
        $userId = (int) ($attributes['user_id'] ?? 0);
        $title = trim((string) ($attributes['title'] ?? ''));

        if ($userId <= 0) {
            throw new RuntimeException('El destinatario de la notificación no es válido.');
        }

        if ($title === '') {
            throw new RuntimeException('El título de la notificación es obligatorio.');
        }

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $type = $this->sanitizeNullableString($attributes['type'] ?? null, 100);
        $body = $this->sanitizeNullableString($attributes['body'] ?? null);
        $actionUrl = $this->sanitizeNullableString($attributes['action_url'] ?? null, 255);
        $data = $this->encodeData($attributes['data'] ?? null);

        $statement = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, body, action_url, data, read_at, created_at, updated_at)'
            . ' VALUES (:user_id, :type, :title, :body, :action_url, :data, NULL, :created_at, :updated_at)'
        );

        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':type', $type);
        $statement->bindValue(':title', $title);
        $statement->bindValue(':body', $body);
        $statement->bindValue(':action_url', $actionUrl);
        $statement->bindValue(':data', $data);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible guardar la notificación.');
        }

        $id = (int) $this->db->lastInsertId();
        $notification = $this->find($id);

        if (!$notification) {
            throw new RuntimeException('No fue posible recuperar la notificación creada.');
        }

        return $notification;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForUser(int $userId, int $limit = 15, bool $onlyUnread = false): array
    {
        $userId = max(0, $userId);
        $limit = max(1, min($limit, 50));

        $sql = 'SELECT * FROM notifications WHERE user_id = :user_id';
        if ($onlyUnread) {
            $sql .= ' AND read_at IS NULL';
        }
        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT :limit';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(fn (array $row): array => $this->formatRow($row), $rows);
    }

    public function countUnreadForUser(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = :user_id AND read_at IS NULL');
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        $this->markManyAsRead([$notificationId], $userId);
    }

    /**
     * @param int[] $notificationIds
     */
    public function markManyAsRead(array $notificationIds, int $userId): void
    {
        $ids = array_values(array_filter(array_map(static fn ($id) => (int) $id, $notificationIds), static fn (int $id) => $id > 0));
        if ($ids === []) {
            return;
        }

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = sprintf(
            'UPDATE notifications SET read_at = ?, updated_at = ? WHERE user_id = ? AND id IN (%s)',
            $placeholders
        );

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $now);
        $statement->bindValue(2, $now);
        $statement->bindValue(3, $userId, PDO::PARAM_INT);

        $offset = 3;
        foreach ($ids as $index => $id) {
            $statement->bindValue($offset + $index + 1, $id, PDO::PARAM_INT);
        }

        $statement->execute();
    }

    public function markAllForUser(int $userId): void
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $statement = $this->db->prepare('UPDATE notifications SET read_at = :read_at, updated_at = :updated_at WHERE user_id = :user_id AND read_at IS NULL');
        $statement->bindValue(':read_at', $now);
        $statement->bindValue(':updated_at', $now);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function find(int $notificationId): ?array
    {
        if ($notificationId <= 0) {
            return null;
        }

        $statement = $this->db->prepare('SELECT * FROM notifications WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $notificationId, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->formatRow($row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatRow(array $row): array
    {
        $data = null;
        if (array_key_exists('data', $row) && $row['data'] !== null && $row['data'] !== '') {
            $decoded = json_decode((string) $row['data'], true);
            $data = is_array($decoded) ? $decoded : null;
        }

        $createdAt = $this->sanitizeNullableString($row['created_at'] ?? null);
        $readAt = $this->sanitizeNullableString($row['read_at'] ?? null);

        return [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'type' => $this->sanitizeNullableString($row['type'] ?? null),
            'title' => (string) ($row['title'] ?? ''),
            'body' => $this->sanitizeNullableString($row['body'] ?? null),
            'action_url' => $this->sanitizeNullableString($row['action_url'] ?? null),
            'data' => $data,
            'read_at' => $readAt,
            'created_at' => $createdAt,
            'updated_at' => $this->sanitizeNullableString($row['updated_at'] ?? null),
            'formatted_time' => $this->formatTimestamp($createdAt),
            'is_unread' => $readAt === null,
        ];
    }

    private function sanitizeNullableString(mixed $value, ?int $maxLength = null): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if ($maxLength !== null && $maxLength > 0) {
            if (function_exists('mb_substr')) {
                $value = mb_substr($value, 0, $maxLength);
            } else {
                $value = substr($value, 0, $maxLength);
            }
        }

        return $value;
    }

    private function encodeData(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function formatTimestamp(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            $date = new DateTimeImmutable($value);
        } catch (\Throwable) {
            return $value;
        }

        return $date->format('d/m/Y H:i');
    }
}
