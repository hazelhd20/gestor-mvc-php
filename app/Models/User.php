<?php

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

class User
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
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(120) NOT NULL,
                email VARCHAR(120) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('estudiante','director') NOT NULL DEFAULT 'estudiante',
                matricula VARCHAR(30) NULL,
                department VARCHAR(120) NULL,
                avatar_path VARCHAR(255) NULL,
                email_verified_at TIMESTAMP NULL DEFAULT NULL,
                verification_token VARCHAR(128) NULL,
                verification_token_expires_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SQL;
        } else {
            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                matricula TEXT NULL,
                department TEXT NULL,
                avatar_path TEXT NULL,
                email_verified_at TEXT NULL,
                verification_token TEXT NULL,
                verification_token_expires_at TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            SQL;
        }

        $this->db->exec($sql);
        $this->ensureAvatarColumn($driver);
        $this->ensureVerificationColumns($driver);
    }

    private function ensureAvatarColumn(string $driver): void
    {
        if ($driver === 'mysql') {
            $statement = $this->db->prepare("SHOW COLUMNS FROM users LIKE 'avatar_path'");
            $statement->execute();
            $exists = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$exists) {
                $this->db->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER department");
            }
            return;
        }

        $columnsStatement = $this->db->query('PRAGMA table_info(users)');
        $columns = $columnsStatement ? $columnsStatement->fetchAll(PDO::FETCH_ASSOC) : [];
        $names = array_map(static fn ($column) => $column['name'] ?? '', $columns);

        if (!in_array('avatar_path', $names, true)) {
            $this->db->exec('ALTER TABLE users ADD COLUMN avatar_path TEXT NULL');
        }
    }

    private function ensureVerificationColumns(string $driver): void
    {
        if ($driver === 'mysql') {
            $columns = [
                'email_verified_at' => "ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER avatar_path",
                'verification_token' => "ALTER TABLE users ADD COLUMN verification_token VARCHAR(128) NULL AFTER email_verified_at",
                'verification_token_expires_at' => "ALTER TABLE users ADD COLUMN verification_token_expires_at TIMESTAMP NULL DEFAULT NULL AFTER verification_token",
            ];

            foreach ($columns as $column => $alterSql) {
                $check = $this->db->prepare('SHOW COLUMNS FROM users LIKE :column');
                $check->bindValue(':column', $column);
                $check->execute();
                if (!$check->fetch(PDO::FETCH_ASSOC)) {
                    $this->db->exec($alterSql);
                }
            }

            return;
        }

        $columnsStatement = $this->db->query('PRAGMA table_info(users)');
        $columns = $columnsStatement ? $columnsStatement->fetchAll(PDO::FETCH_ASSOC) : [];
        $names = array_map(static fn ($column) => $column['name'] ?? '', $columns);

        if (!in_array('email_verified_at', $names, true)) {
            $this->db->exec('ALTER TABLE users ADD COLUMN email_verified_at TEXT NULL');
        }

        if (!in_array('verification_token', $names, true)) {
            $this->db->exec('ALTER TABLE users ADD COLUMN verification_token TEXT NULL');
        }

        if (!in_array('verification_token_expires_at', $names, true)) {
            $this->db->exec('ALTER TABLE users ADD COLUMN verification_token_expires_at TEXT NULL');
        }
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->bindValue(':email', strtolower($email));
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    public function create(array $attributes): array
    {
        $existing = $this->findByEmail($attributes['email']);
        if ($existing) {
            throw new RuntimeException('El correo ya esta registrado.');
        }

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare(
            'INSERT INTO users (full_name, email, password, role, matricula, department, created_at, updated_at)
             VALUES (:full_name, :email, :password, :role, :matricula, :department, :created_at, :updated_at)'
        );

        $statement->bindValue(':full_name', $attributes['full_name']);
        $statement->bindValue(':email', strtolower($attributes['email']));
        $statement->bindValue(':password', password_hash($attributes['password'], PASSWORD_DEFAULT));
        $statement->bindValue(':role', $attributes['role']);
        $statement->bindValue(':matricula', $attributes['matricula']);
        $statement->bindValue(':department', $attributes['department']);
        $statement->bindValue(':created_at', $now);
        $statement->bindValue(':updated_at', $now);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible registrar al usuario.');
        }

        $id = (int) $this->db->lastInsertId();

        return $this->findById($id);
    }

    public function updatePassword(int $userId, string $newPassword): void
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':password', password_hash($newPassword, PASSWORD_DEFAULT));
        $statement->bindValue(':updated_at', $now);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible actualizar la contrasena.');
        }
    }

    public function updateAvatar(int $userId, ?string $avatarPath): void
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('UPDATE users SET avatar_path = :avatar_path, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':avatar_path', $avatarPath);
        $statement->bindValue(':updated_at', $now);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);

        if (!$statement->execute()) {
            throw new RuntimeException('No fue posible actualizar la foto de perfil.');
        }
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function generateEmailVerificationToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+48 hours'))->format('Y-m-d H:i:s');
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('UPDATE users SET verification_token = :token, verification_token_expires_at = :expires_at, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':token', $hash);
        $statement->bindValue(':expires_at', $expiresAt);
        $statement->bindValue(':updated_at', $now);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();

        return $token;
    }

    public function findByVerificationToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('SELECT * FROM users WHERE verification_token = :token AND verification_token_expires_at > :now LIMIT 1');
        $statement->bindValue(':token', $hash);
        $statement->bindValue(':now', $now);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function markEmailAsVerified(int $userId): void
    {
        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $statement = $this->db->prepare('UPDATE users SET email_verified_at = :verified_at, verification_token = NULL, verification_token_expires_at = NULL, updated_at = :updated_at WHERE id = :id');
        $statement->bindValue(':verified_at', $now);
        $statement->bindValue(':updated_at', $now);
        $statement->bindValue(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function allByRole(string $role): array
    {
        $statement = $this->db->prepare('SELECT id, full_name, email, role, matricula, department, avatar_path FROM users WHERE role = :role ORDER BY full_name');
        $statement->bindValue(':role', $role);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

