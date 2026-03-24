<?php

namespace Facil\Support;

class Hash {
    /**
     * Hashes a string (usually a password) using the default PHP algorithm (bcrypt/argon2).
     *
     * @param string $value The raw string to hash
     * @return string
     */
    public static function make(string $value): string {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * Verifies that a given plain-text string matches a given hash.
     *
     * @param string $value The plain-text string
     * @param string $hash The hashed string to compare against
     * @return bool
     */
    public static function check(string $value, string $hash): bool {
        return password_verify($value, $hash);
    }
}