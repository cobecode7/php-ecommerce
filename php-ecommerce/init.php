<?php
/**
 * Application Initialization Script
 * 
 * This script helps with initial setup of the application:
 * - Creates the database if it doesn't exist
 * - Runs initial migrations
 * - Sets up necessary configurations
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/database/migrations/MigrationRunner.php';

echo "PHP E-commerce Application Initialization\n";
echo "=====================================\n\n";

// Check if database is accessible
echo "Checking database connection...\n";
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, PDO_OPTIONS);
    echo "✓ Database connection successful\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '" . DB_NAME . "' created/verified\n";
    
    // Change to the correct database
    $pdo->exec("USE " . DB_NAME);
    
    // Check if tables exist by looking for products table
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tablesExist = $stmt->rowCount() > 0;
    
    if (!$tablesExist) {
        echo "\nDatabase appears to be empty. Running initial migrations...\n";
        
        $migrationRunner = new MigrationRunner();
        $migrationRunner->runMigrations();
        
        echo "\n✓ Initial migrations completed successfully!\n";
    } else {
        echo "\n✓ Database already contains tables. Skipping initial migration.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nApplication initialization completed!\n";
echo "\nTo start the application:\n";
echo "1. Make sure your web server is pointing to the 'public' directory\n";
echo "2. Update your database configuration in app/config/database.php if needed\n";
echo "3. Access your application through your web browser\n";

echo "\nFor API testing, you can also run PHP's built-in server:\n";
echo "cd public && php -S localhost:8000\n\n";