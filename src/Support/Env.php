<?php

namespace Facil\Support;

class Env {
    /**
     * Loads environment variables from a .env file.
     *
     * @param string $path The absolute path to the .env file
     * @return void
     */
    public static function load(string $path): void {
        if (!file_exists($path)) {
            return; // Silently ignore if .env is missing (e.g., in production using server vars)
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignore comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Split by the first equals sign
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove surrounding quotes if they exist
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // Only set if not already set by the server environment
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    /**
     * Retrieves an environment variable with an optional fallback default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}