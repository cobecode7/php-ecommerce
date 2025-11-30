<?php
/**
 * Database Class
 * Handles database connections and operations
 */

class Database
{
    private static $instance = null;
    private $connection;
    private $isConnected = false;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
            $this->isConnected = true;
        } catch (PDOException $e) {
            // Don't die, just mark as not connected
            $this->isConnected = false;
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        if (!$this->isConnected) {
            return null;
        }
        return $this->connection;
    }

    public function prepare($sql)
    {
        if (!$this->isConnected) {
            throw new PDOException("Database connection failed");
        }
        return $this->connection->prepare($sql);
    }

    public function query($sql)
    {
        if (!$this->isConnected) {
            throw new PDOException("Database connection failed");
        }
        return $this->connection->query($sql);
    }

    public function isConnected()
    {
        return $this->isConnected;
    }
}