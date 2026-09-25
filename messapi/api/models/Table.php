<?php
require_once __DIR__ . '/../config/Database.php';

class Table
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function listByRestaurant(int $restaurantId): array
    {
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
    {
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
    {
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
    {
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
    {
        $stmt = $this->conn->prepare("DELETE FROM tables WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function rotateStatus(int $tableId): array
    {
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
    {
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
    {
        return $this->conn
            ->query("SELECT id, name FROM table_statuses ORDER BY id ASC")
            ->fetchAll();
    }
}
