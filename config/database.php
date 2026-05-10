<?php
// FILE: /config/database.php

/**
 * Database Configuration
 *
 * Database connection settings for Splash360 Tours
 */

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

return [
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['DB_NAME'] ?? 'postgres',
    'username' => $_ENV['DB_USER'] ?? 'postgres',
    'password' => $_ENV['DB_PASS'] ?? '',
];
