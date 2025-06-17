<?php

// Security: prohibits direct access to the file
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    // Send HTTP code 404
    http_response_code(404);

    // Fallback for legacy servers (Apache, FastCGI)
    header('Status: 404 Not Found');

    // Stops the script
    exit;
}

?>