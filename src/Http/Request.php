<?php

namespace Facil\Http;

class Request {
    /**
     * Stores dynamic URL parameters parsed by the Router (e.g., [id]).
     * * @var array
     */
    private static array $params = [];

    /**
     * Injects URL parameters into the Request context.
     * This method is called automatically by the Router during dispatch.
     * * @param array $params
     * @return void
     */
    public static function setParams(array $params): void {
        self::$params = $params;
    }

    /**
     * Retrieves a specific dynamic URL parameter.
     * Returns a fallback value if the parameter does not exist.
     * * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function param(string $key, $default = null) {
        return self::$params[$key] ?? $default;
    }

    /**
     * Retrieves all dynamic route parameters at once.
     * * @return array
     */
    public static function allParams(): array {
        return self::$params;
    }

    /**
     * Retrieves all HTTP request headers.
     * Includes a fallback for environments where getallheaders() is unavailable (e.g., Nginx + PHP-FPM).
     * * @return array
     */
    public static function headers(): array {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $formattedName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$formattedName] = $value;
            }
        }
        return $headers;
    }

    /**
     * Retrieves the parsed request body.
     * Automatically handles JSON payloads and falls back to standard form-data ($_POST).
     * * @return array
     */
    public static function body(): array {
        $json = file_get_contents('php://input');
        $decoded = json_decode($json, true);
        
        return $decoded ?? $_POST;
    }

    /**
     * Retrieves data from the query string (e.g., ?key=value).
     * Returns the entire $_GET array if no key is provided.
     * * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function query(?string $key = null, $default = null) {
        if ($key) {
            return $_GET[$key] ?? $default;
        }
        return $_GET;
    }

    /**
     * Verifies the CSRF token from the current request.
     * It checks the request body first, then falls back to the HTTP headers (useful for AJAX/Fetch).
     *
     * @return bool Returns true if the token is valid, false otherwise.
     */
    public static function verifyCsrf(): bool {
        $body = self::body();
        $headers = self::headers();

        // 1. Try to get the token from standard form submission
        $token = $body['csrf_token'] ?? null;

        // 2. Fallback to headers for AJAX/Fetch requests
        if (!$token) {
            // HTTP headers are often case-insensitive or transformed by the server
            $token = $headers['X-CSRF-TOKEN'] ?? $headers['X-Csrf-Token'] ?? null;
        }

        if (!$token) {
            return false;
        }

        return Security::verifyCsrf($token);
    }
}