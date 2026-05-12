<?php
// FILE: /public/index.php

/**
 * Splash360 Tours - Entry Point
 *
 * Main application entry point
 * Handles all HTTP requests and routes them to appropriate controllers
 */

// Set timezone
date_default_timezone_set('UTC');

// Load configuration
$config = require __DIR__ . '/../config/config.php';

// Set timezone from config
date_default_timezone_set($config['timezone']);

// ============================================================
// ERROR DISPLAY — never leak stack traces in production
// In production we still LOG errors (Vercel captures these)
// but never echo file paths or query strings to the browser.
// ============================================================
$isDebug = !empty($config['app_debug']) && $config['app_debug'] !== 'false';
error_reporting(E_ALL);
ini_set('log_errors', 1);
if ($isDebug) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    set_exception_handler(function ($e) {
        error_log('Uncaught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) http_response_code(500);
        echo '<!doctype html><meta charset=utf-8><title>Server error</title>'
           . '<div style="font-family:system-ui;padding:48px;max-width:480px;margin:0 auto;color:#222;">'
           . '<h1 style="font-weight:400;">Something went wrong</h1>'
           . '<p>Sorry — please try again. If the problem persists, contact support.</p>'
           . '</div>';
    });
}

// ============================================================
// SECURITY HEADERS — set before any output
// ============================================================
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');                    // clickjacking
    header('X-Content-Type-Options: nosniff');                // MIME sniffing
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(self)');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    // CSP: allow self + Pannellum CDN + Supabase storage CDN + Google Fonts
    header("Content-Security-Policy: "
        . "default-src 'self'; "
        . "img-src 'self' data: blob: https:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
        . "font-src 'self' https://fonts.gstatic.com data:; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "connect-src 'self' https://*.supabase.co; "
        . "frame-ancestors 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self'");
}

// ============================================================
// SECURE SESSION COOKIES — runtime hardening
// ============================================================
$secureCookies = (
    !empty($_SERVER['HTTPS']) ||
    ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $secureCookies ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/DatabaseSessionHandler.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Request.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/Upload.php';
require_once __DIR__ . '/../app/core/SupabaseStorage.php';
require_once __DIR__ . '/../app/core/CSRF.php';

// Load helpers
require_once __DIR__ . '/../app/helpers/functions.php';

// ============================================================
// AUTOLOAD MODELS
// Any class referenced as `new SomeModel()` will be loaded
// from /app/models/SomeModel.php on demand. Saves us from
// chasing missing require_once statements across controllers.
// ============================================================
spl_autoload_register(function ($class) {
    // Skip core classes (already loaded above) and namespaced classes
    if (strpos($class, '\\') !== false) return;
    $modelFile = __DIR__ . '/../app/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
});

// ============================================================
// STORAGE FILE SERVING
// Serve uploaded files (scenes, property images) via PHP
// On Vercel, uploaded files go to /tmp; here we serve them.
// ============================================================
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strncmp($requestUri, '/storage/', 9) === 0) {
    $relativePath = substr($requestUri, 9); // strip leading '/storage/'
    $relativePath = ltrim($relativePath, '/');
    $relativePath = str_replace('..', '', $relativePath); // security: no traversal

    // Try project storage first, then /tmp (Vercel writable)
    $candidates = [
        $config['storage_path'] . '/' . $relativePath,
        '/tmp/storage/' . $relativePath,
    ];

    $served = false;
    foreach ($candidates as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeMap = [
                'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png'  => 'image/png',  'gif'  => 'image/gif',
                'webp' => 'image/webp', 'pdf'  => 'application/pdf',
            ];
            $mime = $mimeMap[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=2592000');
            readfile($filePath);
            $served = true;
            break;
        }
    }

    if (!$served) {
        http_response_code(404);
        echo 'File not found';
    }
    exit;
}

// Initialize router
$router = new Router();

// ============================================================
// PUBLIC ROUTES
// ============================================================

// Home (redirect to login)
$router->get('/', 'AuthController@showLogin', 'home');

// Authentication routes
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login', 'login.post');
$router->get('/register', 'AuthController@showRegister', 'register');
$router->post('/register', 'AuthController@register', 'register.post');
$router->get('/logout', 'AuthController@logout', 'logout');

// Public tour view
$router->get('/tour/{slug}', 'TourController@viewPublic', 'tour.public');

// ============================================================
// API ROUTES
// ============================================================

$router->get('/api/health', 'ApiController@health', 'api.health');
$router->get('/api/tours', 'ApiController@tours', 'api.tours');
$router->get('/api/tours/{slug}', 'ApiController@tour', 'api.tour');
$router->post('/api/storage/sign-upload', 'ApiController@signUpload', 'api.storage.sign');

