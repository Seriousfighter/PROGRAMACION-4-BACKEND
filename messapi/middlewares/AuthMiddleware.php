<?php
require_once __DIR__ . '/../utils/JwtHandler.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthMiddleware {
    public static function validateToken() {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            JsonView::render(['error' => 'Token no proporcionado o formato inválido'], 401);
        }

        $token = $matches[1];
        $payload = JwtHandler::decode($token);

        if (!$payload) {
            JsonView::render(['error' => 'Token inválido o expirado'], 401);
        }

        return $payload['user_id']; // Devuelve el ID del usuario autenticado
    }
}
?>