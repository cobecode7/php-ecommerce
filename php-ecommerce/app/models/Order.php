<?php
/**
 * Order Model
 * Handles order data and database operations
 */

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new order
     */
    public function create($userId, $cartItems, $shippingAddress, $billingAddress, $paymentMethod)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        try {
            $this->db->getConnection()->beginTransaction();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Insert order
            $sql = "INSERT INTO orders (user_id, total_amount, shipping_address, billing_address, payment_method)
                    VALUES (:user_id, :total_amount, :shipping_address, :billing_address, :payment_method)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':total_amount', $totalAmount, PDO::PARAM_STR); // Using string to preserve decimal precision
            $stmt->bindValue(':shipping_address', $shippingAddress, PDO::PARAM_STR);
            $stmt->bindValue(':billing_address', $billingAddress, PDO::PARAM_STR);
            $stmt->bindValue(':payment_method', $paymentMethod, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order: " . implode(", ", $stmt->errorInfo()));
            }

            $orderId = $this->db->getConnection()->lastInsertId();

            // Insert order items
            foreach ($cartItems as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                        VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                $stmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->bindValue(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindValue(':unit_price', $item['price'], PDO::PARAM_STR);
                $totalPrice = $item['price'] * $item['quantity'];
                $stmt->bindValue(':total_price', $totalPrice, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert order item: " . implode(", ", $stmt->errorInfo()));
                }

                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }

            $this->db->getConnection()->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update product stock after order
     */
    private function updateProductStock($productId, $quantity)
    {
        $sql = "UPDATE products SET stock_quantity = stock_quantity - :quantity
                WHERE id = :product_id AND stock_quantity >= :quantity_check";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity_check', $quantity, PDO::PARAM_INT);
        $stmt->execute();

        // Check if the update affected any rows (if stock was sufficient)
        if ($stmt->rowCount() === 0) {
            throw new Exception("Insufficient stock for product ID: $productId");
        }
    }

    /**
     * Get an order by ID
     */
    public function getById($id)
    {
        if (!$this->db->isConnected()) {
            return false;
        }

        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get orders for a specific user
     */
    public function getByUser($userId)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get order items for a specific order
     */
    public function getOrderItems($orderId)
    {
        if (!$this->db->isConnected()) {
            return [];
        }

        $sql = "SELECT oi.*, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
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