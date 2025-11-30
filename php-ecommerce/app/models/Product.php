<?php
/**
 * Product Model
 * Handles product data and database operations
 */

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all products with optional limit
     */
    public function getAll($limit = null)
    {
        if (!$this->db->isConnected()) {
            // Return empty array if database is not connected
            return [];
        }

        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a product by ID
     */
    public function getById($id)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get products by category ID
     */
    public function getByCategory($categoryId)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT * FROM products WHERE category_id = :category_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Search products by name or description
     */
    public function search($searchTerm, $limit = null)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT * FROM products WHERE name LIKE :search OR description LIKE :search ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchParam);

        if ($limit) {
            $sql .= " LIMIT :limit";
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get product count
     */
    public function count()
    {
        if (!$this->db->isConnected()) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM products";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get the database connection instance
     */
    public function getDb()
    {
        return $this->db;
    }
}