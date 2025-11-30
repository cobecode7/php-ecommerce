<?php
/**
 * Test Database Connection Script
 * This script tests database connectivity with different configurations
 */

require_once __DIR__ . '/app/config/config.php';

// Test different configurations
$configurations = [
    // Default config
    [
        'name' => 'Current Config',
        'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
        'name' => defined('DB_NAME') ? DB_NAME : 'ecommerce_db',
        'user' => defined('DB_USER') ? DB_USER : 'root',
        'pass' => defined('DB_PASS') ? DB_PASS : ''
    ],
    // Common local config variations
    [
        'name' => 'root with empty password',
        'host' => 'localhost',
        'name' => 'ecommerce_db',
        'user' => 'root',
        'pass' => ''
    ],
    [
        'name' => 'user with password',
        'host' => 'localhost',
        'name' => 'ecommerce_db',
        'user' => 'user',
        'pass' => 'password'
    ],
    [
        'name' => 'developer with password',
        'host' => 'localhost',
        'name' => 'ecommerce_db',
        'user' => 'developer',
        'pass' => 'password'
    ]
];

echo "Testing database configurations:\n";
echo "================================\n\n";

foreach ($configurations as $config) {
    echo "Testing: {$config['name']}\n";
    echo "  Host: {$config['host']}\n";
    echo "  DB: {$config['name']}\n";
    echo "  User: {$config['user']}\n";
    echo "  Pass: " . str_repeat('*', strlen($config['pass'])) . "\n";
    
    try {
        $pdo = new PDO("mysql:host={$config['host']}", $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        echo "  ✓ Connection successful\n";
        
        // Check if database exists, create if not
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE {$config['name']}");
        
        // Check if tables exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Database and tables exist\n";
        } else {
            echo "  ℹ Database exists but tables not found\n";
        }
        
        $pdo = null; // Close connection
        
    } catch (PDOException $e) {
        echo "  ✗ Connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";