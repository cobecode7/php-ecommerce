<?php
/**
 * CategoryController Class
 * Handles category-related requests
 */

class CategoryController
{
    private $categoryModel;
    private $productModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
        $this->productModel = new Product();
    }

    public function index()
    {
        $categories = [];
        $error = null;

        try {
            $categories = $this->categoryModel->getAll();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }

        ob_start();
        $title = 'Categories';
        $cats = $categories;
        $catError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Shop by Category</h1>

                <?php if ($catError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($catError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                <?php elseif (empty($cats)): ?>
                    <div class="text-center py-12">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">No categories available</h2>
                        <p class="text-gray-600 mb-6">There are currently no categories in our store.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                        <?php foreach ($cats as $cat): ?>
                        <a href="/products?category=<?php echo $cat['id']; ?>" class="group">
                            <div class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden xl:aspect-w-7 xl:aspect-h-8">
                                <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="w-full h-full object-center object-cover group-hover:opacity-75">
                            </div>
                            <h3 class="mt-4 text-sm text-gray-700"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p class="mt-1 text-lg font-medium text-gray-900"><?php echo htmlspecialchars($cat['description']); ?></p>
                        </a>
                        <?php endforeach; ?>
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
        $id = (int)$params['id'];
        $category = null;
        $products = [];
        $error = null;

        try {
            $category = $this->categoryModel->getById($id);
            if ($category) {
                $products = $this->productModel->getByCategory($id);
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
                    <a href="/categories" class="btn-primary">Back to Categories</a>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            $title = 'Error';
            include __DIR__ . '/../../views/layouts/base.html';
            return;
        }

        if (!$category) {
            http_response_code(404);
            include __DIR__ . '/../../views/404.html';
            return;
        }

        ob_start();
        $title = htmlspecialchars($category['name']);
        $cat = $category;
        $prods = $products;
        $catError = $error;
        ?>
        <div class="bg-white">
            <div class="max-w-2xl mx-auto py-4 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8"><?php echo htmlspecialchars($cat['name']); ?></h1>

                <?php if ($catError): ?>
                    <!-- Error message if database error occurs -->
                    <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <strong>Error:</strong> <?php echo htmlspecialchars($catError); ?>
                        <br>
                        <small>Please make sure your database is configured and running.</small>
                    </div>
                <?php elseif (empty($prods)): ?>
                    <div class="text-center py-12">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">No products in this category</h2>
                        <p class="text-gray-600 mb-6">There are currently no products in <?php echo htmlspecialchars($cat['name']); ?>.</p>
                        <a href="/products" class="btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <!-- Products grid -->
                    <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                        <?php foreach ($prods as $product): ?>
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
        include __DIR__ . '/../../views/layouts/base.html';
    }
}