<?php
// Importamos los modelos y vistas necesarios
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

/**
 * Controlador Público
 * - Maneja las rutas que NO requieren autenticación.
 * - Se usa para que los clientes vean los restaurantes disponibles.
 */
class PublicController
{
    /**
     * Lista la disponibilidad de todos los restaurantes.
     * Se usa en la pantalla principal (vista pública).
     */
    public function listAvailable(): void
    {
        // Obtiene la lista y responde con un JSON (código 200 OK)
        JsonView::render(['data' => (new Restaurant())->getPublicAvailability()], 200);
    }

    /**
     * Trae los datos de un restaurante específico por su ID.
     * @param mixed $id - ID que llega por la URL
     */
    public function getRestaurant($id): void
    {
        // 1. Validamos que el ID sea un número entero mayor o igual a 1
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        
        // Si el ID no es válido (ej: "abc" o "-5"), devolvemos error 422
        if ($id === false) {
            JsonView::render(['message' => 'ID inválido'], 422);
        }

        // 2. Buscamos el restaurante en la base de datos
        $row = (new Restaurant())->getRestaurantById($id);
        
        // Si no existe, devolvemos error 404 (No encontrado)
        if ($row === null) {
            JsonView::render(['message' => 'El restaurante no existe'], 404);
        }

        // 3. Si todo está bien, devolvemos los datos (código 200 OK)
        JsonView::render(['data' => $row], 200);
    }
}