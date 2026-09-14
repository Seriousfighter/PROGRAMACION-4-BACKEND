<?php

class JsonView
{
    public static function render($data, int $statusCode = 200): void
    {
        if ($statusCode === 204) {
            http_response_code(204);
            exit;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al codificar JSON']);
            exit;
        }

        echo $json;
        exit;
    }
}