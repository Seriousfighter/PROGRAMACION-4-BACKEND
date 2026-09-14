<?php
require_once __DIR__ . '/../config/Database.php';

class User
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, created_at, updated_at
             FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmailWithPassword(string $email): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, password, created_at, updated_at
             FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, password) VALUES (:n, :e, :p)"
        );
        $stmt->execute(['n' => $name, 'e' => $email, 'p' => $passwordHash]);
        return (int) $this->conn->lastInsertId();
    }
}