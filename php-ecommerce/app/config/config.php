<?php
/**
 * Main Configuration File
 * Contains general application settings
 */

// Application settings
define('APP_NAME', 'PHP E-commerce Store');
define('APP_VERSION', '1.0.0');

// Determine BASE_URL based on environment
if (php_sapi_name() === 'cli') {
    // When running from CLI (command line), use a default
    define('BASE_URL', 'http://localhost');
} else {
    // When running through web server
    define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
}

// Environment settings
define('ENVIRONMENT', 'development'); // development, staging, production

// Error reporting
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Session settings - only set if session is not already active
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    if (ENVIRONMENT === 'production') {
        ini_set('session.cookie_secure', 1);
    }
}