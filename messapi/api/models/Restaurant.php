<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Restaurant
 * - Se encarga de todas las consultas SQL relacionadas con la tabla "restaurants".
 * - Contiene tanto los métodos para el panel de admin como para la vista pública.
 */
class Restaurant
{
    private PDO $conn; // Conexión a la base de datos

    public function __construct()
    {
        // Obtiene la conexión Singleton
        $this->conn = Database::getConnection();
    }

    /**
     * Busca un restaurante por su ID.
     * @param int $id - ID del restaurante
     */
    public function getRestaurantById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, name, address, phone, description, is_open,
                    created_at, updated_at
             FROM restaurants WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Trae todos los restaurantes con su cantidad de mesas libres y totales.
     * Se usa en la vista pública para las tarjetas.
     */
    public function getPublicAvailability(): array
    {
        $sql = "SELECT
                    r.id, r.name, r.address, r.phone, r.description, r.is_open,
                    COUNT(CASE WHEN ts.name = 'Disponible' THEN 1 END) AS available_tables,
                    COUNT(t.id) AS total_tables
                FROM restaurants r
                LEFT JOIN tables t          ON t.restaurant_id = r.id
                LEFT JOIN table_statuses ts ON ts.id = t.status_id
                GROUP BY r.id, r.name, r.address, r.phone, r.description, r.is_open
                ORDER BY available_tables DESC, r.name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Convertimos los tipos de datos para que el JSON se vea limpio
        return array_map(function ($r) {
            $r['id']               = (int) $r['id'];
            $r['is_open']          = (bool) $r['is_open'];
            $r['available_tables'] = (int) $r['available_tables'];
            $r['total_tables']     = (int) $r['total_tables'];
            return $r;
        }, $rows);
    }

    /**
     * Lista todos los restaurantes de un usuario (para el panel de admin).
     */
    public function listByUser(int $userId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, name, address, phone, description, is_open,
                    created_at, updated_at
             FROM restaurants WHERE user_id = :uid ORDER BY id DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca un restaurante asegurándose de que pertenezca al usuario.
     * (Evita que un admin edite el restaurante de otro).
     */
    public function findOwned(int $id, int $userId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, name, address, phone, description, is_open,
                    created_at, updated_at
             FROM restaurants WHERE id = :id AND user_id = :uid LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Crea un nuevo restaurante en la base de datos.
     * Devuelve el ID generado.
     */
    public function create(int $userId, array $d): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO restaurants (user_id, name, address, phone, description, is_open)
             VALUES (:uid, :name, :address, :phone, :description, :is_open)"
        );
        $stmt->execute([
            'uid'         => $userId,
            'name'        => $d['name'],
            'address'     => $d['address'],
            'phone'       => $d['phone']       ?? null,
            'description' => $d['description'] ?? null,
            'is_open'     => isset($d['is_open']) ? (int) (bool) $d['is_open'] : 1,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Actualiza los datos de un restaurante.
     */
    public function update(int $id, array $d): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE restaurants
                SET name = :name, address = :address, phone = :phone,
                    description = :description, is_open = :is_open
             WHERE id = :id"
        );
        return $stmt->execute([
            'id'          => $id,
            'name'        => $d['name'],
            'address'     => $d['address'],
            'phone'       => $d['phone']       ?? null,
            'description' => $d['description'] ?? null,
            'is_open'     => isset($d['is_open']) ? (int) (bool) $d['is_open'] : 1,
        ]);
    }

    /**
     * Elimina un restaurante (y en cascada sus mesas, si la DB está configurada así).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM restaurants WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}