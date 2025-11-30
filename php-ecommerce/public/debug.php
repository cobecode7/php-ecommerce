<?php
// Debug script to understand request routing

echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP Self: " . $_SERVER['PHP_SELF'] . "\n";

// Check if running as CLI or web server
if (php_sapi_name() === 'cli-server') {
    echo "Running under PHP built-in server\n";
    
    // For the PHP built-in server, we need to handle routing differently
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = ltrim($url, '/');
    
    // If it's the root path, show index
    if ($url === '' || $url === 'index.php') {
        echo "This is the home page request\n";
        echo "Status: 200 OK\n";
        exit;
    }
    
    // Check for static files
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
        return false; // Let the server handle the static file
    }
    
    // For all other routes, we handle it via our router
    echo "Routing for: " . $url . "\n";
    echo "Status: 200 OK\n";
} else {
    echo "Running under different server\n";
}