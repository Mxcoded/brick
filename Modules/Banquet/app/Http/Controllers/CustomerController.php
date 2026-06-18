<?php

namespace Modules\Banquet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Banquet\Models\Customer;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'organizations' => Customer::whereNotNull('organization')->where('organization', '!=', '')->distinct()->count('organization'),
            'repeat_customers' => Customer::has('banquetOrders', '>', 1)->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('banquet::customers.index', compact('stats'));
    }

    /**
     * Get customers for DataTables.
     */
    public function datatable(Request $request)
    {
        $query = Customer::withCount('banquetOrders')
            ->withSum('banquetOrders', 'total_revenue');

        // Apply card filters
        $filter = $request->input('filter', 'all');
        switch ($filter) {
            case 'organizations':
                $query->whereNotNull('organization')->where('organization', '!=', '');
                break;
            case 'repeat':
                $query->has('banquetOrders', '>', 1);
                break;
            case 'new_this_month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
        }

        $query->latest();

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($search = $request->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('organization', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('total_orders', function ($customer) {
                return $customer->banquet_orders_count;
            })
            ->addColumn('total_spent', function ($customer) {
                return '₦'.number_format($customer->banquet_orders_sum_total_revenue ?? 0);
            })
            ->addColumn('organization_display', function ($customer) {
                return $customer->organization ?? '<span class="text-muted">Private</span>';
            })
            ->addColumn('created_at_formatted', function ($customer) {
                return $customer->created_at->format('M d, Y');
            })
            ->addColumn('actions', function ($customer) {
                $showUrl = route('banquet.customers.show', $customer->id);
                $editUrl = route('banquet.customers.edit', $customer->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$showUrl.'" class="btn btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['organization_display', 'actions'])
            ->make(true);
    }

    /**
     * Export customers to Excel/CSV.
     */
    public function export(Request $request)
    {
        $filter = $request->input('filter', 'all');

        $query = Customer::withCount('banquetOrders')
            ->withSum('banquetOrders', 'total_revenue');

        // Apply filters
        switch ($filter) {
            case 'organizations':
                $query->whereNotNull('organization')->where('organization', '!=', '');
                $filterLabel = 'Organizations';
                break;
            case 'repeat':
                $query->has('banquetOrders', '>', 1);
                $filterLabel = 'Repeat Customers';
                break;
            case 'new_this_month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                $filterLabel = 'New This Month';
                break;
            default:
                $filterLabel = 'All Customers';
        }

        $customers = $query->latest()->get();

        $filename = 'banquet-customers-'.($filter !== 'all' ? $filter.'-' : '').now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($customers, $filterLabel) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header row
            fputcsv($file, ['Banquet Customer Export - '.$filterLabel]);
            fputcsv($file, ['Generated: '.now()->format('F d, Y h:i A')]);
            fputcsv($file, []);

            // Column headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Organization',
                'Total Orders',
                'Total Spent (NGN)',
                'Customer Since',
            ]);

            // Data rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->organization ?? 'Private',
                    $customer->banquet_orders_count,
                    number_format($customer->banquet_orders_sum_total_revenue ?? 0, 2),
                    $customer->created_at->format('M d, Y'),
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Customers', $customers->count()]);
            fputcsv($file, ['Total Revenue', 'NGN '.number_format($customers->sum('banquet_orders_sum_total_revenue'), 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('banquet::customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'organization' => 'nullable|string|max:255',
        ]);

        Customer::create($validated);

        return redirect()->route('banquet.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = Customer::with(['banquetOrders' => function ($query) {
            $query->latest('preparation_date')->limit(10);
        }])->findOrFail($id);

        $stats = [
            'total_orders' => $customer->banquetOrders()->count(),
            'total_spent' => $customer->banquetOrders()->sum('total_revenue'),
            'last_order' => $customer->banquetOrders()->latest('preparation_date')->first()?->preparation_date,
            'avg_order_value' => $customer->banquetOrders()->avg('total_revenue') ?? 0,
        ];

        return view('banquet::customers.show', compact('customer', 'stats'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('banquet::customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$id,
            'phone' => 'required|string|max:20',
            'organization' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('banquet.customers.show', $id)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if customer has orders
        if ($customer->banquetOrders()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing orders.');
        }

        $customer->delete();

        return redirect()->route('banquet.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
