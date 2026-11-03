<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../views/JsonView.php';

// RestaurantController se encarga de manejar las rutas relacionadas con los restaurantes y sus mesas.

class RestaurantController
{
    public function list(int $userId): void // Listamos todos los restaurantes que pertenecen al usuario autenticado
    {
        JsonView::render(['data' => (new Restaurant())->listByUser($userId)], 200);// Respondemos con la lista de restaurantes del usuario (código 200 OK)
    }

    public function create(int $userId): void // Creamos un nuevo restaurante para el usuario autenticado
    {
        $b = $this->jsonBody();
        $this->validateOrFail($b);
        $m  = new Restaurant();
        $id = $m->create($userId, $b);
        JsonView::render(['data' => $m->getRestaurantById($id)], 201);// Respondemos con los datos del restaurante creado (código 201 Created)
    }

    public function getById(int $id, int $userId): void // Trae los datos de un restaurante específico por su ID
    {
        $row = (new Restaurant())->findOwned($id, $userId);
        if (!$row) JsonView::render(['message' => 'El restaurante no existe'], 404);// Respondemos con error 404 si el restaurante no pertenece al usuario
        JsonView::render(['data' => $row], 200);
    }

    public function update(int $id, int $userId): void // Actualizamos los datos de un restaurante específico
    {
        $m = new Restaurant();
        if (!$m->findOwned($id, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);// Respondemos con error 404 si el restaurante no pertenece al usuario
        }
        $b = $this->jsonBody();
        $this->validateOrFail($b);
        $m->update($id, $b);
        JsonView::render(['data' => $m->getRestaurantById($id)], 200);// Respondemos con los datos actualizados del restaurante (código 200 OK)
    }

    public function delete(int $id, int $userId): void // Eliminamos un restaurante específico
    {
        $m = new Restaurant();
        if (!$m->findOwned($id, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);// Respondemos con error 404 si el restaurante no pertenece al usuario
        }
        $m->delete($id);
        JsonView::render(null, 204);// Respondemos con un código 204 No Content
    }

    public function listTables(int $restaurantId, int $userId): void // Listamos las mesas de un restaurante específico
    {
        if (!(new Restaurant())->findOwned($restaurantId, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }
        JsonView::render(['data' => (new Table())->listByRestaurant($restaurantId)], 200);
    }

    public function createTable(int $restaurantId, int $userId): void // Creamos una nueva mesa para un restaurante específico
    {
        if (!(new Restaurant())->findOwned($restaurantId, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }
        $b = $this->jsonBody();
        $errs = [];
        if (isset($b['table_number']) && !is_numeric($b['table_number'])) $errs['table_number'] = 'Numérico.';
        if (isset($b['chairs']) && (!is_numeric($b['chairs']) || (int) $b['chairs'] < 1)) $errs['chairs'] = '≥ 1.';
        if (isset($b['status_id']) && !is_numeric($b['status_id'])) $errs['status_id'] = 'Numérico.';
        if ($errs) JsonView::render(['message' => 'Errores de validación', 'errors' => $errs], 422);

        $m  = new Table();
        $id = $m->create($restaurantId, $b);
        JsonView::render(['data' => $m->findById($id)], 201);
    }

    private function validateOrFail(array $b): void // Valida los datos del restaurante y lanza un error si no son válidos
    {
        $e = [];
        if (empty($b['name'])    || mb_strlen($b['name'])    > 150) $e['name']    = 'Obligatorio (máx. 150).';
        if (empty($b['address']) || mb_strlen($b['address']) > 255) $e['address'] = 'Obligatorio (máx. 255).';
        if (isset($b['phone']) && mb_strlen((string) $b['phone']) > 30) $e['phone'] = 'Máx. 30.';
        if (isset($b['description']) && mb_strlen((string) $b['description']) > 1000) $e['description'] = 'Máx. 1000.';
        if ($e) JsonView::render(['message' => 'Errores de validación', 'errors' => $e], 422);
    }

    private function jsonBody(): array // Obtiene el cuerpo de la solicitud en formato JSON
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) return [];
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
}