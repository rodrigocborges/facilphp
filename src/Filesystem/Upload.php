<?php

namespace Facil\Filesystem;

class Upload {
    /**
     * Securely handles a file upload, generating a unique random name.
     *
     * @param array|null $file The file array from Request::file('input_name')
     * @param string|null $destination Optional. Defaults to project_root/public/uploads
     * @param array $allowedExt Allowed file extensions
     * @param int $maxSize Max file size in bytes (default 2MB)
     * @return string|false Returns the saved file name on success, or false on failure.
     */
    public static function save(?array $file, ?string $destination = null, array $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'], int $maxSize = 2097152) {
        // 1. Check if file exists and has no PHP upload errors
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // 2. Validate Size
        if ($file['size'] > $maxSize) {
            return false;
        }

        // 3. Validate Extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return false;
        }

        // 4. Resolve default destination to public/uploads
        if ($destination === null) {
            $destination = dirname(__DIR__, 2) . '/public/uploads';
        }

        // 5. Generate a secure, unique filename
        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        
        // 6. Create destination directory if it doesn't exist
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $targetPath = rtrim($destination, '/') . '/' . $newName;

        // 7. Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $newName;
        }

        return false;
    }

    /**
     * Simple helper to delete a file from the server.
     *
     * @param string $path Absolute path to the file
     * @return bool
     */
    public static function delete(string $path): bool {
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Converts Megabytes to Bytes.
     * Extremely useful for setting the $maxSize parameter cleanly.
     *
     * @param float $megabytes
     * @return int
     */
    public static function mb(float $megabytes): int {
        return (int) ($megabytes * 1024 * 1024);
    }
}