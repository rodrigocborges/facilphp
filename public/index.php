<?php

require __DIR__ . '/../vendor/autoload.php';

use Facil\Routing\Router;
use Facil\Support\Env;
use Facil\Http\Security;
use Facil\Database\Database;

// 1. Load Environment Variables
Env::load(__DIR__ . '/../.env');

// 2. Apply Global Security Headers
Security::setHeaders();

// 3. Apply Global CORS (Optional: Remove if it's a closed Fullstack App, keep if it's an API)
Security::cors(Env::get('CORS_ORIGIN', '*'));

$dbPath = Env::get('DB_DSN', '../database/app.sqlite');

// Connect using the SQLite DSN (No username or password needed)
Database::connect("sqlite:{$dbPath}");

// Optional: Enable foreign keys for SQLite (disabled by default in SQLite)
Database::run("PRAGMA foreign_keys = ON;");

// Boot the application and dispatch the request
Router::dispatch();