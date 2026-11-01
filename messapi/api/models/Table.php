<?php
require_once __DIR__ . '/../config/Database.php';

class Table
{ // Esta clase maneja todas las operaciones relacionadas con la tabla "tables" en la base de datos, incluyendo CRUD y gestión de estados de mesas.
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function listByRestaurant(int $restaurantId): array
    { // Devuelve todas las mesas de un restaurante específico, incluyendo su estado y detalles.
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.restaurant_id, t.table_number, t.details, t.chairs,
                    t.status_id, ts.name AS status_name,
                    t.created_at, t.updated_at
             FROM tables t
             INNER JOIN table_statuses ts ON ts.id = t.status_id
             WHERE t.restaurant_id = :rid
             ORDER BY t.table_number ASC"
        );
        $stmt->execute(['rid' => $restaurantId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    { // Busca una mesa por su ID y devuelve sus detalles, incluyendo el estado y la información del restaurante al que pertenece.
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.restaurant_id, t.table_number, t.details, t.chairs,
                    t.status_id, ts.name AS status_name,
                    t.created_at, t.updated_at
             FROM tables t
             INNER JOIN table_statuses ts ON ts.id = t.status_id
             WHERE t.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $restaurantId, array $d): int
    { // Crea una nueva mesa para un restaurante específico y devuelve el ID de la mesa recién creada.
        $stmt = $this->conn->prepare(
            "INSERT INTO tables (restaurant_id, table_number, details, chairs, status_id)
             VALUES (:rid, :tn, :dt, :ch, :st)"
        );
        $stmt->execute([
            'rid' => $restaurantId,
            'tn'  => (int) ($d['table_number'] ?? 0),
            'dt'  => $d['details'] ?? null,
            'ch'  => (int) ($d['chairs'] ?? 2),
            'st'  => (int) ($d['status_id'] ?? 1),
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, array $d): bool
    { // Actualiza los detalles de una mesa específica, incluyendo número de mesa, detalles, cantidad de sillas y estado.
        $stmt = $this->conn->prepare(
            "UPDATE tables
                SET table_number = :tn, details = :dt,
                    chairs = :ch, status_id = :st
             WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'tn' => (int) ($d['table_number'] ?? 0),
            'dt' => $d['details'] ?? null,
            'ch' => (int) ($d['chairs'] ?? 2),
            'st' => (int) ($d['status_id'] ?? 1),
        ]);
    }

    public function delete(int $id): bool
    { // Elimina una mesa específica de la base de datos.
        $stmt = $this->conn->prepare("DELETE FROM tables WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function rotateStatus(int $tableId): array
    { // Cambia el estado de una mesa específica al siguiente estado en la secuencia (por ejemplo, de disponible a ocupada, de ocupada a reservada, etc.) y devuelve el estado actualizado de la mesa.
        $stmt = $this->conn->prepare(
            "SELECT id, status_id
         FROM tables
         WHERE id = :tid
         LIMIT 1"
        );

        $stmt->execute([
            'tid' => $tableId
        ]);

        $row = $stmt->fetch();

        $current = (int) $row['status_id'];

        $totalStatuses = (int) $this->conn
            ->query("SELECT COUNT(*) FROM table_statuses")
            ->fetchColumn();

        $next = $current + 1;

        if ($next > $totalStatuses) {
            $next = 1;
        }

        $this->updateStatus($tableId, $next);

        return [
            'success' => true,
            'code' => 200,
            'table' => $this->findById($tableId)
        ];
    }
    public function updateStatus(int $tableId, int $statusId): void
    { // Actualiza el estado de una mesa específica a un estado dado.
        $stmt = $this->conn->prepare(
            "UPDATE tables
         SET status_id = :status,
             updated_at = NOW()
         WHERE id = :id"
        );

        $stmt->execute([
            'status' => $statusId,
            'id' => $tableId
        ]);
    }

    public function listStatuses(): array
    { // Devuelve todos los estados posibles de las mesas, incluyendo su ID y nombre.
        return $this->conn
            ->query("SELECT id, name FROM table_statuses ORDER BY id ASC")
            ->fetchAll();
    }
}
