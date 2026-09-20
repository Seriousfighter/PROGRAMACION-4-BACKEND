<?php

require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../views/JsonView.php';

class TableController
{
    private $tableModel;

    public function __construct()
    {
        $this->tableModel = new Table();
    }

    // LISTAR mesas de un restaurante
    public function index($restaurantId, $userId)
    {
        $tables = $this->tableModel->getAllByRestaurant(
            $restaurantId,
            $userId
        );

        if ($tables === false) {
            JsonView::render([
                'success' => false,
                'message' => 'Restaurante no encontrado.'
            ], 404);
            return;
        }

        JsonView::render([
            'success' => true,
            'data' => $tables
        ]);
    }

    // VER una mesa
    public function show($tableId, $userId)
    {
        $table = $this->tableModel->getById(
            $tableId,
            $userId
        );

        if (!$table) {
            JsonView::render([
                'success' => false,
                'message' => 'Mesa no encontrada.'
            ], 404);
            return;
        }

        JsonView::render([
            'success' => true,
            'data' => $table
        ]);
    }

    // CREAR mesa
    public function create($restaurantId, $userId)
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($data)) {
            JsonView::render([
                'success' => false,
                'message' => 'Datos inválidos.'
            ], 400);
            return;
        }

        $tableNumber = (int) ($data['table_number'] ?? 0);
        $details = trim($data['details'] ?? '');
        $chairs = (int) ($data['chairs'] ?? 0);

        if ($tableNumber <= 0) {
            JsonView::render([
                'success' => false,
                'message' => 'El número de mesa debe ser mayor a 0.'
            ], 400);
            return;
        }

        if ($chairs <= 0) {
            JsonView::render([
                'success' => false,
                'message' => 'La cantidad de sillas debe ser mayor a 0.'
            ], 400);
            return;
        }

        $result = $this->tableModel->create(
            $restaurantId,
            $userId,
            $tableNumber,
            $details,
            $chairs
        );

        if (!$result['success']) {
            JsonView::render([
                'success' => false,
                'message' => $result['message']
            ], 400);
            return;
        }

        JsonView::render([
            'success' => true,
            'message' => 'Mesa creada correctamente.',
            'data' => [
                'table_id' => $result['table_id']
            ]
        ], 201);
    }

    // EDITAR mesa
    public function update($tableId, $userId)
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($data)) {
            JsonView::render([
                'success' => false,
                'message' => 'Datos inválidos.'
            ], 400);
            return;
        }

        $tableNumber = (int) ($data['table_number'] ?? 0);
        $details = trim($data['details'] ?? '');
        $chairs = (int) ($data['chairs'] ?? 0);

        if ($tableNumber <= 0 || $chairs <= 0) {
            JsonView::render([
                'success' => false,
                'message' =>
                'El número de mesa y la cantidad de sillas deben ser mayores a 0.'
            ], 400);
            return;
        }

        $result = $this->tableModel->update(
            $tableId,
            $userId,
            $tableNumber,
            $details,
            $chairs
        );

        if ($result === false) {
            JsonView::render([
                'success' => false,
                'message' => 'Mesa no encontrada.'
            ], 404);
            return;
        }

        if ($result === 'duplicate') {
            JsonView::render([
                'success' => false,
                'message' => 'Ya existe una mesa con ese número.'
            ], 400);
            return;
        }

        JsonView::render([
            'success' => true,
            'message' => 'Mesa actualizada correctamente.'
        ]);
    }

    // ELIMINAR mesa
    public function delete($tableId, $userId)
    {
        $deleted = $this->tableModel->delete(
            $tableId,
            $userId
        );

        if (!$deleted) {
            JsonView::render([
                'success' => false,
                'message' => 'Mesa no encontrada.'
            ], 404);
            return;
        }

        JsonView::render([
            'success' => true,
            'message' => 'Mesa eliminada correctamente.'
        ]);
    }

    // CAMBIO RÁPIDO DE ESTADO
    public function rotateStatus($tableId, $userId)
    {
        $result = $this->tableModel->rotateStatus(
            $tableId,
            $userId
        );

        if (!$result) {
            JsonView::render([
                'success' => false,
                'message' => 'Mesa no encontrada.'
            ], 404);
            return;
        }

        JsonView::render([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'data' => [
                'status_id' => $result['status_id'],
                'status' => $result['status']
            ]
        ]);
    }
}
