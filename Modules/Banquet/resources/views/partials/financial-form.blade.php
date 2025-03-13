<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="total_revenue">Total Revenue</label>
            <input type="number" step="0.01" name="total_revenue" id="total_revenue" class="form-control" 
                   value="{{ old('total_revenue', $order->total_revenue ?? 0.00) }}" required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="expenses">Expenses</label>
            <input type="number" step="0.01" name="expenses" id="expenses" class="form-control" 
                   value="{{ old('expenses', $order->expenses ?? 0.00) }}" required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="profit_margin">Profit Margin</label>
            <input type="number" step="0.01" name="profit_margin" id="profit_margin" class="form-control" 
                   value="{{ old('profit_margin', $order->profit_margin ?? 0.00) }}" readonly>
        </div>
    </div>
</div>

<!-- Auto-calculate profit margin -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const revenueInput = document.getElementById('total_revenue');
        const expensesInput = document.getElementById('expenses');
        const profitInput = document.getElementById('profit_margin');

        function calculateProfit() {
            const revenue = parseFloat(revenueInput.value) || 0;
            const expenses = parseFloat(expensesInput.value) || 0;
            profitInput.value = (revenue - expenses).toFixed(2);
        }

        revenueInput.addEventListener('input', calculateProfit);
        expensesInput.addEventListener('input', calculateProfit);
    });
</script>