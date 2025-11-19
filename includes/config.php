<?php
/**
 * Configuration File
 * 
 * Contains all application-wide settings and constants
 * This file should be included at the top of every PHP file
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone setting
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_NAME', getenv('DB_NAME') ?: 'musicplayer');
define('DB_USER', getenv('DB_USER') ?: 'musicplayer_user');
define('DB_PASS', getenv('DB_PASS') ?: 'musicplayer_pass');
define('DB_CHARSET', 'utf8mb4');

// Application paths
define('BASE_PATH', '/var/www/html');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('AUDIO_PATH', UPLOAD_PATH . '/songs');
define('COVER_PATH', UPLOAD_PATH . '/covers');
define('ASSETS_AUDIO_PATH', BASE_PATH . '/assets/audio');
define('ASSETS_IMG_PATH', BASE_PATH . '/assets/img');

// URL paths (for browser)
define('BASE_URL', 'http://localhost:8080');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Security settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_LIFETIME', 3600 * 24); // 24 hours in seconds
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'musicplayer_jwt_secret_key_change_this_in_production');
define('JWT_EXPIRY', 3600 * 24 * 7); // 7 days

// File upload settings
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB in bytes
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// Pagination settings
define('SONGS_PER_PAGE', 20);
define('HISTORY_PER_PAGE', 50);

// Application version
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Music Player');

// Only set API headers if we're in the API directory
$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($currentScript, '/api/') !== false) {
    // CORS settings for API endpoints only
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    // Handle OPTIONS requests for CORS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

