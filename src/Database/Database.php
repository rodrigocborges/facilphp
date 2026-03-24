<?php

namespace Facil\Database;

use PDO;
use PDOException;
use Exception;

class Database {
    /**
     * The active PDO connection instance.
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Initializes the database connection.
     * Should be called once, typically in your Front Controller (index.php).
     *
     * @param string $dsn Data Source Name (e.g., 'mysql:host=localhost;dbname=test' or 'sqlite:/path/to/db.sqlite')
     * @param string $username Database username
     * @param string $password Database password
     * @param array $options Additional PDO options
     * @return void
     */
    public static function connect(string $dsn, string $username = '', string $password = '', array $options = []): void {
        if (self::$pdo === null) {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Forces real prepared statements
            ];
            
            try {
                self::$pdo = new PDO($dsn, $username, $password, array_replace($defaultOptions, $options));
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Internal helper to prepare and execute statements safely.
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     * @throws Exception
     */
    private static function execute(string $sql, array $params = []): \PDOStatement {
        if (self::$pdo === null) {
            throw new Exception("Database not connected. Call Database::connect() first.");
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Executes a query that does not return rows (INSERT, UPDATE, DELETE).
     *
     * @param string $sql The SQL query with placeholders
     * @param array $params The values to bind
     * @return int The number of affected rows
     */
    public static function run(string $sql, array $params = []): int {
        $stmt = self::execute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Fetches a single row from the database.
     *
     * @param string $sql The SQL query with placeholders
     * @param array $params The values to bind
     * @return array|false Returns the row as an associative array, or false if not found
     */
    public static function get(string $sql, array $params = []) {
        $stmt = self::execute($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetches all matching rows from the database.
     *
     * @param string $sql The SQL query with placeholders
     * @param array $params The values to bind
     * @return array Returns an array of associative arrays
     */
    public static function all(string $sql, array $params = []): array {
        $stmt = self::execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Returns the ID of the last inserted row.
     *
     * @return string
     */
    public static function id(): string {
        return self::$pdo->lastInsertId();
    }
}