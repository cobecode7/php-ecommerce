<?php
/**
 * Database Configuration File
 * Contains database connection settings
 * 
 * For Linux systems with MariaDB using unix_socket authentication,
 * you may need to run the application with appropriate database permissions.
 */

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
// Changed to a user that can be created for this application
define('DB_USER', 'ecommerce_user');
define('DB_PASS', 'ecommerce_password');
define('DB_CHARSET', 'utf8mb4');

// PDO options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => true,
]);