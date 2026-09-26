<?php
require_once __DIR__ . '/../controllers/TableController.php';
require_once __DIR__ . '/../controllers/PublicController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
// Agregamos el controlador de Restaurantes que vas a necesitar
require_once __DIR__ . '/../controllers/RestaurantController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../views/JsonView.php';

class Router {
    public static function dispatch($method, $uri) {
        // --- HABILITAR CORS ---
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Manejar las peticiones pre-flight de seguridad del navegador
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // ==========================================
        // 1. RUTAS PÚBLICAS (No requieren Token JWT)
        // ==========================================

        if ($method === 'GET' && preg_match('/\/api\/public\/restaurants\/?$/', $uri)) {
            $controller = new PublicController();
            $controller->listAvailable();
            exit;
        }

        if ($method === 'POST' && preg_match('/\/api\/login\/?$/', $uri)) {
            $controller = new AuthController();
            $controller->login();
            exit;
        }

        if ($method === 'POST' && preg_match('/\/api\/register\/?$/', $uri)) {
            $controller = new AuthController();
            $controller->register(); // Método a crear en AuthController
            exit;
        }

        // ==========================================
        // 2. RUTAS PRIVADAS (Protegidas por JWT)
        // ==========================================
        
        // Si la ruta empieza con /api/ y NO es login, register o public, exigimos token
        if (preg_match('/^\/api\/(?!login|register|public)/', $uri)) {
            
            // Validamos el token una sola vez para todas estas rutas
            $jwt_user_id = AuthMiddleware::validateToken(); 

            // --- AUTH ---
            if ($method === 'POST' && preg_match('/\/api\/logout\/?$/', $uri)) {
                $controller = new AuthController();
                $controller->logout();
                exit;
            }
            if ($method === 'GET' && preg_match('/\/api\/me\/?$/', $uri)) {
                $controller = new AuthController();
                $controller->me($jwt_user_id);
                exit;
            }

            // --- CRUD RESTAURANTES ---
            if ($method === 'GET' && preg_match('/\/api\/restaurants\/?$/', $uri)) {
                $controller = new RestaurantController();
                $controller->index($jwt_user_id);
                exit;
            }
            if ($method === 'POST' && preg_match('/\/api\/restaurants\/?$/', $uri)) {
                $controller = new RestaurantController();
                $controller->store($jwt_user_id);
                exit;
            }
            if ($method === 'GET' && preg_match('/\/api\/restaurants\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new RestaurantController();
                $controller->show($matches[1], $jwt_user_id);
                exit;
            }
            if ($method === 'PUT' && preg_match('/\/api\/restaurants\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new RestaurantController();
                $controller->update($matches[1], $jwt_user_id);
                exit;
            }
            if ($method === 'DELETE' && preg_match('/\/api\/restaurants\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new RestaurantController();
                $controller->destroy($matches[1], $jwt_user_id);
                exit;
            }

            // --- MESAS ASIGNADAS A UN RESTAURANTE ---
            if ($method === 'GET' && preg_match('/\/api\/restaurants\/([0-9]+)\/tables\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->indexByRestaurant($matches[1], $jwt_user_id);
                exit;
            }
            if ($method === 'POST' && preg_match('/\/api\/restaurants\/([0-9]+)\/tables\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->store($matches[1], $jwt_user_id);
                exit;
            }

            // --- CRUD MESAS INDIVIDUALES ---
            if ($method === 'GET' && preg_match('/\/api\/tables\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->show($matches[1], $jwt_user_id);
                exit;
            }
            if ($method === 'PUT' && preg_match('/\/api\/tables\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->update($matches[1], $jwt_user_id);
                exit;
            }
            if ($method === 'DELETE' && preg_match('/\/api\/tables\/([0-9]+)\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->destroy($matches[1], $jwt_user_id);
                exit;
            }
            
            // RF-12: Cambiar estado (El que ya tenías)
            if ($method === 'PATCH' && preg_match('/\/api\/tables\/([0-9]+)\/status\/?$/', $uri, $matches)) {
                $controller = new TableController();
                $controller->rotateStatus($matches[1], $jwt_user_id);
                exit;
            }
        }

        // ==========================================
        // 3. RUTAS POR DEFECTO Y ERRORES
        // ==========================================

        if($method === 'GET' && $uri === '/')  { 
            JsonView::render(['message' => 'API de mesas disponibles v1.0. Revisa la documentación para los endpoints.'], 200);
            exit;
        }

        // 404 - Ruta no encontrada
        JsonView::render(['error' => 'Not Found - Endpoint no válido'], 404);
    }
}
?>