<?php

namespace Facil\Support;

class Str {
    /**
     * Generates a URL-friendly "slug" from a given string.
     *
     * @param string $string The string to convert
     * @param string $separator The separator to use (default is '-')
     * @return string
     */
    public static function slug(string $string, string $separator = '-'): string {
        // Replace non-letter or digits by separator
        $string = preg_replace('~[^\pL\d]+~u', $separator, $string);
        // Transliterate
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        // Remove unwanted characters
        $string = preg_replace('~[^-\w]+~', '', $string);
        // Trim
        $string = trim($string, $separator);
        // Remove duplicate separators
        $string = preg_replace('~-+~', $separator, $string);
        // Lowercase
        return strtolower($string);
    }

    /**
     * Generates a cryptographically secure random string.
     *
     * @param int $length The length of the string
     * @return string
     */
    public static function random(int $length = 16): string {
        $bytes = random_bytes(ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $string
     * @return string
     */
    public static function camel(string $string): string {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));
        return lcfirst(str_replace(' ', '', $string));
    }
}