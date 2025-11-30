<?php
/**
 * Database Setup Script for Linux/MariaDB
 * 
 * This script should be run with sudo privileges to setup the database user:
 * sudo php setup_db_user.php
 */

if (!isset($argv[1]) || $argv[1] !== '--confirmed') {
    echo "This script will create a database user for the e-commerce application.\n";
    echo "You'll need sudo privileges to execute this script.\n\n";
    echo "Run: sudo php setup_db_user.php --confirmed\n";
    exit(1);
}

echo "Setting up database user for e-commerce application...\n";

// Connect as root using sudo access
$command = "mysql -u root -e \"CREATE USER IF NOT EXISTS 'ecommerce_user'@'localhost' IDENTIFIED BY 'ecommerce_password';\"";
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Failed to create database user. Error: " . implode("\n", $output) . "\n";
    exit(1);
}

echo "✓ Database user 'ecommerce_user' created/updated.\n";

// Grant required privileges
$command = "mysql -u root -e \"GRANT ALL PRIVILEGES ON ecommerce_db.* TO 'ecommerce_user'@'localhost';\"";
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Failed to grant privileges to database user. Error: " . implode("\n", $output) . "\n";
    exit(1);
}

echo "✓ Privileges granted to 'ecommerce_user'.\n";

// Flush privileges
$command = "mysql -u root -e \"FLUSH PRIVILEGES;\"";
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Failed to flush privileges. Error: " . implode("\n", $output) . "\n";
    exit(1);
}

echo "✓ Privileges flushed.\n";
echo "\nDatabase user setup complete!\n";
echo "You can now run the application initialization: php init.php\n";