<?php
// FILE: /app/helpers/functions.php

/**
 * Helper Functions
 *
 * Global helper functions for Splash360 Tours
 */

/**
 * Get configuration value
 *
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed
 */
function config($key, $default = null) {
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../../config/config.php';
    }

    return $config[$key] ?? $default;
}

/**
 * Escape HTML output
 *
 * @param string $string String to escape
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL
 *
 * @param string $path URL path
 * @return string
 */
function url($path = '') {
    $baseUrl = rtrim(config('app_url'), '/');
    $basePath = config('base_path');

    $path = ltrim($path, '/');

    if ($basePath) {
        return $baseUrl . '/' . trim($basePath, '/') . '/' . $path;
    }

    return $baseUrl . '/' . $path;
}

/**
 * Generate asset URL
 *
 * @param string $path Asset path
 * @return string
 */
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 *
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Redirect back
 */
function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    redirect($referer);
}

/**
 * Get old input value (for form repopulation)
 *
 * @param string $key Input key
 * @param mixed $default Default value
 * @return mixed
 */
function old($key, $default = '') {
    $session = new Session();
    $oldInput = $session->get('old_input', []);
    return $oldInput[$key] ?? $default;
}

/**
 * Get flash message
 *
 * @param string $type Message type
 * @return string|null
 */
function flash($type = null) {
    $session = new Session();
    return $session->getFlash($type);
}

/**
 * Check if flash message exists
 *
 * @param string $type Message type
 * @return bool
 */
function hasFlash($type = null) {
    $session = new Session();
    return $session->hasFlash($type);
}

/**
 * Get authenticated user
 *
 * @return array|null
 */
function auth() {
    $auth = new Auth();
    return $auth->user();
}

/**
 * Check if user is authenticated
 *
 * @return bool
 */
function isAuth() {
    $auth = new Auth();
    return $auth->check();
}

/**
 * Format date
 *
 * @param string $date Date string
 * @param string $format Date format
 * @return string
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (!$date) {
        return '';
    }

    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format file size
 *
 * @param int $bytes File size in bytes
 * @param int $precision Decimal precision
 * @return string
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Format currency
 *
 * @param float $amount Amount
 * @param string $currency Currency symbol
 * @return string
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Generate random string
 *
 * @param int $length String length
 * @return string
 */
function randomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Dump and die (for debugging)
 *
 * @param mixed $data Data to dump
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Get storage path — uses /tmp/storage when the project dir isn't writable (Vercel)
 *
 * @param string $path Relative path
 * @return string
 */
function storagePath($path = '') {
    $projectStorage = config('storage_path');

    // On Vercel the project filesystem is read-only; /tmp is the only writable dir.
    // Fall back transparently so uploads always land somewhere writable.
    if (!is_writable($projectStorage) && !is_writable(dirname($projectStorage))) {
        $base = '/tmp/storage';
    } else {
        $base = $projectStorage;
    }

    if ($path) {
        $full = $base . '/' . ltrim($path, '/');
        // Ensure parent directory exists
        $dir = dirname($full);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $full;
    }

    return $base;
}

/**
 * Get upload URL — always routes through the PHP storage server
 *
 * @param string $path Relative path from uploads directory
 * @return string
 */
function uploadUrl($path) {
    // If $path is already a full URL (e.g. Supabase CDN), return as-is
    if (strncmp($path, 'http', 4) === 0) {
        return $path;
    }
    return url('storage/uploads/' . ltrim($path, '/'));
}

/**
 * Truncate string
 *
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $append Append string
 * @return string
 */
function strLimit($string, $length = 100, $append = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }

    return substr($string, 0, $length) . $append;
}

/**
 * Create slug from string
 *
 * @param string $string String to slugify
 * @return string
 */
function slugify($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Check if current route matches
 *
 * @param string $route Route to check
 * @return bool
 */
function isRoute($route) {
    $currentUri = $_SERVER['REQUEST_URI'];
    $currentPath = parse_url($currentUri, PHP_URL_PATH);

    return strpos($currentPath, $route) !== false;
}

/**
 * Get active class for navigation
 *
 * @param string $route Route to check
 * @param string $class Class to return if active
 * @return string
 */
function activeRoute($route, $class = 'active') {
    return isRoute($route) ? $class : '';
}
