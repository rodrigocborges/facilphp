<?php

namespace Facil\Support;

use Facil\Database\Database;
use Facil\Support\Hash;

class Auth {
    /**
     * The database table where users are stored.
     * @var string
     */
    private static string $table = 'users';

    /**
     * The session key used to store the authenticated user's ID.
     * @var string
     */
    private static string $sessionKey = 'facil_auth_id';

    /**
     * In-memory cache of the authenticated user to prevent multiple DB queries per request.
     * @var array|null
     */
    private static ?array $userCache = null;

    /**
     * Ensures the PHP session is started before interacting with it.
     * @return void
     */
    private static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Allows overriding the default table name and session key.
     * Best called in the front controller (index.php) if your table is not 'users'.
     *
     * @param string $table
     * @param string $sessionKey
     * @return void
     */
    public static function config(string $table = 'users', string $sessionKey = 'facil_auth_id'): void {
        self::$table = $table;
        self::$sessionKey = $sessionKey;
    }

    /**
     * Attempts to authenticate a user using email and password.
     *
     * @param string $email
     * @param string $password
     * @param string $emailColumn The database column for email
     * @param string $passwordColumn The database column for the hashed password
     * @return bool Returns true if login was successful, false otherwise.
     */
    public static function attempt(string $email, string $password, string $emailColumn = 'email', string $passwordColumn = 'password'): bool {
        $user = Database::get("SELECT * FROM " . self::$table . " WHERE {$emailColumn} = ?", [$email]);

        if ($user && Hash::check($password, $user[$passwordColumn])) {
            self::login($user);
            return true;
        }

        return false;
    }

    /**
     * Manually logs a user into the application.
     *
     * @param array $user The user database record
     * @param string $idColumn The database column for the primary key
     * @return void
     */
    public static function login(array $user, string $idColumn = 'id'): void {
        self::startSession();
        
        // Prevent Session Fixation attacks
        session_regenerate_id(true); 
        
        $_SESSION[self::$sessionKey] = $user[$idColumn];
        self::$userCache = $user;
    }

    /**
     * Logs the currently authenticated user out.
     *
     * @return void
     */
    public static function logout(): void {
        self::startSession();
        unset($_SESSION[self::$sessionKey]);
        session_destroy();
        self::$userCache = null;
    }

    /**
     * Checks if there is an authenticated user.
     *
     * @return bool
     */
    public static function check(): bool {
        self::startSession();
        return isset($_SESSION[self::$sessionKey]);
    }

    /**
     * Retrieves the authenticated user's primary ID.
     *
     * @return mixed|null
     */
    public static function id() {
        self::startSession();
        return $_SESSION[self::$sessionKey] ?? null;
    }

    /**
     * Retrieves the full database record of the authenticated user.
     *
     * @param string $idColumn The database column for the primary key
     * @return array|null Returns the user array, or null if not logged in.
     */
    public static function user(string $idColumn = 'id'): ?array {
        if (!self::check()) {
            return null;
        }

        // Return from memory if already fetched during this request lifecycle
        if (self::$userCache !== null) {
            return self::$userCache;
        }

        $id = self::id();
        self::$userCache = Database::get("SELECT * FROM " . self::$table . " WHERE {$idColumn} = ?", [$id]);

        // If user was deleted from DB but session still exists, log them out
        if (!self::$userCache) {
            self::logout();
            return null;
        }

        return self::$userCache;
    }
}