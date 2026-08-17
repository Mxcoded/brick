const restaurantConfig = window.restaurantConfig || {};
const cartRoutes = restaurantConfig.routes || {};

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.store('darkMode', {
        isDarkMode: localStorage.getItem('darkMode') === 'true',
        toggle() {
            this.isDarkMode = !this.isDarkMode;
            localStorage.setItem('darkMode', this.isDarkMode);
        }
    });

    Alpine.data('appData', () => ({
        showMobileMenu: false
    }));

    Alpine.store('cart', {
        items: [],
        isLoading: true,
        isSubmitting: false,
        routes: cartRoutes,

        get totalPrice() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        async fetchApi(route, method, body) {
            try {
                const response = await fetch(route, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(body)
                });
                if (!response.ok) throw new Error('Network response was not ok');
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                this.showAlert('danger', 'An error occurred. Please try again.');
                return null;
            }
        },

        async refreshCart() {
            this.isLoading = true;
            try {
                const response = await fetch(this.routes.get, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Network error');
                const data = await response.json();
                if (data.success) {
                    this.items = data.cart;
                }
            } catch (error) {
                console.error('Error refreshing cart:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async addToCart(itemId, name, price, quantity, instructions) {
            this.isSubmitting = true;
            if (this.items.some(item => item.item_id === itemId && item.instructions === instructions)) {
                this.showAlert('info', `${name} is already in your cart.`);
                const cartEl = document.getElementById('cartModal');
                if (cartEl) new bootstrap.Modal(cartEl).show();
                this.isSubmitting = false;
                return;
            }
            const data = await this.fetchApi(this.routes.add, 'POST', { item_id: itemId, quantity, instructions });
            if (data?.success) {
                this.items = data.cart;
                this.showAlert('success', `${name} has been added to your cart.`);
                const cartEl = document.getElementById('cartModal');
                if (cartEl) new bootstrap.Modal(cartEl).show();
            } else {
                this.showAlert('danger', data?.message || 'Failed to add item.');
            }
            this.isSubmitting = false;
        },

        async updateQuantity(index, quantity) {
            const originalQuantity = this.items[index]?.quantity;
            if (quantity < 1 || !this.items[index]) return;
            this.items[index].quantity = quantity;
            this.isSubmitting = true;
            const data = await this.fetchApi(this.routes.update, 'POST', { index, quantity });
            if (data?.success) {
                this.items = data.cart;
            } else {
                if (this.items[index]) this.items[index].quantity = originalQuantity;
                this.showAlert('danger', data?.message || 'Failed to update cart.');
            }
            this.isSubmitting = false;
        },

        async removeItem(index) {
            const originalItems = [...this.items];
            this.items.splice(index, 1);
            this.isSubmitting = true;
            const data = await this.fetchApi(this.routes.remove, 'POST', { index });
            if (data?.success) {
                this.items = data.cart;
            } else {
                this.items = originalItems;
                this.showAlert('danger', data?.message || 'Failed to remove item.');
            }
            this.isSubmitting = false;
        },

        redirectTo() {
            if (this.routes.cart) window.location.href = this.routes.cart;
        },

        showAlert(type, message) {
            const alertContainer = document.getElementById('cart-alerts');
            if (!alertContainer) return;
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            alertContainer.appendChild(alert);
            setTimeout(() => alert.remove(), 4000);
        }
    });
});
