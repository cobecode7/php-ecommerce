<?php
/**
 * Database Migration Runner
 * Executes database migrations from SQL files
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/Database.php';

class MigrationRunner 
{
    private $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function runMigrations() 
    {
        echo "Starting database migrations...\n";
        
        // Run the schema migration
        $this->runSchemaMigration();
        
        echo "Migrations completed successfully!\n";
    }
    
    private function runSchemaMigration()
    {
        $schemaFile = __DIR__ . '/../schema.sql';  // Go up one directory to find the schema.sql

        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        
        // Split SQL by semicolons to handle multiple statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($statement) {
                return !empty($statement);
            }
        );
        
        foreach ($statements as $statement) {
            try {
                $this->db->exec($statement);
                echo "Executed: " . substr($statement, 0, 60) . "...\n";
            } catch (PDOException $e) {
                echo "Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . $statement . "\n";
            }
        }
    }
    
    public function rollbackLast() 
    {
        // In a real application, this would implement migration rollback
        // For this example, we'll just output a message
        echo "Rollback functionality would go here.\n";
    }
}

// Run migrations if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $migrationRunner = new MigrationRunner();
        $migrationRunner->runMigrations();
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}