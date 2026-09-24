<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Jwt.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ========================================
    // REGISTRAR RESTAURANTE
    // ========================================

    public function register()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        // ========================================
        // OBTENER DATOS
        // ========================================

        $restaurantName =
            trim($data['restaurant_name'] ?? '');

        $address =
            trim($data['address'] ?? '');

        $city =
            trim($data['city'] ?? '');

        $phone =
            trim($data['phone'] ?? '');

        $description =
            trim($data['description'] ?? '');

        $email =
            trim($data['email'] ?? '');

        $password =
            $data['password'] ?? '';

        // ========================================
        // VALIDACIONES
        // ========================================

        if ($restaurantName === '') {

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

        if ($phone === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'El teléfono es obligatorio.'
            ], 422);

            return;
        }

        if ($email === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'El correo electrónico es obligatorio.'
            ], 422);

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            JsonView::render([
                'success' => false,
                'message' =>
                'El correo electrónico no es válido.'
            ], 422);

            return;
        }

        if ($password === '') {

            JsonView::render([
                'success' => false,
                'message' =>
                'La contraseña es obligatoria.'
            ], 422);

            return;
        }

        if (strlen($password) < 6) {

            JsonView::render([
                'success' => false,
                'message' =>
                'La contraseña debe tener al menos 6 caracteres.'
            ], 422);

            return;
        }

        // ========================================
        // REGISTRAR USUARIO Y RESTAURANTE
        // ========================================

        $result =
            $this->userModel->register(
                $restaurantName,
                $address,
                $city,
                $phone,
                $description,
                $email,
                $password
            );

        // ========================================
        // VERIFICAR RESULTADO
        // ========================================

        if (!$result['success']) {

            JsonView::render([
                'success' => false,
                'message' =>
                $result['message']
            ], 400);

            return;
        }

        // ========================================
        // REGISTRO CORRECTO
        // ========================================

        JsonView::render([
            'success' => true,

            'message' =>
            'Restaurante registrado correctamente.',

            'data' => [

                'user_id' =>
                $result['user_id'],

                'restaurant_id' =>
                $result['restaurant_id']
            ]

        ], 201);
    }

    // ========================================
    // LOGIN
    // ========================================

    public function login()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        // ========================================
        // OBTENER DATOS
        // ========================================

        $email =
            trim($data['email'] ?? '');

        $password =
            $data['password'] ?? '';

        // ========================================
        // VALIDACIONES
        // ========================================

        if (
            $email === '' ||
            $password === ''
        ) {

            JsonView::render([
                'success' => false,
                'message' =>
                'Correo y contraseña son obligatorios.'
            ], 422);

            return;
        }

        // ========================================
        // BUSCAR USUARIO
        // ========================================

        $result =
            $this->userModel->login(
                $email,
                $password
            );

        // ========================================
        // VERIFICAR LOGIN
        // ========================================

        if (!$result['success']) {

            JsonView::render([
                'success' => false,
                'message' =>
                $result['message']
            ], 401);

            return;
        }

        // ========================================
        // CREAR TOKEN JWT
        // ========================================
        //
        // IMPORTANTE:
        // Nuestro Jwt.php utiliza Jwt::create()
        // y recibe solamente el ID del usuario.
        // ========================================

        $token =
            Jwt::create(
                $result['user_id']
            );

        // ========================================
        // LOGIN CORRECTO
        // ========================================

        JsonView::render([
            'success' => true,

            'message' =>
            'Inicio de sesión correcto.',

            'data' => [

                'token' =>
                $token,

                'user_id' =>
                $result['user_id'],

                'user_name' =>
                $result['user_name'],

                'restaurant_id' =>
                $result['restaurant_id'],

                'restaurant_name' =>
                $result['restaurant_name']
            ]
        ]);
    }
}
