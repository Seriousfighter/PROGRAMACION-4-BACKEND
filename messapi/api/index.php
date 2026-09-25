<?php

require_once __DIR__ . '/views/JsonView.php';
require_once __DIR__ . '/helpers/JwtHelper.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RestaurantController.php';
require_once __DIR__ . '/controllers/TableController.php';
require_once __DIR__ . '/controllers/PublicController.php';

// -------- CORS --------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    // ===================== PÚBLICO =====================

    // POST /api/register
    if ($method === 'POST' && preg_match('#/api/register/?$#', $uri)) {
        (new AuthController())->register();
    }

    // POST /api/login
    if ($method === 'POST' && preg_match('#/api/login/?$#', $uri)) {
        (new AuthController())->login();
    }

    // GET /api/public/restaurants/{id}
    if ($method === 'GET' && preg_match('#/api/public/restaurants/([0-9]+)/?$#', $uri, $m)) {
        (new PublicController())->getRestaurant($m[1]);
    }

    // GET /api/public/restaurants
    if ($method === 'GET' && preg_match('#/api/public/restaurants/?$#', $uri)) {
        (new PublicController())->listAvailable();
    }

    // ===================== PRIVADO (JWT obligatorio) =====================
    $userId = AuthMiddleware::userIdFromRequest();

    // POST /api/logout
    if ($method === 'POST' && preg_match('#/api/logout/?$#', $uri)) {
        (new AuthController())->logout();
    }

    // GET /api/me
    if ($method === 'GET' && preg_match('#/api/me/?$#', $uri)) {
        (new AuthController())->me($userId);
    }

    // GET /api/table-statuses
    if ($method === 'GET' && preg_match('#/api/table-statuses/?$#', $uri)) {
        (new TableController())->listStatuses();
    }

    // ----- Restaurantes -----

    // GET /api/restaurants/{id}/tables
    if ($method === 'GET' && preg_match('#/api/restaurants/([0-9]+)/tables/?$#', $uri, $m)) {
        (new RestaurantController())->listTables((int) $m[1], $userId);
    }

    // POST /api/restaurants/{id}/tables
    if ($method === 'POST' && preg_match('#/api/restaurants/([0-9]+)/tables/?$#', $uri, $m)) {
        (new RestaurantController())->createTable((int) $m[1], $userId);
    }

    // GET /api/restaurants
    if ($method === 'GET' && preg_match('#/api/restaurants/?$#', $uri)) {
        (new RestaurantController())->list($userId);
    }

    // POST /api/restaurants
    if ($method === 'POST' && preg_match('#/api/restaurants/?$#', $uri)) {
        (new RestaurantController())->create($userId);
    }

    // GET /api/restaurants/{id}
    if ($method === 'GET' && preg_match('#/api/restaurants/([0-9]+)/?$#', $uri, $m)) {
        (new RestaurantController())->getById((int) $m[1], $userId);
    }

    // PUT /api/restaurants/{id}
    if ($method === 'PUT' && preg_match('#/api/restaurants/([0-9]+)/?$#', $uri, $m)) {
        (new RestaurantController())->update((int) $m[1], $userId);
    }

    // DELETE /api/restaurants/{id}
    if ($method === 'DELETE' && preg_match('#/api/restaurants/([0-9]+)/?$#', $uri, $m)) {
        (new RestaurantController())->delete((int) $m[1], $userId);
    }

    // ----- Mesas -----

    // PATCH /api/tables/{id}/status
    if ($method === 'PATCH' && preg_match('#/api/tables/([0-9]+)/status/?$#', $uri, $m)) {
        (new TableController())->rotateStatus((int) $m[1]);
    }

    // GET /api/tables/{id}
    if ($method === 'GET' && preg_match('#/api/tables/([0-9]+)/?$#', $uri, $m)) {
        (new TableController())->getById((int) $m[1], $userId);
    }

    // PUT /api/tables/{id}
    if ($method === 'PUT' && preg_match('#/api/tables/([0-9]+)/?$#', $uri, $m)) {
        (new TableController())->update((int) $m[1], $userId);
    }

    // DELETE /api/tables/{id}
    if ($method === 'DELETE' && preg_match('#/api/tables/([0-9]+)/?$#', $uri, $m)) {
        (new TableController())->delete((int) $m[1], $userId);
    }

    // ===================== 404 =====================
    JsonView::render([
        'message' => 'Endpoint no válido',
        'path'    => $uri,
        'method'  => $method,
    ], 404);

} catch (Throwable $e) {
    error_log('[UNCAUGHT] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    JsonView::render(['message' => 'Error interno del servidor'], 500);
}