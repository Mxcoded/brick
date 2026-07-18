@extends('restaurant::layouts.adminMaster')
@section('title', 'Sales Report')
@section('admin-content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Sales Report</h4>
        <div class="d-flex gap-2">
            <a href="#" id="exportCsv" class="btn btn-outline-success btn-sm rounded-pill">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <form class="row g-3 align-items-end" id="reportForm">
                @csrf
                <div class="col-auto">
                    <label class="form-label small fw-medium">Period</label>
                    <select class="form-select" id="period" onchange="toggleCustom()">
                        <option value="today">Today</option>
                        <option value="week" selected>This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-auto" id="dateFromGroup" style="display:none">
                    <label class="form-label small fw-medium">From</label>
                    <input type="date" class="form-control" id="dateFrom">
                </div>
                <div class="col-auto" id="dateToGroup" style="display:none">
                    <label class="form-label small fw-medium">To</label>
                    <input type="date" class="form-control" id="dateTo">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary rounded-pill">
                        <i class="bi bi-search me-1"></i>Load Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                <div class="text-muted small">Total Sales</div>
                <div class="fs-3 fw-bold" id="totalSales">₦0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                <div class="text-muted small">Orders</div>
                <div class="fs-3 fw-bold" id="orderCount">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                <div class="text-muted small">Average Order</div>
                <div class="fs-3 fw-bold" id="avgOrder">₦0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center p-3">
                <div class="text-muted small">Total Discounts</div>
                <div class="fs-3 fw-bold text-danger" id="totalDiscounts">₦0</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-light py-3">
                    <h6 class="fw-bold mb-0">Payment Methods</h6>
                </div>
                <div class="card-body p-4" id="paymentMethods">
                    <div class="text-center text-muted py-3">No data</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-light py-3">
                    <h6 class="fw-bold mb-0">Hourly Breakdown</h6>
                </div>
                <div class="card-body p-4" id="hourlyBreakdown">
                    <div class="text-center text-muted py-3">No data</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCustom() {
    const show = document.getElementById('period').value === 'custom';
    document.getElementById('dateFromGroup').style.display = show ? 'block' : 'none';
    document.getElementById('dateToGroup').style.display = show ? 'block' : 'none';
}

document.getElementById('reportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const period = document.getElementById('period').value;
    const params = new URLSearchParams({ period });
    if (period === 'custom') {
        params.set('date_from', document.getElementById('dateFrom').value);
        params.set('date_to', document.getElementById('dateTo').value);
    }

    try {
        const res = await fetch('{{ route("restaurant.admin.reports.sales.data") }}?' + params);
        const data = await res.json();
        if (!data.success) return;

        document.getElementById('totalSales').textContent = '₦' + Number(data.summary.total_sales).toLocaleString();
        document.getElementById('orderCount').textContent = data.summary.order_count;
        document.getElementById('avgOrder').textContent = '₦' + Number(data.summary.average_order).toLocaleString();
        document.getElementById('totalDiscounts').textContent = '-₦' + Number(data.summary.total_discounts).toLocaleString();

        let pmHtml = '';
        const methods = { cash: 'Cash', card: 'Card', mobile_money: 'Mobile Money', transfer: 'Transfer' };
        for (const [key, val] of Object.entries(data.payment_methods)) {
            pmHtml += `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div><strong>${methods[key] || key}</strong><br><small class="text-muted">${val.count} payments</small></div>
                    <div class="fw-bold">₦${Number(val.total).toLocaleString()}</div>
                </div>`;
        }
        document.getElementById('paymentMethods').innerHTML = pmHtml || '<div class="text-center text-muted py-3">No payment data</div>';

        let hrHtml = `<table class="table table-sm mb-0"><thead><tr><th>Hour</th><th class="text-end">Orders</th><th class="text-end">Sales</th></tr></thead><tbody>`;
        data.hourly.forEach(h => {
            hrHtml += `<tr><td>${h.hour}</td><td class="text-end">${h.count}</td><td class="text-end">₦${Number(h.total).toLocaleString()}</td></tr>`;
        });
        hrHtml += '</tbody></table>';
        document.getElementById('hourlyBreakdown').innerHTML = hrHtml || '<div class="text-center text-muted py-3">No hourly data</div>';
    } catch (e) {
        console.error('Report error', e);
    }
});

document.getElementById('reportForm').dispatchEvent(new Event('submit'));

document.getElementById('exportCsv').addEventListener('click', function(e) {
    e.preventDefault();
    const period = document.getElementById('period').value;
    const params = new URLSearchParams({ period });
    if (period === 'custom') {
        params.set('date_from', document.getElementById('dateFrom').value);
        params.set('date_to', document.getElementById('dateTo').value);
    }
    window.location.href = '{{ route("restaurant.admin.reports.sales.export") }}?' + params;
});
</script>
@endpush
@endSection
