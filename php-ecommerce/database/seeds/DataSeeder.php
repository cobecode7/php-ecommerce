<?php
/**
 * Sample Data Seeder
 * 
 * This script creates sample data for the e-commerce application
 * to be used when the database is properly configured.
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/Product.php';

class DataSeeder 
{
    private $db;
    private $productModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->productModel = new Product();
    }

    public function seedProducts() {
        if (!$this->db->isConnected()) {
            echo "Database is not connected. Please set up the database first.\n";
            echo "Follow instructions in SETUP_INSTRUCTIONS.md\n";
            return false;
        }

        // Sample products data
        $sampleProducts = [
            [
                'name' => 'Smartphone X1',
                'description' => 'Latest model smartphone with advanced features including high-resolution camera, long battery life, and fast processor.',
                'price' => 699.99,
                'stock_quantity' => 50,
                'sku' => 'PHONE-X1-001',
                'image_url' => 'https://placehold.co/600x600/3b82f6/FFFFFF?text=Smartphone',
                'category_id' => 1
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'High-performance laptop for professionals with 16GB RAM, 512GB SSD, and powerful graphics.',
                'price' => 1299.99,
                'stock_quantity' => 30,
                'sku' => 'LAP-PRO-001',
                'image_url' => 'https://placehold.co/600x600/ef4444/FFFFFF?text=Laptop',
                'category_id' => 1
            ],
            [
                'name' => 'Cotton T-Shirt',
                'description' => 'Comfortable cotton t-shirt for everyday wear, available in multiple colors and sizes.',
                'price' => 19.99,
                'stock_quantity' => 100,
                'sku' => 'TS-CTN-001',
                'image_url' => 'https://placehold.co/600x600/10b981/FFFFFF?text=T-Shirt',
                'category_id' => 2
            ],
            [
                'name' => 'Coffee Maker',
                'description' => 'Automatic coffee maker with timer, perfect for your morning routine.',
                'price' => 89.99,
                'stock_quantity' => 25,
                'sku' => 'CFM-AUT-001',
                'image_url' => 'https://placehold.co/600x600/f97316/FFFFFF?text=Coffee+Maker',
                'category_id' => 3
            ],
            [
                'name' => 'PHP Programming Book',
                'description' => 'Complete guide to PHP development, covering everything from basics to advanced topics.',
                'price' => 39.99,
                'stock_quantity' => 40,
                'sku' => 'BK-PHP-001',
                'image_url' => 'https://placehold.co/600x600/8b5cf6/FFFFFF?text=PHP+Book',
                'category_id' => 4
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Premium sound quality with noise cancellation and 20-hour battery life.',
                'price' => 129.99,
                'stock_quantity' => 35,
                'sku' => 'WH-BLK-001',
                'image_url' => 'https://placehold.co/600x600/6366f1/FFFFFF?text=Headphones',
                'category_id' => 1
            ],
            [
                'name' => 'Running Shoes',
                'description' => 'Lightweight running shoes with extra cushioning for maximum comfort.',
                'price' => 89.95,
                'stock_quantity' => 50,
                'sku' => 'RS-RED-001',
                'image_url' => 'https://placehold.co/600x600/ec4899/FFFFFF?text=Shoes',
                'category_id' => 2
            ],
            [
                'name' => 'Blender',
                'description' => 'High-speed blender perfect for smoothies and healthy recipes.',
                'price' => 79.99,
                'stock_quantity' => 40,
                'sku' => 'BL-GRN-001',
                'image_url' => 'https://placehold.co/600x600/22c55e/FFFFFF?text=Blender',
                'category_id' => 3
            ],
            [
                'name' => 'JavaScript Guide',
                'description' => 'Complete guide to modern JavaScript development techniques.',
                'price' => 44.99,
                'stock_quantity' => 30,
                'sku' => 'BK-JS-001',
                'image_url' => 'https://placehold.co/600x600/eab308/FFFFFF?text=JS+Book',
                'category_id' => 4
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Track your fitness and stay connected with this versatile smartwatch.',
                'price' => 249.99,
                'stock_quantity' => 20,
                'sku' => 'SW-BLK-001',
                'image_url' => 'https://placehold.co/600x600/000000/FFFFFF?text=Smart+Watch',
                'category_id' => 1
            ]
        ];

        $pdo = $this->db->getConnection();
        
        // Clear existing products (be careful with this in production)
        $pdo->exec("DELETE FROM products WHERE id > 0");
        
        // Insert new sample products
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, stock_quantity, sku, image_url, category_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertedCount = 0;
        foreach ($sampleProducts as $product) {
            try {
                $result = $stmt->execute([
                    $product['name'],
                    $product['description'],
                    $product['price'],
                    $product['stock_quantity'],
                    $product['sku'],
                    $product['image_url'],
                    $product['category_id']
                ]);
                
                if ($result) {
                    $insertedCount++;
                }
            } catch (PDOException $e) {
                echo "Error inserting product: " . $product['name'] . " - " . $e->getMessage() . "\n";
            }
        }
        
        echo "Successfully inserted {$insertedCount} products into the database.\n";
        return true;
    }
    
    public function seedCategories() {
        if (!$this->db->isConnected()) {
            echo "Database is not connected. Please set up the database first.\n";
            echo "Follow instructions in SETUP_INSTRUCTIONS.md\n";
            return false;
        }

        $sampleCategories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Apparel and fashion items'],
            ['name' => 'Home & Kitchen', 'description' => 'Home appliances and kitchen items'],
            ['name' => 'Books', 'description' => 'Books and educational materials'],
            ['name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear'],
            ['name' => 'Beauty & Health', 'description' => 'Beauty products and health items']
        ];

        $pdo = $this->db->getConnection();
        
        // Clear existing categories (be careful with this in production)
        $pdo->exec("DELETE FROM categories WHERE id > 0");
        
        // Insert new sample categories
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, description) 
            VALUES (?, ?)
        ");
        
        $insertedCount = 0;
        foreach ($sampleCategories as $category) {
            try {
                $result = $stmt->execute([
                    $category['name'],
                    $category['description']
                ]);
                
                if ($result) {
                    $insertedCount++;
                }
            } catch (PDOException $e) {
                echo "Error inserting category: " . $category['name'] . " - " . $e->getMessage() . "\n";
            }
        }
        
        echo "Successfully inserted {$insertedCount} categories into the database.\n";
        return true;
    }
    
    public function run() {
        echo "Starting data seeding process...\n";
        
        echo "\nSeeding categories...\n";
        $this->seedCategories();
        
        echo "\nSeeding products...\n";
        $this->seedProducts();
        
        echo "\nData seeding completed!\n";
    }
}

// Run seeder if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $seeder = new DataSeeder();
    $seeder->run();
}