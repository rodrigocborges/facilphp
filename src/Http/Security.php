<?php

namespace Facil\Http;

class Security {
    /**
     * Injects standard HTTP security headers to protect against XSS, Clickjacking, and MIME-sniffing.
     * Should be called early in the Front Controller.
     *
     * @return void
     */
    public static function setHeaders(): void {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY'); // Prevents the site from being embedded in an iframe
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // Forces HTTPS
    }

    /**
     * Configures Cross-Origin Resource Sharing (CORS) for API endpoints.
     *
     * @param string $allowedOrigin The domain allowed to access the API (use '*' for public APIs)
     * @return void
     */
    public static function cors(string $allowedOrigin = '*'): void {
        header("Access-Control-Allow-Origin: {$allowedOrigin}");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        // Handle preflight requests automatically
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Generates and returns a CSRF token for forms (Fullstack mode).
     *
     * @return string
     */
    public static function csrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifies if a provided CSRF token matches the session token.
     *
     * @param string $token
     * @return bool
     */
    public static function verifyCsrf(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}