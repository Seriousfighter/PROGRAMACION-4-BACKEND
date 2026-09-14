<?php
require_once __DIR__ . '/../views/JsonView.php';

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $host     = 'localhost';
        $db_name  = 'mesas_disponibles';
        $username = 'root';
        $password = '';

        try {
            $this->conn = new PDO(
                "mysql:host={$host};dbname={$db_name};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('[DB ERROR] ' . $e->getMessage());
            JsonView::render(['message' => 'Error de conexión a la base de datos'], 500);
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}