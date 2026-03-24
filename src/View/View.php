<?php

namespace Facil\View;

use Facil\Http\Response;
use Facil\Http\Security;

class View {
    /**
     * Defines the default directory where view files are stored.
     * By default, it looks for a 'views' folder at the root level (parallel to the 'public' folder).
     *
     * @var string|null
     */
    public static ?string $viewPath = null;

    /**
     * Renders a PHP/HTML view file and passes data to it.
     * Uses dot notation for subdirectories (e.g., 'users.profile' maps to 'users/profile.php').
     *
     * @param string $viewName The name of the view file without the .php extension.
     * @param array $data Associative array of data to be extracted into variables.
     * @return void
     */
    public static function render(string $viewName, array $data = []): void {
        // Set default path if not manually configured
        if (self::$viewPath === null) {
            // Assuming the entry point is public/index.php, dirname goes up to the root
            self::$viewPath = dirname($_SERVER['DOCUMENT_ROOT']) . '/views';
        }

        // Convert dot notation to directory separators
        $formattedViewName = str_replace('.', '/', $viewName);
        $filePath = self::$viewPath . '/' . $formattedViewName . '.php';

        if (!file_exists($filePath)) {
            Response::html("<h1>Framework Error: View [{$viewName}] not found.</h1><p>Looked in: {$filePath}</p>", 404);
        }

        // Extracts array keys as variables (e.g., ['title' => 'Facil'] becomes $title)
        extract($data);

        // Start output buffering to capture the included file's content
        ob_start();
        
        require $filePath;
        
        $htmlContent = ob_get_clean();

        // Output the final HTML using the Response class
        Response::html($htmlContent);
    }

    /**
     * Generates a hidden HTML input field containing the CSRF token.
     * Perfect for dropping directly into HTML forms.
     *
     * @return string The HTML input tag
     */
    public static function csrf(): string {
        $token = Security::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}