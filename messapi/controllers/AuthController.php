<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../views/JsonView.php';
require_once __DIR__ . '/../utils/JwtHandler.php';

class AuthController {
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            JsonView::render(['error' => 'Email y password requeridos'], 400);
        }

        $userModel = new User();
        $user = $userModel->login($data['email'], $data['password']);

        if ($user) {
            // El token durará 2 horas (7200 segundos)
            $payload = [
                'user_id' => $user['id'],
                'name' => $user['name'],
                'exp' => time() + 7200 
            ];
            $jwt = JwtHandler::encode($payload);
            JsonView::render(['message' => 'Login exitoso', 'token' => $jwt], 200);
        } else {
            JsonView::render(['error' => 'Credenciales inválidas'], 401);
        }
    }
}
?>