<?php
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../views/JsonView.php';

class TableController {
    
    // RF-09: Listar mesas de un restaurante (El endpoint que usará el frontend para el mapa 2.5D)
    public function indexByRestaurant($restaurant_id, $user_id) {
        $model = new Table();
        // Validamos que el restaurante le pertenece al usuario antes de devolver mesas
        $data = $model->getByRestaurant($restaurant_id, $user_id);
        JsonView::render(['data' => $data], 200);
    }

    // RF-08: Crear mesa
    public function store($restaurant_id, $user_id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['detail']) || !isset($input['chairs']) || !isset($input['status_id'])) {
            JsonView::render(['error' => 'Faltan campos obligatorios para la mesa'], 422);
            exit;
        }

        $model = new Table();
        $result = $model->create($restaurant_id, $user_id, $input);
        
        if (isset($result['error'])) {
            JsonView::render(['error' => $result['error']], 403);
            exit;
        }

        JsonView::render(['message' => 'Mesa creada con éxito', 'id' => $result], 201);
    }

    // Detalle de una mesa específica
    public function show($id, $user_id) {
        $model = new Table();
        $data = $model->getById($id, $user_id);
        
        if (!$data) {
            JsonView::render(['error' => 'Mesa no encontrada'], 404);
            exit;
        }
        JsonView::render(['data' => $data], 200);
    }

    // RF-10: Actualizar datos de una mesa
    public function update($id, $user_id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $model = new Table();
        
        $result = $model->update($id, $user_id, $input);
        if (isset($result['error'])) {
            JsonView::render(['error' => $result['error']], 404);
            exit;
        }
        JsonView::render(['message' => 'Mesa actualizada correctamente'], 200);
    }

    // RF-11: Eliminar una mesa
    public function destroy($id, $user_id) {
        $model = new Table();
        $result = $model->delete($id, $user_id);
        
        if (isset($result['error'])) {
            JsonView::render(['error' => $result['error']], 404);
            exit;
        }
        JsonView::render(['message' => 'Mesa eliminada correctamente'], 200);
    }

    // RF-12: Rotar estado (El método que ya tenías armado)
    public function rotateStatus($table_id, $user_id) {
        $model = new Table(); //[cite: 2]
        $result = $model->rotateStatus($table_id, $user_id); //[cite: 2]

        if (isset($result['error'])) { //[cite: 2]
            JsonView::render(['message' => $result['error']], $result['code']); //[cite: 2]
            exit;
        }

        JsonView::render([ //[cite: 2]
            'message' => 'Estado de mesa actualizado correctamente', //[cite: 2]
            'data' => ['new_status_id' => $result['new_status_id']] //[cite: 2]
        ], 200); //[cite: 2]
    }
}
?>