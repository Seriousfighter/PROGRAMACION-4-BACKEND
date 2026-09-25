<?php
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

class TableController
{
    public function getById(int $id, int $userId): void
    {
        JsonView::render(['data' => $this->assertOwned($id, $userId)], 200);
    }

    public function update(int $id, int $userId): void
    {
        $this->assertOwned($id, $userId);
        $b = $this->jsonBody();
        $e = [];
        if (isset($b['table_number']) && !is_numeric($b['table_number'])) $e['table_number'] = 'Numérico.';
        if (isset($b['chairs']) && (!is_numeric($b['chairs']) || (int) $b['chairs'] < 1)) $e['chairs'] = '≥ 1.';
        if (isset($b['status_id']) && !is_numeric($b['status_id'])) $e['status_id'] = 'Numérico.';
        if ($e) JsonView::render(['message' => 'Errores de validación', 'errors' => $e], 422);

        $m = new Table();
        $m->update($id, $b);
        JsonView::render(['data' => $m->findById($id)], 200);
    }

    public function delete(int $id, int $userId): void
    {
        $this->assertOwned($id, $userId);
        (new Table())->delete($id);
        JsonView::render(null, 204);
    }

    public function rotateStatus(int $id): void
    {
        $result = (new Table())->rotateStatus($id);
        if (isset($result['error'])) {
            JsonView::render(['message' => $result['error']], $result['code']);
        }
        JsonView::render([
            'message' => 'Estado de mesa actualizado correctamente',
            'data'    => $result['table'],
        ], 200);
    }

    public function listStatuses(): void
    {
        JsonView::render(['data' => (new Table())->listStatuses()], 200);
    }

    private function assertOwned(int $tableId, int $userId): array
    {
        $t = (new Table())->findById($tableId);
        if (!$t) JsonView::render(['message' => 'La mesa no existe'], 404);

        if (!(new Restaurant())->findOwned((int) $t['restaurant_id'], $userId)) {
            JsonView::render(['message' => 'No autorizado'], 403);
        }
        return $t;
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) return [];
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
}