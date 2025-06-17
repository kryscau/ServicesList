<?php

include_once __DIR__ . '/env.php';

// Database configuration
$db['host'] = $env['DB_HOST'] ?? 'localhost';
$db['name'] = $env['DB_NAME'] ?? 'my_webapp';
$db['user'] = $env['DB_USER'] ?? 'my_webapp';
$db['pass'] = $env['DB_PASS'] ?? 'my_webapp_pass';
$db['port'] = $env['DB_PORT'] ?? '3306';

if (!$db['host'] || !$db['name'] || !$db['user'] || !$db['pass'] || !$db['port']) {
    die("DB configuration error: missing parameters. Please check your environment variables.");
}


$db['dsn'] = 'mysql:host=' . $db['host'] . ';port='. $db['port'] .';dbname=' . $db['name'] . ';charset=utf8mb4';

?>