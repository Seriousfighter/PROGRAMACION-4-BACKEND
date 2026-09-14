<?php
require_once __DIR__ . '/../config/Database.php';

class Restaurant
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getRestaurantById($id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, name, address, phone, description, is_open,
                    created_at, updated_at
             FROM restaurants WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

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

        return array_map(function ($r) {
            $r['id']               = (int) $r['id'];
            $r['is_open']          = (bool) $r['is_open'];
            $r['available_tables'] = (int) $r['available_tables'];
            $r['total_tables']     = (int) $r['total_tables'];
            return $r;
        }, $rows);
    }

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

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM restaurants WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}