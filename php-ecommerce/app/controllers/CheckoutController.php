<?php
/**
 * CheckoutController Class
 * Handles the checkout process
 */

class CheckoutController
{
    private $cartModel;
    private $orderModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
    }

    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Store the intended URL and redirect to login
            $_SESSION['redirect_after_login'] = '/checkout';
            header('Location: /login');
            exit();
        }

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

        // If cart is empty, redirect to cart page
        if (empty($cartItems)) {
            header('Location: /cart');
            exit();
        }

        ob_start();
        $title = 'Checkout';
        $items = $cartItems;
        $total = $cartTotal;
        $itemCount = $cartItemCount;
        $checkoutError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Checkout</h1>

                <?php if ($checkoutError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($checkoutError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                <?php endif; ?>

                <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                    <div class="lg:col-span-1">
                        <div class="bg-white">
                            <h2 class="sr-only">Items in your shopping cart</h2>
                            <ul class="divide-y divide-gray-200 border-b border-t border-gray-200">
                                <?php foreach ($items as $item): ?>
                                <li class="flex py-6 sm:py-10">
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
                                                <p class="text-sm font-medium text-gray-900">Qty: <?php echo $item['quantity']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Order summary and checkout form -->
                    <div class="mt-6 border-t border-gray-200 pt-6 lg:col-span-1 lg:mt-0 lg:border-t-0 lg:pt-0">
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

                        <form id="checkout-form" class="mt-6">
                            <!-- Shipping Address -->
                            <div class="mt-8">
                                <h3 class="text-lg font-medium text-gray-900">Shipping Address</h3>
                                <div class="mt-4 space-y-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="first-name" class="block text-sm font-medium text-gray-700">First name</label>
                                            <input type="text" id="first-name" name="first_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="last-name" class="block text-sm font-medium text-gray-700">Last name</label>
                                            <input type="text" id="last-name" name="last_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="company" class="block text-sm font-medium text-gray-700">Company (optional)</label>
                                        <input type="text" id="company" name="company" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <input type="text" id="address" name="address" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="apartment" class="block text-sm font-medium text-gray-700">Apartment, suite, etc. (optional)</label>
                                        <input type="text" id="apartment" name="apartment" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                            <input type="text" id="city" name="city" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                            <select id="state" name="state" required class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Select</option>
                                                <option value="AL">Alabama</option>
                                                <option value="AK">Alaska</option>
                                                <option value="AZ">Arizona</option>
                                                <option value="AR">Arkansas</option>
                                                <option value="CA">California</option>
                                                <!-- Add more states as needed -->
                                            </select>
                                        </div>
                                        <div>
                                            <label for="zip" class="block text-sm font-medium text-gray-700">ZIP / Postal</label>
                                            <input type="text" id="zip" name="zip" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                        <select id="country" name="country" required class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="US">United States</option>
                                            <!-- Add more countries as needed -->
                                        </select>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone (optional)</label>
                                        <input type="tel" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="mt-8">
                                <h3 class="text-lg font-medium text-gray-900">Payment Method</h3>
                                <div class="mt-4 space-y-6">
                                    <div class="flex items-center">
                                        <input id="payment-credit" name="payment_method" type="radio" value="credit_card" checked class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <label for="payment-credit" class="ml-3 block text-sm font-medium text-gray-700">
                                            Credit Card
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="payment-paypal" name="payment_method" type="radio" value="paypal" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <label for="payment-paypal" class="ml-3 block text-sm font-medium text-gray-700">
                                            PayPal
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10">
                                <button type="submit" id="complete-order-btn" class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Complete Order
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 text-center text-sm text-gray-500">
                            <p>
                                or
                                <a href="/cart" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    Return to Cart
                                    <span aria-hidden="true"> &larr;</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('checkout-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Collect form data
                const formData = new FormData(this);
                
                // Create shipping address string
                const firstName = formData.get('first_name');
                const lastName = formData.get('last_name');
                const address = formData.get('address');
                const apartment = formData.get('apartment');
                const city = formData.get('city');
                const state = formData.get('state');
                const zip = formData.get('zip');
                const country = formData.get('country');
                
                const shippingAddress = `${firstName} ${lastName}, ${address} ${apartment ? apartment + ', ' : ''}${city}, ${state} ${zip}, ${country}`;
                const billingAddress = shippingAddress; // For simplicity, using same as shipping
                
                const paymentMethod = formData.get('payment_method');
                
                // Submit order
                try {
                    const response = await fetch('/order/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `shipping_address=${encodeURIComponent(shippingAddress)}&billing_address=${encodeURIComponent(billingAddress)}&payment_method=${encodeURIComponent(paymentMethod)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Redirect to order confirmation page
                        window.location.href = `/order/${result.order_id}`;
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error occurred'));
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    alert('An error occurred while creating your order. Please try again.');
                }
            });
        </script>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layouts/base.html';
    }
}