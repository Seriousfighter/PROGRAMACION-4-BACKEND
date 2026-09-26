<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

class RestaurantController {
    
    // RF-05: Listar restaurantes del administrador
    public function index($user_id) {
        $model = new Restaurant();
        $data = $model->getAllByUser($user_id);
        JsonView::render(['data' => $data], 200);
    }

    // RF-04: Crear restaurante
    public function store($user_id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['name']) || !isset($input['address'])) {
            JsonView::render(['error' => 'Faltan campos obligatorios (name, address)'], 422);
            exit;
        }

        $model = new Restaurant();
        $result = $model->create($user_id, $input);
        JsonView::render(['message' => 'Restaurante creado con éxito', 'id' => $result], 201);
    }

    // Obtener detalle de un restaurante específico
    public function show($id, $user_id) {
        $model = new Restaurant();
        $data = $model->getById($id, $user_id);
        
        if (!$data) {
            JsonView::render(['error' => 'Restaurante no encontrado o acceso denegado'], 404);
            exit;
        }
        JsonView::render(['data' => $data], 200);
    }

    // RF-06: Modificar restaurante
    public function update($id, $user_id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $model = new Restaurant();
        
        $result = $model->update($id, $user_id, $input);
        if (isset($result['error'])) {
            JsonView::render(['error' => $result['error']], 404);
            exit;
        }
        
        JsonView::render(['message' => 'Restaurante actualizado correctamente'], 200);
    }

    // RF-07: Eliminar restaurante
    public function destroy($id, $user_id) {
        $model = new Restaurant();
        $result = $model->delete($id, $user_id);
        
        if (isset($result['error'])) {
            JsonView::render(['error' => $result['error']], 404);
            exit;
        }
        
        JsonView::render(['message' => 'Restaurante eliminado correctamente'], 200);
    }
}
?>