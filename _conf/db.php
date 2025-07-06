<?php

include_once __DIR__ . '/protect.php';
include_once __DIR__ . '/env.php';

// Database configuration
$db = [
    'host' => $env['DB_HOST'] ?? 'localhost',
    'name' => $env['DB_NAME'] ?? 'my_webapp',
    'user' => $env['DB_USER'] ?? 'my_webapp',
    'pass' => $env['DB_PASSWORD'] ?? '',
    'port' => $env['DB_PORT'] ?? '3306',
];

if ($config['site']['ynh_data'] === false) {
    if (!$db['host'] || !$db['name'] || !$db['user']) {
        die("DB configuration error: missing parameters. Please check your environment variables.");
    }

    $db['dsn'] = 'mysql:host=' . $db['host'] . ';'
            . 'port=' . $db['port'] . ';'
            . 'dbname=' . $db['name'] . ';charset=utf8mb4';

    try {
        $pdo = new PDO($db['dsn'], $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
} else {
    // Mode YunoHost : aucune connexion BDD, aucune variable $pdo
}

?>
