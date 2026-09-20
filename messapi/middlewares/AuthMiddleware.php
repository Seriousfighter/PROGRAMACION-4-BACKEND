<?php

require_once __DIR__ . '/../config/Jwt.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthMiddleware
{
    public static function authenticate()
    {
        $authorizationHeader = null;

        // Buscar Authorization en los headers
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $authorizationHeader = $value;
                    break;
                }
            }
        }

        // Alternativa para Apache / XAMPP
        if (
            !$authorizationHeader &&
            isset($_SERVER['HTTP_AUTHORIZATION'])
        ) {
            $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        // No llegó token
        if (!$authorizationHeader) {
            JsonView::render([
                'success' => false,
                'message' => 'No autorizado. Token requerido.'
            ], 401);

            exit;
        }

        // Formato esperado: Bearer TOKEN
        if (!preg_match(
            '/Bearer\s+(.+)/i',
            $authorizationHeader,
            $matches
        )) {
            JsonView::render([
                'success' => false,
                'message' => 'Token no válido.'
            ], 401);

            exit;
        }

        $token = trim($matches[1]);

        // Verificar token
        $payload = Jwt::verify($token);

        if (!$payload) {
            JsonView::render([
                'success' => false,
                'message' => 'Token inválido o vencido.'
            ], 401);

            exit;
        }

        return (int) $payload['sub'];
    }
}
