<?php
require_once __DIR__ . '/../controllers/TableController.php';
require_once __DIR__ . '/../controllers/PublicController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../views/JsonView.php';

class Router {
    public static function dispatch($method, $uri) {
        // --- ENDPOINT PÚBLICO (RF-13) ---
        if ($method === 'GET' && preg_match('/\/api\/public\/restaurants\/?$/', $uri)) {
            $controller = new PublicController();
            $controller->listAvailable();
            exit;
        }

        // --- ENDPOINT DE LOGIN ---
        if ($method === 'POST' && preg_match('/\/api\/login\/?$/', $uri)) {
            $controller = new AuthController();
            $controller->login();
            exit;
        }

        // --- ENDPOINTS PRIVADOS (Protegidos por JWT) ---
        if (preg_match('/\/api\/tables/', $uri)) {
            $jwt_user_id = AuthMiddleware::validateToken(); 
            
            // PATCH /api/tables/{id}/status (RF-12)
            if ($method === 'PATCH' && preg_match('/\/api\/tables\/([0-9]+)\/status\/?$/', $uri, $matches)) {
                $table_id = $matches[1];
                $controller = new TableController();
                $controller->rotateStatus($table_id, $jwt_user_id);
                exit;
            }
        }

        // 404 - Ruta no encontrada
        JsonView::render(['error' => 'Not Found - Endpoint no valido'], 404);
    }
}
?>