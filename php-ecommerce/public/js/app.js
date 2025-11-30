// Main application JavaScript

// Cart functionality
class ShoppingCart {
    constructor() {
        this.cartItems = [];
        this.cartCountElement = document.getElementById('cart-count');
        this.loadCart();
        this.updateCartCount();
    }

    // Load cart from localStorage
    loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            this.cartItems = JSON.parse(savedCart);
        }
    }

    // Save cart to localStorage
    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cartItems));
        this.updateCartCount();
    }

    // Add item to cart
    addItem(productId, name, price, quantity = 1) {
        const existingItem = this.cartItems.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cartItems.push({
                id: productId,
                name: name,
                price: price,
                quantity: quantity
            });
        }
        
        this.saveCart();
        this.notify('Item added to cart!', 'success');
    }

    // Remove item from cart
    removeItem(productId) {
        this.cartItems = this.cartItems.filter(item => item.id !== productId);
        this.saveCart();
        this.notify('Item removed from cart!', 'info');
    }

    // Update item quantity
    updateQuantity(productId, quantity) {
        if (quantity <= 0) {
            this.removeItem(productId);
            return;
        }

        const item = this.cartItems.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            this.saveCart();
        }
    }

    // Get cart total
    getTotal() {
        return this.cartItems.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }

    // Get item count
    getItemCount() {
        return this.cartItems.reduce((count, item) => {
            return count + item.quantity;
        }, 0);
    }

    // Update cart count display
    updateCartCount() {
        if (this.cartCountElement) {
            this.cartCountElement.textContent = this.getItemCount().toString();
        }
    }

    // Show notification
    notify(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fixed top-4 right-4 z-50`;
        notification.textContent = message;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const cart = new ShoppingCart();

    // Function to update cart count from server
    async function updateCartCountFromServer() {
        try {
            const response = await fetch('/api/cart/count');
            const data = await response.json();

            if (data.count !== undefined) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                }
            }
        } catch (error) {
            console.error('Error fetching cart count:', error);
        }
    }

    // Update cart count when page loads
    updateCartCountFromServer();

    // Add event listeners to "Add to Cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    if (addToCartButtons) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();

                const productId = parseInt(button.getAttribute('data-product-id') || '0');
                const productName = button.getAttribute('data-product-name') || 'Unknown Product';
                const productPrice = parseFloat(button.getAttribute('data-product-price') || '0');

                // Add to server-side cart via AJAX
                try {
                    const response = await fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=1`
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update server-side cart count
                        updateCartCountFromServer();

                        // Also update local storage and UI
                        cart.addItem(productId, productName, productPrice);

                        // Show notification
                        cart.notify(result.message, 'success');
                    } else {
                        cart.notify(result.error || 'Failed to add item to cart', 'error');
                    }
                } catch (error) {
                    cart.notify('Network error, please try again', 'error');
                    console.error('Error adding to cart:', error);
                }
            });
        });
    }

    // Add event listeners to quantity selectors in cart
    const quantitySelectors = document.querySelectorAll('.quantity-select');
    if (quantitySelectors) {
        quantitySelectors.forEach(selector => {
            selector.addEventListener('change', async (e) => {
                const selectElement = e.target;
                const productId = parseInt(selectElement.getAttribute('data-product-id') || '0');
                const newQuantity = parseInt(selectElement.value);

                // Update server-side cart via AJAX
                try {
                    const response = await fetch('/cart/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=${newQuantity}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update server-side cart count
                        updateCartCountFromServer();

                        // Also update local storage and UI
                        cart.updateQuantity(productId, newQuantity);
                    } else {
                        cart.notify(result.error || 'Failed to update quantity', 'error');
                        // Reset to previous value
                        selectElement.value = selectElement.oldValue;
                    }
                } catch (error) {
                    cart.notify('Network error, please try again', 'error');
                    console.error('Error updating quantity:', error);
                    // Reset to previous value
                    selectElement.value = selectElement.oldValue;
                }

                // Store the old value for reset if needed
                selectElement.oldValue = newQuantity;
            });
        });
    }

    // Add event listeners to remove buttons in cart
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    if (removeButtons) {
        removeButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();

                const productId = parseInt(button.getAttribute('data-product-id') || '0');

                // Remove from server-side cart via AJAX
                try {
                    const response = await fetch('/cart/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update server-side cart count
                        updateCartCountFromServer();

                        // Also update local storage and UI
                        cart.removeItem(productId);

                        // Remove the cart item element from the DOM
                        const cartItem = button.closest('.cart-item');
                        if (cartItem) {
                            cartItem.remove();
                        }
                    } else {
                        cart.notify(result.error || 'Failed to remove item from cart', 'error');
                    }
                } catch (error) {
                    cart.notify('Network error, please try again', 'error');
                    console.error('Error removing from cart:', error);
                }
            });
        });
    }

    // Initialize search functionality
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    window.location.href = `/products?search=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
    }

    // Mobile menu toggle
    const menuButton = document.getElementById('mobile-menu-button');
    if (menuButton) {
        menuButton.addEventListener('click', () => {
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu) {
                mobileMenu.classList.toggle('hidden');
            }
        });
    }
});