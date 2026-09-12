<?php
require_once __DIR__ . '/views/JsonView.php';
require_once __DIR__ . '/controllers/TableController.php';
require_once __DIR__ . '/controllers/PublicController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// --- ENDPOINT PÚBLICO (RF-13) ---
// Quitamos el '^' inicial para que coincida independientemente de la subcarpeta (/messapi/)
if ($method === 'GET' && preg_match('/\/api\/public\/restaurants\/?$/', $uri)) {
    $controller = new PublicController();
    $controller->listAvailable();
    exit;
}

// --- ENDPOINTS PRIVADOS ---
$jwt_user_id = 1; 

// PATCH /api/tables/{id}/status (RF-12)
if ($method === 'PATCH' && preg_match('/\/api\/tables\/([0-9]+)\/status\/?$/', $uri, $matches)) {
    $table_id = $matches[1];
    $controller = new TableController();
    $controller->rotateStatus($table_id, $jwt_user_id);
    exit;
}

// Si la ruta no coincide, devolvemos la ruta exacta para que veas qué está leyendo PHP
JsonView::render([
    'error' => 'Not Found - Endpoint no valido',
    'debug_ruta_recibida' => $uri
], 404);
?>