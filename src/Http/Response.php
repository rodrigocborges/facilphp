<?php

namespace Facil\Http;

class Response {
    /**
     * Returns a JSON response and halts script execution.
     *
     * @param array $data The data to be encoded as JSON
     * @param HttpStatusCode|int $statusCode The HTTP status code
     * @return void
     */
    public static function json(array $data, HttpStatusCode|int $statusCode = HttpStatusCode::OK): void {
        // Resolve the integer value if an Enum is passed
        $code = $statusCode instanceof HttpStatusCode ? $statusCode->value : $statusCode;

        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Returns an HTML response and halts script execution.
     *
     * @param string $html The HTML content
     * @param HttpStatusCode|int $statusCode The HTTP status code
     * @return void
     */
    public static function html(string $html, HttpStatusCode|int $statusCode = HttpStatusCode::OK): void {
        $code = $statusCode instanceof HttpStatusCode ? $statusCode->value : $statusCode;

        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}