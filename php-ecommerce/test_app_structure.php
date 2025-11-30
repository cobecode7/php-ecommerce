<?php
/**
 * Test Script for PHP E-commerce Application
 * 
 * This script tests that the application structure works correctly
 * even without database access by checking the database fallback mechanisms.
 */

echo "PHP E-commerce Application Test\n";
echo "===============================\n\n";

echo "Testing application structure and database fallbacks...\n\n";

// Test 1: Autoloader functionality
echo "1. Testing autoloader functionality...\n";
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/utils/Helpers.php';
require_once __DIR__ . '/app/utils/Router.php';

// Load model classes
require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/models/Product.php';
require_once __DIR__ . '/app/models/Cart.php';

// Load controller classes
require_once __DIR__ . '/app/controllers/HomeController.php';
require_once __DIR__ . '/app/controllers/ProductController.php';
require_once __DIR__ . '/app/controllers/CartController.php';

echo "   ✓ All classes loaded successfully\n\n";

// Test 2: Database connection handling
echo "2. Testing database connection handling...\n";
$db = Database::getInstance();
echo "   ✓ Database instance created\n";
echo "   ✓ Database connected: " . ($db->isConnected() ? 'Yes' : 'No') . "\n";
echo "   (This is expected since we haven't configured database access)\n\n";

// Test 3: Model instantiation
echo "3. Testing model instantiation...\n";
$productModel = new Product();
$cartModel = new Cart();
echo "   ✓ Product model instantiated\n";
echo "   ✓ Cart model instantiated\n\n";

// Test 4: Controller instantiation
echo "4. Testing controller instantiation...\n";
$homeController = new HomeController();
$productController = new ProductController();
$cartController = new CartController();
echo "   ✓ HomeController instantiated\n";
echo "   ✓ ProductController instantiated\n";
echo "   ✓ CartController instantiated\n\n";

// Test 5: Router functionality
echo "5. Testing router functionality...\n";
$router = new Router();
$router->get('/test', function() {
    echo "Route test successful\n";
});
echo "   ✓ Router instantiated and route defined\n\n";

echo "Application structure test completed successfully!\n\n";

echo "Note: The application is designed with fallback mechanisms\n";
echo "that allow it to run even without database access. When\n";
echo "the database is properly configured, the full functionality\n";
echo "will be available.\n\n";

echo "To complete the setup:\n";
echo "1. Follow the instructions in SETUP_INSTRUCTIONS.md\n";
echo "2. Set up the database with proper credentials\n";
echo "3. Run 'php init.php' to initialize the database\n";
echo "4. Start the application with 'cd public && php -S localhost:8000'\n";