<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

class PublicController
{
    public function listAvailable(): void
    {
        JsonView::render(['data' => (new Restaurant())->getPublicAvailability()], 200);
    }

    public function getRestaurant($id): void
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) JsonView::render(['message' => 'ID inválido'], 422);

        $row = (new Restaurant())->getRestaurantById($id);
        if ($row === null) JsonView::render(['message' => 'El restaurante no existe'], 404);

        JsonView::render(['data' => $row], 200);
    }
}