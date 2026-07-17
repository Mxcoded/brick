@extends('layouts.master')

@section('title', 'Print Barcode Labels')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Print Barcode Labels</h1>
            <p class="text-muted mb-0">Generate scannable barcode labels for your inventory items.</p>
        </div>
    </div>

    <div class="card shadow border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Select Items</label>
                    <select id="itemSelect" class="form-select" multiple style="height: 200px;">
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" data-sku="{{ $item->sku ?? 'N/A' }}" data-desc="{{ $item->description }}" data-price="{{ number_format($item->price, 2) }}" data-uom="{{ $item->unit_of_measurement ?? '' }}">
                                {{ $item->sku ? '['.$item->sku.'] ' : '' }}{{ $item->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Copies per Item</label>
                    <input type="number" id="copiesInput" class="form-control" value="1" min="1" max="20">
                </div>
                <div class="col-md-2">
                    <button id="generateBtn" class="btn btn-primary w-100">
                        <i class="fas fa-print me-2"></i>Generate Labels
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="labelsContainer" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><span id="labelCount"></span> label(s) generated</h5>
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print me-2"></i>Print Labels
            </button>
        </div>
        <div id="labelsGrid" class="row"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    $('#generateBtn').on('click', function () {
        const selected = $('#itemSelect').val();
        const copies = parseInt($('#copiesInput').val()) || 1;

        if (!selected || selected.length === 0) {
            alert('Please select at least one item.');
            return;
        }

        const grid = $('#labelsGrid');
        grid.empty();

        let total = 0;

        selected.forEach(function (id) {
            const opt = $('#itemSelect option[value="' + id + '"]');
            const sku = opt.data('sku');
            const desc = opt.data('desc');
            const price = opt.data('price');
            const uom = opt.data('uom');

            for (let c = 0; c < copies; c++) {
                const col = $('<div class="col-3 mb-4"></div>');
                const label = $('<div class="label-card"></div>');
                label.append('<div class="label-barcode"><svg class="barcode-svg" data-sku="' + sku + '"></svg></div>');
                label.append('<div class="label-sku">' + sku + '</div>');
                label.append('<div class="label-desc">' + desc + '</div>');
                if (uom) label.append('<div class="label-uom">' + uom + '</div>');
                label.append('<div class="label-price">₦' + price + '</div>');
                col.append(label);
                grid.append(col);
                total++;
            }
        });

        $('#labelCount').text(total);
        $('#labelsContainer').removeClass('d-none');

        setTimeout(function () {
            document.querySelectorAll('.barcode-svg').forEach(function (el) {
                try {
                    JsBarcode(el, el.dataset.sku, {
                        format: 'CODE128',
                        width: 1.5,
                        height: 35,
                        displayValue: false,
                        margin: 0,
                    });
                } catch (e) {
                    el.outerHTML = '<span class="text-muted small">Invalid SKU</span>';
                }
            });
        }, 100);
    });

    $(document).on('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p' && !$('#labelsContainer').hasClass('d-none')) {
            // allow print
        }
    });
</script>
<style>
    .label-card {
        border: 1px dashed #ccc;
        border-radius: 6px;
        padding: 10px 8px;
        text-align: center;
        background: #fff;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .label-barcode { margin-bottom: 4px; }
    .label-barcode svg { max-width: 100%; height: auto; }
    .label-sku { font-family: monospace; font-size: 11px; font-weight: bold; letter-spacing: 1px; color: #333; }
    .label-desc { font-size: 10px; color: #555; line-height: 1.2; margin: 2px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .label-uom { font-size: 9px; color: #999; }
    .label-price { font-size: 11px; font-weight: bold; color: #1a472a; }

    @media print {
        @page { margin: 6mm; }
        body * { visibility: hidden; }
        #labelsGrid, #labelsGrid * { visibility: visible; }
        #labelsGrid { display: flex !important; flex-wrap: wrap !important; position: absolute; left: 0; top: 0; }
        #labelsGrid .col-3 { width: 25%; float: left; padding: 4px; }
        .label-card { border: 1px solid #ddd !important; page-break-inside: avoid; break-inside: avoid; }
    }
</style>
@endsection
