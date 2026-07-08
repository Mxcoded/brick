<?php

namespace Modules\Banquet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Banquet\Models\BanquetEnquiry;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\Customer;
use Yajra\DataTables\DataTables;

class EnquiryController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => BanquetEnquiry::count(),
            'pending' => BanquetEnquiry::where('status', 'Pending')->count(),
            'contacted' => BanquetEnquiry::where('status', 'Contacted')->count(),
            'converted' => BanquetEnquiry::where('status', 'Converted')->count(),
            'new_this_month' => BanquetEnquiry::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('banquet::enquiries.index', compact('stats'));
    }

    public function datatable(Request $request)
    {
        $query = BanquetEnquiry::query();

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($search = $request->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('event_date_formatted', function ($enquiry) {
                return $enquiry->event_date->format('M d, Y');
            })
            ->addColumn('status_badge', function ($enquiry) {
                $colors = ['Pending' => 'warning', 'Contacted' => 'info', 'Converted' => 'success', 'Closed' => 'secondary'];
                $color = $colors[$enquiry->status] ?? 'secondary';

                return '<span class="badge bg-'.$color.' rounded-pill px-3">'.$enquiry->status.'</span>';
            })
            ->addColumn('created_at_formatted', function ($enquiry) {
                return $enquiry->created_at->format('M d, Y h:i A');
            })
            ->addColumn('actions', function ($enquiry) {
                return view('banquet::enquiries.partials.actions', compact('enquiry'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show($id)
    {
        $enquiry = BanquetEnquiry::with('convertedOrder')->findOrFail($id);

        return view('banquet::enquiries.show', compact('enquiry'));
    }

    public function updateStatus(Request $request, $id)
    {
        $enquiry = BanquetEnquiry::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Pending,Contacted,Converted,Closed',
        ]);

        $enquiry->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => $enquiry->status, 'message' => 'Status updated successfully.']);
        }

        return redirect()->route('banquet.enquiries.show', $id)
            ->with('success', 'Enquiry status updated to '.$validated['status'].'.');
    }

    public function updateNotes(Request $request, $id)
    {
        $enquiry = BanquetEnquiry::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $enquiry->update($validated);

        return redirect()->route('banquet.enquiries.show', $id)
            ->with('success', 'Notes updated successfully.');
    }

    public function convertToOrder(Request $request, $id)
    {
        $enquiry = BanquetEnquiry::with('convertedOrder')->findOrFail($id);

        if ($enquiry->convertedOrder) {
            return redirect()->route('banquet.enquiries.show', $id)
                ->with('error', 'This enquiry has already been converted.');
        }

        $validated = $request->validate([
            'preparation_date' => 'required|date',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Completed',
            'hall_rental_fees' => 'nullable|numeric|min:0',
            'department' => 'nullable|string|max:255',
        ]);

        try {
            return DB::transaction(function () use ($enquiry, $validated) {
                $customer = Customer::firstOrCreate(
                    ['email' => $enquiry->email],
                    [
                        'name' => $enquiry->name,
                        'phone' => $enquiry->phone,
                        'organization' => $enquiry->company,
                    ]
                );

                $order = BanquetOrder::create([
                    'order_id' => $this->generateOrderId(),
                    'preparation_date' => $validated['preparation_date'],
                    'customer_id' => $customer->id,
                    'contact_person_name' => $enquiry->name,
                    'contact_person_phone' => $enquiry->phone,
                    'contact_person_email' => $enquiry->email,
                    'department' => $validated['department'],
                    'hall_rental_fees' => $validated['hall_rental_fees'] ?? 0,
                    'total_revenue' => $validated['hall_rental_fees'] ?? 0,
                    'status' => $validated['status'],
                ]);

                $enquiry->update([
                    'converted_to_order_id' => $order->id,
                    'status' => 'Converted',
                ]);

                return redirect()->route('banquet.orders.add-day', $order->order_id)
                    ->with('success', 'Enquiry converted to order #'.$order->order_id.'. Please add event days.');
            });
        } catch (\Exception $e) {
            Log::error('Enquiry conversion failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Failed to convert enquiry: '.$e->getMessage());
        }
    }

    private function generateOrderId()
    {
        $latestOrder = BanquetOrder::latest('id')->first();
        $nextId = $latestOrder ? $latestOrder->id + 1 : 1;

        return sprintf('%04d-%d', $nextId, now()->year);
    }

    public function destroy($id)
    {
        $enquiry = BanquetEnquiry::findOrFail($id);
        $enquiry->delete();

        return redirect()->route('banquet.enquiries.index')
            ->with('success', 'Enquiry deleted successfully.');
    }
}
