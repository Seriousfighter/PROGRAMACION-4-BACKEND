<?php

// ========================================
// MESSAPI - API REST
// ========================================

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Responder solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ========================================
// ARCHIVOS NECESARIOS
// ========================================

require_once __DIR__ . '/views/JsonView.php';

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RestaurantController.php';
require_once __DIR__ . '/controllers/TableController.php';

require_once __DIR__ . '/middlewares/AuthMiddleware.php';

// ========================================
// OBTENER MÉTODO Y RUTA
// ========================================

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$basePath = '/PROGRAMACION-4-BACKEND-PABLO/messapi';

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

$uri = '/' . trim($uri, '/');

// ========================================
// CONTROLADORES
// ========================================

$authController = new AuthController();
$restaurantController = new RestaurantController();
$tableController = new TableController();

// ========================================
// RUTAS PÚBLICAS
// ========================================

// HEALTH
if ($method === 'GET' && $uri === '/api/health') {

    JsonView::render([
        'success' => true,
        'message' => 'API de Messapi funcionando correctamente.'
    ]);

    exit;
}

// REGISTRO
if ($method === 'POST' && $uri === '/api/register') {

    $authController->register();

    exit;
}

// LOGIN
if ($method === 'POST' && $uri === '/api/login') {

    $authController->login();

    exit;
}

// CONSULTA PÚBLICA DE RESTAURANTES Y MESAS
if (
    $method === 'GET' &&
    $uri === '/api/public/restaurants'
) {

    $restaurantController->publicAvailability();

    exit;
}

// ========================================
// RUTAS PRIVADAS - RESTAURANTES
// ========================================

// LISTAR RESTAURANTES
if ($method === 'GET' && $uri === '/api/restaurants') {

    $userId = AuthMiddleware::authenticate();

    $restaurantController->index($userId);

    exit;
}

// CREAR RESTAURANTE
if ($method === 'POST' && $uri === '/api/restaurants') {

    $userId = AuthMiddleware::authenticate();

    $restaurantController->create($userId);

    exit;
}

// VER RESTAURANTE
if (
    $method === 'GET' &&
    preg_match(
        '#^/api/restaurants/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $restaurantController->show(
        $restaurantId,
        $userId
    );

    exit;
}

// EDITAR RESTAURANTE
if (
    $method === 'PUT' &&
    preg_match(
        '#^/api/restaurants/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $restaurantController->update(
        $restaurantId,
        $userId
    );

    exit;
}

// ELIMINAR RESTAURANTE
if (
    $method === 'DELETE' &&
    preg_match(
        '#^/api/restaurants/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $restaurantController->delete(
        $restaurantId,
        $userId
    );

    exit;
}

// ABRIR / CERRAR RESTAURANTE
if (
    $method === 'PATCH' &&
    preg_match(
        '#^/api/restaurants/(\d+)/status$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $restaurantController->toggleStatus(
        $restaurantId,
        $userId
    );

    exit;
}

// ========================================
// RUTAS PRIVADAS - MESAS
// ========================================

// LISTAR MESAS DE UN RESTAURANTE
if (
    $method === 'GET' &&
    preg_match(
        '#^/api/restaurants/(\d+)/tables$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $tableController->index(
        $restaurantId,
        $userId
    );

    exit;
}

// CREAR MESA
if (
    $method === 'POST' &&
    preg_match(
        '#^/api/restaurants/(\d+)/tables$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $restaurantId = (int) $matches[1];

    $tableController->create(
        $restaurantId,
        $userId
    );

    exit;
}

// VER UNA MESA
if (
    $method === 'GET' &&
    preg_match(
        '#^/api/tables/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $tableId = (int) $matches[1];

    $tableController->show(
        $tableId,
        $userId
    );

    exit;
}

// EDITAR MESA
if (
    $method === 'PUT' &&
    preg_match(
        '#^/api/tables/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $tableId = (int) $matches[1];

    $tableController->update(
        $tableId,
        $userId
    );

    exit;
}

// ELIMINAR MESA
if (
    $method === 'DELETE' &&
    preg_match(
        '#^/api/tables/(\d+)$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $tableId = (int) $matches[1];

    $tableController->delete(
        $tableId,
        $userId
    );

    exit;
}

// CAMBIO RÁPIDO DE ESTADO
if (
    $method === 'PATCH' &&
    preg_match(
        '#^/api/tables/(\d+)/status$#',
        $uri,
        $matches
    )
) {

    $userId = AuthMiddleware::authenticate();

    $tableId = (int) $matches[1];

    $tableController->rotateStatus(
        $tableId,
        $userId
    );

    exit;
}

// ========================================
// RUTA NO ENCONTRADA
// ========================================

JsonView::render([
    'success' => false,
    'message' => 'Ruta no encontrada.'
], 404);
