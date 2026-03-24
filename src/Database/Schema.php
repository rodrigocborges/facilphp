<?php

namespace Facil\Database;

class Schema {
    /**
     * Creates a new table using the Blueprint builder.
     *
     * @param string $table The table name
     * @param callable $callback The closure containing the Blueprint definitions
     */
    public static function create(string $table, callable $callback): void {
        $blueprint = new Blueprint();
        $callback($blueprint);
        
        $columnsSql = $blueprint->build();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columnsSql})";
        
        Database::run($sql);
    }

    /**
     * Drops a table if it exists.
     */
    public static function drop(string $table): void {
        Database::run("DROP TABLE IF EXISTS {$table}");
    }
}