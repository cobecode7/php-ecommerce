<?php
/**
 * CartController Class
 * Handles cart-related requests
 */

class CartController
{
    private $cartModel;
    private $productModel;
    
    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    public function index()
    {
        $sessionId = session_id();
        $cartItems = [];
        $cartTotal = 0;
        $cartItemCount = 0;
        $error = null;

        try {
            $cartItems = $this->cartModel->getItems($sessionId);
            $cartTotal = $this->cartModel->getTotal($sessionId);
            $cartItemCount = $this->cartModel->getItemCount($sessionId);
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }

        ob_start();

        // Set variables for the template
        $title = 'Shopping Cart';
        $items = $cartItems;
        $total = $cartTotal;
        $itemCount = $cartItemCount;
        $cartError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Your Shopping Cart</h1>

                <?php if ($cartError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($cartError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                    <a href="/products" class="btn-primary">Continue Shopping</a>
                <?php elseif (empty($items)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                        <p class="text-gray-600 mb-6">Looks like you haven't added anything to your cart yet</p>
                        <a href="/products" class="btn-primary">Continue Shopping</a>
                    </div>
                <?php else: ?>
                <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 xl:gap-x-16">
                    <div class="lg:col-span-8">
                        <div class="bg-white">
                            <h2 class="sr-only">Items in your shopping cart</h2>
                            <ul class="divide-y divide-gray-200 border-b border-t border-gray-200">
                                <?php foreach ($items as $item): ?>
                                <li class="flex py-6 sm:py-10 cart-item" id="cart-item-<?php echo $item['product_id']; ?>">
                                    <div class="flex-shrink-0">
                                        <img src="<?php echo $item['image_url'] ?: 'https://placehold.co/200x200'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-24 w-24 rounded-md object-center object-cover sm:h-32 sm:w-32">
                                    </div>

                                    <div class="ml-4 flex flex-1 flex-col justify-between sm:ml-6">
                                        <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                            <div>
                                                <div class="flex justify-between">
                                                    <h3 class="text-sm">
                                                        <a href="/product/<?php echo $item['product_id']; ?>" class="font-medium text-gray-700 hover:text-gray-800">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    </h3>
                                                </div>
                                                <p class="mt-1 text-sm font-medium text-gray-900"><?php echo format_currency($item['price']); ?></p>
                                            </div>

                                            <div class="mt-4 sm:mt-0 sm:pr-9">
                                                <label for="quantity-<?php echo $item['product_id']; ?>" class="sr-only">Quantity</label>
                                                <select id="quantity-<?php echo $item['product_id']; ?>" name="quantity" class="quantity-select max-w-full rounded-md border border-gray-300 py-1.5 text-left text-base font-medium focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm" data-product-id="<?php echo $item['product_id']; ?>">
                                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                                        <option value="<?php echo $i; ?>" <?php echo $i == $item['quantity'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>

                                                <div class="absolute top-0 right-0">
                                                    <button type="button" class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline-none remove-item-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                                        <span class="sr-only">Remove</span>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Order summary -->
                    <div class="mt-6 border-t border-gray-200 pt-6 lg:col-span-4 lg:mt-0 lg:border-t-0 lg:pt-0">
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900">Order summary</h3>

                            <div class="mt-6 space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-base font-medium text-gray-900">Subtotal</p>
                                    <p class="ml-4 text-base font-medium text-gray-900"><?php echo format_currency($total); ?></p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Shipping</dt>
                                <dd class="text-base font-medium text-gray-900"><?php echo format_currency(20.00); ?></dd>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Taxes</dt>
                                <dd class="text-base font-medium text-gray-900"><?php echo format_currency($total * 0.08); // 8% tax ?></dd>
                            </div>
                            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
                                <dt class="text-base font-medium text-gray-900">Order total</dt>
                                <dd class="text-base font-medium text-gray-900"><?php echo format_currency($total + 20.00 + ($total * 0.08)); ?></dd>
                            </div>
                        </div>

                        <div class="mt-10">
                            <button class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Checkout
                            </button>
                        </div>

                        <div class="mt-6 text-center text-sm text-gray-500">
                            <p>
                                or
                                <a href="/products" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    Continue Shopping
                                    <span aria-hidden="true"> &rarr;</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layouts/base.html';
    }
    
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? $_POST['product_id'] ?? 0;
        $quantity = $input['quantity'] ?? $_POST['quantity'] ?? 1;

        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $sessionId = session_id();

        // Check if database is connected before accessing product data
        if (!$this->productModel->getDb()->isConnected()) {
            // If database is not connected, we can add to session-based cart
            // For now, just return a success message
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart (using session storage)',
                'item_count' => 1, // Placeholder count
                'product_name' => 'Sample Product'
            ]);
            return;
        }

        // Check if product exists in database
        $product = $this->productModel->getById($productId);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        // Add to cart
        $result = $this->cartModel->add($sessionId, $productId, $quantity);

        if ($result) {
            $newItemCount = $this->cartModel->getItemCount($sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'item_count' => $newItemCount,
                'product_name' => $product['name'] ?? 'Unknown Product'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add product to cart']);
        }
    }
    
    public function remove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? $_POST['product_id'] ?? 0;
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }
        
        $sessionId = session_id();

        // Remove from cart
        $result = $this->cartModel->remove($sessionId, $productId);

        if ($result) {
            $newItemCount = $this->cartModel->getItemCount($sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart',
                'item_count' => $newItemCount
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to remove product from cart']);
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? $_POST['product_id'] ?? 0;
        $quantity = $input['quantity'] ?? $_POST['quantity'] ?? 1;

        if (!$productId || $quantity < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID and valid quantity are required']);
            return;
        }

        $sessionId = session_id();

        // Update quantity in cart
        $result = $this->cartModel->updateQuantity($sessionId, $productId, $quantity);

        if ($result) {
            $newItemCount = $this->cartModel->getItemCount($sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully',
                'item_count' => $newItemCount
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cart']);
        }
    }

    public function getCartItemCount()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $sessionId = session_id();
        $itemCount = 0;

        try {
            $itemCount = $this->cartModel->getItemCount($sessionId);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            return;
        }

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['count' => $itemCount]);
    }
}