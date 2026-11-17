<?php
// ==========================================================
// Application Configuration
// ==========================================================

// Autodetect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/jasa/index.php'));
$baseUrl = rtrim($protocol . $host . $scriptName, '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'ServisYuk - Layanan Servis Rumah Tangga Berbasis AI');
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', APP_ROOT . '/uploads');
}

if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', BASE_URL . '/uploads');
}

// Autoloader for project classes (models, core, controllers)
spl_autoload_register(function (string $class): void {
    $directories = [
        APP_ROOT . '/models/',
        APP_ROOT . '/core/',
        APP_ROOT . '/controllers/'
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Session initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
