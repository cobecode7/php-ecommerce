<?php
/**
 * OrderController Class
 * Handles order-related requests
 */

class OrderController
{
    private $orderModel;
    private $cartModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
    }

    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Store the intended URL and redirect to login
            $_SESSION['redirect_after_login'] = '/orders';
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        $orders = [];
        $error = null;

        try {
            $orders = $this->orderModel->getByUser($userId);
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }

        ob_start();
        $title = 'Order History';
        $userOrders = $orders;
        $orderError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Your Order History</h1>

                <?php if ($orderError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($orderError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                <?php elseif (empty($userOrders)): ?>
                    <div class="text-center py-12">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">No orders yet</h2>
                        <p class="text-gray-600 mb-6">You haven't placed any orders yet.</p>
                        <a href="/products" class="btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($userOrders as $order): ?>
                            <li>
                                <a href="/order/<?php echo $order['id']; ?>" class="block hover:bg-gray-50">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-indigo-600 truncate">
                                                Order #<?php echo $order['id']; ?>
                                            </p>
                                            <div class="ml-2 flex-shrink-0 flex">
                                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2 sm:flex sm:justify-between">
                                            <div class="sm:flex">
                                                <p class="flex items-center text-sm text-gray-500">
                                                    <i class="fas fa-dollar-sign mr-1"></i>
                                                    <?php echo format_currency($order['total_amount']); ?>
                                                </p>
                                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                                </p>
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                <i class="fas fa-credit-card mr-1"></i>
                                                <?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layouts/base.html';
    }

    public function show($params)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Store the intended URL and redirect to login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit();
        }

        $id = (int)$params['id'];
        $userId = $_SESSION['user_id'];
        $order = null;
        $orderItems = [];
        $error = null;

        try {
            $order = $this->orderModel->getById($id);

            // Verify the order belongs to the current user
            if ($order && $order['user_id'] != $userId) {
                http_response_code(403);
                include __DIR__ . '/../../views/403.html';
                return;
            }

            if ($order) {
                $orderItems = $this->orderModel->getOrderItems($id);
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }

        if ($error) {
            ob_start();
            ?>
            <div class="bg-white">
                <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                    <a href="/orders" class="btn-primary">Back to Orders</a>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            $title = 'Error';
            include __DIR__ . '/../../views/layouts/base.html';
            return;
        }

        if (!$order) {
            http_response_code(404);
            include __DIR__ . '/../../views/404.html';
            return;
        }

        ob_start();
        $title = "Order #" . $order['id'];
        $ord = $order;
        $items = $orderItems;
        $orderError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Order Details</h1>

                <?php if ($orderError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($orderError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                <?php else: ?>
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Order #<?php echo $ord['id']; ?>
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Order placed on <?php echo date('F j, Y \a\t g:i A', strtotime($ord['created_at'])); ?>
                            </p>
                        </div>

                        <div class="border-b border-gray-200">
                            <dl>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <?php echo ucfirst($ord['status']); ?>
                                        </span>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo format_currency($ord['total_amount']); ?>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars(ucfirst($ord['payment_method'])); ?>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Shipping Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($ord['shipping_address']); ?>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Billing Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($ord['billing_address']); ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                        </div>
                        
                        <ul class="border-t border-gray-200 divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-16 w-16 rounded-md object-cover" 
                                             src="<?php echo $item['image_url'] ?: 'https://placehold.co/100x100'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="text-sm text-gray-500">Quantity: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-sm font-medium text-gray-900"><?php echo format_currency($item['unit_price']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo format_currency($item['total_price']); ?></p>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layouts/base.html';
    }

    public function createFromCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'You must be logged in to place an order']);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        $sessionId = session_id();
        $shippingAddress = $_POST['shipping_address'] ?? '';
        $billingAddress = $_POST['billing_address'] ?? $shippingAddress;
        $paymentMethod = $_POST['payment_method'] ?? 'credit_card';

        // Get cart items for the current session
        $cartItems = $this->cartModel->getItems($sessionId);
        
        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['error' => 'Cart is empty']);
            return;
        }

        if (empty($shippingAddress)) {
            http_response_code(400);
            echo json_encode(['error' => 'Shipping address is required']);
            return;
        }

        // Create the order
        $orderId = $this->orderModel->create($userId, $cartItems, $shippingAddress, $billingAddress, $paymentMethod);

        if ($orderId) {
            // Clear the cart after successful order creation
            $this->cartModel->clear($sessionId);

            echo json_encode([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $orderId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create order - please check product availability and try again']);
        }
    }
}