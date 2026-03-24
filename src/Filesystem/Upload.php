<?php

namespace Facil\Filesystem;

class Upload {
    public static function file(string $inputName, string $destination, array $allowedExt = ['jpg', 'png'], int $maxSize = 2048576): bool {
        if (!isset($_FILES[$inputName])) return false;
        
        $file = $_FILES[$inputName];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if ($file['size'] > $maxSize) return false;
        if (!in_array(strtolower($ext), $allowedExt)) return false;

        return move_uploaded_file($file['tmp_name'], $destination . '/' . basename($file['name']));
    }
}