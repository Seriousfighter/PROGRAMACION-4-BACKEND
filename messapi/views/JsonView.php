<?php

class JsonView
{
    public static function render($data, $statusCode = 200)
    {
        http_response_code($statusCode);

        header("Content-Type: application/json; charset=UTF-8");

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}