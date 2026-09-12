<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

class PublicController {
    public function listAvailable() {
        $model = new Restaurant();
        $data = $model->getPublicAvailability();
        JsonView::render($data, 200);
    }
}
?>