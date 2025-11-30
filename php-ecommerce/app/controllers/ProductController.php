<?php
/**
 * Enhanced ProductController with search and filtering capabilities
 */

class ProductController
{
    private $productModel;
    
    public function __construct()
    {
        $this->productModel = new Product();
    }
    
    public function index()
    {
        // Get query parameters for filtering
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';

        $products = [];
        $error = null;

        try {
            if ($search) {
                // Perform search if search term is provided
                $products = $this->productModel->search($search);
            } else {
                // Get all products or filter by category
                if ($category) {
                    $products = $this->productModel->getByCategory($category);
                } else {
                    $products = $this->productModel->getAll();
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
        
        // Apply price filtering if specified
        if ($minPrice !== null || $maxPrice !== null) {
            $filteredProducts = [];
            foreach ($products as $product) {
                $price = $product['price'];
                if (($minPrice === null || $price >= $minPrice) &&
                    ($maxPrice === null || $price <= $maxPrice)) {
                    $filteredProducts[] = $product;
                }
            }
            $products = $filteredProducts;
        }

        // Apply sorting
        if ($sort === 'price_low') {
            usort($products, function($a, $b) {
                return $a['price'] <=> $b['price'];
            });
        } elseif ($sort === 'price_high') {
            usort($products, function($a, $b) {
                return $b['price'] <=> $a['price'];
            });
        } elseif ($sort === 'name') {
            usort($products, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }
        // Default is 'newest' which is already handled by the model

        ob_start();
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Products</h1>

                    <!-- Search and filter controls -->
                    <form method="GET" class="flex space-x-2">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search products..."
                            value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            class="max-w-xs border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >

                        <select
                            name="sort"
                            class="border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        </select>

                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </form>
                </div>

                <?php if ($error): ?>
                <!-- Error message if database error occurs -->
                <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    <br>
                    <small>Please make sure your database is configured and running.</small>
                </div>
                <?php else: ?>
                <!-- Results info -->
                <p class="text-sm text-gray-700 mb-6">
                    Showing <span class="font-medium"><?php echo count($products); ?></span> results
                    <?php if ($search): ?>
                        for "<span class="font-medium"><?php echo htmlspecialchars($search); ?></span>"
                    <?php endif; ?>
                </p>

                <!-- Products grid -->
                <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                    <?php foreach ($products as $product): ?>
                    <div class="group relative">
                        <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden lg:aspect-none group-hover:opacity-75 lg:h-80">
                            <img src="<?php echo $product['image_url'] ?: 'https://placehold.co/400x400'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                        </div>
                        <div class="mt-4 flex justify-between">
                            <div>
                                <h3 class="text-sm text-gray-700">
                                    <a href="/product/<?php echo $product['id']; ?>">
                                        <span aria-hidden="true" class="absolute inset-0"></span>
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    <?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...
                                </p>
                            </div>
                            <p class="text-base font-medium text-gray-900"><?php echo format_currency($product['price']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Products';
        include __DIR__ . '/../../views/layouts/base.html';
    }
    
    public function show($params)
    {
        $id = (int)$params['id'];
        $product = null;
        $error = null;

        try {
            $product = $this->productModel->getById($id);
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }

        if ($error) {
            // Handle database error
            ob_start();
            ?>
            <div class="bg-white">
                <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                    <a href="/products" class="btn-primary">Back to Products</a>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            $title = 'Error';
            include __DIR__ . '/../../views/layouts/base.html';
            return;
        }

        if (!$product) {
            http_response_code(404);
            include __DIR__ . '/../../views/404.html';
            return;
        }

        ob_start();
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
                    <!-- Image gallery -->
                    <div class="flex flex-col-reverse">
                        <!-- Image selector -->
                        <div class="hidden mt-6 w-full max-w-2xl mx-auto sm:block lg:max-w-none">
                            <div class="grid grid-cols-4 gap-6" aria-orientation="horizontal" role="tablist">
                                <button class="relative h-24 bg-white rounded-md flex items-center justify-center text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-offset focus:ring-indigo-500" role="tab" type="button">
                                    <span class="sr-only">Angled view</span>
                                    <span class="absolute w-full h-full inset-0 overflow-hidden rounded-md">
                                        <img src="<?php echo $product['image_url'] ?: 'https://placehold.co/400x400'; ?>" alt="" class="w-full h-full object-center object-cover">
                                    </span>
                                    <span class="absolute inset-0 rounded-md ring-2 ring-offset-2 ring-indigo-500" aria-hidden="true"></span>
                                </button>
        
                                <!-- More thumbnails... -->
                            </div>
                        </div>
        
                        <div class="w-full aspect-w-1 aspect-h-1">
                            <img src="<?php echo $product['image_url'] ?: 'https://placehold.co/600x600'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-center object-cover sm:rounded-lg">
                        </div>
                    </div>
        
                    <!-- Product info -->
                    <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h1>
        
                        <div class="mt-3">
                            <h2 class="sr-only">Product information</h2>
                            <p class="text-3xl text-gray-900"><?php echo format_currency($product['price']); ?></p>
                        </div>
        
                        <!-- Reviews -->
                        <div class="mt-3">
                            <h3 class="sr-only">Reviews</h3>
                            <div class="flex items-center">
                                <div class="flex items-center">
                                    <!-- Stars -->
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-gray-300"></i>
                                </div>
                                <p class="sr-only">4 out of 5 stars</p>
                                <a href="#" class="ml-3 text-sm font-medium text-indigo-600 hover:text-indigo-500">117 reviews</a>
                            </div>
                        </div>
        
                        <div class="mt-6">
                            <h3 class="sr-only">Description</h3>
                            <div class="text-base text-gray-700">
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                            </div>
                        </div>
        
                        <form class="mt-6">
                            <!-- Colors -->
                            <div>
                                <h3 class="text-sm text-gray-900 font-medium">Color</h3>
                                <div class="mt-4 flex space-x-3">
                                    <button type="button" class="relative w-8 h-8 rounded-full bg-gray-900 border border-gray-300 focus:outline-none">
                                        <span class="sr-only">Gray</span>
                                    </button>
                                    <button type="button" class="relative w-8 h-8 rounded-full bg-white border border-gray-300 focus:outline-none">
                                        <span class="sr-only">White</span>
                                    </button>
                                    <button type="button" class="relative w-8 h-8 rounded-full bg-red-600 border border-gray-300 focus:outline-none">
                                        <span class="sr-only">Red</span>
                                    </button>
                                </div>
                            </div>
        
                            <!-- Sizes -->
                            <div class="mt-6">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm text-gray-900 font-medium">Size</h3>
                                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Size guide</a>
                                </div>
        
                                <div class="mt-4 grid grid-cols-3 gap-3">
                                    <button type="button" class="flex items-center justify-center px-3 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:flex-1">
                                        S
                                    </button>
                                    <button type="button" class="flex items-center justify-center px-3 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:flex-1">
                                        M
                                    </button>
                                    <button type="button" class="flex items-center justify-center px-3 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:flex-1">
                                        L
                                    </button>
                                </div>
                            </div>
        
                            <button type="submit" class="mt-8 w-full bg-indigo-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 add-to-cart-btn" 
                                data-product-id="<?php echo $product['id']; ?>" 
                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                data-product-price="<?php echo $product['price']; ?>">
                                Add to cart
                            </button>
                        </form>
        
                        <!-- Product details -->
                        <section class="mt-12">
                            <h2 class="text-sm font-medium text-gray-900">Product Details</h2>
        
                            <div class="mt-4 space-y-6">
                                <p class="text-sm text-gray-600">
                                    The <?php echo htmlspecialchars($product['name']); ?> features premium materials and craftsmanship. With its sleek design and attention to detail, this product is both functional and stylish.
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = htmlspecialchars($product['name']);
        include __DIR__ . '/../../views/layouts/base.html';
    }
    
    /**
     * Search endpoint for AJAX requests
     */
    public function search()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['q'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            return;
        }
        
        $searchTerm = $_GET['q'];
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $results = $this->productModel->search($searchTerm, $limit);
        
        header('Content-Type: application/json');
        echo json_encode([
            'results' => $results,
            'count' => count($results)
        ]);
    }
}