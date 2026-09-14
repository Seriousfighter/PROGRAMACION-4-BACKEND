<?php
require_once __DIR__ . '/../helpers/JwtHelper.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthMiddleware
{
    public static function userIdFromRequest(): int
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            JsonView::render(['message' => 'Token no proporcionado'], 401);
        }

        $payload = JwtHelper::decode(trim($m[1]));
        if ($payload === null || !isset($payload['sub'])) {
            JsonView::render(['message' => 'Token inválido o expirado'], 401);
        }

        $userId = (int) $payload['sub'];

        if (!(new User())->findById($userId)) {
            JsonView::render(['message' => 'Usuario no válido'], 401);
        }

        return $userId;
    }
}