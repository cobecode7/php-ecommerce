<?php
/**
 * Category Model
 * Handles category data and database operations
 */

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all categories
     */
    public function getAll()
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get a category by ID
     */
    public function getById($id)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get subcategories of a category
     */
    public function getSubcategories($parentId)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT * FROM categories WHERE parent_id = :parent_id ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get the database connection instance
     */
    public function getDb()
    {
        return $this->db;
    }
}