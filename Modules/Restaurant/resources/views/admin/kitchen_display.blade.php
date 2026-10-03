@extends('restaurant::layouts.adminMaster')
@section('title', 'Kitchen Display')
@section('admin-content')
<style>
    /* Projection: a kitchen screen is read from across the room, so going
       fullscreen enlarges the tickets and lets the grid fill the display.
       Fullscreening the shell itself drops the sidebar and navbar away. */
    #kdsShell:fullscreen {
        overflow-y: auto;
        padding: 1rem 1.5rem;
        background: #f5f7fa;
    }
    #kdsShell:fullscreen h4 { font-size: 1.9rem; }
    #kdsShell:fullscreen h5 { font-size: 1.6rem; }
    #kdsShell:fullscreen .fs-5 { font-size: 2rem !important; }
    #kdsShell:fullscreen .small,
    #kdsShell:fullscreen small { font-size: 1rem !important; }
    #kdsShell:fullscreen .card-body,
    #kdsShell:fullscreen .card-header { font-size: 1.1rem; }
</style>
<div class="container-fluid py-3" id="kdsShell">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-tv me-2"></i>Kitchen Display</h4>
        <div>
            <span class="text-muted small me-2" id="lastUpdated"></span>
            <button class="btn btn-outline-secondary btn-sm rounded-pill me-1" id="alarmToggle" onclick="toggleAlarm()">
                Alarm on
            </button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill me-1" id="fullscreenToggle" onclick="toggleFullscreen()">
                <i class="bi bi-arrows-fullscreen me-1"></i>Fullscreen
            </button>
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

@section('admin-scripts')
<script src="{{ asset('js/kds-alarm.js') }}"></script>
<script>
const kdsAlarm = window.createKdsAlarm({ intervalMs: 10000 });

const DATA_URL = '{{ route("restaurant.admin.kitchen.data") }}';
const ACCEPT_URL = '{{ route("restaurant.admin.kitchen.accept", ["order" => "ORDER_ID_PLACEHOLDER"]) }}';

function setAlarmButton() {
    const button = document.getElementById('alarmToggle');
    if (button) {
        button.textContent = kdsAlarm.isMuted() ? 'Alarm off' : 'Alarm on';
    }
}

function toggleAlarm() {
    kdsAlarm.toggleMute();
    setAlarmButton();
}

/**
 * Project the board. Fullscreening the shell element shows the tickets and
 * nothing else -- the sidebar, navbar and footer are outside it, so they drop
 * away without any layout CSS to maintain.
 */
function toggleFullscreen() {
    const shell = document.getElementById('kdsShell');
    if (!document.fullscreenElement) {
        shell.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen();
    }
}

function setFullscreenButton() {
    const button = document.getElementById('fullscreenToggle');
    if (!button) return;
    const active = document.fullscreenElement === document.getElementById('kdsShell');
    button.innerHTML = active
        ? '<i class="bi bi-fullscreen-exit me-1"></i>Exit'
        : '<i class="bi bi-arrows-fullscreen me-1"></i>Fullscreen';
}

document.addEventListener('fullscreenchange', setFullscreenButton);

/**
 * Escape a value before it is folded into a template string that becomes
 * innerHTML. Dish and customer names are data; treating them as markup would
 * let a name like `<img src=x onerror=...>` run as code on the kitchen screen.
 */
function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[character]));
}

async function refreshKDS() {
    try {
        const res = await fetch(DATA_URL);
        const data = await res.json();
        if (!data.success) return;

        document.getElementById('lastUpdated').textContent = 'Updated: ' + new Date(data.now).toLocaleTimeString();

        const unacceptedOrders = data.orders.filter(o => o.status === 'pending');
        const preparing = data.orders.filter(o => o.tracking_status === 'preparing');
        const ready = data.orders.filter(o => o.tracking_status === 'ready');

        // Rings until the last waiting ticket is accepted, then stops itself.
        kdsAlarm.setPendingCount(unacceptedOrders.length);

        let html = '';

        if (unacceptedOrders.length) {
            html += `
                <div class="col-12"><h5 class="text-danger fw-bold"><i class="bi bi-bell-fill me-1"></i>Pending Orders</h5></div>
                ${unacceptedOrders.map(order => renderOrderCard(order, 'danger', true)).join('')}
                <div class="col-12"><hr></div>`;
        }

        if (preparing.length) {
            html += `
                <div class="col-12"><h5 class="text-primary fw-bold"><i class="bi bi-fire me-1"></i>Preparing</h5></div>
                ${preparing.map(order => renderOrderCard(order, 'primary')).join('')}
                <div class="col-12"><hr></div>`;
        }

        if (ready.length) {
            html += `
                <div class="col-12"><h5 class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Ready to Serve</h5></div>
                ${ready.map(order => renderOrderCard(order, 'success')).join('')}`;
        }

        if (!unacceptedOrders.length && !preparing.length && !ready.length) {
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
        const res = await fetch(ACCEPT_URL.replace('ORDER_ID_PLACEHOLDER', orderId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            refreshKDS();
        }
    } catch (e) {
        console.error('Accept failed', e);
    }
}

/**
 * Where a dish is made. A fixed icon and label rather than an emoji keeps the
 * two kitchen screens looking the same and survives whatever font the
 * projecting machine happens to have.
 */
function getStation(item) {
    const cat = item.menu_item?.category?.name || '';
    if (cat === 'Beverages') {
        return { key: 'bar', label: 'Bar', icon: 'bi-cup-straw' };
    }
    return { key: 'kitchen', label: 'Kitchen', icon: 'bi-fire' };
}

function stationBadge(station, classes) {
    return `<span class="badge ${classes}"><i class="bi ${station.icon} me-1"></i>${escapeHtml(station.label)}</span>`;
}

function renderOrderCard(order, color, showAccept) {
    const timeAgo = Math.floor((new Date() - new Date(order.created_at)) / 60000);
    const stations = [...new Map(order.order_items.map(i => {
        const station = getStation(i);
        return [station.key, station];
    })).values()];
    const source = order.customer_name || ('Table ' + order.source_id);
    return `
        <div class="col-md-4 col-lg-3">
            <div class="card border-${color} shadow-sm h-100">
                <div class="card-header bg-${color} bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <strong class="fs-5">#${escapeHtml(order.id)}</strong>
                    <div class="d-flex gap-1">
                        ${stations.map(s => stationBadge(s, 'bg-dark bg-opacity-75')).join('')}
                    </div>
                </div>
                <div class="card-body py-2">
                    <small class="text-muted">${escapeHtml(source)} · ${escapeHtml(timeAgo)}m ago</small>
                    <table class="table table-sm table-borderless mt-1 mb-0 small">
                        ${order.order_items.map(item => `
                            <tr>
                                <td>${stationBadge(getStation(item), 'bg-secondary bg-opacity-25 text-dark me-1')}${escapeHtml(item.menu_item?.name || 'Item')}</td>
                                <td class="text-end fw-bold">x${escapeHtml(item.quantity)}</td>
                            </tr>
                        `).join('')}
                    </table>
                    ${showAccept ? `<button class="btn btn-success btn-sm w-100 mt-2 rounded-pill" onclick="acceptOrder(${Number(order.id)})"><i class="bi bi-check-lg me-1"></i>Accept Order</button>` : ''}
                </div>
            </div>
        </div>`;
}

setAlarmButton();
setFullscreenButton();
refreshKDS();
setInterval(refreshKDS, 15000);
</script>
@endsection

@endSection
