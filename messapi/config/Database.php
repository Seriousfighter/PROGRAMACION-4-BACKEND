<?php
class Database {
    private static $instance = null;
    private $conn;

    private $host = '127.0.0.1';
    private $db_name = 'mesa_disponibles';
    private $username = 'pochi';
    private $password = 'pochi';

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            JsonView::render(['error' => 'Error de conexión a la base de datos', 'message' => $exception->getMessage()], 500);
        }
    }

    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
?>