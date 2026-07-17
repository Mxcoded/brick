@extends('layouts.master')

@section('title', 'Room Inventory Calendar')

@section('page-content')
<div class="container-fluid px-2 px-lg-4 py-3">
    {{-- Header Card --}}
    <div class="card inventory-card shadow-lg border-0 mb-3">
        {{-- Top Navigation Bar --}}
        <div class="card-header inventory-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                {{-- Title & Selection Badge --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-white">Room Inventory</h4>
                        <small class="text-white-50">Manage availability & restrictions</small>
                    </div>
                    <span class="badge selection-badge" id="selection-badge" style="display: none;">
                        <i class="fas fa-check-square me-1"></i>
                        <span id="selection-count">0</span> selected
                    </span>
                </div>
                
                {{-- Navigation Controls --}}
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    {{-- Date Navigation --}}
                    <div class="btn-group nav-btn-group">
                        <button class="btn btn-nav" onclick="changeMonth(-1)" title="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-nav month-label" id="month-label">
                            <i class="fas fa-spinner fa-spin"></i>
                        </button>
                        <button class="btn btn-nav" onclick="changeMonth(1)" title="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <button class="btn btn-today" onclick="goToToday()">
                        <i class="fas fa-calendar-day me-1"></i>Today
                    </button>
                    
                    <div class="btn-group view-toggle">
                        <button class="btn btn-view active" id="btn-monthly" onclick="setView('monthly')">
                            <i class="fas fa-calendar-alt me-1"></i>Month
                        </button>
                        <button class="btn btn-view" id="btn-weekly" onclick="setView('weekly')">
                            <i class="fas fa-calendar-week me-1"></i>Week
                        </button>
                    </div>
                    
                    <button class="btn btn-refresh" onclick="loadInventoryData()" title="Refresh Data">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Legend & Quick Actions Bar --}}
        <div class="legend-bar">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                {{-- Status Legend --}}
                <div class="d-flex gap-3 flex-wrap legend-items">
                    <span class="legend-item">
                        <span class="legend-dot available"></span>
                        <span>Available</span>
                    </span>
                    <span class="legend-item">
                        <span class="legend-dot limited"></span>
                        <span>Limited (&lt;30%)</span>
                    </span>
                    <span class="legend-item">
                        <span class="legend-dot full"></span>
                        <span>Sold Out</span>
                    </span>
                    <span class="legend-item">
                        <span class="legend-dot blocked"></span>
                        <span>Blocked</span>
                    </span>
                </div>
                
                {{-- Quick Actions (shown when cells selected) --}}
                <div class="quick-actions-group" id="quick-actions" style="display: none !important;">
                    <button class="btn btn-action btn-action-success" onclick="showBlockModal('open')">
                        <i class="fas fa-lock-open"></i> Open
                    </button>
                    <button class="btn btn-action btn-action-danger" onclick="quickAction('stop-sell')">
                        <i class="fas fa-ban"></i> Stop Sell
                    </button>
                    <button class="btn btn-action btn-action-warning" onclick="showBlockModal()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-action btn-action-secondary" onclick="clearSelection()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Inventory Grid --}}
        <div class="card-body p-0">
            <div class="inventory-grid-wrapper">
                <table class="inventory-table" id="inventory-table">
                    <thead id="inventory-thead">
                        <tr id="date-header-row"></tr>
                    </thead>
                    <tbody id="inventory-body">
                        <tr>
                            <td colspan="35" class="text-center py-5">
                                <div class="loading-state">
                                    <div class="spinner-grow text-primary" role="status"></div>
                                    <div class="spinner-grow text-primary" role="status" style="animation-delay: 0.1s;"></div>
                                    <div class="spinner-grow text-primary" role="status" style="animation-delay: 0.2s;"></div>
                                </div>
                                <div class="mt-3 text-muted">Loading inventory data...</div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="inventory-footer"></tfoot>
                </table>
            </div>
        </div>

        {{-- Summary Stats Footer --}}
        <div class="card-footer stats-footer">
            <div class="row g-3" id="summary-stats">
                <div class="col-6 col-md">
                    <div class="stat-card stat-total">
                        <div class="stat-icon"><i class="fas fa-bed"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="stat-total-nights">-</div>
                            <div class="stat-label">Total Nights</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-card stat-available">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="stat-available">-</div>
                            <div class="stat-label">Available</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-card stat-booked">
                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="stat-booked">-</div>
                            <div class="stat-label">Booked</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="stat-card stat-blocked">
                        <div class="stat-icon"><i class="fas fa-ban"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="stat-blocked">-</div>
                            <div class="stat-label">Blocked</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md">
                    <div class="stat-card stat-occupancy">
                        <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="stat-occupancy">-</div>
                            <div class="stat-label">Occupancy</div>
                        </div>
                    </div>
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
                                <option value="{{ $type->id }}" data-units="{{ $type->units->count() }}">{{ $type->name }} ({{ $type->units->count() }} rooms)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Action Type</label>
                            <select class="form-select" id="modal-block-type" onchange="toggleBlockFields()">
                                <option value="manual">Block Rooms</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="stop_sell">Stop Sell</option>
                                <option value="open">Open Rooms</option>
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
                    
                    {{-- Open Options --}}
                    <div id="open-options" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rooms to Open</label>
                                <input type="number" class="form-control" id="modal-open-count" min="0">
                                <small class="text-muted">Leave at the total to open all blocked rooms in this range, or enter fewer to open only that many.</small>
                            </div>
                        </div>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-lock-open me-1"></i>
                            Opening frees rooms for the selected room type within the date range
                            below. You can open a sub-range of a previously blocked range — the
                            remaining dates (and any unopened rooms) stay blocked.
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
                <button type="button" class="btn btn-primary" id="btn-apply-changes" onclick="submitBlockForm()">
                    <i class="fas fa-save me-1"></i> Apply Changes
                </button>
                <button type="button" id="modal-open-trigger" style="display:none" data-bs-toggle="modal" data-bs-target="#blockModal"></button>
            </div>
        </div>
    </div>
