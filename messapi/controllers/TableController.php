<?php
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../views/JsonView.php';

class TableController {
    public function rotateStatus($table_id, $user_id) {
        $model = new Table();
        $result = $model->rotateStatus($table_id, $user_id);

        if (isset($result['error'])) {
            JsonView::render(['message' => $result['error']], $result['code']);
        }

        JsonView::render([
            'message' => 'Estado de mesa actualizado correctamente',
            'data' => ['new_status_id' => $result['new_status_id']]
        ], 200);
    }
}
?>