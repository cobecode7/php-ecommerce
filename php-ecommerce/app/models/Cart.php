<?php
/**
 * Cart Model
 * Handles cart data and database operations
 */

class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Add a product to the cart
     */
    public function add($sessionId, $productId, $quantity = 1)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        // Check if the product is already in the cart
        $sql = "SELECT id, quantity FROM cart WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $existingItem = $stmt->fetch();

        if ($existingItem) {
            // Update quantity if item already exists
            $newQuantity = $existingItem['quantity'] + $quantity;
            $sql = "UPDATE cart SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
            $stmt->bindParam(':id', $existingItem['id'], PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Insert new item if it doesn't exist
            $sql = "INSERT INTO cart (session_id, product_id, quantity) VALUES (:session_id, :product_id, :quantity)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->execute();
        }

        return true;
    }

    /**
     * Remove a product from the cart
     */
    public function remove($sessionId, $productId)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        $sql = "DELETE FROM cart WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }

    /**
     * Update quantity of a product in the cart
     */
    public function updateQuantity($sessionId, $productId, $quantity)
    {
        if (!$this->db->isConnected() || $quantity <= 0) {
            return false;
        }

        $sql = "UPDATE cart SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }

    /**
     * Get all items in the cart for a session
     */
    public function getItems($sessionId)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "
            SELECT c.*, p.name, p.price, p.image_url
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = :session_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get the total count of items in the cart
     */
    public function getItemCount($sessionId)
    {
        if (!$this->db->isConnected()) {
            return 0;
        }

        $sql = "SELECT SUM(quantity) as total FROM cart WHERE session_id = :session_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Get the total price of items in the cart
     */
    public function getTotal($sessionId)
    {
        if (!$this->db->isConnected()) {
            return 0;
        }

        $sql = "
            SELECT SUM(c.quantity * p.price) as total
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = :session_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? (float)$result['total'] : 0;
    }

    /**
     * Clear the cart for a session
     */
    public function clear($sessionId)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        $sql = "DELETE FROM cart WHERE session_id = :session_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    /**
     * Get the database connection instance
     */
    public function getDb()
    {
        return $this->db;
    }
}