</div>

{{-- Cell Detail Tooltip (hidden, shown on hover) --}}
<div id="cell-tooltip" class="inventory-tooltip" style="display: none;"></div>

<style>
    :root {
        --inv-primary: #1a1a2e;
        --inv-secondary: #16213e;
        --inv-accent: #c5a572;
        --inv-accent-light: #d4bc8e;
        --inv-success: #10b981;
        --inv-warning: #f59e0b;
        --inv-danger: #ef4444;
        --inv-muted: #64748b;
        --inv-light: #f8fafc;
        --inv-border: #e2e8f0;
        --inv-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --inv-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    }
    
    /* Card Container */
    .inventory-card {
        border-radius: 16px;
        overflow: hidden;
        background: white;
    }
    
    /* Header Styles */
    .inventory-header {
        background: linear-gradient(135deg, var(--inv-primary) 0%, var(--inv-secondary) 100%);
        padding: 1.25rem 1.5rem;
        border: none;
    }
    
    .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--inv-accent) 0%, var(--inv-accent-light) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--inv-primary);
    }
    
    .selection-badge {
        background: var(--inv-accent);
        color: var(--inv-primary);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    /* Navigation Buttons */
    .nav-btn-group {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 3px;
    }
    
    .btn-nav {
        background: transparent;
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .btn-nav:hover {
        background: rgba(255,255,255,0.15);
        color: white;
    }
    
    .btn-nav.month-label {
        min-width: 160px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .btn-today {
        background: var(--inv-accent);
        color: var(--inv-primary);
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .btn-today:hover {
        background: var(--inv-accent-light);
        color: var(--inv-primary);
        transform: translateY(-1px);
    }
    
    .view-toggle {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 3px;
    }
    
    .btn-view {
        background: transparent;
        color: rgba(255,255,255,0.7);
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    
    .btn-view:hover {
        color: white;
    }
    
    .btn-view.active {
        background: white;
        color: var(--inv-primary);
        font-weight: 600;
    }
    
    .btn-refresh {
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    
    .btn-refresh:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        transform: rotate(90deg);
    }
    
    /* Legend Bar */
    .legend-bar {
        background: var(--inv-light);
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid var(--inv-border);
    }
    
    .legend-items {
        font-size: 0.85rem;
        color: var(--inv-muted);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        box-shadow: inset 0 -2px 0 rgba(0,0,0,0.1);
    }
    
    .legend-dot.available { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
    .legend-dot.limited { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); }
    .legend-dot.full { background: linear-gradient(135deg, #f87171 0%, #ef4444 100%); }
    .legend-dot.blocked { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); }
    
    /* Quick Actions */
    .quick-actions-group {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        transition: all 0.2s;
    }
    
    .btn-action-success { background: var(--inv-success); color: white; }
    .btn-action-success:hover { background: #059669; color: white; transform: translateY(-1px); }
    .btn-action-danger { background: var(--inv-danger); color: white; }
    .btn-action-danger:hover { background: #dc2626; color: white; transform: translateY(-1px); }
    .btn-action-warning { background: var(--inv-warning); color: white; }
    .btn-action-warning:hover { background: #d97706; color: white; transform: translateY(-1px); }
    .btn-action-secondary { background: var(--inv-muted); color: white; }
    .btn-action-secondary:hover { background: #475569; color: white; }
    
    /* Inventory Grid */
    .inventory-grid-wrapper {
        max-height: 60vh;
        overflow: auto;
        background: white;
    }
    
    .inventory-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.85rem;
    }
    
    .inventory-table th,
    .inventory-table td {
        padding: 0;
        text-align: center;
        vertical-align: middle;
        border: 1px solid var(--inv-border);
    }
    
    /* Room Type Column - WIDER */
    .room-type-cell {
        position: sticky;
        left: 0;
        z-index: 20;
        background: linear-gradient(135deg, var(--inv-primary) 0%, var(--inv-secondary) 100%);
        color: white;
        min-width: 220px;
        width: 220px;
        max-width: 220px;
        padding: 12px 16px !important;
        text-align: left !important;
        border: none !important;
        box-shadow: 4px 0 8px rgba(0,0,0,0.1);
    }
    
    .room-type-cell .room-name {
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
        line-height: 1.3;
        white-space: normal;
        word-wrap: break-word;
    }
    
    .room-type-cell .room-info {
        font-size: 0.75rem;
        opacity: 0.8;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .room-type-cell .room-info i {
        font-size: 0.7rem;
    }
    
    /* Header Room Type Cell */
    #inventory-thead .room-type-cell {
        background: var(--inv-primary);
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Date Headers */
    .date-cell {
        min-width: 58px;
        width: 58px;
        padding: 8px 4px !important;
        background: var(--inv-light);
        border-bottom: 2px solid var(--inv-border) !important;
    }
    
    .date-cell .day-name {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--inv-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .date-cell .day-num {
        font-size: 1rem;
        font-weight: 700;
        color: var(--inv-primary);
        line-height: 1.2;
    }
    
    .date-cell.today {
        background: linear-gradient(135deg, var(--inv-accent) 0%, var(--inv-accent-light) 100%) !important;
    }
    
    .date-cell.today .day-name,
    .date-cell.today .day-num {
        color: var(--inv-primary) !important;
    }
    
    .date-cell.weekend {
        background: #f1f5f9;
    }
    
    .date-cell.weekend .day-name {
        color: var(--inv-danger);
    }
    
    /* Inventory Cells */
    .inventory-cell {
        min-width: 58px;
        width: 58px;
        height: 56px;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
        user-select: none;
        padding: 6px 4px !important;
    }
    
    .inventory-cell:hover {
        transform: scale(1.08);
        z-index: 10;
        box-shadow: var(--inv-shadow-lg);
        border-radius: 6px;
    }
    
    .inventory-cell.selected {
        outline: 3px solid var(--inv-accent) !important;
        outline-offset: -2px;
        border-radius: 4px;
    }
    
    .inventory-cell .cell-avail {
        font-weight: 800;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    
    .inventory-cell .cell-total {
        font-size: 0.7rem;
        opacity: 0.85;
        font-weight: 500;
    }
    
    .inventory-cell .cell-icons {
        position: absolute;
        top: 3px;
        right: 3px;
        font-size: 0.6rem;
        display: flex;
        gap: 2px;
    }
    
    .inventory-cell .cell-icons i {
        background: rgba(0,0,0,0.2);
        padding: 2px;
        border-radius: 3px;
    }
    
    /* Status Colors - Gradient Style */
    .status-available { 
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); 
        color: white; 
    }
    .status-limited { 
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); 
        color: var(--inv-primary); 
    }
    .status-full { 
        background: linear-gradient(135deg, #f87171 0%, #ef4444 100%); 
        color: white; 
    }
    .status-stop_sell { 
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); 
        color: white; 
    }
    
    /* Today Cell Highlight */
    .today-col {
        background-color: rgba(197, 165, 114, 0.1) !important;
    }
    
    /* Totals Row */
    .totals-row .room-type-cell {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 100%);
        font-weight: 800;
        letter-spacing: 1px;
    }
    
    .totals-row .inventory-cell {
        background: #1e293b !important;
        color: white;
        font-weight: 700;
    }
    
    /* Stats Footer */
    .stats-footer {
        background: var(--inv-light);
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--inv-border);
    }
    
    .stat-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: white;
        border-radius: 12px;
        box-shadow: var(--inv-shadow);
        transition: all 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--inv-shadow-lg);
    }
    
    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .stat-total .stat-icon { background: #ede9fe; color: #7c3aed; }
    .stat-available .stat-icon { background: #d1fae5; color: #059669; }
    .stat-booked .stat-icon { background: #fee2e2; color: #dc2626; }
    .stat-blocked .stat-icon { background: #f1f5f9; color: #64748b; }
    .stat-occupancy .stat-icon { background: #dbeafe; color: #2563eb; }
    
    .stat-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--inv-primary);
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: var(--inv-muted);
        font-weight: 500;
    }
    
    /* Tooltip */
    .inventory-tooltip {
        position: fixed;
        background: var(--inv-primary);
        color: white;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 0.85rem;
        max-width: 280px;
        z-index: 9999;
        pointer-events: none;
        box-shadow: var(--inv-shadow-lg);
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .inventory-tooltip::before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 8px 8px 0;
        border-style: solid;
        border-color: var(--inv-primary) transparent transparent;
    }
    
    .tooltip-header {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }
    
    .tooltip-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        margin-bottom: 8px;
    }
    
    .tooltip-status.available { background: var(--inv-success); }
    .tooltip-status.limited { background: var(--inv-warning); color: var(--inv-primary); }
    .tooltip-status.full { background: var(--inv-danger); }
    .tooltip-status.stop_sell { background: var(--inv-muted); }
    
    .tooltip-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 0.8rem;
    }
    
    .tooltip-row span:first-child {
        opacity: 0.7;
    }
    
    .tooltip-row span:last-child {
        font-weight: 600;
    }
    
    .tooltip-restriction {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255,255,255,0.15);
        font-size: 0.75rem;
    }
    
    .tooltip-restriction i {
        margin-right: 6px;
    }
    
    /* Loading State */
    .loading-state {
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    
    /* Print styles */
    @media print {
        .inventory-grid-wrapper { max-height: none; overflow: visible; }
        .inventory-cell { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .inventory-header, .legend-bar, .stats-footer { display: none; }
    }
    
    /* Responsive Styles */
    @media (max-width: 1200px) {
        .room-type-cell {
            min-width: 180px;
            width: 180px;
            max-width: 180px;
        }
        .date-cell, .inventory-cell {
            min-width: 52px;
            width: 52px;
        }
    }
    
    @media (max-width: 992px) {
        .inventory-header {
            padding: 1rem;
        }
        .inventory-header > .d-flex {
            flex-direction: column;
            align-items: stretch !important;
            gap: 1rem !important;
        }
        .inventory-header .d-flex:first-child {
            justify-content: center;
        }
        .inventory-header .d-flex:last-child {
            justify-content: center;
        }
        .header-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        .inventory-header h4 {
            font-size: 1.1rem;
        }
        .room-type-cell {
            min-width: 150px;
            width: 150px;
            max-width: 150px;
            padding: 10px 12px !important;
        }
        .room-type-cell .room-name {
            font-size: 0.85rem;
        }
        .room-type-cell .room-info {
            font-size: 0.7rem;
        }
        .date-cell, .inventory-cell {
            min-width: 48px;
            width: 48px;
        }
        .inventory-cell {
            height: 50px;
        }
        .inventory-cell .cell-avail {
            font-size: 1rem;
        }
        .stat-card {
            padding: 10px 12px;
        }
        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }
        .stat-value {
            font-size: 1.1rem;
        }
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .inventory-grid-wrapper {
            max-height: 55vh;
        }
        .room-type-cell {
            min-width: 120px;
            width: 120px;
            max-width: 120px;
            padding: 8px 10px !important;
        }
        .room-type-cell .room-name {
            font-size: 0.8rem;
        }
        .room-type-cell .room-info {
            font-size: 0.65rem;
            flex-direction: column;
            gap: 2px;
            align-items: flex-start;
        }
        .date-cell, .inventory-cell {
            min-width: 42px;
            width: 42px;
        }
        .date-cell .day-name {
            font-size: 0.6rem;
        }
        .date-cell .day-num {
            font-size: 0.85rem;
        }
        .inventory-cell {
            height: 44px;
        }
        .inventory-cell .cell-avail {
            font-size: 0.9rem;
        }
        .inventory-cell .cell-total {
            font-size: 0.6rem;
        }
        .inventory-cell .cell-icons {
            font-size: 0.5rem;
        }
        .legend-bar {
            padding: 0.5rem 1rem;
        }
        .legend-items {
            font-size: 0.75rem;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
        }
        .btn-nav.month-label {
            min-width: 130px;
            font-size: 0.85rem;
        }
        .btn-today span {
            display: none;
        }
        .btn-view {
            font-size: 0.75rem;
            padding: 0.4rem 0.5rem;
        }
        .btn-view i {
            margin-right: 0 !important;
        }
        .btn-view span:not(.d-none) {
            display: none;
        }
        .stats-footer {
            padding: 1rem;
        }
        .stat-card {
            padding: 8px 10px;
        }
        .stat-icon {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
        .stat-value {
            font-size: 1rem;
        }
        .stat-label {
            font-size: 0.7rem;
        }
    }
    
    @media (max-width: 576px) {
        .inventory-header > .d-flex > .d-flex:first-child {
            flex-direction: column;
            text-align: center;
        }
        .header-icon {
            margin: 0 auto 8px;
        }
        .nav-btn-group, .view-toggle {
            width: 100%;
            justify-content: center;
        }
        .btn-nav.month-label {
            flex: 1;
        }
        .btn-view {
            flex: 1;
        }
        .legend-bar .d-flex {
            flex-direction: column;
            gap: 0.75rem !important;
        }
        .legend-items {
            justify-content: center;
        }
        .quick-actions-group {
            width: 100%;
            justify-content: center;
        }
        .quick-actions-group .btn-action {
            flex: 1;
        }
        .room-type-cell {
            min-width: 100px;
            width: 100px;
            max-width: 100px;
        }
        .date-cell, .inventory-cell {
            min-width: 38px;
            width: 38px;
        }
        .inventory-cell {
            height: 40px;
        }
        .inventory-cell .cell-avail {
            font-size: 0.85rem;
        }
        .inventory-cell .cell-total {
            display: none;
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
    
    // Build header row with new styling
    let headerHtml = '<th class="room-type-cell">Room Type</th>';
    data.dates.forEach(day => {
        const classes = ['date-cell'];
        if (day.date === today) classes.push('today');
        if (day.is_weekend) classes.push('weekend');
        headerHtml += `<th class="${classes.join(' ')}">
            <div class="day-name">${day.weekday}</div>
            <div class="day-num">${day.day}</div>
        </th>`;
    });
    headerRow.innerHTML = headerHtml;
    
    // Build room type rows with enhanced styling
    let bodyHtml = '';
    data.room_types.forEach(roomType => {
        bodyHtml += `<tr data-room-type="${roomType.id}">`;
        bodyHtml += `<td class="room-type-cell">
            <div class="room-name">${roomType.name}</div>
            <div class="room-info">
                <span><i class="fas fa-door-open"></i> ${roomType.total_units} rooms</span>
                <span><i class="fas fa-users"></i> ${roomType.capacity || '-'} guests</span>
            </div>
        </td>`;
        
        data.dates.forEach(day => {
            const dateData = roomType.dates[day.date];
            if (dateData) {
                bodyHtml += renderInventoryCell(roomType.id, day.date, dateData, day.date === today);
            } else {
                bodyHtml += `<td class="inventory-cell"><div class="cell-avail">-</div></td>`;
            }
        });
        bodyHtml += '</tr>';
    });
    body.innerHTML = bodyHtml;
    
    // Build totals row with enhanced styling
    let footerHtml = '<tr class="totals-row"><td class="room-type-cell"><div class="room-name">TOTAL</div></td>';
    data.dates.forEach(day => {
        const totals = data.daily_totals[day.date];
        if (totals) {
            footerHtml += `<td class="inventory-cell">
                <div class="cell-avail">${totals.available}</div>
                <div class="cell-total">of ${totals.total}</div>
            </td>`;
        } else {
            footerHtml += '<td class="inventory-cell"><div class="cell-avail">-</div></td>';
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
    if (data.stop_sell) icons += '<i class="fas fa-ban" title="Stop Sell"></i>';
    if (data.closed_to_arrival) icons += '<i class="fas fa-sign-in-alt" title="CTA"></i>';
    if (data.min_stay) icons += `<i class="fas fa-moon" title="Min ${data.min_stay} nights"></i>`;
    
    return `<td class="inventory-cell ${statusClass} ${isToday ? 'today-col' : ''}" 
        data-cell-id="${cellId}" data-room-type="${roomTypeId}" data-date="${date}"
        data-info='${JSON.stringify(data)}'>
        ${icons ? `<div class="cell-icons">${icons}</div>` : ''}
        <div class="cell-avail">${data.available}</div>
        <div class="cell-total">of ${data.total}</div>
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

function showBlockModal(mode = 'block') {
    const selection = getSelectedInfo();
    if (selection.roomTypes.length === 0) return;
    document.getElementById('modal-room-type').value = selection.roomTypes[0];
    document.getElementById('modal-start-date').value = selection.startDate;
    document.getElementById('modal-end-date').value = selection.endDate;
    document.getElementById('modal-block-type').value = (mode === 'open') ? 'open' : 'manual';
    toggleBlockFields();
    document.getElementById('modal-notes').value = '';
    document.getElementById('modal-blocked-count').value = 0;
    document.getElementById('modal-min-stay').value = '';
    document.getElementById('modal-max-stay').value = '';
    document.getElementById('modal-stop-sell').checked = false;
    document.getElementById('modal-cta').checked = false;
    document.getElementById('modal-ctd').checked = false;
    // Default "Rooms to open" to the room type's total so opening clears all
    // blocked rooms in the range; the user can lower it to open fewer.
    const roomTypeSelect = document.getElementById('modal-room-type');
    const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
    document.getElementById('modal-open-count').value = selectedOption ? selectedOption.dataset.units : '';
    document.getElementById('modal-open-trigger').click();
}

function toggleBlockFields() {
    const type = document.getElementById('modal-block-type').value;
    const isRestrictions = type === 'restrictions';
    const isOpen = type === 'open';
    document.getElementById('block-options').style.display = (isRestrictions || isOpen) ? 'none' : 'block';
    document.getElementById('restriction-options').style.display = isRestrictions ? 'block' : 'none';
    document.getElementById('open-options').style.display = isOpen ? 'block' : 'none';
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
    if (blockType === 'open') {
        url = OPEN_URL;
        const openCount = document.getElementById('modal-open-count').value;
        payload.open_count = openCount === '' ? null : parseInt(openCount, 10);
    } else if (blockType === 'restrictions') {
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
    
    document.getElementById('btn-apply-changes').disabled = true;
    document.getElementById('btn-apply-changes').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Applying...';

    apiRequest(url, { method: 'POST', body: JSON.stringify(payload) })
        .then(result => {
            if (result.success) {
                document.querySelector('#blockModal .btn-close').click();
                clearSelection();
                loadInventoryData();
            } else {
                alert('Failed: ' + result.message);
            }
        })
        .catch(err => alert('Error: ' + err.message))
        .finally(() => {
            document.getElementById('btn-apply-changes').disabled = false;
            document.getElementById('btn-apply-changes').innerHTML = '<i class="fas fa-save me-1"></i> Apply Changes';
        });
}

function showCellTooltip(e) {
    const cell = e.currentTarget;
    const info = JSON.parse(cell.dataset.info || '{}');
    const tooltip = document.getElementById('cell-tooltip');
    
    // Format date nicely
    const dateObj = new Date(info.date + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    
    // Get status label and icon
    const statusConfig = {
        'available': { label: 'Available', icon: 'fa-check-circle', class: 'available' },
        'limited': { label: 'Limited', icon: 'fa-exclamation-circle', class: 'limited' },
        'full': { label: 'Sold Out', icon: 'fa-times-circle', class: 'full' },
        'stop_sell': { label: 'Stop Sell', icon: 'fa-ban', class: 'stop_sell' }
    };
    const status = statusConfig[info.status] || { label: info.status || 'Unknown', icon: 'fa-question-circle', class: 'stop_sell' };
    
    let html = `<div class="tooltip-header">${formattedDate}</div>`;
    html += `<div class="tooltip-status ${status.class}"><i class="fas ${status.icon}"></i> ${status.label}</div>`;
    
    html += `<div class="tooltip-row"><span>Available</span><span>${info.available} rooms</span></div>`;
    html += `<div class="tooltip-row"><span>Total</span><span>${info.total} rooms</span></div>`;
    html += `<div class="tooltip-row"><span>Booked</span><span>${info.booked} rooms</span></div>`;
    
    if (info.blocked > 0) {
        html += `<div class="tooltip-row"><span>Blocked</span><span>${info.blocked} rooms</span></div>`;
    }
    
    // Restrictions section
    let restrictions = [];
    if (info.stop_sell) restrictions.push('<i class="fas fa-ban"></i> Stop Sell');
    if (info.closed_to_arrival) restrictions.push('<i class="fas fa-sign-in-alt"></i> No Arrival');
    if (info.closed_to_departure) restrictions.push('<i class="fas fa-sign-out-alt"></i> No Departure');
    if (info.min_stay) restrictions.push(`<i class="fas fa-moon"></i> Min ${info.min_stay} nights`);
    if (info.max_stay) restrictions.push(`<i class="fas fa-calendar-times"></i> Max ${info.max_stay} nights`);
    
    if (restrictions.length > 0) {
        html += `<div class="tooltip-restriction">${restrictions.join('<br>')}</div>`;
    }
    
    tooltip.innerHTML = html;
    tooltip.style.display = 'block';
    
    const rect = cell.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    // Position tooltip above the cell, centered
    let left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2;
    let top = rect.top - tooltip.offsetHeight - 10;
    
    // Keep within viewport bounds
    if (left < 10) left = 10;
    if (left + tooltip.offsetWidth > window.innerWidth - 10) left = window.innerWidth - tooltip.offsetWidth - 10;
    if (top < 10) top = rect.bottom + 10; // Show below if no room above
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
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
