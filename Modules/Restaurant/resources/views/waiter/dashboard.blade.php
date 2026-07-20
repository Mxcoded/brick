@extends('restaurant::layouts.master')
@section('title', 'Waiter POS')
@section('hideNav', true)

@section('head')
<script src="https://js.paystack.co/v1/inline.js"></script>
<style>
:root {
    --pos-header: #1a1d23;
    --pos-sidebar: #f8f9fa;
    --pos-card-bg: #ffffff;
    --pos-accent: #0d6efd;
    --pos-success: #198754;
    --pos-danger: #dc3545;
}

body.dark-mode .waiter-pos {
    --pos-header: #141517;
    --pos-sidebar: #1e1f23;
    --pos-card-bg: #2a2b30;
}

.waiter-pos {
    background: var(--pos-sidebar);
    height: 100vh;
    overflow: hidden;
    font-size: 0.9rem;
}

.pos-header {
    background: var(--pos-header);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #fff;
    flex-shrink: 0;
}

.pos-header .search-box { max-width: 280px; }

.pos-header .search-box input {
    border-radius: 20px;
    border: none;
    padding: 0.35rem 0.9rem;
    font-size: 0.85rem;
    background: rgba(255,255,255,0.12);
    color: #fff;
    width: 100%;
}

.pos-header .search-box input::placeholder { color: rgba(255,255,255,0.5); }
.pos-header .search-box input:focus { outline: none; background: rgba(255,255,255,0.2); }

.shift-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    background: rgba(255,255,255,0.1);
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    color: #fff;
    white-space: nowrap;
}

