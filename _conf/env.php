<?php

include_once __DIR__ . '/protect.php';

function loadEnv($path) {
    $env = [];
    if (!file_exists($path)) return $env;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        if (strpos($line, '=') === false) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        $env[$key] = $value;

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    return $env;
}

$env = loadEnv(__DIR__ . '/.env');

?>