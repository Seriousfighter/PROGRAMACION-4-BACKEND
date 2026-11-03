<?php
require_once __DIR__ . '/../views/JsonView.php'; // Importamos la clase JsonView para poder enviar errores en formato JSON

/**
 * Clase Database (Patrón Singleton)
 * Se encarga de manejar la conexión a MySQL de forma centralizada.
 * Al usar Singleton, garantiza que solo se abra UNA conexión en todo el sistema.
 */
class Database
{
    private static ?Database $instance = null;// Instancia única de la clase (Singleton)
    private PDO $conn; // Objeto de conexión PDO


    /**
     * Constructor privado: impide que se haga "new Database()" desde afuera.
     * Solo se puede instanciar desde getConnection().
     */
    private function __construct()
    {
        // Parámetros de conexión a la base de datos local
        $host     = 'localhost';
        $db_name  = 'mesas_disponibles';
        $username = 'root';
        $password = '';

        try {
             // Creamos la conexión PDO con configuración de seguridad y codificación UTF-8
            $this->conn = new PDO(
                "mysql:host={$host};dbname={$db_name};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones si hay errores SQL
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve los resultados como arrays asociativos
                    PDO::ATTR_EMULATE_PREPARES   => false, // Usa prepares reales (más seguro contra inyección SQL)
                ]
            );
        } catch (PDOException $e) {
            // Si falla la conexión, guardamos el error en el log y devolvemos un JSON de error 500
            error_log('[DB ERROR] ' . $e->getMessage());
            JsonView::render(['message' => 'Error de conexión a la base de datos'], 500);
        }
    }


     /**
     * Método estático para obtener la conexión.
     * Si no existe la instancia, la crea. Si ya existe, devuelve la misma.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}