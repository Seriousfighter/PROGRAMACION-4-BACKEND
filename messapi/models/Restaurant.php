<?php
require_once __DIR__ . '/../config/Database.php';

class Restaurant {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getPublicAvailability() {
        $query = "SELECT 
                    r.id AS restaurant_id, 
                    r.name, 
                    r.address, 
                    r.description, 
                    r.is_open,
                    t.id AS table_id,
                    t.table_number,
                    t.details,
                    t.chairs,
                    ts.name AS status_name
                  FROM restaurants r 
                  LEFT JOIN tables t ON r.id = t.restaurant_id 
                  LEFT JOIN table_statuses ts ON t.status_id = ts.id
                  ORDER BY r.is_open DESC, r.id ASC, t.table_number ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $restaurants = [];
        
        foreach ($results as $row) {
            $r_id = $row['restaurant_id'];
            
            if (!isset($restaurants[$r_id])) {
                $restaurants[$r_id] = [
                    'id' => (int) $r_id,
                    'name' => $row['name'],
                    'address' => $row['address'],
                    'description' => $row['description'],
                    'is_open' => (bool) $row['is_open'],
                    'total_tables' => 0,
                    'tables' => []
                ];
            }
            
            if ($row['table_id'] !== null) {
                // Incrementamos el contador de mesas totales por cada mesa que pertenezca al restaurante
                $restaurants[$r_id]['total_tables']++;
                
                $restaurants[$r_id]['tables'][] = [
                    'id' => (int) $row['table_id'],
                    'table_number' => (int) $row['table_number'],
                    'details' => $row['details'],
                    'chairs' => (int) $row['chairs'],
                    'status' => strtolower($row['status_name'])
                ];
            }
        }
        
        return array_values($restaurants);
    }
}
?>