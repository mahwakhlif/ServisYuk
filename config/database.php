<?php
// ==========================================================
// Database Configuration (Environment-Aware)
// ==========================================================

require_once __DIR__ . '/../core/Env.php';
Env::load();

return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => (int)Env::get('DB_PORT', 3306),
    'dbname' => Env::get('DB_DATABASE', 'db_jasa_perbaikan'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
