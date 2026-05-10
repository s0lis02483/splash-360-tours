<?php
// FILE: /config/config.php

/**
 * Application Configuration
 *
 * Main configuration file for Splash360 Tours
 */

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

return [
    // Application settings
    'app_name' => 'Splash360 Tours',
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'app_env' => $_ENV['APP_ENV'] ?? 'development',
    'app_debug' => $_ENV['APP_DEBUG'] ?? true,

    // Timezone
    'timezone' => 'UTC',

    // Session settings
    'session_lifetime' => 7200, // 2 hours in seconds

    // Upload settings
    // On Vercel (read-only filesystem), writes go to /tmp/storage which is writable.
    // On traditional hosts, writes go to storage/ inside the project.
    'upload_path' => __DIR__ . '/../storage/uploads',
    'max_upload_size' => 52428800, // 50MB (supports large 360° panoramic images)
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowed_document_types' => ['pdf', 'doc', 'docx'],

    // Pagination
    'per_page' => 20,

    // API settings
    'api_enabled' => true,

    // Email settings (simulated for now)
    'mail_from' => $_ENV['MAIL_FROM'] ?? 'noreply@splash360tours.com',
    'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Splash360 Tours',

    // Storage
    'storage_path' => __DIR__ . '/../storage',

    // Base path for routing
    'base_path' => $_ENV['BASE_PATH'] ?? '',
];