// ============================================================
// DASHBOARD ROUTES (Protected)
// ============================================================

$router->get('/dashboard', 'DashboardController@index', 'dashboard');

// ============================================================
// PROPERTY ROUTES (Protected)
// ============================================================

$router->get('/properties', 'PropertyController@index', 'properties');
$router->get('/properties/create', 'PropertyController@create', 'properties.create');
$router->post('/properties/create', 'PropertyController@store', 'properties.store');
$router->get('/properties/{id}', 'PropertyController@show', 'properties.show');
$router->get('/properties/{id}/edit', 'PropertyController@edit', 'properties.edit');
$router->post('/properties/{id}/edit', 'PropertyController@update', 'properties.update');
$router->post('/properties/{id}/delete', 'PropertyController@delete', 'properties.delete');

// ============================================================
// TOUR ROUTES (Protected)
// ============================================================

$router->get('/tours', 'TourController@index', 'tours');
$router->get('/tours/create', 'TourController@create', 'tours.create');
$router->post('/tours/create', 'TourController@store', 'tours.store');
$router->get('/tours/{id}', 'TourController@show', 'tours.show');
$router->get('/tours/{id}/edit', 'TourController@edit', 'tours.edit');
$router->post('/tours/{id}/edit', 'TourController@update', 'tours.update');
$router->post('/tours/{id}/delete', 'TourController@delete', 'tours.delete');

// ============================================================
// SCENE ROUTES (Protected)
// ============================================================

$router->get('/tours/{tourId}/scenes', 'SceneController@index', 'scenes');
$router->get('/tours/{tourId}/scenes/create', 'SceneController@create', 'scenes.create');
$router->post('/tours/{tourId}/scenes/create', 'SceneController@store', 'scenes.store');
$router->get('/tours/{tourId}/scenes/{sceneId}/edit', 'SceneController@edit', 'scenes.edit');
$router->post('/tours/{tourId}/scenes/{sceneId}/edit', 'SceneController@update', 'scenes.update');
$router->post('/tours/{tourId}/scenes/{sceneId}/delete', 'SceneController@delete', 'scenes.delete');

// ============================================================
// HOTSPOT ROUTES (Protected)
// ============================================================

$router->get('/tours/{tourId}/scenes/{sceneId}/hotspots/create', 'HotspotController@create', 'hotspots.create');
$router->post('/tours/{tourId}/scenes/{sceneId}/hotspots/create', 'HotspotController@store', 'hotspots.store');
$router->get('/tours/{tourId}/scenes/{sceneId}/hotspots/{hotspotId}/edit', 'HotspotController@edit', 'hotspots.edit');
$router->post('/tours/{tourId}/scenes/{sceneId}/hotspots/{hotspotId}/edit', 'HotspotController@update', 'hotspots.update');
$router->post('/tours/{tourId}/scenes/{sceneId}/hotspots/{hotspotId}/delete', 'HotspotController@delete', 'hotspots.delete');

// ============================================================
// ANALYTICS ROUTES (Protected)
// ============================================================

$router->get('/analytics', 'AnalyticsController@index', 'analytics');

// ============================================================
// SUBSCRIPTION ROUTES (Protected)
// ============================================================

$router->get('/subscriptions', 'SubscriptionController@index', 'subscriptions');
$router->get('/subscriptions/plans', 'SubscriptionController@plans', 'subscriptions.plans');
$router->post('/subscriptions/change-plan', 'SubscriptionController@changePlan', 'subscriptions.change');
$router->post('/subscriptions/invoices/{invoiceId}/pay', 'SubscriptionController@pay', 'subscriptions.pay');

// ============================================================
// USER ROUTES (Protected - Tenant Admin)
// ============================================================

$router->get('/users', 'UserController@index', 'users');
$router->get('/users/create', 'UserController@create', 'users.create');
$router->post('/users/create', 'UserController@store', 'users.store');
$router->get('/users/{id}/edit', 'UserController@edit', 'users.edit');
$router->post('/users/{id}/edit', 'UserController@update', 'users.update');
$router->post('/users/{id}/delete', 'UserController@delete', 'users.delete');

// ============================================================
// TENANT ROUTES (Protected - Platform Admin)
// ============================================================

$router->get('/tenants', 'TenantController@index', 'tenants');
$router->get('/tenants/create', 'TenantController@create', 'tenants.create');
$router->post('/tenants/create', 'TenantController@store', 'tenants.store');
$router->get('/tenants/{id}/edit', 'TenantController@edit', 'tenants.edit');
$router->post('/tenants/{id}/edit', 'TenantController@update', 'tenants.update');

// ============================================================
// DISPATCH REQUEST
// ============================================================

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
