<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Jwt.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthController
{
    // ========================================
    // REGISTRO
    // ========================================

    public function register()
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

        $restaurantName = trim(
            $data['restaurant_name'] ?? ''
        );

        $address = trim(
            $data['address'] ?? ''
        );

        $phone = trim(
            $data['phone'] ?? ''
        );

        $email = trim(
            $data['email'] ?? ''
        );

        $password =
            $data['password'] ?? '';

        // ========================================
        // VALIDAR CAMPOS OBLIGATORIOS
        // ========================================

        if (
            $restaurantName === '' ||
            $address === '' ||
            $phone === '' ||
            $email === '' ||
            $password === ''
        ) {
            JsonView::render([
                'success' => false,
                'message' =>
                'Todos los campos son obligatorios.'
            ], 400);

            return;
        }

        // ========================================
        // VALIDAR EMAIL
        // ========================================

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            JsonView::render([
                'success' => false,
                'message' =>
                'El correo electrónico no es válido.'
            ], 400);

            return;
        }

        // ========================================
        // VALIDAR CONTRASEÑA
        // ========================================

        if (strlen($password) < 6) {
            JsonView::render([
                'success' => false,
                'message' =>
                'La contraseña debe tener al menos 6 caracteres.'
            ], 400);

            return;
        }

        // ========================================
        // REGISTRAR USUARIO
        // ========================================

        $userModel = new User();

        $result = $userModel->register(
            $restaurantName,
            $address,
            $phone,
            $email,
            $password
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
            'message' =>
            'Registro realizado correctamente.',
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

        if (!is_array($data)) {
            JsonView::render([
                'success' => false,
                'message' => 'Datos inválidos.'
            ], 400);

            return;
        }

        $email = trim(
            $data['email'] ?? ''
        );

        $password =
            $data['password'] ?? '';

        if (
            $email === '' ||
            $password === ''
        ) {
            JsonView::render([
                'success' => false,
                'message' =>
                'Email y contraseña son obligatorios.'
            ], 400);

            return;
        }

        // ========================================
        // BUSCAR USUARIO
        // ========================================

        $userModel = new User();

        $result = $userModel->login(
            $email,
            $password
        );

        if (!$result['success']) {
            JsonView::render([
                'success' => false,
                'message' =>
                $result['message']
            ], 401);

            return;
        }

        // ========================================
        // CREAR JWT
        // ========================================

        $token = Jwt::create(
            $result['user_id']
        );

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

                'email' =>
                $result['email'],

                'restaurant_id' =>
                $result['restaurant_id'],

                'restaurant_name' =>
                $result['restaurant_name']
            ]
        ], 200);
    }
}
