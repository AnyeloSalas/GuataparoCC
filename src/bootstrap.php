<?php
/**
 * Bootstrap file - Initialize application
 * This file should be included at the top of every entry point
 */

// Define base paths
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
if (!defined('SRC_PATH')) define('SRC_PATH', BASE_PATH . '/src');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', BASE_PATH . '/config');
if (!defined('LOGS_PATH')) define('LOGS_PATH', BASE_PATH . '/logs');
if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', BASE_PATH);

// Load composer autoloader
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// DEBUG: Verificar existencia y ruta
// die("BASE_PATH: " . BASE_PATH . " | .env existe: " . (file_exists(BASE_PATH . '/.env') ? 'SI' : 'NO'));

if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            
            // Clean key and value from quotes and invisible characters (BOM, etc)
            $key = trim($key, " \t\n\r\0\x0B\xEF\xBB\xBF");
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            if ($key !== '') {
                // Set environment variable in all possible sources
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Configure and start session
if (session_status() == PHP_SESSION_NONE) {
    // Set custom session lifetime if defined in .env
    // Set custom session lifetime if defined in .env, otherwise fail or use a very low safe value
    $lifetime = getenv('SESSION_LIFETIME');
    if ($lifetime === false) {
        die("Error Crítico: SESSION_LIFETIME no definido en .env");
    }
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params([
        'lifetime' => (int)$lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set error reporting based on environment
$isProduction = getenv('APP_ENV') === 'production';
if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set timezone
date_default_timezone_set('America/Caracas');

// Autoloader function for classes
spl_autoload_register(function ($class) {
    // Remove namespace prefix if present
    $class = str_replace('App\\', '', $class);
    
    // Convert namespace separators to directory separators
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Build the potential file paths
    $paths = [
        SRC_PATH . DIRECTORY_SEPARATOR . $class . '.php',
        SRC_PATH . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $class . '.php',
        SRC_PATH . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $class . '.php',
        CONFIG_PATH . DIRECTORY_SEPARATOR . $class . '.php'
    ];
    
    // Check each path
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load core classes
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Models/Csrf.php';
require_once SRC_PATH . '/Models/Validator.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Middleware/CsrfMiddleware.php';

// Helper function to get environment variable with default
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }
    return $value !== false ? $value : $default;
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to get base URL
function baseUrl($path = '') {
    $baseUrl = env('APP_URL');
    if (!$baseUrl) {
        die("Error Crítico: APP_URL no definida en el archivo .env");
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

// Helper function to check if request is POST
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Helper function to check if request is GET
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// Helper function to get POST data safely
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

// Helper function to get GET data safely
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

// Helper function to display flash messages
function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

// Helper function to old input (for form repopulation)
function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

// Helper function to set old input
function setOld($data) {
    $_SESSION['old'] = $data;
}

// Helper function to clear old input
function clearOld() {
    unset($_SESSION['old']);
}

// Set up error handler for production
if ($isProduction) {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $logFile = LOGS_PATH . '/error_' . date('Y-m-d') . '.log';
        $message = date('[Y-m-d H:i:s] ') . "Error [$errno]: $errstr in $errfile on line $errline\n";
        error_log($message, 3, $logFile);
        return true;
    });
}
?>
