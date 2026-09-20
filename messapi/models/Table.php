<?php

require_once __DIR__ . '/../config/Database.php';

class Table
{
    private $db;

    // =====================================================
    // CONSTRUCTOR
    // =====================================================

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }


    // =====================================================
    // LISTAR MESAS DE UN RESTAURANTE
    // =====================================================

    public function getAllByRestaurant($restaurantId, $userId)
    {
        // Verificamos que el restaurante pertenezca
        // al usuario que inició sesión.

        $sqlRestaurant = "
            SELECT id
            FROM restaurants
            WHERE id = ?
              AND user_id = ?
            LIMIT 1
        ";

        $stmtRestaurant = $this->db->prepare($sqlRestaurant);

        $stmtRestaurant->execute([
            $restaurantId,
            $userId
        ]);

        $restaurant = $stmtRestaurant->fetch(PDO::FETCH_ASSOC);


        if (!$restaurant) {
            return false;
        }


        // Buscamos las mesas del restaurante.

        $sql = "
            SELECT
                t.id,
                t.restaurant_id,
                t.table_number,
                t.details,
                t.chairs,
                t.status_id,
                ts.name AS status
            FROM tables t
            LEFT JOIN table_statuses ts
                ON ts.id = t.status_id
            WHERE t.restaurant_id = ?
            ORDER BY t.table_number ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $restaurantId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // =====================================================
    // VER UNA MESA
    // =====================================================

    public function getById($tableId, $userId)
    {
        $sql = "
            SELECT
                t.id,
                t.restaurant_id,
                t.table_number,
                t.details,
                t.chairs,
                t.status_id,
                ts.name AS status
            FROM tables t
            INNER JOIN restaurants r
                ON r.id = t.restaurant_id
            LEFT JOIN table_statuses ts
                ON ts.id = t.status_id
            WHERE t.id = ?
              AND r.user_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $tableId,
            $userId
        ]);

        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        return $table ?: false;
    }


    // =====================================================
    // CREAR MESA
    // =====================================================

    public function create(
        $restaurantId,
        $userId,
        $tableNumber,
        $details,
        $chairs
    ) {
        // Primero verificamos que el restaurante
        // pertenezca al usuario.

        $sqlRestaurant = "
            SELECT id
            FROM restaurants
            WHERE id = ?
              AND user_id = ?
            LIMIT 1
        ";

        $stmtRestaurant = $this->db->prepare($sqlRestaurant);

        $stmtRestaurant->execute([
            $restaurantId,
            $userId
        ]);

        if (!$stmtRestaurant->fetch(PDO::FETCH_ASSOC)) {
            return [
                'success' => false,
                'message' => 'Restaurante no encontrado.'
            ];
        }


        // Verificamos que no exista otra mesa
        // con el mismo número en ese restaurante.

        $sqlDuplicate = "
            SELECT id
            FROM tables
            WHERE restaurant_id = ?
              AND table_number = ?
            LIMIT 1
        ";

        $stmtDuplicate = $this->db->prepare($sqlDuplicate);

        $stmtDuplicate->execute([
            $restaurantId,
            $tableNumber
        ]);

        if ($stmtDuplicate->fetch(PDO::FETCH_ASSOC)) {
            return [
                'success' => false,
                'message' => 'Ya existe una mesa con ese número.'
            ];
        }


        // Las mesas nuevas empiezan libres.
        // status_id 1 = Disponible.

        $statusId = 1;


        $sql = "
            INSERT INTO tables
            (
                restaurant_id,
                table_number,
                details,
                chairs,
                status_id
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $restaurantId,
            $tableNumber,
            $details,
            $chairs,
            $statusId
        ]);


        return [
            'success' => true,
            'table_id' => (int) $this->db->lastInsertId()
        ];
    }


    // =====================================================
    // EDITAR MESA
    // =====================================================

    public function update(
        $tableId,
        $userId,
        $tableNumber,
        $details,
        $chairs
    ) {
        // Buscamos la mesa y verificamos que
        // pertenezca al usuario.

        $table = $this->getById(
            $tableId,
            $userId
        );

        if (!$table) {
            return false;
        }


        // Verificamos que no exista otra mesa
        // con ese número dentro del mismo restaurante.

        $sqlDuplicate = "
            SELECT id
            FROM tables
            WHERE restaurant_id = ?
              AND table_number = ?
              AND id <> ?
            LIMIT 1
        ";

        $stmtDuplicate = $this->db->prepare($sqlDuplicate);

        $stmtDuplicate->execute([
            $table['restaurant_id'],
            $tableNumber,
            $tableId
        ]);


        if ($stmtDuplicate->fetch(PDO::FETCH_ASSOC)) {
            return 'duplicate';
        }


        $sql = "
            UPDATE tables
            SET
                table_number = ?,
                details = ?,
                chairs = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $tableNumber,
            $details,
            $chairs,
            $tableId
        ]);


        return true;
    }


    // =====================================================
    // ELIMINAR MESA
    // =====================================================

    public function delete($tableId, $userId)
    {
        // Verificamos que la mesa pertenezca
        // al usuario.

        $table = $this->getById(
            $tableId,
            $userId
        );

        if (!$table) {
            return false;
        }


        $sql = "
            DELETE FROM tables
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $tableId
        ]);


        return true;
    }


    // =====================================================
    // CAMBIO RÁPIDO DE ESTADO
    // =====================================================
    //
    // NUEVO FUNCIONAMIENTO:
    //
    // 1 = Disponible = LIBRE
    // 2 = Ocupada    = OCUPADA
    //
    // LIBRE -> OCUPADA -> LIBRE
    //
    // El estado 3 (Reservada) puede seguir existiendo
    // en MySQL, pero ya no se usa normalmente.
    //
    // Si una mesa vieja está en estado 3,
    // al cambiar su estado pasa a LIBRE.
    //
    // =====================================================

    public function rotateStatus($tableId, $userId)
    {
        // Buscamos la mesa y verificamos
        // que pertenezca al usuario.

        $table = $this->getById(
            $tableId,
            $userId
        );

        if (!$table) {
            return false;
        }


        $currentStatusId = (int) $table['status_id'];


        // ==========================================
        // SOLO DOS ESTADOS
        // ==========================================

        if ($currentStatusId === 1) {

            // LIBRE -> OCUPADA
            $newStatusId = 2;
        } else {

            // OCUPADA o antigua RESERVADA -> LIBRE
            $newStatusId = 1;
        }


        $sql = "
            UPDATE tables
            SET status_id = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $newStatusId,
            $tableId
        ]);


        // Obtenemos la mesa nuevamente para devolver
        // el nombre del estado actualizado.

        $updatedTable = $this->getById(
            $tableId,
            $userId
        );


        if (!$updatedTable) {
            return false;
        }


        return [
            'status_id' => (int) $updatedTable['status_id'],
            'status' => $updatedTable['status']
        ];
    }
}
