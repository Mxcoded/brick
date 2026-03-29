@extends('layouts.master')

@section('title', 'Room Inventory Calendar')

@section('page-content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-alt me-2"></i>Room Inventory Calendar
                    </h5>
                    <span class="badge bg-info" id="selection-badge" style="display: none;">
                        <span id="selection-count">0</span> cells selected
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    {{-- Date Navigation --}}
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-light" onclick="changeMonth(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-light" id="month-label" style="min-width: 140px;">
                            Loading...
                        </button>
                        <button class="btn btn-sm btn-outline-light" onclick="changeMonth(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-info" onclick="goToToday()">
                        <i class="fas fa-calendar-day"></i> Today
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-light active" id="btn-monthly" onclick="setView('monthly')">Month</button>
                        <button class="btn btn-sm btn-outline-light" id="btn-weekly" onclick="setView('weekly')">Week</button>
                    </div>
                    <button class="btn btn-sm btn-success" onclick="loadInventoryData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Legend & Quick Actions --}}
        <div class="card-body bg-light py-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-3 flex-wrap small">
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #28a745;"></span>
                        Available
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #ffc107;"></span>
                        Limited (&lt;30%)
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #dc3545;"></span>
                        Fully Booked
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #6c757d;"></span>
                        Stop Sell / Blocked
                    </span>
                </div>
                <div class="d-flex gap-2" id="quick-actions" style="display: none !important;">
                    <button class="btn btn-sm btn-success" onclick="quickAction('open')">
                        <i class="fas fa-lock-open"></i> Open
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="quickAction('stop-sell')">
                        <i class="fas fa-ban"></i> Stop Sell
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="showBlockModal()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        {{-- Inventory Grid --}}
        <div class="card-body p-0">
            <div class="inventory-grid-wrapper">
                <table class="table table-bordered inventory-table mb-0" id="inventory-table">
                    <thead class="sticky-top bg-white" style="z-index: 10;">
                        <tr id="date-header-row"></tr>
                    </thead>
                    <tbody id="inventory-body">
                        <tr>
                            <td colspan="35" class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                                <div class="mt-2 text-muted">Loading inventory data...</div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="inventory-footer" class="bg-light"></tfoot>
                </table>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="card-footer bg-white py-3">
            <div class="row text-center" id="summary-stats">
                <div class="col">
                    <div class="h5 mb-0 text-primary" id="stat-total-nights">-</div>
                    <small class="text-muted">Total Room Nights</small>
                </div>
                <div class="col">
                    <div class="h5 mb-0 text-success" id="stat-available">-</div>
                    <small class="text-muted">Available</small>
                </div>
                <div class="col">
                    <div class="h5 mb-0 text-danger" id="stat-booked">-</div>
                    <small class="text-muted">Booked</small>
                </div>
                <div class="col">
                    <div class="h5 mb-0 text-secondary" id="stat-blocked">-</div>
                    <small class="text-muted">Blocked</small>
                </div>
                <div class="col">
                    <div class="h5 mb-0 text-info" id="stat-occupancy">-</div>
                    <small class="text-muted">Occupancy Rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Block/Restriction Modal --}}
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="blockForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Room Type</label>
                            <select class="form-select" id="modal-room-type" required>
                                @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->units->count() }} rooms)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Action Type</label>
                            <select class="form-select" id="modal-block-type" onchange="toggleBlockFields()">
                                <option value="manual">Block Rooms</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="stop_sell">Stop Sell</option>
                                <option value="restrictions">Set Restrictions</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" class="form-control" id="modal-start-date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" class="form-control" id="modal-end-date" required>
                        </div>
                    </div>
                    
                    {{-- Block Options --}}
                    <div id="block-options">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rooms to Block</label>
                                <input type="number" class="form-control" id="modal-blocked-count" min="0" value="0">
                                <small class="text-muted">Number of rooms to block from inventory</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Restriction Options --}}
                    <div id="restriction-options" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Minimum Stay (nights)</label>
                                <input type="number" class="form-control" id="modal-min-stay" min="1" max="30">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Maximum Stay (nights)</label>
                                <input type="number" class="form-control" id="modal-max-stay" min="1" max="365">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal-stop-sell">
                                    <label class="form-check-label" for="modal-stop-sell">
                                        <i class="fas fa-ban text-danger me-1"></i> Stop Sell
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal-cta">
                                    <label class="form-check-label" for="modal-cta">
                                        <i class="fas fa-sign-in-alt text-warning me-1"></i> Closed to Arrival
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal-ctd">
                                    <label class="form-check-label" for="modal-ctd">
                                        <i class="fas fa-sign-out-alt text-warning me-1"></i> Closed to Departure
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" id="modal-notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitBlockForm()">
                    <i class="fas fa-save me-1"></i> Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cell Detail Tooltip (hidden, shown on hover) --}}
