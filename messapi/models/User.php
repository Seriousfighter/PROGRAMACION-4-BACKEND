<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function login($email, $password) {
        $query = "SELECT id, name, password FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // password_verify compara la clave en texto plano con el hash guardado en MySQL
        if ($user && password_verify($password, $user['password'])) {
            return ['id' => $user['id'], 'name' => $user['name']];
        }
        return false;
    }
}
?>