.shift-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shift-card {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

body.dark-mode .shift-card {
    background: var(--pos-card-bg);
    color: #e0e0e0;
}

.floor-plan {
    padding: 0.6rem 1.25rem;
    background: var(--pos-card-bg);
    border-bottom: 1px solid rgba(0,0,0,0.06);
    flex-shrink: 0;
    overflow-x: auto;
}

.floor-plan .section-group {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
}

.floor-plan .section {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.floor-plan .section-label {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #adb5bd;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.floor-plan .section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(0,0,0,0.06);
}

.floor-plan .tables-row {
    display: flex;
    gap: 0.35rem;
    flex-wrap: nowrap;
}

.table-btn {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 2px solid #dee2e6;
    background: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    position: relative;
    color: #495057;
}
.table-btn .table-icon { font-size: 0.75rem; line-height: 1; }
.table-btn .table-label { font-size: 0.55rem; line-height: 1; opacity: 0.6; }
.table-btn:hover { border-color: #adb5bd; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.table-btn:active { transform: scale(0.95); }
.table-btn.active { background: #1a1d23; color: #fff; border-color: #1a1d23; }
.table-btn.occupied { border-color: #dc3545; background: #fff5f5; color: #dc3545; }
.table-btn.occupied.active { background: #dc3545; color: #fff; border-color: #dc3545; }
.table-btn.occupied::after {
    content: '';
    position: absolute;
    top: 3px;
    right: 3px;
    width: 7px;
    height: 7px;
    background: #dc3545;
    border-radius: 50%;
}
.table-btn.walk-in { border-color: #198754; color: #198754; width: auto; height: auto; padding: 0.35rem 0.85rem; flex-direction: row; gap: 0.35rem; border-radius: 20px; font-size: 0.75rem; }
.table-btn.walk-in:hover { background: #d1e7dd; }
.table-btn.walk-in.active { background: #198754; color: #fff; border-color: #198754; }

body.dark-mode .table-btn {
    background: #2a2b30;
    color: #e0e0e0;
    border-color: #3a3b40;
}
body.dark-mode .table-btn:hover { background: rgba(255,255,255,0.1); }
body.dark-mode .table-btn.occupied { border-color: #dc3545; background: rgba(220,53,69,0.15); color: #f08a8a; }
body.dark-mode .table-btn.occupied.active { background: #dc3545; color: #fff; }
body.dark-mode .table-btn.walk-in { border-color: #198754; color: #75b798; }
body.dark-mode .table-btn.walk-in:hover { background: rgba(25,135,84,0.15); }
body.dark-mode .table-btn.walk-in.active { background: #198754; color: #fff; }

.cat-strip {
    padding: 0.5rem 1.25rem;
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    flex-shrink: 0;
}

.cat-btn {
    border-radius: 20px;
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    white-space: nowrap;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #495057;
    transition: all 0.15s;
    cursor: pointer;
    flex-shrink: 0;
}

.cat-btn:hover { background: #e9ecef; }
.cat-btn.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

body.dark-mode .cat-btn {
    background: var(--pos-card-bg);
    color: #e0e0e0;
    border-color: rgba(255,255,255,0.15);
}

body.dark-mode .cat-btn:hover { background: rgba(255,255,255,0.1); }
body.dark-mode .cat-btn.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

.item-grid {
    padding: 0.75rem 1.25rem 1rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.item-grid .item-card {
    border: 1px solid rgba(0,0,0,0.07) !important;
    border-radius: 12px;
    background: var(--pos-card-bg);
    transition: all 0.15s;
    cursor: pointer;
    width: 100%;
    padding: 0.85rem 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.item-grid .item-card:hover {
    border-color: var(--pos-accent) !important;
    box-shadow: 0 2px 12px rgba(13,110,253,0.12);
    transform: translateY(-1px);
}

.item-grid .item-card:active { transform: scale(0.97); }
.item-grid .item-card:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
    filter: grayscale(0.4);
}

body.dark-mode .item-grid .item-card:disabled {
    opacity: 0.35;
    filter: grayscale(0.6);
}

.item-grid .item-card .item-icon { font-size: 1.8rem; line-height: 1; }
.item-grid .item-card .item-name { font-weight: 600; font-size: 0.8rem; text-align: center; line-height: 1.2; }
.item-grid .item-card .item-price { color: var(--pos-accent); font-weight: 700; font-size: 0.85rem; }

.no-source-hint {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #adb5bd;
    gap: 0.4rem;
    padding: 2rem;
}

.no-source-hint i { font-size: 2.5rem; }
.no-source-hint p { margin: 0; font-size: 0.9rem; text-align: center; }

body.dark-mode .no-source-hint { color: #6c757d; }

.cart-panel {
    background: var(--pos-card-bg);
    border-left: 1px solid rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.cart-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
    min-height: 0;
}

.cart-item {
    border: none !important;
    border-radius: 10px;
    background: var(--pos-sidebar);
    margin-bottom: 0.4rem;
    transition: all 0.15s;
}

.cart-item .btn-remove {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    padding: 0;
    transition: all 0.15s;
}

.cart-item .btn-remove:hover { background: rgba(220,53,69,0.1); }

body.dark-mode .cart-item .btn-remove:hover { background: rgba(220,53,69,0.25); }

.cart-item .qty-group {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.cart-item .qty-group button {
    border: none;
    background: transparent;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
}

.cart-item .qty-group button:hover { background: rgba(0,0,0,0.05); }
.cart-item .qty-group input {
    width: 32px;
    text-align: center;
    border: none;
    border-left: 1px solid rgba(0,0,0,0.1);
    border-right: 1px solid rgba(0,0,0,0.1);
    background: transparent;
    font-size: 0.8rem;
    padding: 0;
    -moz-appearance: textfield;
}

.cart-item .qty-group input::-webkit-outer-spin-button,
.cart-item .qty-group input::-webkit-inner-spin-button { -webkit-appearance: none; }

.cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #adb5bd;
}

.cart-footer {
    padding: 1rem;
    border-top: 1px solid rgba(0,0,0,0.06);
    flex-shrink: 0;
}

.breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.15rem 0;
    font-size: 0.82rem;
}

.breakdown-row .text-muted { font-size: 0.8rem; }

.toast-pos {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.toast-pos .toast {
    min-width: 260px;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.order-tray {
    display: flex;
    background: var(--pos-card-bg);
    border-top: 1px solid rgba(0,0,0,0.08);
    flex-shrink: 0;
}

.order-tray .nav-item { flex: 1; }
.order-tray .nav-link {
    border-radius: 0;
    padding: 0.6rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    text-align: center;
}

.order-tray .nav-link {
    position: relative;
}

.order-tray .nav-link.active {
    background: var(--pos-accent) !important;
    color: #fff !important;
}

.order-tray .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #fff;
    border-radius: 3px 3px 0 0;
}

.order-tray .nav-link:not(.active) {
    color: #6c757d;
}

.order-tray .nav-link:not(.active):hover {
    background: rgba(0,0,0,0.04);
    color: #495057;
}

body.dark-mode .order-tray .nav-link:not(.active):hover {
    background: rgba(255,255,255,0.08);
    color: #e0e0e0;
}

.order-panel {
    display: none;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.order-panel.open { display: flex; }

.order-cards {
    padding: 1rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.ticket-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.07);
    margin-bottom: 0.75rem;
    background: var(--pos-card-bg);
    border-left: 4px solid #0d6efd;
    overflow: hidden;
}

.ticket-card.pending-border { border-left-color: #ffc107; }
.ticket-card.active-border { border-left-color: #0d6efd; }

.ticket-card .ticket-header {
    padding: 0.6rem 1rem;
    border-bottom: 2px dashed rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-card .ticket-body { padding: 0.6rem 1rem; }
.ticket-card .ticket-footer { padding: 0.6rem 1rem; border-top: 1px solid rgba(0,0,0,0.06); }

@media (max-width: 991.98px) {
    .waiter-pos .cart-panel {
        position: fixed;
        right: 0;
        top: 0;
        width: 85%;
        max-width: 360px;
        z-index: 1050;
        box-shadow: -4px 0 20px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.25s;
    }

    .waiter-pos .cart-panel.open { transform: translateX(0); }
    .cart-toggle-mobile {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 1040;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--pos-accent);
        color: #fff;
        border: none;
        box-shadow: 0 4px 16px rgba(13,110,253,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
}

@media (min-width: 992px) { .cart-toggle-mobile { display: none !important; } }
</style>
@endSection

@section('content')
<div class="waiter-pos d-flex flex-column" x-data="posState">
    <div class="toast-pos" id="toastContainer"></div>

    <div class="pos-header">
        <h6 class="fw-bold mb-0 me-2"><i class="bi bi-calculator me-2"></i>POS</h6>
        <div class="search-box flex-grow-1">
            <input type="text" placeholder="Search items..." x-model="searchQuery">
        </div>
        <template x-if="shiftData">
            <div class="shift-badge">
                <i class="bi bi-clock-history"></i>
                <span x-text="'Since ' + shiftTime"></span>
                <span class="badge bg-light text-dark ms-1" x-text="'₦' + Number(shiftData.total_sales).toLocaleString()"></span>
                <button class="btn btn-sm btn-outline-light rounded-pill ms-1 py-0 px-2" data-bs-toggle="modal" data-bs-target="#endShiftModal">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </template>
        <button class="btn btn-sm btn-outline-light rounded-pill" @click="openCartPanel">
            <i class="bi bi-cart me-1"></i>Cart
            <span class="badge bg-light text-dark ms-1" x-show="cart.length" x-text="cart.length"></span>
        </button>
    </div>

    <div class="shift-overlay" x-show="!shiftData" style="display: none;">
        <div class="shift-card">
            <i class="bi bi-clock" style="font-size: 3rem; color: var(--pos-accent);"></i>
            <h4 class="fw-bold mt-3 mb-2">Start Your Shift</h4>
            <p class="text-muted small mb-4">Clock in to begin taking orders.</p>
            <div class="mb-3 text-start">
                <label class="form-label small fw-medium">Starting Cash (₦)</label>
                <input type="number" class="form-control form-control-lg text-center" x-model="startingCash" min="0" step="100" placeholder="0">
            </div>
            <button class="btn btn-primary btn-lg w-100 fw-bold rounded-pill" @click="startShift" :disabled="shiftLoading">
                <span x-show="!shiftLoading"><i class="bi bi-play-fill me-1"></i>Start Shift</span>
                <span x-show="shiftLoading"><span class="spinner-border spinner-border-sm me-1"></span>Starting...</span>
            </button>
        </div>
    </div>

    <div class="d-flex flex-grow-1 overflow-hidden">
        <div class="col-lg-7 col-12 d-flex flex-column overflow-hidden" style="min-width: 0;">
            <div class="floor-plan">
                <div class="section-group">
                    <template x-for="(group, section) in sectionedTables" :key="section">
                        <div class="section">
                            <div class="section-label"><span x-text="section"></span></div>
                            <div class="tables-row">
                                <template x-for="table in group" :key="table.id">
                                    <button class="table-btn"
                                        :class="{
                                            active: selectedTable === table.id,
                                            occupied: occupiedTableIds.includes(table.id) && selectedTable !== table.id
                                        }"
                                        @click="selectTable(table.id, table.number)">
                                        <span class="table-icon">🍽</span>
                                        <span class="table-label" x-text="table.number"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div class="section">
                        <div class="section-label">Quick</div>
                        <button class="table-btn walk-in"
                            :class="{ active: selectedWalkIn }"
                            @click="selectWalkIn">
                            <i class="bi bi-person-plus me-1"></i>Walk-in
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-3 pt-1 pb-0 d-flex align-items-center gap-2" x-show="selectedTable || selectedWalkIn">
                <template x-if="selectedTable">
                    <span class="badge bg-dark rounded-pill px-3 py-1.5">
                        <i class="bi bi-table me-1"></i>Table <span x-text="selectedTableNumber"></span>
                    </span>
                </template>
                <template x-if="selectedWalkIn">
                    <span class="badge bg-success rounded-pill px-3 py-1.5">
                        <i class="bi bi-person me-1"></i>Walk-in
                    </span>
                </template>
                <span class="text-muted small">Select items to add to order</span>
            </div>

            <div class="cat-strip">
                <template x-for="cat in categories" :key="cat.id">
                    <button class="cat-btn"
                        :class="{ active: activeCategory === cat.id }"
                        @click="activeCategory = cat.id"
                        x-text="cat.name">
                    </button>
                </template>
            </div>

            <div class="item-grid">
                <div class="no-source-hint" x-show="!selectedTable && !selectedWalkIn">
                    <i class="bi bi-hand-index-thumb"></i>
                    <p>Select a table or walk-in above to start</p>
                </div>
                <div class="row g-2" x-show="selectedTable || selectedWalkIn">
                    <template x-for="cat in categories" :key="cat.id">
                        <template x-if="activeCategory === cat.id">
                            <template x-for="item in filteredItems(cat)" :key="item.id">
                                <div class="col-4 col-md-3 col-xl-2">
                                    <button class="item-card"
                                        @click="addItem(item)"
                                        :disabled="!selectedTable && !selectedWalkIn">
                                        <span class="item-icon" x-text="getEmoji(cat.name)"></span>
                                        <span class="item-name" x-text="item.name"></span>
                                        <span class="item-price" x-text="'₦' + Number(item.price).toLocaleString()"></span>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!filteredItems(cat).length">
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="bi bi-search fs-2"></i>
                                    <p class="mt-1 small">No items match "<span x-text="searchQuery"></span>"</p>
                                </div>
                            </template>
                        </template>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-lg-5 d-none d-lg-flex flex-column cart-panel">
            <div class="cart-header">
                <span class="fw-bold"><i class="bi bi-cart me-2"></i>Current Order</span>
                <span class="text-muted small" x-text="cart.length + ' item(s)'"></span>
            </div>

            <div class="cart-items">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="cart-item p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-semibold small" x-text="item.name"></span>
                            <button class="btn-remove text-danger border-0 bg-transparent" @click="removeItem(index)" title="Remove item">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="qty-group">
                                <button @click="updateQty(index, item.quantity - 1)" :disabled="item.quantity <= 1">−</button>
                                <input type="number" :value="item.quantity"
                                    @change="updateQty(index, parseInt($event.target.value) || 1)" min="1">
                                <button @click="updateQty(index, item.quantity + 1)">+</button>
                            </div>
                            <span class="fw-bold small text-success" x-text="'₦' + (item.price * item.quantity).toLocaleString()"></span>
                        </div>
                    </div>
                </template>
                <div x-show="!cart.length" class="cart-empty">
                    <i class="bi bi-cart-x" style="font-size: 2.5rem;"></i>
                    <p class="mt-2 small">Cart is empty</p>
                </div>
            </div>

            <div class="px-3" x-show="selectedWalkIn && !selectedTable">
                <input type="text" class="form-control form-control-sm mb-2" x-model="customerName"
                    placeholder="Customer name (optional)">
            </div>

            <div class="cart-footer" x-show="cart.length">
                <div class="breakdown-row">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-medium" x-text="'₦' + subtotal.toLocaleString()"></span>
                </div>
                <div class="breakdown-row">
                    <span class="text-muted">Discount</span>
                    <div class="d-flex align-items-center gap-1">
                        <input type="number" class="form-control form-control-sm text-end"
                            style="width: 68px;" x-model="discountValue" min="0"
                            :step="discountType === 'percentage' ? 1 : 100"
                            @input.debounce="validateDiscount">
                        <select class="form-select form-select-sm" style="width: 72px;" x-model="discountType">
                            <option value="fixed">₦</option>
                            <option value="percentage">%</option>
                        </select>
                    </div>
                </div>
                <div class="breakdown-row" x-show="discountAmount > 0">
                    <span class="text-muted small">Discount Amount</span>
                    <span class="text-danger fw-medium">-₦<span x-text="discountAmount.toLocaleString()"></span></span>
                </div>
                <div class="breakdown-row">
                    <span class="text-muted">VAT (<span x-text="vatRate"></span>%)</span>
                    <span x-text="'₦' + vatAmount.toLocaleString()"></span>
                </div>
                <hr class="my-1">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h5 mb-0 fw-bold">Total</span>
                    <span class="h4 mb-0 fw-bold text-success" x-text="'₦' + grandTotal.toLocaleString()"></span>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-outline-danger" @click="clearCart" title="Clear all">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button class="btn btn-success w-100 fw-bold" @click="submitOrder" :disabled="isSubmitting">
                        <span x-show="!isSubmitting"><i class="bi bi-send me-1"></i>Send to Kitchen</span>
                        <span x-show="isSubmitting"><span class="spinner-border spinner-border-sm me-1"></span>Sending...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <button class="cart-toggle-mobile" @click="openCartPanel">
        <i class="bi bi-cart"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" x-show="cart.length" x-text="cart.length"></span>
    </button>

    <div class="order-tray">
        <ul class="nav nav-pills w-100" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'pos' }" @click="activeTab = activeTab === 'pos' ? null : 'pos'" type="button">
                    <i class="bi bi-calculator me-1"></i>POS
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'pending' }" @click="activeTab = activeTab === 'pending' ? 'pos' : 'pending'" type="button">
                    <i class="bi bi-bell me-1"></i>Pending <span class="badge bg-danger ms-1" x-text="pendingCount"></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'active' }" @click="activeTab = activeTab === 'active' ? 'pos' : 'active'" type="button">
                    <i class="bi bi-activity me-1"></i>Active <span class="badge bg-light text-dark ms-1" x-text="activeCount"></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'paid' }" @click="activeTab = activeTab === 'paid' ? 'pos' : 'paid'" type="button">
                    <i class="bi bi-receipt me-1"></i>Paid <span class="badge bg-light text-dark ms-1" x-text="paidCount"></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="order-panel flex-grow-1" :class="{ open: activeTab === 'pending' }">
        <div class="order-cards">
            <template x-for="order in pendingOrders" :key="order.id">
                <div class="ticket-card pending-border">
                    <div class="ticket-header">
                        <div>
                            <strong class="fs-6">Order #<span x-text="order.id"></span></strong>
                            <span class="badge bg-light text-dark ms-2" x-text="order.type"></span>
                        </div>
                        <small class="text-muted" x-text="timeAgo(order.created_at)"></small>
                    </div>
                    <div class="ticket-body">
                        <div class="small mb-1">
                            <span class="text-muted">Source:</span>
                            <span x-text="order.customer_name || 'Table ' + order.source_id"></span>
                        </div>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tbody>
                                <template x-for="item in order.order_items" :key="item.id">
                                    <tr>
                                        <td x-text="item.menu_item?.name || 'Item'"></td>
                                        <td class="text-center" x-text="'x' + item.quantity"></td>
                                        <td class="text-end" x-text="'₦' + Number(item.menu_item?.price * item.quantity).toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="ticket-footer d-flex gap-2">
                        <form method="POST" :action="'/restaurant-waiter/order/' + order.id + '/accept'" class="flex-grow-1">
                            <input type="hidden" name="_token" :value="csrfToken">
                            <button type="submit" class="btn btn-success w-100 btn-sm fw-bold">
                                <i class="bi bi-check-circle me-1"></i>Accept
                            </button>
                        </form>
                        <button class="btn btn-outline-danger btn-sm flex-grow-1 fw-bold"
                            @click="openReason('reject', order.id)">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="!pendingOrders.length" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-2"></i>
                <p class="mt-2">No pending orders</p>
            </div>
        </div>
    </div>

    <div class="order-panel flex-grow-1" :class="{ open: activeTab === 'active' }">
        <div class="order-cards">
            <template x-for="order in activeOrders" :key="order.id">
                <div class="ticket-card active-border">
                    <div class="ticket-header">
                        <div>
                            <strong class="fs-6">Order #<span x-text="order.id"></span></strong>
                            <span class="badge bg-primary ms-2" x-text="order.tracking_status"></span>
                        </div>
                        <small class="text-muted" x-text="timeAgo(order.updated_at)"></small>
                    </div>
                    <div class="ticket-body">
                        <div class="small mb-1">
                            <span class="text-muted">Source:</span>
                            <span x-text="order.customer_name || 'Table ' + order.source_id"></span>
                        </div>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tbody>
                                <template x-for="item in order.order_items" :key="item.id">
                                    <tr>
                                        <td x-text="item.menu_item?.name || 'Item'"></td>
                                        <td class="text-center" x-text="'x' + item.quantity"></td>
                                        <td class="text-end" x-text="'₦' + Number(item.menu_item?.price * item.quantity).toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="ticket-footer">
                        <div class="d-flex gap-2 mb-2">
                            <form method="POST" :action="'/restaurant-waiter/order/' + order.id + '/update-status'" class="flex-grow-1 d-flex gap-1">
                                <input type="hidden" name="_token" :value="csrfToken">
                                <select name="tracking_status" class="form-select form-select-sm" x-model="order.tracking_status">
                                    <option value="preparing">Preparing</option>
                                    <option value="ready">Ready</option>
                                    <option value="served">Served</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Update</button>
                            </form>
                        </div>
                        <button class="btn btn-success w-100 btn-sm fw-bold" @click="openPayment(order)">
                            <i class="bi bi-credit-card me-1"></i>Collect Payment
                        </button>
                        <button class="btn btn-outline-primary btn-sm w-100 mt-1 fw-bold"
                            @click="reprintReceipt(order)">
                            <i class="bi bi-printer me-1"></i>Print Receipt
                        </button>
                        <button class="btn btn-outline-warning btn-sm w-100 mt-1 fw-bold"
                            @click="openReason('void', order.id)">
                            <i class="bi bi-x-octagon me-1"></i>Void
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="!activeOrders.length" class="text-center text-muted py-5">
                <i class="bi bi-activity fs-2"></i>
                <p class="mt-2">No active orders</p>
            </div>
        </div>
    </div>

    <div class="order-panel flex-grow-1" :class="{ open: activeTab === 'paid' }">
        <div class="order-cards">
            <template x-for="order in paidOrders" :key="order.id">
                <div class="ticket-card active-border">
                    <div class="ticket-header">
                        <div>
                            <strong class="fs-6">Order #<span x-text="order.id"></span></strong>
                            <span class="badge bg-success ms-2">Paid</span>
                        </div>
                        <small class="text-muted" x-text="timeAgo(order.updated_at)"></small>
                    </div>
                    <div class="ticket-body">
                        <div class="small mb-1">
                            <span class="text-muted">Source:</span>
                            <span x-text="order.customer_name || 'Table ' + order.source_id"></span>
                        </div>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tbody>
                                <template x-for="item in order.order_items" :key="item.id">
                                    <tr>
                                        <td x-text="item.menu_item?.name || 'Item'"></td>
                                        <td class="text-center" x-text="'x' + item.quantity"></td>
                                        <td class="text-end" x-text="'₦' + Number(item.menu_item?.price * item.quantity).toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div class="mt-1 text-end small fw-bold text-success" x-text="'Total: ₦' + Number(order.grand_total).toLocaleString()"></div>
                    </div>
                    <div class="ticket-footer d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm flex-grow-1 fw-bold"
                            @click="reprintReceipt(order)">
                            <i class="bi bi-printer me-1"></i>Print Receipt
                        </button>
                        <button class="btn btn-outline-danger btn-sm fw-bold"
                            @click="refundOrder(order)">
                            <i class="bi bi-arrow-return-left me-1"></i>Refund
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="!paidOrders.length" class="text-center text-muted py-5">
                <i class="bi bi-receipt fs-2"></i>
                <p class="mt-2">No paid orders yet</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="reasonModalLabel">Reason</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reasonForm" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <div class="modal-body">
                        <p class="small text-muted mb-2">Provide a reason for <strong id="modalActionText"></strong> order #<strong id="modalOrderIdText"></strong>.</p>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g., Out of stock, customer cancelled..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm" id="modalSubmitButton">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-credit-card me-1"></i>Payment - Order #<span x-text="paymentOrder?.id"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="text-muted small">Balance Due</div>
                        <div class="fs-3 fw-bold" x-text="'₦' + Number(paymentAmountDue).toLocaleString()"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Payment Method</label>
                        <select class="form-select" x-model="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Amount to Pay (₦)</label>
                        <input type="number" class="form-control" x-model="paymentAmount" min="1" :max="paymentAmountDue" step="100">
                        <div class="d-flex gap-1 mt-1">
                            <button class="btn btn-outline-secondary btn-xs py-0 px-2" @click="paymentAmount = Math.ceil(paymentAmountDue / 2)" style="font-size:0.7rem">½</button>
                            <button class="btn btn-outline-secondary btn-xs py-0 px-2" @click="paymentAmount = paymentAmountDue" style="font-size:0.7rem">Full</button>
                        </div>
                    </div>
                    <div class="mb-2" x-show="paymentMethod === 'cash'">
                        <label class="form-label small fw-medium">Amount Tendered (₦)</label>
                        <input type="number" class="form-control" x-model="amountTendered" min="0" step="100">
                    </div>
                    <div class="mb-2" x-show="paymentMethod === 'card'">
                        <p class="small text-muted mb-1">Customer will be redirected to Paystack to complete payment.</p>
                    </div>
                    <div class="mb-2" x-show="paymentMethod === 'mobile_money' || paymentMethod === 'transfer'">
                        <label class="form-label small fw-medium">Reference (optional)</label>
                        <input type="text" class="form-control" x-model="paymentReference" placeholder="Transaction ID">
                    </div>
                    <div x-show="changeDue > 0" class="alert alert-success py-2 mb-0 text-center">
                        <small>Change Due:</small>
                        <strong x-text="'₦' + Number(changeDue).toLocaleString()"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success btn-sm fw-bold" @click="submitPayment" :disabled="paymentLoading || !paymentAmount || paymentAmount > paymentAmountDue">
                        <span x-show="!paymentLoading"><i class="bi bi-check-circle me-1"></i><span x-text="paymentAmount < paymentAmountDue ? 'Record Partial Payment' : 'Complete Payment'"></span></span>
                        <span x-show="paymentLoading"><span class="spinner-border spinner-border-sm me-1"></span>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="endShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-box-arrow-right me-1"></i>End Shift</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Total sales this shift: <strong x-text="'₦' + Number(shiftData?.total_sales || 0).toLocaleString()"></strong></p>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Ending Cash (₦)</label>
                        <input type="number" class="form-control" x-model="endingCash" min="0" step="100">
                    </div>
                    <div>
                        <label class="form-label small fw-medium">Notes (optional)</label>
                        <textarea class="form-control" x-model="shiftNotes" rows="2" placeholder="Any notes about this shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-warning btn-sm fw-bold" @click="endShift" :disabled="endShiftLoading">
                        <span x-show="!endShiftLoading"><i class="bi bi-check-circle me-1"></i>End Shift</span>
                        <span x-show="endShiftLoading"><span class="spinner-border spinner-border-sm me-1"></span>Ending...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow">
                <div class="modal-body py-4">
                    <i class="bi bi-check2-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-2">Order #<span id="successOrderId"></span></h5>
                    <p class="text-muted small mb-3">Sent to kitchen</p>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endSection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reasonModal = document.getElementById('reasonModal');
    if (reasonModal) {
        reasonModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const action = btn.getAttribute('data-action');
            const orderId = btn.getAttribute('data-order-id');
            const form = reasonModal.querySelector('#reasonForm');
            document.getElementById('modalOrderIdText').textContent = orderId;
            const actionText = document.getElementById('modalActionText');
            const submitBtn = document.getElementById('modalSubmitButton');
            if (action === 'reject') {
                form.action = '/restaurant-waiter/order/' + orderId + '/reject';
                document.getElementById('reasonModalLabel').textContent = 'Reject Order';
                actionText.textContent = 'rejecting';
                submitBtn.className = 'btn btn-danger btn-sm';
                submitBtn.textContent = 'Reject';
            } else if (action === 'void') {
                form.action = '/restaurant-waiter/order/' + orderId + '/void';
                document.getElementById('reasonModalLabel').textContent = 'Void Order';
                actionText.textContent = 'voiding';
                submitBtn.className = 'btn btn-warning btn-sm text-dark';
                submitBtn.textContent = 'Void';
            }
        });
    }

    const endShiftModal = document.getElementById('endShiftModal');
    if (endShiftModal) {
        endShiftModal.addEventListener('hidden.bs.modal', function () {
            const alpineEl = document.querySelector('[x-data="posState"]');
            if (alpineEl && alpineEl.__x) {
                alpineEl.__x.$data.showEndShiftModal = false;
            }
        });
    }
});

document.addEventListener('alpine:init', function () {
    const tablesData = @json($tables);
    const categoriesData = @json($categories);
    const pendingOrdersData = @json($pendingOrders);
    const activeOrdersData = @json($activeOrders);
    const paidOrdersData = @json($paidOrders);

    Alpine.data('posState', () => ({
        tables: tablesData,
        occupiedTableIds: @json($occupiedTableIds),
        categories: categoriesData,
        pendingOrders: pendingOrdersData,
        activeOrders: activeOrdersData,
        paidOrders: paidOrdersData,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        selectedTable: null,
        selectedTableNumber: null,
        selectedWalkIn: false,
        customerName: '',
        activeCategory: categoriesData[0]?.id || null,
        searchQuery: '',
        cart: [],
        isSubmitting: false,
        cartLoading: false,
        activeTab: 'pos',

        shiftData: null,
        shiftLoading: false,
        endShiftLoading: false,
        showEndShiftModal: false,
        startingCash: 0,
        endingCash: 0,
        shiftNotes: '',

        vatRate: 7.5,
        discountLimit: 10000,
        discountValue: 0,
        discountType: 'fixed',

        paymentOrder: null,
        paymentMethod: 'cash',
        paymentAmount: 0,
        amountTendered: 0,
        paymentReference: '',
        paymentLoading: false,
        paystackKey: '',
        paymentAmountDue: 0,

        init() {
            this.checkShift();
            this.loadSettings();
            this.refreshOrders();
            setInterval(() => this.refreshOrders(), 30000);
        },

        get pendingCount() { return this.pendingOrders.length; },
        get activeCount() { return this.activeOrders.length; },
        get paidCount() { return this.paidOrders.length; },

        get sectionedTables() {
            const groups = {};
            this.tables.forEach(t => {
                const section = t.section || 'Other';
                if (!groups[section]) groups[section] = [];
                groups[section].push(t);
            });
            const order = ['Window', 'Center', 'Patio', 'VIP', 'Other'];
            const sorted = {};
            order.forEach(s => { if (groups[s]) sorted[s] = groups[s]; });
            Object.keys(groups).filter(s => !order.includes(s)).forEach(s => { sorted[s] = groups[s]; });
            return sorted;
        },

        get shiftTime() {
            if (!this.shiftData) return '';
            const d = new Date(this.shiftData.clock_in + ' UTC');
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get discountAmount() {
            if (!this.discountValue || this.discountValue <= 0) return 0;
            return this.discountType === 'percentage'
                ? this.subtotal * this.discountValue / 100
                : this.discountValue;
        },

        get vatAmount() {
            const base = this.subtotal - this.discountAmount;
            if (base <= 0) return 0;
            return base * this.vatRate / 100;
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - this.discountAmount + this.vatAmount);
        },

        get changeDue() {
            if (!this.paymentOrder || this.paymentMethod !== 'cash') return 0;
            return Math.max(0, (parseFloat(this.amountTendered) || 0) - this.paymentOrder.grand_total);
        },

        validateDiscount() {
            if (!this.discountValue || this.discountValue < 0) {
                this.discountValue = 0;
                return;
            }
            const maxDisc = this.discountType === 'percentage' ? 100 : this.discountLimit;
            if (this.discountValue > maxDisc) {
                this.discountValue = maxDisc;
                this.showToast('warning', 'Discount limited to ' + (this.discountType === 'percentage' ? '100%' : '₦' + Number(maxDisc).toLocaleString()));
            }
        },

        getEmoji(catName) {
            const map = {
                'Appetizers': '🍤', 'appetizers': '🍤',
                'Main Courses': '🍗', 'main courses': '🍗',
                'Desserts': '🍰', 'desserts': '🍰',
                'Beverages': '🥤', 'beverages': '🥤',
            };
            return map[catName] || '🍽️';
        },

        filteredItems(cat) {
            if (!this.searchQuery) return cat.menu_items || [];
            const q = this.searchQuery.toLowerCase();
            return (cat.menu_items || []).filter(item =>
                item.name.toLowerCase().includes(q)
            );
        },

        selectTable(id, number) {
            this.selectedTable = id;
            this.selectedTableNumber = number;
            this.selectedWalkIn = false;
            this.customerName = '';
            this.refreshCart();
        },

        selectWalkIn() {
            this.selectedTable = null;
            this.selectedTableNumber = null;
            this.selectedWalkIn = true;
            this.refreshCart();
        },

        async refreshCart() {
            try {
                const res = await fetch('/restaurant-waiter/pos/cart');
                const data = await res.json();
                if (data.success) this.cart = data.cart;
            } catch (e) {
                console.error('refresh cart error', e);
            }
        },

        async loadSettings() {
            try {
                const res = await fetch('/restaurant-waiter/pos/settings');
                const data = await res.json();
                if (data.success) {
                    this.vatRate = parseFloat(data.settings.vat_rate) || 0;
                    this.discountLimit = parseFloat(data.settings.discount_limit) || 0;
                    this.paystackKey = data.settings.paystack_public_key || '';
                }
            } catch (e) {
                console.error('load settings error', e);
            }
        },

        async refreshOrders() {
            try {
                const res = await fetch('/restaurant-waiter/pos/orders-data');
                const data = await res.json();
                if (data.success) {
                    this.pendingOrders = data.pendingOrders;
                    this.activeOrders = data.activeOrders;
                    this.paidOrders = data.paidOrders;
                }
            } catch (e) {
                console.error('refresh orders error', e);
            }
        },

        async checkShift() {
            try {
                const res = await fetch('/restaurant-waiter/shift/current');
                const data = await res.json();
                if (data.success && data.shift) {
                    this.shiftData = data.shift;
                }
            } catch (e) {
                console.error('check shift error', e);
            }
        },

        async startShift() {
            this.shiftLoading = true;
            try {
                const res = await fetch('/restaurant-waiter/shift/start', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ starting_cash: this.startingCash || 0 })
                });
                const data = await res.json();
                if (data.success) {
                    this.shiftData = data.shift;
                    this.showToast('success', 'Shift started');
                    this.loadSettings();
                } else {
                    this.showToast('danger', data.message || 'Failed to start shift');
                }
            } catch (e) {
                this.showToast('danger', 'Failed to start shift');
            } finally {
                this.shiftLoading = false;
            }
        },

        async endShift() {
            if (!this.endingCash && this.endingCash !== 0) {
                this.showToast('warning', 'Enter ending cash amount');
                return;
            }
            this.endShiftLoading = true;
            try {
                const res = await fetch('/restaurant-waiter/shift/end', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ ending_cash: this.endingCash, notes: this.shiftNotes })
                });
                const data = await res.json();
                if (data.success) {
                    this.shiftData = null;
                    this.endingCash = 0;
                    this.shiftNotes = '';
                    bootstrap.Modal.getInstance(document.getElementById('endShiftModal'))?.hide();
                    this.showToast('success', 'Shift ended');
                } else {
                    this.showToast('danger', data.message || 'Failed to end shift');
                }
            } catch (e) {
                this.showToast('danger', 'Failed to end shift');
            } finally {
                this.endShiftLoading = false;
            }
        },

        async addItem(item) {
            if (!this.selectedTable && !this.selectedWalkIn) {
                this.showToast('warning', 'Select a table or walk-in first');
                return;
            }
            try {
                const res = await fetch('/restaurant-waiter/pos/cart/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ item_id: item.id, quantity: 1 })
                });
                const data = await res.json();
                if (data.success) {
                    this.cart = data.cart;
                    this.showToast('success', item.name + ' added to cart');
                }
            } catch (e) {
                this.showToast('danger', 'Failed to add item');
            }
        },

        async updateQty(index, qty) {
            if (qty < 1) return;
            if (qty === 0) return this.removeItem(index);
            try {
                const res = await fetch('/restaurant-waiter/pos/cart/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ index, quantity: qty })
                });
                const data = await res.json();
                if (data.success) this.cart = data.cart;
            } catch (e) {
                this.showToast('danger', 'Failed to update');
            }
        },

        async removeItem(index) {
            try {
                const res = await fetch('/restaurant-waiter/pos/cart/remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ index })
                });
                const data = await res.json();
                if (data.success) this.cart = data.cart;
            } catch (e) {
                this.showToast('danger', 'Failed to remove');
            }
        },

        async clearCart() {
            if (this.cart.length === 0) return;
            try {
                const res = await fetch('/restaurant-waiter/pos/cart/remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ index: -1 })
                });
                if (res.ok) {
                    this.cart = [];
                    this.showToast('info', 'Cart cleared');
                }
            } catch (e) {
                this.showToast('danger', 'Failed to clear');
            }
        },

        async submitOrder() {
            if (!this.selectedTable && !this.selectedWalkIn) return;
            if (this.cart.length === 0) return;
            this.isSubmitting = true;
            try {
                const payload = {
                    source_type: this.selectedTable ? 'table' : 'walk_in',
                    source_id: this.selectedTable,
                    customer_name: this.customerName,
                    discount: this.discountValue || 0,
                    discount_type: this.discountValue > 0 ? this.discountType : null,
                };
                const res = await fetch('/restaurant-waiter/pos/order/submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    this.cart = [];
                    this.customerName = '';
                    this.discountValue = 0;
                    this.discountType = 'fixed';
                    document.getElementById('successOrderId').textContent = data.order_id;
                    new bootstrap.Modal(document.getElementById('orderSuccessModal')).show();
                    this.checkShift();
                    this.refreshOrders();
                } else {
                    this.showToast('danger', data.message || 'Failed to submit order');
                }
            } catch (e) {
                this.showToast('danger', 'An error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },

        openPayment(order) {
            this.paymentOrder = order;
            this.paymentMethod = 'cash';
            this.paymentAmountDue = order.balance ?? order.grand_total;
            this.paymentAmount = this.paymentAmountDue;
            this.amountTendered = this.paymentAmountDue;
            this.paymentReference = '';
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        },

        async submitPayment() {
            if (!this.paymentOrder) return;
            if (!this.paymentAmount || this.paymentAmount <= 0) return;
            if (this.paymentMethod === 'cash' && this.amountTendered < this.paymentAmount) return;

            if (this.paymentMethod === 'card') {
                await this.payWithPaystack();
                return;
            }

            await this.recordPayment();
        },

        async payWithPaystack() {
            if (!this.paystackKey) {
                this.showToast('warning', 'Paystack not configured. Ask admin to set Paystack key.');
                return;
            }
            this.paymentLoading = true;
            try {
                const payAmount = this.paymentAmount || this.paymentAmountDue;
                const ref = 'POS-' + this.paymentOrder.id + '-' + Date.now();
                const handler = PaystackPop.setup({
                    key: this.paystackKey,
                    email: this.paymentOrder.customer_name + '@brick.pos' || 'pos@restaurant.local',
                    amount: Math.round(payAmount * 100),
                    ref: ref,
                    currency: 'NGN',
                    callback: async (response) => {
                        this.paymentReference = response.reference;
                        await this.recordPayment();
                    },
                    onClose: () => {
                        this.paymentLoading = false;
                        this.showToast('info', 'Payment cancelled');
                    }
                });
                handler.openIframe();
            } catch (e) {
                this.paymentLoading = false;
                this.showToast('danger', 'Paystack error');
            }
        },

        async recordPayment() {
            this.paymentLoading = true;
            try {
                const res = await fetch('/restaurant-waiter/order/' + this.paymentOrder.id + '/pay', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({
                        amount: this.paymentAmount || this.paymentAmountDue,
                        amount_tendered: this.amountTendered,
                        method: this.paymentMethod,
                        reference: this.paymentReference,
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (data.fully_paid) {
                        this.activeOrders = this.activeOrders.filter(o => o.id !== this.paymentOrder.id);
                        this.paymentOrder.status = 'completed';
                        this.paymentOrder.tracking_status = 'paid';
                        this.paidOrders.unshift({...this.paymentOrder});
                        this.showToast('success', 'Order #' + this.paymentOrder.id + ' paid (' + this.paymentMethod + ')');
                        this.reprintReceipt(this.paymentOrder);
                    } else {
                        this.showToast('info', 'Partial payment of ₦' + Number(data.payment.amount).toLocaleString() + ' recorded. Balance: ₦' + Number(data.balance_remaining).toLocaleString());
                    }
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();
                    this.refreshOrders();
                    this.paymentOrder = null;
                } else {
                    this.showToast('danger', data.message || 'Payment failed');
                }
            } catch (e) {
                this.showToast('danger', 'Payment failed');
            } finally {
                this.paymentLoading = false;
            }
        },

        reprintReceipt(order) {
            window.open('/restaurant-waiter/order/' + order.id + '/receipt', '_blank', 'width=400,height=700');
        },

        async refundOrder(order) {
            const reason = prompt('Reason for refunding Order #' + order.id + ':');
            if (!reason) return;
            try {
                const res = await fetch('/restaurant-waiter/order/' + order.id + '/refund', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ reason })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast('success', 'Order #' + order.id + ' refunded');
                    this.refreshOrders();
                } else {
                    this.showToast('danger', data.message || 'Refund failed');
                }
            } catch (e) {
                this.showToast('danger', 'Refund failed');
            }
        },

        openReason(action, orderId) {
            const modal = document.getElementById('reasonModal');
            if (!modal) return;
            document.getElementById('modalOrderIdText').textContent = orderId;
            const actionText = document.getElementById('modalActionText');
            const submitBtn = document.getElementById('modalSubmitButton');
            if (action === 'reject') {
                document.getElementById('reasonForm').action = '/restaurant-waiter/order/' + orderId + '/reject';
                document.getElementById('reasonModalLabel').textContent = 'Reject Order';
                actionText.textContent = 'rejecting';
                submitBtn.className = 'btn btn-danger btn-sm';
                submitBtn.textContent = 'Reject';
            } else if (action === 'void') {
                document.getElementById('reasonForm').action = '/restaurant-waiter/order/' + orderId + '/void';
                document.getElementById('reasonModalLabel').textContent = 'Void Order';
                actionText.textContent = 'voiding';
                submitBtn.className = 'btn btn-warning btn-sm text-dark';
                submitBtn.textContent = 'Void';
            }
            new bootstrap.Modal(modal).show();
        },

        openCartPanel() {
            const panel = document.querySelector('.waiter-pos .cart-panel');
            if (panel) panel.classList.toggle('open');
        },

        showToast(type, message) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
            const bg = { success: 'bg-success', danger: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-info text-dark' };
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white border-0 ' + (bg[type] || 'bg-dark');
            toast.role = 'alert';
            toast.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="bi ' + (icons[type] || '') + ' me-2"></i>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
            container.appendChild(toast);
            const bs = new bootstrap.Toast(toast, { autohide: true, delay: 3000 });
            bs.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        },

        timeAgo(dateStr) {
            if (!dateStr) return '';
            const now = new Date();
            const date = new Date(dateStr);
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return date.toLocaleDateString();
        }
    }));
});
</script>
@endpush
