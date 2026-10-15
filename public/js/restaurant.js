const restaurantConfig = window.restaurantConfig || {};
const cartRoutes = restaurantConfig.routes || {};

// Request bookkeeping lives out here rather than on the store, so Alpine never
// makes it reactive. Only one basket read is ever in flight: a newer read
// supersedes an older one instead of racing it, so a slow reply cannot land
// after a newer one and resurrect a basket the customer has since changed.
let cartRequest = null;

function supersedeCartRequest() {
    if (cartRequest) {
        cartRequest.abort();
        cartRequest = null;
    }
}

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
        // A basket is not loading until something asks it to load. Seeding this
        // as true made "loading" the resting state of the panel, so any path
        // that failed to start a read left the customer watching a spinner that
        // never resolved. The modal sets it synchronously on open instead.
        isLoading: false,
        loadError: null,
        isSubmitting: false,
        routes: cartRoutes,

        /**
         * Ceiling on a basket read, in milliseconds.
         *
         * `isLoading` is only cleared once a request settles. A request that
         * never settles would therefore hold the panel in its loading state
         * forever and block checkout, so the read gives up instead of hanging.
         */
        refreshTimeoutMs: 8000,
        refreshSequence: 0,

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

        /**
         * Re-read the basket from the server, which owns it.
         *
         * Leaves the panel in one of three settled states, never a fourth: the
         * basket as the server describes it, a retryable error, or the previous
         * basket with a warning. It never leaves `isLoading` set.
         */
        async refreshCart() {
            supersedeCartRequest();

            const sequence = ++this.refreshSequence;
            const controller = new AbortController();
            cartRequest = controller;
            const timer = setTimeout(() => controller.abort(), this.refreshTimeoutMs);

            this.isLoading = true;
            this.loadError = null;

            try {
                const response = await fetch(this.routes.get, {
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Basket request failed: ' + response.status);
                const data = await response.json();

                // A newer read already started, so this reply is stale.
                if (sequence !== this.refreshSequence) return;

                if (data.success) {
                    this.items = Array.isArray(data.cart) ? data.cart : [];
                } else {
                    this.loadError = data.message || 'We could not load your basket.';
                }
            } catch (error) {
                if (sequence !== this.refreshSequence) return;

                console.error('Error refreshing cart:', error);

                // The items still held are the last thing the server confirmed,
                // so they are kept. A failed read must not present itself as the
                // customer having emptied their own order, and must not strand
                // them on a spinner with no way forward.
                this.loadError = error.name === 'AbortError'
                    ? 'Your basket took too long to load. Check your connection and try again.'
                    : 'We could not load your basket. Check your connection and try again.';
            } finally {
                clearTimeout(timer);
                if (cartRequest === controller) cartRequest = null;
                if (sequence === this.refreshSequence) this.isLoading = false;
            }
        },

        /**
         * Adopt a basket the server has just confirmed.
         *
         * Clears any earlier read failure, because a response carrying the new
         * basket is itself proof that the connection is working. Without this a
         * customer who recovered by adding a dish would still be looking at a
         * stale "could not load" banner above their items.
         */
        applyServerCart(data) {
            this.items = Array.isArray(data?.cart) ? data.cart : [];
            this.loadError = null;
        },

        /**
         * Add a dish to the basket.
         *
         * The basket panel deliberately stays closed. Opening a modal on every
         * tap interrupts a customer who is still reading the menu, and on a
         * phone the panel covers the list they want to come back to. The
         * confirmation toast plus the growing "My order" button is enough to
         * show the tap landed, and the basket opens on request.
         */
        async addToCart(itemId, name, price, quantity, instructions) {
            this.isSubmitting = true;
            if (this.items.some(item => item.item_id === itemId && item.instructions === instructions)) {
                this.showAlert('info', `${name} is already in your order. Press + if you want more.`);
                this.isSubmitting = false;
                return;
            }
            const data = await this.fetchApi(this.routes.add, 'POST', { item_id: itemId, quantity, instructions });
            if (data?.success) {
                this.applyServerCart(data);
                this.showAlert('success', `${name} added. Tap "My order" when you are ready.`);
            } else {
                this.showAlert('danger', data?.message || 'We could not add that. Please try again.');
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
                this.applyServerCart(data);
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
                this.applyServerCart(data);
            } else {
                this.items = originalItems;
                this.showAlert('danger', data?.message || 'Failed to remove item.');
            }
            this.isSubmitting = false;
        },

        redirectTo() {
            if (this.routes.cart) window.location.href = this.routes.cart;
        },

        /**
         * Build a notice without ever parsing the message as HTML.
         *
         * The message carries item names and validation copy, and item names
         * are data rather than code, so they are attached as text. The icon is
         * the only markup here, and the set is closed to the two values below,
         * so nothing caller-supplied reaches the parser.
         */
        showAlert(type, message) {
            const alertContainer = document.getElementById('cart-alerts');
            if (!alertContainer) return;

            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.setAttribute('role', type === 'danger' ? 'alert' : 'status');
            alert.setAttribute('aria-live', type === 'danger' ? 'assertive' : 'polite');

            const icon = document.createElement('i');
            icon.className = `fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2`;

            const text = document.createElement('span');
            text.className = 'flex-grow-1';
            text.textContent = message;

            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'btn-close';
            close.setAttribute('data-bs-dismiss', 'alert');
            close.setAttribute('aria-label', 'Close');

            alert.append(icon, text, close);
            alertContainer.appendChild(alert);

            setTimeout(() => alert.remove(), 4000);
        }
    });
});
