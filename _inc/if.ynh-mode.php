<?php

include_once __DIR__ . '/../_conf/protect.php';
include_once __DIR__ . '/../_conf/global.php';


if (!empty($config['site']['ynh_data']) && $config['site']['ynh_data'] === true) {
    echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Page disabled</title>
            <style>
                body { font-family: sans-serif; text-align: center; margin-top: 50px; }
            </style>
            <!--<script>
                setTimeout(function() {
                    window.location.href = "/";
                }, 5000);
            </script>-->
        </head>
        <body>
            <h1>✋ Page is disabled</h1>
            <p>Services are managed automatically by YunoHost.</p>
            <p>You will be redirected to the <a href="/">homepage</a> in <strong>5 seconds</strong>.</p>
        </body>
        </html>';
    exit;
}