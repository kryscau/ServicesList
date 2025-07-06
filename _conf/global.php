<?php

include_once __DIR__ . '/protect.php';
include_once __DIR__ . '/env.php';

$config['site'] = [
	'name' => $env['GLOBAL_SITE_NAME'] ?? "My Web App",
	'main_domain' => $env['GLOBAL_SITE_URL'] ?? "localhost",
	'imgs_domain' => $env['GLOBAL_SITE_IMG'] ?? $env['GLOBAL_SITE_URL'] ?? "localhost",
	'ynh_data' => filter_var($env['GLOBAL_SITE_YNHDATA'], FILTER_VALIDATE_BOOLEAN) ?? false
];

$config['author'] = [
    'name' => $env['GLOBAL_AUTHOR_NAME'] ?? "Username",
    'bio' => $env['GLOBAL_AUTHOR_BIO_URL'] ?? "https://google.com/?q=Kryscau"
];

?>