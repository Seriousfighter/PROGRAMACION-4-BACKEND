<?php
require_once __DIR__ . '/../config/Database.php';

class User
{ // Esta clase maneja todas las operaciones relacionadas con la tabla "users" en la base de datos, incluyendo búsqueda, creación y verificación de existencia de usuarios.
    private PDO $conn;

    public function __construct()
    { // Inicializa la conexión a la base de datos utilizando el patrón Singleton para garantizar que solo haya una conexión activa en todo el sistema.
        $this->conn = Database::getConnection();
    }

    public function findById(int $id): ?array
    { // Busca un usuario por su ID y devuelve sus detalles, excluyendo la contraseña para mayor seguridad.
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, created_at, updated_at
             FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmailWithPassword(string $email): ?array
    { // Busca un usuario por su email y devuelve sus detalles, incluyendo la contraseña hasheada para poder verificarla durante el login.
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, password, created_at, updated_at
             FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    { // Verifica si un email ya está registrado en la base de datos, devolviendo true si existe y false si no.
        $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $name, string $email, string $passwordHash): int
    { // Crea un nuevo usuario en la base de datos con el nombre, email y contraseña hasheada proporcionados, devolviendo el ID del usuario recién creado.
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, password) VALUES (:n, :e, :p)"
        );
        $stmt->execute(['n' => $name, 'e' => $email, 'p' => $passwordHash]);
        return (int) $this->conn->lastInsertId();
    }
}