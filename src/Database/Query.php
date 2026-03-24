<?php

namespace Facil\Database;

class Query {
    private string $table;
    private array $wheres = [];
    private array $params = [];
    private string $select = '*';
    private string $orderBy = '';
    private string $limit = '';

	/**
     * Stores the requested relationships.
     * @var array
     */
    private array $with = [];

    public function __construct(string $table) {
        $this->table = $table;
    }

    /**
     * Starts a new query builder for a specific table.
     */
    public static function table(string $table): self {
        return new self($table);
    }

    /**
     * Specifies the columns to select.
     */
    public function select(string $columns = '*'): self {
        $this->select = $columns;
        return $this;
    }

    /**
     * Adds a WHERE clause.
     * Can be used as: where('id', 1) OR where('age', '>', 18)
     */
    public function where(string $column, $operator, $value = null): self {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = "{$column} {$operator} ?";
        $this->params[] = $value;
        return $this;
    }

    /**
     * Adds an ORDER BY clause.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy = " ORDER BY {$column} {$direction}";
        return $this;
    }

    /**
     * Fetches all matching records.
     */
    public function get(): array {
        $sql = "SELECT {$this->select} FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $sql .= $this->orderBy . $this->limit;
        
        $results = Database::all($sql, $this->params);

        // Load relationships if requested!
        return $this->loadRelations($results);
    }

    /**
     * Fetches only the first matching record.
     */
    public function first(): ?array {
        $this->limit = " LIMIT 1";
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Inserts a new record and returns its ID.
     */
    public function insert(array $data): string {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        Database::run($sql, array_values($data));
        
        return Database::id();
    }

    /**
     * Updates matching records.
     */
    public function update(array $data): int {
        $set = [];
        $updateParams = [];
        foreach ($data as $col => $val) {
            $set[] = "{$col} = ?";
            $updateParams[] = $val;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        // Merge values to update with values from the WHERE clause
        $finalParams = array_merge($updateParams, $this->params);
        return Database::run($sql, $finalParams);
    }

    /**
     * Deletes matching records.
     */
    public function delete(): int {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        return Database::run($sql, $this->params);
    }

	/**
     * Counts the total number of records matching the current query.
     * Useful for pagination metadata.
     *
     * @return int
     */
    public function count(): int {
        $sql = "SELECT COUNT(*) as aggregate FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $result = Database::get($sql, $this->params);
        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Paginates the query results and returns data along with pagination metadata.
     * Ideal for returning directly in API responses.
     *
     * @param int $page The current page number (starts at 1)
     * @param int $pageSize The number of items per page
     * @return array Contains 'data' and 'meta' keys
     */
    public function paginate(int $page = 1, int $pageSize = 15): array {
        // Ensure valid positive numbers to prevent SQL errors
        $page = max(1, $page);
        $pageSize = max(1, $pageSize);

        // Get total records for metadata BEFORE applying LIMIT/OFFSET
        $total = $this->count();
        $lastPage = (int) ceil($total / $pageSize);

        // Calculate offset and apply limit
        $offset = ($page - 1) * $pageSize;
        $this->limit = " LIMIT {$pageSize} OFFSET {$offset}";

        // Fetch the actual data
        $data = $this->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'page_size' => $pageSize,
                'last_page' => $lastPage,
                'has_more' => $page < $lastPage
            ]
        ];
    }

    /**
     * Defines a related table to be fetched and attached to the results.
     *
     * @param string $table The related table name (e.g., 'posts')
     * @param string $foreignKey The column in the related table (e.g., 'user_id')
     * @param string $localKey The column in the current table (default: 'id')
     * @param string|null $relationName The array key for the result (defaults to table name)
     * @return self
     */
    public function with(string $table, string $foreignKey, string $localKey = 'id', ?string $relationName = null): self {
        $this->with[] = [
            'table' => $table,
            'foreign_key' => $foreignKey,
            'local_key' => $localKey,
            'name' => $relationName ?? $table
        ];
        return $this;
    }

    /**
     * Internal helper to attach related records to the main result set.
     * Uses a single IN (...) query per relationship for maximum performance.
     *
     * @param array $results The main query results
     * @return array
     */
    private function loadRelations(array $results): array {
        if (empty($results) || empty($this->with)) {
            return $results;
        }

        foreach ($this->with as $relation) {
            // 1. Extract all local keys (e.g., get all User IDs from the current page of results)
            $localKeys = array_unique(array_column($results, $relation['local_key']));
            if (empty($localKeys)) continue;

            // 2. Build the WHERE IN (?, ?, ?) placeholders
            $placeholders = implode(',', array_fill(0, count($localKeys), '?'));
            $sql = "SELECT * FROM {$relation['table']} WHERE {$relation['foreign_key']} IN ({$placeholders})";
            
            // 3. Fetch all related records in ONE single query
            $relatedRecords = Database::all($sql, array_values($localKeys));

            // 4. Group the related records by their foreign key for fast in-memory lookup
            $grouped = [];
            foreach ($relatedRecords as $record) {
                $fk = $record[$relation['foreign_key']];
                $grouped[$fk][] = $record;
            }

            // 5. Attach the grouped records back to the main results
            foreach ($results as &$row) {
                $lk = $row[$relation['local_key']];
                // If it's a one-to-one or one-to-many, we attach the array of matches
                $row[$relation['name']] = $grouped[$lk] ?? [];
            }
        }

        return $results;
    }
}