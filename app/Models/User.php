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
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            SQL;
        }

        $this->db->exec($sql);
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

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function allByRole(string $role): array
    {
        $statement = $this->db->prepare('SELECT id, full_name, email, role, matricula, department FROM users WHERE role = :role ORDER BY full_name');
        $statement->bindValue(':role', $role);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findManyByIds(array $ids): array
    {
        $uniqueIds = array_values(array_unique(array_filter($ids, static fn ($id) => is_numeric($id))));
        if ($uniqueIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
        $statement = $this->db->prepare("SELECT id, full_name, email, role FROM users WHERE id IN ($placeholders)");

        foreach ($uniqueIds as $index => $id) {
            $statement->bindValue($index + 1, (int) $id, PDO::PARAM_INT);
        }

        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $mapped = [];
        foreach ($results as $user) {
            $mapped[(int) $user['id']] = $user;
        }

        return $mapped;
    }
}