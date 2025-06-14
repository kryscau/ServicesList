<?php

// Database configuration
$db['host'] = 'localhost';
$db['name'] = 'my_webapp';
$db['user'] = 'my_webapp';
$db['pass'] = ' '; // Replace with your actual password in production
$db['dsn'] = 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8mb4';

?>