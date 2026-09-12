<?php
require_once __DIR__ . '/../config/Database.php';

class Table {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // RF-12: Rotar estado de la mesa (Disponible 1 -> Ocupada 2 -> Reservada 3 -> Disponible 1)
    public function rotateStatus($table_id, $restaurant_id_of_user) {
        // 1. Verificar que la mesa existe y pertenece a un restaurante del usuario
        $query = "SELECT t.id, t.status_id FROM tables t 
                  INNER JOIN restaurants r ON t.restaurant_id = r.id 
                  WHERE t.id = :table_id AND r.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['table_id' => $table_id, 'user_id' => $restaurant_id_of_user]);
        $mesa = $stmt->fetch();

        if (!$mesa) {
            return ['error' => 'Mesa no encontrada o no autorizada', 'code' => 404];
        }

        // 2. Calcular el siguiente estado (Ejemplo asumiendo 3 estados: 1, 2, 3)
        $estado_actual = (int)$mesa['status_id'];
        $nuevo_estado = ($estado_actual % 3) + 1; // Si es 1->2, 2->3, 3->1

        // 3. Actualizar en Base de Datos
        $updateQuery = "UPDATE tables SET status_id = :nuevo_estado, updated_at = NOW() WHERE id = :table_id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute(['nuevo_estado' => $nuevo_estado, 'table_id' => $table_id]);

        return ['success' => true, 'new_status_id' => $nuevo_estado, 'code' => 200];
    }
}
?>