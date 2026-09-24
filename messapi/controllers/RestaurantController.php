<?php

require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../views/JsonView.php';

class RestaurantController
{
    private $restaurantModel;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
    }

    // ========================================
    // LISTAR RESTAURANTES DEL USUARIO
    // ========================================

    public function index($userId)
    {
        $restaurants =
            $this->restaurantModel->getAllByUser($userId);

        JsonView::render([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    // ========================================
    // VER UN RESTAURANTE
    // ========================================

    public function show($restaurantId, $userId)
    {
        $restaurant =
            $this->restaurantModel->getById(
                $restaurantId,
                $userId
            );

        if (!$restaurant) {

            JsonView::render([
                'success' => false,
                'message' => 'Restaurante no encontrado.'
            ], 404);

            return;
        }

        JsonView::render([
            'success' => true,
            'data' => $restaurant
        ]);
    }

    // ========================================
    // CREAR RESTAURANTE
    // ========================================

    public function create($userId)
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $name =
            trim($data['name'] ?? '');

        $address =
            trim($data['address'] ?? '');

        $city =
            trim($data['city'] ?? '');

        $phone =
            trim($data['phone'] ?? '');

        $description =
            trim($data['description'] ?? '');


        // ====================================
        // VALIDACIONES
        // ====================================

        if ($name === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'El nombre del restaurante es obligatorio.'
            ], 422);

            return;
        }


        if ($address === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'La dirección es obligatoria.'
            ], 422);

            return;
        }


        if ($city === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'La ciudad es obligatoria.'
            ], 422);

            return;
        }


        // ====================================
        // CREAR
        // ====================================

        $restaurantId =
            $this->restaurantModel->create(
                $userId,
                $name,
                $address,
                $city,
                $phone,
                $description
            );


        JsonView::render([
            'success' => true,
            'message' =>
            'Restaurante creado correctamente.',
            'data' => [
                'restaurant_id' =>
                (int) $restaurantId
            ]
        ], 201);
    }

    // ========================================
    // EDITAR RESTAURANTE
    // ========================================

    public function update($restaurantId, $userId)
    {
        // Comprobamos que el restaurante
        // pertenezca al usuario autenticado.

        $restaurant =
            $this->restaurantModel->getById(
                $restaurantId,
                $userId
            );


        if (!$restaurant) {

            JsonView::render([
                'success' => false,
                'message' =>
                'Restaurante no encontrado.'
            ], 404);

            return;
        }


        $data = json_decode(
            file_get_contents("php://input"),
            true
        );


        $name =
            trim($data['name'] ?? '');

        $address =
            trim($data['address'] ?? '');

        $city =
            trim($data['city'] ?? '');

        $phone =
            trim($data['phone'] ?? '');

        $description =
            trim($data['description'] ?? '');


        // ====================================
        // VALIDACIONES
        // ====================================

        if ($name === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'El nombre del restaurante es obligatorio.'
            ], 422);

            return;
        }


        if ($address === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'La dirección es obligatoria.'
            ], 422);

            return;
        }


        if ($city === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'La ciudad es obligatoria.'
            ], 422);

            return;
        }


        // ====================================
        // ACTUALIZAR
        // ====================================

        $this->restaurantModel->update(
            $restaurantId,
            $userId,
            $name,
            $address,
            $city,
            $phone,
            $description
        );


        JsonView::render([
            'success' => true,
            'message' =>
            'Restaurante actualizado correctamente.'
        ]);
    }

    // ========================================
    // ELIMINAR RESTAURANTE
    // ========================================

    public function delete($restaurantId, $userId)
    {
        $restaurant =
            $this->restaurantModel->getById(
                $restaurantId,
                $userId
            );


        if (!$restaurant) {

            JsonView::render([
                'success' => false,
                'message' =>
                'Restaurante no encontrado.'
            ], 404);

            return;
        }


        try {

            $deleted =
                $this->restaurantModel->delete(
                    $restaurantId,
                    $userId
                );


            if (!$deleted) {

                JsonView::render([
                    'success' => false,
                    'message' =>
                    'No fue posible eliminar el restaurante.'
                ], 400);

                return;
            }


            JsonView::render([
                'success' => true,
                'message' =>
                'Restaurante eliminado correctamente.'
            ]);
        } catch (PDOException $e) {

            JsonView::render([
                'success' => false,
                'message' =>
                'No se puede eliminar el restaurante porque tiene mesas asociadas.'
            ], 409);
        }
    }

    // ========================================
    // ABRIR / CERRAR RESTAURANTE
    // ========================================

    public function toggleStatus($restaurantId, $userId)
    {
        $restaurant =
            $this->restaurantModel->getById(
                $restaurantId,
                $userId
            );


        if (!$restaurant) {

            JsonView::render([
                'success' => false,
                'message' =>
                'Restaurante no encontrado.'
            ], 404);

            return;
        }


        $newStatus =
            $this->restaurantModel->toggleStatus(
                $restaurantId,
                $userId
            );


        $message =
            ((int) $newStatus === 1)
            ? 'Restaurante abierto.'
            : 'Restaurante cerrado.';


        JsonView::render([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_open' =>
                (int) $newStatus
            ]
        ]);
    }

    // ========================================
    // CONSULTA PÚBLICA
    // ========================================

    public function publicAvailability()
    {
        $restaurants =
            $this->restaurantModel
            ->getPublicAvailability();


        JsonView::render([
            'success' => true,
            'data' => $restaurants
        ]);
    }
}
