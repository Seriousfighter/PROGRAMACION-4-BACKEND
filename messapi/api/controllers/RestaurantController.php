<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../views/JsonView.php';

class RestaurantController
{
    public function list(int $userId): void
    {
        JsonView::render(['data' => (new Restaurant())->listByUser($userId)], 200);
    }

    public function create(int $userId): void
    {
        $b = $this->jsonBody();
        $this->validateOrFail($b);
        $m  = new Restaurant();
        $id = $m->create($userId, $b);
        JsonView::render(['data' => $m->getRestaurantById($id)], 201);
    }

    public function getById(int $id, int $userId): void
    {
        $row = (new Restaurant())->findOwned($id, $userId);
        if (!$row) JsonView::render(['message' => 'El restaurante no existe'], 404);
        JsonView::render(['data' => $row], 200);
    }

    public function update(int $id, int $userId): void
    {
        $m = new Restaurant();
        if (!$m->findOwned($id, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }
        $b = $this->jsonBody();
        $this->validateOrFail($b);
        $m->update($id, $b);
        JsonView::render(['data' => $m->getRestaurantById($id)], 200);
    }

    public function delete(int $id, int $userId): void
    {
        $m = new Restaurant();
        if (!$m->findOwned($id, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }
        $m->delete($id);
        JsonView::render(null, 204);
    }

    public function listTables(int $restaurantId, int $userId): void
    {
        if (!(new Restaurant())->findOwned($restaurantId, $userId)) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }
        JsonView::render(['data' => (new Table())->listByRestaurant($restaurantId)], 200);
    }

    public function createTable(int $restaurantId, int $userId): void
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

    private function validateOrFail(array $b): void
    {
        $e = [];
        if (empty($b['name'])    || mb_strlen($b['name'])    > 150) $e['name']    = 'Obligatorio (máx. 150).';
        if (empty($b['address']) || mb_strlen($b['address']) > 255) $e['address'] = 'Obligatorio (máx. 255).';
        if (isset($b['phone']) && mb_strlen((string) $b['phone']) > 30) $e['phone'] = 'Máx. 30.';
        if (isset($b['description']) && mb_strlen((string) $b['description']) > 1000) $e['description'] = 'Máx. 1000.';
        if ($e) JsonView::render(['message' => 'Errores de validación', 'errors' => $e], 422);
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) return [];
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
}