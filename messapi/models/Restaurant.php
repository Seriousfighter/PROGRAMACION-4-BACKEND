<?php

require_once __DIR__ . '/../config/Database.php';

class Restaurant
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ========================================
    // LISTAR RESTAURANTES DEL USUARIO
    // ========================================

    public function getAllByUser($userId)
    {
        $query = "
            SELECT
                id,
                user_id,
                name,
                address,
                city,
                phone,
                description,
                is_open,
                created_at
            FROM restaurants
            WHERE user_id = ?
            ORDER BY id ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================
    // OBTENER UN RESTAURANTE
    // ========================================

    public function getById($restaurantId, $userId)
    {
        $query = "
            SELECT
                id,
                user_id,
                name,
                address,
                city,
                phone,
                description,
                is_open,
                created_at
            FROM restaurants
            WHERE id = ?
            AND user_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            $restaurantId,
            $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ========================================
    // CREAR RESTAURANTE
    // ========================================

    public function create(
        $userId,
        $name,
        $address,
        $city,
        $phone,
        $description
    ) {
        $query = "
            INSERT INTO restaurants
            (
                user_id,
                name,
                address,
                city,
                phone,
                description,
                is_open
            )
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            $userId,
            $name,
            $address,
            $city,
            $phone,
            $description
        ]);

        return $this->db->lastInsertId();
    }

    // ========================================
    // EDITAR RESTAURANTE
    // ========================================

    public function update(
        $restaurantId,
        $userId,
        $name,
        $address,
        $city,
        $phone,
        $description
    ) {
        $query = "
            UPDATE restaurants
            SET
                name = ?,
                address = ?,
                city = ?,
                phone = ?,
                description = ?
            WHERE id = ?
            AND user_id = ?
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            $name,
            $address,
            $city,
            $phone,
            $description,
            $restaurantId,
            $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    // ========================================
    // ELIMINAR RESTAURANTE
    // ========================================

    public function delete($restaurantId, $userId)
    {
        $query = "
            DELETE FROM restaurants
            WHERE id = ?
            AND user_id = ?
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            $restaurantId,
            $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    // ========================================
    // ABRIR / CERRAR RESTAURANTE
    // ========================================

    public function toggleStatus($restaurantId, $userId)
    {
        $restaurant = $this->getById(
            $restaurantId,
            $userId
        );

        if (!$restaurant) {
            return false;
        }

        // Si está abierto, lo cerramos.
        // Si está cerrado, lo abrimos.
        $newStatus = ((int) $restaurant['is_open'] === 1)
            ? 0
            : 1;

        $query = "
            UPDATE restaurants
            SET is_open = ?
            WHERE id = ?
            AND user_id = ?
        ";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            $newStatus,
            $restaurantId,
            $userId
        ]);

        return $newStatus;
    }

    // ========================================
    // CONSULTA PÚBLICA DE DISPONIBILIDAD
    // ========================================

    public function getPublicAvailability()
    {
        $query = "
            SELECT
                r.id AS restaurant_id,
                r.name AS restaurant_name,
                r.address,
                r.city,
                r.phone,
                r.description,
                r.is_open,

                t.id AS table_id,
                t.table_number,
                t.details,
                t.chairs,

                ts.id AS status_id,
                ts.name AS status

            FROM restaurants r

            LEFT JOIN tables t
                ON t.restaurant_id = r.id

            LEFT JOIN table_statuses ts
                ON ts.id = t.status_id

            ORDER BY
                r.name ASC,
                t.table_number ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurants = [];

        foreach ($rows as $row) {

            $restaurantId = (int) $row['restaurant_id'];

            // Creamos el restaurante una sola vez
            if (!isset($restaurants[$restaurantId])) {

                $restaurants[$restaurantId] = [
                    'id' => $restaurantId,
                    'name' => $row['restaurant_name'],
                    'address' => $row['address'],
                    'city' => $row['city'],
                    'phone' => $row['phone'],
                    'description' => $row['description'],
                    'is_open' => (bool) $row['is_open'],
                    'total_tables' => 0,
                    'available_tables' => 0,
                    'tables' => []
                ];
            }

            // Agregamos sus mesas
            if ($row['table_id'] !== null) {

                $statusId = (int) $row['status_id'];

                $restaurants[$restaurantId]['tables'][] = [
                    'id' => (int) $row['table_id'],
                    'table_number' => (int) $row['table_number'],
                    'details' => $row['details'],
                    'chairs' => (int) $row['chairs'],
                    'status_id' => $statusId,
                    'status' => $row['status']
                ];

                $restaurants[$restaurantId]['total_tables']++;

                // Estado 1 = Disponible
                if ($statusId === 1) {
                    $restaurants[$restaurantId]['available_tables']++;
                }
            }
        }

        $restaurants = array_values($restaurants);

        // ========================================
        // ORDENAR POR MESAS DISPONIBLES
        // ========================================

        usort($restaurants, function ($a, $b) {

            // Primero el que tenga más mesas disponibles
            if (
                $a['available_tables'] !==
                $b['available_tables']
            ) {
                return
                    $b['available_tables']
                    <=>
                    $a['available_tables'];
            }

            // Si tienen la misma cantidad,
            // los ordenamos alfabéticamente
            return strcasecmp(
                $a['name'],
                $b['name']
            );
        });

        return $restaurants;
    }
}
