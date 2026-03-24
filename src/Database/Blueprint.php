<?php

namespace Facil\Database;

class Blueprint {
    private array $columns = [];
    private int $lastIndex = -1;

    /**
     * Adds an Auto-Increment Primary Key.
     */
    public function id(string $name = 'id'): self {
        $this->columns[] = "{$name} INTEGER PRIMARY KEY AUTOINCREMENT";
        $this->lastIndex = count($this->columns) - 1;
        return $this;
    }

    /**
     * Adds a TEXT column (in SQLite, VARCHAR is treated as TEXT).
     */
    public function string(string $name): self {
        $this->columns[] = "{$name} TEXT NOT NULL";
        $this->lastIndex = count($this->columns) - 1;
        return $this;
    }

    /**
     * Adds an INTEGER column.
     */
    public function integer(string $name): self {
        $this->columns[] = "{$name} INTEGER NOT NULL";
        $this->lastIndex = count($this->columns) - 1;
        return $this;
    }

    /**
     * Adds a BOOLEAN column (SQLite uses INTEGER 0 or 1).
     */
    public function boolean(string $name): self {
        $this->columns[] = "{$name} INTEGER NOT NULL DEFAULT 0";
        $this->lastIndex = count($this->columns) - 1;
        return $this;
    }

    /**
     * Makes the last added column nullable.
     */
    public function nullable(): self {
        if ($this->lastIndex >= 0) {
            $this->columns[$this->lastIndex] = str_replace(' NOT NULL', '', $this->columns[$this->lastIndex]);
        }
        return $this;
    }

    /**
     * Makes the last added column unique.
     */
    public function unique(): self {
        if ($this->lastIndex >= 0) {
            $this->columns[$this->lastIndex] .= " UNIQUE";
        }
        return $this;
    }

    /**
     * Adds created_at and updated_at timestamps.
     */
    public function timestamps(): self {
        $this->columns[] = "created_at DATETIME DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at DATETIME DEFAULT CURRENT_TIMESTAMP";
        return $this;
    }

    /**
     * Builds the final SQL string for the columns.
     */
    public function build(): string {
        return implode(', ', $this->columns);
    }
}