<div id="cell-tooltip" class="inventory-tooltip" style="display: none;"></div>

<style>
    .inventory-grid-wrapper {
        max-height: 65vh;
        overflow: auto;
    }
    
    .inventory-table {
        border-collapse: collapse;
        font-size: 0.8rem;
        table-layout: fixed;
    }
    
    .inventory-table th,
    .inventory-table td {
        padding: 4px 6px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        white-space: nowrap;
    }
    
    .room-type-header {
        position: sticky;
        left: 0;
        background: #343a40 !important;
        color: white;
        font-weight: 600;
        min-width: 160px;
        max-width: 180px;
        z-index: 15;
        text-align: left !important;
    }
    
    .date-header {
        min-width: 55px;
        max-width: 60px;
        font-weight: bold;
        background: #f8f9fa;
    }
    
    .date-header.today {
        background: #0d6efd !important;
        color: white;
    }
    
    .date-header.weekend {
        background: #e9ecef;
    }
    
    .inventory-cell {
        min-width: 55px;
        height: 45px;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
        user-select: none;
    }
    
    .inventory-cell:hover {
        transform: scale(1.05);
        z-index: 5;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    .inventory-cell.selected {
        outline: 3px solid #0d6efd !important;
        outline-offset: -2px;
    }
    
    .inventory-cell .cell-main {
        font-weight: bold;
        font-size: 0.85rem;
    }
    
    .inventory-cell .cell-sub {
        font-size: 0.65rem;
        opacity: 0.8;
    }
    
    .inventory-cell .cell-icons {
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 0.6rem;
    }
    
    .legend-box {
        width: 16px;
        height: 16px;
        display: inline-block;
        margin-right: 4px;
        border-radius: 3px;
        border: 1px solid rgba(0,0,0,0.2);
    }
    
    .inventory-tooltip {
        position: fixed;
        background: #333;
        color: white;
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 0.8rem;
        max-width: 250px;
        z-index: 9999;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .inventory-tooltip::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 8px 8px 0;
        border-style: solid;
        border-color: #333 transparent transparent;
    }
    
    .totals-row td {
        background: #e9ecef !important;
        font-weight: bold;
    }
    
    /* Status colors */
    .status-available { background-color: #28a745; color: white; }
    .status-limited { background-color: #ffc107; color: #333; }
    .status-full { background-color: #dc3545; color: white; }
    .status-stop_sell { background-color: #6c757d; color: white; }
    
    /* Print styles */
    @media print {
        .inventory-grid-wrapper {
            max-height: none;
            overflow: visible;
        }
        .inventory-cell {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
    
    /* Responsive styles */
    @media (max-width: 992px) {
        .inventory-grid-wrapper {
            max-height: 55vh;
        }
        .room-type-header {
            min-width: 120px;
            max-width: 140px;
            font-size: 0.75rem;
        }
        .date-header, .inventory-cell {
            min-width: 45px;
            max-width: 50px;
        }
        .inventory-cell {
            height: 40px;
        }
        .inventory-cell .cell-main {
            font-size: 0.75rem;
        }
        .inventory-cell .cell-sub {
            font-size: 0.6rem;
        }
        .card-header .d-flex {
            flex-direction: column;
            gap: 0.75rem !important;
        }
        .card-header h5 {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .inventory-grid-wrapper {
            max-height: 50vh;
        }
        .room-type-header {
            min-width: 100px;
            max-width: 110px;
            font-size: 0.7rem;
        }
        .date-header, .inventory-cell {
            min-width: 38px;
            max-width: 42px;
            font-size: 0.7rem;
        }
        .inventory-cell {
            height: 36px;
            padding: 2px !important;
        }
        .inventory-cell .cell-main {
            font-size: 0.7rem;
        }
        .inventory-cell .cell-sub {
            display: none;
        }
        .inventory-cell .cell-icons {
            font-size: 0.5rem;
        }
        .legend-box {
            width: 12px;
            height: 12px;
        }
        #summary-stats .col {
            flex: 0 0 50%;
            margin-bottom: 0.5rem;
        }
        #summary-stats .h5 {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .card-body.bg-light .d-flex {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        #quick-actions {
            width: 100%;
            justify-content: center;
        }
        #quick-actions .btn {
            flex: 1;
        }
    }
</style>

<script>
// State variables (global scope for onclick handlers)
let currentDate = new Date();
let viewMode = 'monthly';
let selectedCells = new Set();
let isDragging = false;
let dragStartCell = null;

const API_URL = '{{ route("website.admin.inventory.api.data") }}';
const BLOCK_URL = '{{ route("website.admin.inventory.block") }}';
const RESTRICT_URL = '{{ route("website.admin.inventory.restrict") }}';
const OPEN_URL = '{{ route("website.admin.inventory.open") }}';
const STOP_SELL_URL = '{{ route("website.admin.inventory.stop-sell") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// Common fetch headers for JSON API calls
const API_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': CSRF_TOKEN,
    'X-Requested-With': 'XMLHttpRequest'
};

// Helper to handle API responses
async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: { ...API_HEADERS, ...options.headers }
    });
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Session expired. Please refresh the page and try again.');
    }
    
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`);
    }
    return data;
}

// All functions defined in global scope for onclick handlers
function loadInventoryData() {
    const { start, end } = getDateRange();
    updateMonthLabel();
    
    apiRequest(`${API_URL}?start=${start}&end=${end}`)
        .then(data => {
            if (data.error) {
                throw new Error(data.message + (data.file ? ' (File: ' + data.file + ':' + data.line + ')' : ''));
            }
            if (!data.room_types || !data.dates) {
                throw new Error('Invalid data format - missing room_types or dates');
            }
            renderInventoryGrid(data);
            updateSummaryStats(data.summary);
        })
        .catch(err => {
            console.error('Failed to load inventory:', err);
            const errorMsg = err.message || 'Unknown error';
            document.getElementById('inventory-body').innerHTML = 
                `<tr><td colspan="35" class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                    Failed to load inventory data<br>
                    <small class="text-muted">${errorMsg}</small><br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="loadInventoryData()">
                        <i class="fas fa-sync-alt"></i> Retry
                    </button>
                </td></tr>`;
        });
}

function getDateRange() {
    let start, end;
    if (viewMode === 'weekly') {
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
        start = formatDate(startOfWeek);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        end = formatDate(endOfWeek);
    } else {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        start = formatDate(new Date(year, month, 1));
        end = formatDate(new Date(year, month + 1, 0));
    }
    return { start, end };
}

function formatDate(date) {
    return date.toISOString().slice(0, 10);
}

function updateMonthLabel() {
    const label = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
    document.getElementById('month-label').textContent = label;
}

function changeMonth(offset) {
    if (viewMode === 'weekly') {
        currentDate.setDate(currentDate.getDate() + (offset * 7));
    } else {
        currentDate.setMonth(currentDate.getMonth() + offset);
    }
    clearSelection();
    loadInventoryData();
}

function goToToday() {
    currentDate = new Date();
    clearSelection();
    loadInventoryData();
}

function setView(mode) {
    viewMode = mode;
    document.getElementById('btn-monthly').classList.toggle('active', mode === 'monthly');
    document.getElementById('btn-weekly').classList.toggle('active', mode === 'weekly');
    clearSelection();
    loadInventoryData();
}

function renderInventoryGrid(data) {
    const headerRow = document.getElementById('date-header-row');
    const body = document.getElementById('inventory-body');
    const footer = document.getElementById('inventory-footer');
    const today = formatDate(new Date());
    
    // Build header row
    let headerHtml = '<th class="room-type-header">Room Category</th>';
    data.dates.forEach(day => {
        const classes = ['date-header'];
        if (day.date === today) classes.push('today');
        if (day.is_weekend) classes.push('weekend');
        headerHtml += `<th class="${classes.join(' ')}"><div>${day.weekday}</div><div>${day.day}</div></th>`;
    });
    headerRow.innerHTML = headerHtml;
    
    // Build room type rows
    let bodyHtml = '';
    data.room_types.forEach(roomType => {
        bodyHtml += `<tr data-room-type="${roomType.id}">`;
        bodyHtml += `<td class="room-type-header"><div class="fw-bold">${roomType.name}</div><small class="opacity-75">${roomType.total_units} rooms</small></td>`;
        
        data.dates.forEach(day => {
            const dateData = roomType.dates[day.date];
            if (dateData) {
                bodyHtml += renderInventoryCell(roomType.id, day.date, dateData, day.date === today);
            } else {
                bodyHtml += `<td class="inventory-cell">-</td>`;
            }
        });
        bodyHtml += '</tr>';
    });
    body.innerHTML = bodyHtml;
    
    // Build totals row
    let footerHtml = '<tr class="totals-row"><td class="room-type-header">TOTAL</td>';
    data.dates.forEach(day => {
        const totals = data.daily_totals[day.date];
        if (totals) {
            const statusClass = totals.percent > 30 ? 'status-available' : (totals.percent > 0 ? 'status-limited' : 'status-full');
            footerHtml += `<td class="inventory-cell ${statusClass}"><div class="cell-main">${totals.available}/${totals.total}</div></td>`;
        } else {
            footerHtml += '<td>-</td>';
        }
    });
    footerHtml += '</tr>';
    footer.innerHTML = footerHtml;
    
    attachCellListeners();
}

function renderInventoryCell(roomTypeId, date, data, isToday) {
    const statusClass = `status-${data.status}`;
    const cellId = `${roomTypeId}-${date}`;
    let icons = '';
    if (data.stop_sell) icons += '<i class="fas fa-ban" title="Stop Sell"></i> ';
    if (data.closed_to_arrival) icons += '<i class="fas fa-sign-in-alt" title="CTA"></i> ';
    if (data.min_stay) icons += `<i class="fas fa-moon" title="Min ${data.min_stay} nights"></i> `;
    
    return `<td class="inventory-cell ${statusClass} ${isToday ? 'today-cell' : ''}" 
        data-cell-id="${cellId}" data-room-type="${roomTypeId}" data-date="${date}"
        data-info='${JSON.stringify(data)}'>
        ${icons ? `<div class="cell-icons">${icons}</div>` : ''}
        <div class="cell-main">${data.available}/${data.total}</div>
        ${data.booked > 0 ? `<div class="cell-sub">${data.booked} booked</div>` : ''}
    </td>`;
}

function attachCellListeners() {
    document.querySelectorAll('.inventory-cell[data-cell-id]').forEach(cell => {
        cell.addEventListener('mousedown', handleCellMouseDown);
        cell.addEventListener('mouseenter', handleCellMouseEnter);
        cell.addEventListener('mouseup', () => { isDragging = false; });
        cell.addEventListener('mouseover', showCellTooltip);
        cell.addEventListener('mouseout', hideCellTooltip);
    });
}

function handleCellMouseDown(e) {
    e.preventDefault();
    isDragging = true;
    dragStartCell = e.currentTarget;
    if (!e.shiftKey && !e.ctrlKey) clearSelection();
    toggleCellSelection(e.currentTarget);
}

function handleCellMouseEnter(e) {
    if (isDragging) selectCellsInRange(dragStartCell, e.currentTarget);
}

function toggleCellSelection(cell) {
    const cellId = cell.dataset.cellId;
    if (selectedCells.has(cellId)) {
        selectedCells.delete(cellId);
        cell.classList.remove('selected');
    } else {
        selectedCells.add(cellId);
        cell.classList.add('selected');
    }
    updateSelectionUI();
}

function selectCellsInRange(startCell, endCell) {
    const allCells = Array.from(document.querySelectorAll('.inventory-cell[data-cell-id]'));
    const rows = Array.from(document.querySelectorAll('#inventory-body tr'));
    const startRow = startCell.closest('tr');
    const endRow = endCell.closest('tr');
    const startRowIdx = rows.indexOf(startRow);
    const endRowIdx = rows.indexOf(endRow);
    const minRowIdx = Math.min(startRowIdx, endRowIdx);
    const maxRowIdx = Math.max(startRowIdx, endRowIdx);
    const startDate = startCell.dataset.date;
    const endDate = endCell.dataset.date;
    
    allCells.forEach(cell => {
        const row = cell.closest('tr');
        const rowIdx = rows.indexOf(row);
        const cellDate = cell.dataset.date;
        if (rowIdx >= minRowIdx && rowIdx <= maxRowIdx) {
            if ((cellDate >= startDate && cellDate <= endDate) || (cellDate <= startDate && cellDate >= endDate)) {
                selectedCells.add(cell.dataset.cellId);
                cell.classList.add('selected');
            }
        }
    });
    updateSelectionUI();
}

function clearSelection() {
    selectedCells.clear();
    document.querySelectorAll('.inventory-cell.selected').forEach(cell => cell.classList.remove('selected'));
    updateSelectionUI();
}

function updateSelectionUI() {
    const count = selectedCells.size;
    const badge = document.getElementById('selection-badge');
    const actions = document.getElementById('quick-actions');
    if (count > 0) {
        badge.style.display = 'inline-block';
        document.getElementById('selection-count').textContent = count;
        actions.style.cssText = 'display: flex !important';
    } else {
        badge.style.display = 'none';
        actions.style.cssText = 'display: none !important';
    }
}

function getSelectedInfo() {
    const info = { roomTypes: new Set(), dates: new Set(), cells: [] };
    selectedCells.forEach(cellId => {
        // cellId format is "roomTypeId-YYYY-MM-DD", split only on first dash
        const firstDash = cellId.indexOf('-');
        const roomTypeId = cellId.substring(0, firstDash);
        const date = cellId.substring(firstDash + 1);
        info.roomTypes.add(parseInt(roomTypeId));
        info.dates.add(date);
        info.cells.push({ roomTypeId: parseInt(roomTypeId), date });
    });
    const dates = Array.from(info.dates).sort();
    return { roomTypes: Array.from(info.roomTypes), startDate: dates[0], endDate: dates[dates.length - 1], cells: info.cells };
}

function quickAction(action) {
    const selection = getSelectedInfo();
    if (selection.roomTypes.length === 0) return;
    const url = action === 'open' ? OPEN_URL : STOP_SELL_URL;
    
    const promises = selection.roomTypes.map(roomTypeId => {
        return apiRequest(url, {
            method: 'POST',
            body: JSON.stringify({ room_type_id: roomTypeId, start_date: selection.startDate, end_date: selection.endDate })
        });
    });
    
    Promise.all(promises).then(results => {
        const allSuccess = results.every(r => r.success);
        alert(allSuccess ? 'Changes applied successfully!' : 'Some changes failed.');
        clearSelection();
        loadInventoryData();
    }).catch(err => {
        alert('Failed: ' + err.message);
    });
}

function showBlockModal() {
    const selection = getSelectedInfo();
    if (selection.roomTypes.length === 0) return;
    document.getElementById('modal-room-type').value = selection.roomTypes[0];
    document.getElementById('modal-start-date').value = selection.startDate;
    document.getElementById('modal-end-date').value = selection.endDate;
    new bootstrap.Modal(document.getElementById('blockModal')).show();
}

function toggleBlockFields() {
    const type = document.getElementById('modal-block-type').value;
    document.getElementById('block-options').style.display = type === 'restrictions' ? 'none' : 'block';
    document.getElementById('restriction-options').style.display = type === 'restrictions' ? 'block' : 'none';
}

function submitBlockForm() {
    const blockType = document.getElementById('modal-block-type').value;
    const payload = {
        room_type_id: document.getElementById('modal-room-type').value,
        start_date: document.getElementById('modal-start-date').value,
        end_date: document.getElementById('modal-end-date').value,
        notes: document.getElementById('modal-notes').value
    };
    
    let url;
    if (blockType === 'restrictions') {
        url = RESTRICT_URL;
        payload.min_stay = document.getElementById('modal-min-stay').value || null;
        payload.max_stay = document.getElementById('modal-max-stay').value || null;
        payload.stop_sell = document.getElementById('modal-stop-sell').checked;
        payload.closed_to_arrival = document.getElementById('modal-cta').checked;
        payload.closed_to_departure = document.getElementById('modal-ctd').checked;
    } else {
        url = BLOCK_URL;
        payload.blocked_count = document.getElementById('modal-blocked-count').value || 0;
        payload.block_type = blockType;
    }
    
    apiRequest(url, { method: 'POST', body: JSON.stringify(payload) })
        .then(result => {
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('blockModal')).hide();
                clearSelection();
                loadInventoryData();
                alert('Changes applied successfully!');
            } else {
                alert('Failed: ' + result.message);
            }
        })
        .catch(err => alert('Error: ' + err.message));
}

function showCellTooltip(e) {
    const cell = e.currentTarget;
    const info = JSON.parse(cell.dataset.info || '{}');
    const tooltip = document.getElementById('cell-tooltip');
    
    // Get status label and color
    const statusLabels = {
        'available': { label: 'Available', color: '#28a745' },
        'limited': { label: 'Limited Availability', color: '#ffc107' },
        'full': { label: 'Fully Booked', color: '#dc3545' },
        'stop_sell': { label: 'Stop Sell', color: '#6c757d' }
    };
    const status = statusLabels[info.status] || { label: info.status, color: '#6c757d' };
    
    let html = `<div><strong>${info.date}</strong></div>`;
    html += `<div class="mt-1" style="color: ${status.color}; font-weight: bold;"><i class="fas ${info.stop_sell ? 'fa-ban' : (info.status === 'full' ? 'fa-times-circle' : 'fa-check-circle')} me-1"></i>${status.label}</div>`;
    html += `<div class="mt-1">Available: <strong>${info.available}</strong> / ${info.total}</div>`;
    html += `<div>Booked: ${info.booked}</div>`;
    if (info.blocked > 0) {
        html += `<div>Blocked: <span class="text-warning">${info.blocked}</span></div>`;
    }
    if (info.stop_sell) {
        html += `<div class="mt-1 text-danger"><i class="fas fa-ban me-1"></i>Stop Sell Active</div>`;
    }
    if (info.closed_to_arrival) {
        html += `<div class="text-warning"><i class="fas fa-sign-in-alt me-1"></i>Closed to Arrival</div>`;
    }
    if (info.closed_to_departure) {
        html += `<div class="text-warning"><i class="fas fa-sign-out-alt me-1"></i>Closed to Departure</div>`;
    }
    if (info.min_stay) {
        html += `<div class="text-info"><i class="fas fa-moon me-1"></i>Min Stay: ${info.min_stay} nights</div>`;
    }
    if (info.max_stay) {
        html += `<div class="text-info"><i class="fas fa-calendar-times me-1"></i>Max Stay: ${info.max_stay} nights</div>`;
    }
    if (info.restrictions && info.restrictions.length > 0) {
        html += `<div class="mt-1 text-warning">Restrictions: ${info.restrictions.join(', ')}</div>`;
    }
    
    tooltip.innerHTML = html;
    tooltip.style.display = 'block';
    const rect = cell.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
}

function hideCellTooltip() {
    document.getElementById('cell-tooltip').style.display = 'none';
}

function updateSummaryStats(summary) {
    document.getElementById('stat-total-nights').textContent = summary.total_room_nights.toLocaleString();
    document.getElementById('stat-available').textContent = summary.available_room_nights.toLocaleString();
    document.getElementById('stat-booked').textContent = summary.booked_room_nights.toLocaleString();
    document.getElementById('stat-blocked').textContent = summary.blocked_room_nights.toLocaleString();
    document.getElementById('stat-occupancy').textContent = summary.occupancy_rate + '%';
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    loadInventoryData();
    document.addEventListener('mouseup', () => { isDragging = false; });
    setInterval(loadInventoryData, 60000);
});
</script>

@endsection
