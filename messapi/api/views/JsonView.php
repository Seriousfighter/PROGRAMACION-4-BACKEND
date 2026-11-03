<?php

class JsonView
{ // Esta clase se encarga de renderizar las respuestas en formato JSON para la API, incluyendo el manejo de códigos de estado HTTP y errores de codificación JSON.
    public static function render(mixed $data, int $statusCode = 200): void
    { // Renderiza la respuesta en formato JSON con el código de estado HTTP especificado. Si el código es 204, no se envía contenido.
        if ($statusCode === 204) {
            http_response_code(204);
            exit;
        }

        http_response_code($statusCode); // Establece el código de estado HTTP de la respuesta
        header('Content-Type: application/json; charset=utf-8');// Establece el encabezado de tipo de contenido a JSON con codificación UTF-8

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Codifica los datos a JSON sin escapar caracteres Unicode ni barras diagonales

        if ($json === false) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al codificar JSON']); // Si ocurre un error al codificar a JSON, respondemos con un error 500
            exit;
        }

        echo $json;
        exit;
    }
}