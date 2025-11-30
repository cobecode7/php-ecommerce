// Main application JavaScript/TypeScript

// Cart functionality
class ShoppingCart {
    private cartItems: any[] = [];
    private cartCountElement: HTMLElement | null;

    constructor() {
        this.cartCountElement = document.getElementById('cart-count');
        this.loadCart();
        this.updateCartCount();
    }

    // Load cart from localStorage
    private loadCart(): void {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            this.cartItems = JSON.parse(savedCart);
        }
    }

    // Save cart to localStorage
    private saveCart(): void {
        localStorage.setItem('cart', JSON.stringify(this.cartItems));
        this.updateCartCount();
    }

    // Add item to cart
    public addItem(productId: number, name: string, price: number, quantity: number = 1): void {
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
    public removeItem(productId: number): void {
        this.cartItems = this.cartItems.filter(item => item.id !== productId);
        this.saveCart();
        this.notify('Item removed from cart!', 'info');
    }

    // Update item quantity
    public updateQuantity(productId: number, quantity: number): void {
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
    public getTotal(): number {
        return this.cartItems.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }

    // Get item count
    public getItemCount(): number {
        return this.cartItems.reduce((count, item) => {
            return count + item.quantity;
        }, 0);
    }

    // Update cart count display
    private updateCartCount(): void {
        if (this.cartCountElement) {
            this.cartCountElement.textContent = this.getItemCount().toString();
        }
    }

    // Show notification
    private notify(message: string, type: string = 'info'): void {
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

    // Add event listeners to "Add to Cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const productId = parseInt(button.getAttribute('data-product-id') || '0');
            const productName = button.getAttribute('data-product-name') || 'Unknown Product';
            const productPrice = parseFloat(button.getAttribute('data-product-price') || '0');
            
            cart.addItem(productId, productName, productPrice);
        });
    });

    // Add event listeners to quantity selectors in cart
    const quantitySelectors = document.querySelectorAll('.quantity-select');
    quantitySelectors.forEach(selector => {
        selector.addEventListener('change', (e) => {
            const selectElement = e.target as HTMLSelectElement;
            const productId = parseInt(selectElement.getAttribute('data-product-id') || '0');
            const newQuantity = parseInt(selectElement.value);
            
            cart.updateQuantity(productId, newQuantity);
        });
    });

    // Add event listeners to remove buttons in cart
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const productId = parseInt(button.getAttribute('data-product-id') || '0');
            cart.removeItem(productId);
            
            // Remove the cart item element from the DOM
            const cartItem = button.closest('.cart-item');
            if (cartItem) {
                cartItem.remove();
            }
        });
    });

    // Initialize search functionality
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchInput = document.getElementById('search') as HTMLInputElement;
            if (searchInput) {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    // In a real app, this would send the search to the server
                    console.log(`Searching for: ${searchTerm}`);
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