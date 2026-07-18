@extends('restaurant::layouts.adminMaster')
@section('title', 'Kitchen Display')
@section('admin-content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-tv me-2"></i>Kitchen Display</h4>
        <div>
            <span class="text-muted small me-2" id="lastUpdated"></span>
            <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="refreshKDS()">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
        </div>
    </div>

    <div class="row g-3" id="kdsGrid">
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-hourglass fs-1"></i>
            <p class="mt-2">Loading orders...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
let kdsAudio = null;

function playNewOrderSound() {
    try {
        if (!kdsAudio) {
            kdsAudio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACAf39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+Af39/f3+AgH+AgH+AgH+AgICA f4CAf3+AgH+Af3+AgH+Af3+AgH+A');
        }
        kdsAudio.play().catch(() => {});
    } catch (e) {}
}

let previousOrderIds = new Set();

async function refreshKDS() {
    try {
        const res = await fetch('{{ route("restaurant.admin.kitchen.data") }}');
        const data = await res.json();
        if (!data.success) return;

        document.getElementById('lastUpdated').textContent = 'Updated: ' + new Date(data.now).toLocaleTimeString();

        const currentIds = new Set(data.orders.map(o => o.id));
        const hasNew = [...currentIds].some(id => !previousOrderIds.has(id));
        if (hasNew && previousOrderIds.size > 0) {
            playNewOrderSound();
        }
        previousOrderIds = currentIds;

        const unacceptedOrders = data.orders.filter(o => !o.tracking_status && o.status === 'pending');
        const pendingOrders = data.orders.filter(o => o.tracking_status === 'pending');
        const preparing = data.orders.filter(o => o.tracking_status === 'preparing');
        const ready = data.orders.filter(o => o.tracking_status === 'ready');

        let html = '';

        if (unacceptedOrders.length) {
            html += `
                <div class="col-12"><h5 class="text-danger fw-bold"><i class="bi bi-bell-fill me-1"></i>Unaccepted Orders</h5></div>
                ${unacceptedOrders.map(order => renderOrderCard(order, 'danger', 'accept')).join('')}
                <div class="col-12"><hr></div>`;
        }

        if (pendingOrders.length) {
            html += `
                <div class="col-12"><h5 class="text-warning fw-bold"><i class="bi bi-bell me-1"></i>New Orders (${pendingOrders.length})</h5></div>
                ${pendingOrders.map(order => renderOrderCard(order, 'warning', 'accept')).join('')}
                <div class="col-12"><hr></div>`;
        }

        if (preparing.length) {
            html += `
                <div class="col-12"><h5 class="text-primary fw-bold"><i class="bi bi-fire me-1"></i>Preparing (${preparing.length})</h5></div>
                ${preparing.map(order => renderOrderCard(order, 'primary', 'ready')).join('')}
                <div class="col-12"><hr></div>`;
        }

        if (ready.length) {
            html += `
                <div class="col-12"><h5 class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Ready to Serve (${ready.length})</h5></div>
                ${ready.map(order => renderOrderCard(order, 'success', 'served')).join('')}`;
        }

        if (!unacceptedOrders.length && !pendingOrders.length && !preparing.length && !ready.length) {
            html = `<div class="col-12 text-center text-muted py-5">
                <i class="bi bi-emoji-smile fs-1"></i>
                <p class="mt-2">All caught up! No orders in the kitchen.</p>
            </div>`;
        }

        document.getElementById('kdsGrid').innerHTML = html;
    } catch (e) {
        console.error('KDS refresh error', e);
    }
}

async function acceptOrder(orderId) {
    try {
        const res = await fetch('{{ route("restaurant.admin.kitchen.accept", "_ORDER_ID_") }}'.replace('_ORDER_ID_', orderId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) refreshKDS();
    } catch (e) {
        console.error('Accept failed', e);
    }
}

async function updateOrderStatus(orderId, status) {
    try {
        const res = await fetch('{{ route("restaurant.admin.kitchen.status", "_ORDER_ID_") }}'.replace('_ORDER_ID_', orderId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ tracking_status: status })
        });
        const data = await res.json();
        if (data.success) refreshKDS();
    } catch (e) {
        console.error('Status update failed', e);
    }
}

function getItemRoute(item) {
    const cat = item.menu_item?.category?.name || '';
    const foodCats = ['Main Courses', 'Appetizers', 'Desserts'];
    const barCats = ['Beverages'];
    if (barCats.includes(cat)) return 'Bar';
    if (foodCats.includes(cat)) return 'Kitchen';
    return 'Kitchen';
}

function getSourceLabel(order) {
    const type = order.type || 'table';
    if (order.customer_name) return order.customer_name;
    if (type === 'room') return 'Room ' + order.source_id;
    if (type === 'walk_in') return 'Walk-in';
    return 'Table ' + order.source_id;
}

function renderOrderCard(order, color, action) {
    const timeAgo = Math.floor((new Date() - new Date(order.created_at)) / 60000);
    const routes = [...new Set(order.order_items.map(i => getItemRoute(i)))];
    const sourceLabel = getSourceLabel(order);
    let actionButton = '';
    if (action === 'accept') {
        actionButton = `<button class="btn btn-success btn-sm w-100 mt-2 rounded-pill fw-bold" onclick="acceptOrder(${order.id})"><i class="bi bi-check-lg me-1"></i>Accept Order</button>`;
    } else if (action === 'ready') {
        actionButton = `<button class="btn btn-warning btn-sm w-100 mt-2 rounded-pill fw-bold" onclick="updateOrderStatus(${order.id}, 'ready')"><i class="bi bi-check2-all me-1"></i>Mark Ready</button>`;
    } else if (action === 'served') {
        actionButton = `<button class="btn btn-info btn-sm w-100 mt-2 rounded-pill fw-bold" onclick="updateOrderStatus(${order.id}, 'served')"><i class="bi bi-check2-circle me-1"></i>Mark Served</button>`;
    }

    return `
        <div class="col-md-4 col-lg-3">
            <div class="card border-${color} shadow-sm h-100">
                <div class="card-header bg-${color} bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <strong class="fs-5">#${order.id}</strong>
                    <div class="d-flex gap-1">
                        ${routes.map(r => `<span class="badge bg-dark bg-opacity-75">${r}</span>`).join('')}
                    </div>
                </div>
                <div class="card-body py-2">
                    <small class="text-muted">${sourceLabel} · ${timeAgo}m ago</small>
                    <table class="table table-sm table-borderless mt-1 mb-0 small">
                        ${order.order_items.map(item => `
                            <tr>
                                <td><span class="badge bg-secondary bg-opacity-25 text-dark me-1" style="font-size:0.55rem">${getItemRoute(item)}</span>${item.menu_item?.name || 'Item'}</td>
                                <td class="text-end fw-bold">x${item.quantity}</td>
                            </tr>
                        `).join('')}
                    </table>
                    ${actionButton}
                </div>
            </div>
        </div>`;
}

refreshKDS();
setInterval(refreshKDS, 5000);
</script>
@endpush
@endsection
