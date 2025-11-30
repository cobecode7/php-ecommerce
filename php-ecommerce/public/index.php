<?php
/**
 * Main Entry Point for PHP Built-in Server
 * All requests are routed through this file
 */

// For PHP built-in server, we need to handle all routes here
if (php_sapi_name() === 'cli-server') {
    // Check if the requested file exists (static files)
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

    // If it's a static file that exists, let the server handle it
    if (is_file($filename) && !preg_match('/\.(php|phtml|php3|php4|php5|php7|php8)$/i', $filename)) {
        return false;
    }
}

// Load configuration files first
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

// Start session after config is loaded
session_start();

// Autoload classes (simple implementation)
spl_autoload_register(function ($class_name) {
    // First, try to load from app directory structure
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class_name) . '.php';

    // If not found there, try loading from specific subdirectories
    if (!file_exists($file)) {
        $dirs = ['controllers', 'models', 'utils', 'middleware', 'config'];
        foreach ($dirs as $dir) {
            $file = __DIR__ . "/../app/{$dir}/{$class_name}.php";
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    } else {
        require_once $file;
        return;
    }
});

// Load utility functions
require_once __DIR__ . '/../app/utils/Helpers.php';
require_once __DIR__ . '/../app/utils/Router.php';

// Initialize router
$router = new Router();

// Define routes
$router->get('/', ['HomeController', 'index']);
$router->get('/products', ['ProductController', 'index']);
$router->get('/product/{id:\d+}', ['ProductController', 'show']);
$router->get('/cart', ['CartController', 'index']);
$router->post('/cart/add', ['CartController', 'add']);
$router->post('/cart/remove', ['CartController', 'remove']);
$router->post('/cart/update', ['CartController', 'update']);
$router->get('/categories', ['CategoryController', 'index']);
$router->get('/category/{id:\d+}', ['CategoryController', 'show']);
$router->get('/checkout', ['CheckoutController', 'index']);
$router->post('/order/create', ['OrderController', 'createFromCart']);
$router->get('/orders', ['OrderController', 'index']);
$router->get('/order/{id:\d+}', ['OrderController', 'show']);

// Authentication routes
$router->get('/login', ['UserController', 'showLogin']);
$router->post('/login', ['UserController', 'login']);
$router->get('/register', ['UserController', 'showRegister']);
$router->post('/register', ['UserController', 'register']);
$router->get('/logout', ['UserController', 'logout']);

// Cart API routes
$router->get('/api/cart/count', ['CartController', 'getCartItemCount']);

// Example of a route with middleware (for authenticated users)
// $router->get('/dashboard', ['DashboardController', 'index'], ['AuthMiddleware']);

// Resolve the current route
$router->resolve();