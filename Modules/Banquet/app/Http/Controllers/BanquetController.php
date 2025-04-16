<?php

namespace Modules\Banquet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetOrderDay;
use Modules\Banquet\Models\BanquetOrderMenuItem;
use Modules\Banquet\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;


class BanquetController extends Controller
{
    /**
     * Display a listing of the banquet orders.
     */
    public function index()
    {
        $orders = BanquetOrder::with(['customer', 'eventDays'])->latest()->paginate(10);
        $statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
        return view('banquet::index', compact('orders', 'statuses'));
    }

    /**
     * Show the form for creating a new banquet order.
     */
    public function create()
    {
        $customers = Customer::all(['id', 'name', 'email', 'phone', 'organization']);
        $statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
        return view('banquet::create', compact('customers', 'statuses'));
    }
    /**
     * Display the specified banquet order.
     */
    public function show($order_id)
    {
        $order = BanquetOrder::with(['customer', 'eventDays.menuItems'])
            ->where('order_id', $order_id)
            ->firstOrFail();
        $customers = Customer::all(['id', 'name', 'email', 'phone', 'organization']);
        return view('banquet::show', compact('order', 'customers'));
    }

    /**
     * Store a new banquet order (customer details only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'preparation_date' => 'required|date',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_phone' => 'required|string|max:20',
            'contact_person_email' => 'required|email|max:255',
            'department' => 'nullable|string|max:255',
            'referred_by' => 'nullable|string|max:255',
            'contact_person_name_ii' => 'nullable|string|max:255',
            'contact_person_phone_ii' => 'nullable|string|max:20',
            'contact_person_email_ii' => 'nullable|email|max:255',
            'expenses' => 'nullable|numeric|min:0',
            'organization' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'hall_rental_fees' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                // Handle customer
                if (isset($validated['customer_id'])) {
                    $customer = Customer::findOrFail($validated['customer_id']);
                } else {
                    $customer = Customer::where('email', $validated['contact_person_email'])->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name' => $validated['contact_person_name'],
                            'email' => $validated['contact_person_email'],
                            'phone' => $validated['contact_person_phone'],
                            'organization' => $validated['organization'] ?? null,
                        ]);
                    } else {
                        if (isset($validated['organization'])) {
                            $customer->update(['organization' => $validated['organization']]);
                        }
                    }
                }

                // Calculate initial total_revenue and profit_margin
                $totalRevenue = $validated['hall_rental_fees'] ?? 0;
                $expenses = $validated['expenses'] ?? 0;
                $profitMargin = $this->calculateProfitMargin($totalRevenue, $expenses);

                // Create order
                $order = BanquetOrder::create([
                    'order_id' => $this->generateOrderId(),
                    'preparation_date' => $validated['preparation_date'],
                    'customer_id' => $customer->id,
                    'contact_person_name' => $validated['contact_person_name'],
                    'department' => $validated['department'],
                    'contact_person_phone' => $validated['contact_person_phone'],
                    'contact_person_email' => $validated['contact_person_email'],
                    'referred_by' => $validated['referred_by'],
                    'contact_person_name_ii' => $validated['contact_person_name_ii'],
                    'contact_person_phone_ii' => $validated['contact_person_phone_ii'],
                    'contact_person_email_ii' => $validated['contact_person_email_ii'],
                    'hall_rental_fees' => $validated['hall_rental_fees'] ?? 0,
                    'status' => 'Pending',
                    'total_revenue' => $totalRevenue,
                    'expenses' => $expenses,
                    'profit_margin' => $profitMargin,
                ]);

                return redirect()->route('banquet.orders.add-day', $order->order_id)
                    ->with('success', 'Order created! Now add event days.');
            });
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }
    /**
     * Update an existing banquet order.
     */
    public function update(Request $request, $order_id)
    {
        $request->validate([
            'contact_person_name' => 'required|string|max:255',
            'contact_person_phone' => 'required|string|max:20',
            'contact_person_email' => 'required|email|max:255',
            'department' => 'nullable|string|max:255',
            'referred_by' => 'nullable|string|max:255',
            'contact_person_name_ii' => 'nullable|string|max:255',
            'contact_person_phone_ii' => 'nullable|string|max:20',
            'contact_person_email_ii' => 'nullable|email|max:255',
            'expenses' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Completed',
            'organization' => 'nullable|string|max:255',
            'hall_rental_fees' => 'nullable|numeric|min:0',
        ]);

        try {
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();

            return DB::transaction(function () use ($request, $order) {
                // Update customer
                if ($order->customer) {
                    $order->customer->update([
                        'name' => $request->contact_person_name,
                        'phone' => $request->contact_person_phone,
                        'email' => $request->contact_person_email,
                        'organization' => $request->input('organization'),
                    ]);
                } else {
                    Log::warning("No customer associated with order ID: {$order->order_id}. Creating new customer.");
                    $customer = Customer::create([
                        'name' => $request->contact_person_name,
                        'email' => $request->contact_person_email,
                        'phone' => $request->contact_person_phone,
                        'organization' => $request->input('organization'),
                    ]);
                    $order->customer_id = $customer->id;
                }

                // Calculate total revenue and profit margin
                $menuRevenue = $order->eventDays->flatMap->menuItems->sum('total_price') ?? 0;
                $hallFees = $request->hall_rental_fees ?? 0;
                $totalRevenue = $menuRevenue + $hallFees;
                $expenses = $request->expenses ?? 0;
                $profitMargin = $this->calculateProfitMargin($totalRevenue, $expenses);

                // Update order
                $order->update([
                    'contact_person_name' => $request->contact_person_name,
                    'contact_person_phone' => $request->contact_person_phone,
                    'contact_person_email' => $request->contact_person_email,
                    'department' => $request->department,
                    'referred_by' => $request->referred_by,
                    'contact_person_name_ii' => $request->contact_person_name_ii,
                    'contact_person_phone_ii' => $request->contact_person_phone_ii,
                    'contact_person_email_ii' => $request->contact_person_email_ii,
                    'expenses' => $expenses,
                    'hall_rental_fees' => $hallFees,
                    'status' => $request->status,
                    'total_revenue' => $totalRevenue,
                    'profit_margin' => $profitMargin,
                ]);

                return redirect()->route('banquet.orders.show', $order->order_id)
                    ->with('success', 'Order updated successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
    /**
     * Show the form to add an event day to an existing order.
     */
    public function addDayForm($order_id)
    {
        $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
        $eventStatuses = ['Pending', 'Confirmed', 'Cancelled'];
        $eventTypes = ['Wedding', 'Conference', 'Meeting', 'Banquet', 'Other'];
        $setupStyles = ['Theater Style', 'Classroom Style', 'Boardroom Style', 'U-Shape', 'Banquet Style'];
        $location = ['Admawa Hall', 'Kano Hall', 'Admawa Hall + Kano Hall', 'Board Room', 'Pent House', 'Restaurant', 'Pool Party'];
        return view('banquet::add-day', compact('order', 'eventStatuses', 'eventTypes', 'setupStyles', 'location'));
    }

    /**
     * Store a new event day for an existing order.
     */
    public function storeDay(Request $request, $order_id)
    {
        $request->validate([
            'event_date' => 'required|date',
            'event_description' => 'nullable|string',
            'guest_count' => 'required|integer|min:1',
            'event_status' => 'required|in:Pending,Confirmed,Cancelled',
            'event_type' => 'required|in:Wedding,Conference,Meeting,Banquet,Other',
            'room' => 'required|string|max:255',
            'setup_style' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $day = $order->eventDays()->create([
                'event_date' => $request->event_date,
                'event_description' => $request->event_description,
                'guest_count' => $request->guest_count,
                'event_status' => $request->event_status,
                'event_type' => $request->event_type,
                'room' => $request->room,
                'setup_style' => $request->setup_style,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $this->calculateDuration($request->start_time, $request->end_time),
            ]);

            return redirect()->route('banquet.orders.add-menu-item', [$order->order_id, $day->id])
                ->with('success', 'Event day added! Now add menu items.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to add event day: ' . $e->getMessage());
        }
    }

    /**
     * Show the form to add a menu item to an existing event day.
     */
    public function addMenuItemForm($order_id, $day_id)
    {
        $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
        $day = BanquetOrderDay::findOrFail($day_id);
        $mealTypes = ['Breakfast', 'Lunch', 'Dinner', 'Snack']; // Example options
        return view('banquet::add-menu-item', compact('order', 'day', 'mealTypes'));
    }

    /**
     * Store a new menu item for an existing event day.
     */
    public function storeMenuItem(Request $request, $order_id, $day_id)
    {
        $request->validate([
            'meal_type' => 'required|string|max:255',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'dietary_restrictions' => 'nullable|array',
            'dietary_restrictions.*' => 'string|max:255',
        ]);

        try {
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $day = BanquetOrderDay::findOrFail($day_id);

            $totalPrice = $request->quantity * $request->unit_price;
            $day->menuItems()->create([
                'meal_type' => $request->meal_type,
                'menu_items' => json_encode($request->menu_items),
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $totalPrice,
                'dietary_restrictions' => json_encode($request->dietary_restrictions ?? []),
            ]);

            // Update total revenue and profit margin
            $totalRevenue = ($order->total_revenue ?? 0) + $totalPrice;
            $profitMargin = $this->calculateProfitMargin($totalRevenue, $order->expenses);

            $order->total_revenue = $totalRevenue;
            $order->profit_margin = $profitMargin;
            $order->save();

            return redirect()->route('banquet.orders.add-menu-item', [$order->order_id, $day->id])
                ->with('success', 'Menu item added! Add another or go back to Event List.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to add menu item: ' . $e->getMessage());
        }
    }
    /**
     * Update an existing menu item.
     */
    public function updateMenuItem(Request $request, $order_id, $day_id, $menu_item_id)
    {
        $request->validate([
            'meal_type' => 'required|string|max:255',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'dietary_restrictions' => 'nullable|array',
            'dietary_restrictions.*' => 'string|max:255',
        ]);

        try {
            $menuItem = BanquetOrderMenuItem::findOrFail($menu_item_id);
            $oldTotalPrice = $menuItem->total_price;
            $newTotalPrice = $request->quantity * $request->unit_price;

            $menuItem->update([
                'meal_type' => $request->meal_type,
                'menu_items' => json_encode($request->menu_items),
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $newTotalPrice,
                'dietary_restrictions' => json_encode($request->dietary_restrictions ?? []),
            ]);

            // Adjust order's total_revenue and profit_margin
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $totalRevenue = ($order->total_revenue - $oldTotalPrice) + $newTotalPrice;
            $profitMargin = $this->calculateProfitMargin($totalRevenue, $order->expenses);

            $order->total_revenue = $totalRevenue;
            $order->profit_margin = $profitMargin;
            $order->save();

            return redirect()->route('banquet.orders.show', $order_id)
                ->with('success', 'Menu item updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
    }
    public function deleteMenuItem($order_id, $day_id, $menu_item_id)
    {
        $menuItem = BanquetOrderMenuItem::findOrFail($menu_item_id);
        $totalPrice = $menuItem->total_price;
        $menuItem->delete();

        $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
        $totalRevenue = $order->total_revenue - $totalPrice;
        $profitMargin = $this->calculateProfitMargin($totalRevenue, $order->expenses);

        $order->total_revenue = $totalRevenue;
        $order->profit_margin = $profitMargin;
        $order->save();

        return redirect()->route('banquet.orders.show', $order_id)
            ->with('success', 'Menu item deleted.');
    }
    /**
     * Search customers based on query string.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->input('query');
        $customers = Customer::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->get(['id', 'name', 'email', 'phone', 'organization']);
        return response()->json($customers);
    }
    public function edit($order_id)
    {
        $order = BanquetOrder::with(['customer', 'eventDays'])->where('order_id', $order_id)->firstOrFail();
        $customers = Customer::all(['id', 'name', 'email', 'phone', 'organization']);
        $statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
        return view('banquet::edit', compact('order', 'customers', 'statuses'));
    }
    /**
     * Delete banquet Order
     */
    public function destroy($order_id)
    {
        try {
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $order->eventDays()->delete(); // Delete related event days (if relation exists)
            $order->delete();
            return response()->json(['success' => true]);
            return redirect()->route('banquet.orders.index', $order_id)
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Show the form for editing an existing event day.
     */
    public function editDay($order_id, $day_id)
    {
        $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
        $day = BanquetOrderDay::findOrFail($day_id);
        $eventStatuses = ['Pending', 'Confirmed', 'Cancelled'];
        $eventTypes = ['Wedding', 'Conference', 'Meeting', 'Banquet', 'Other'];
        $setupStyles = ['Theater Style', 'Classroom Style', 'Boardroom Style', 'U-Shape', 'Banquet Style'];
        $locations = ['Admawa Hall', 'Kano Hall', 'Admawa Hall + Kano Hall', 'Board Room', 'Pent House', 'Restaurant', 'Pool Party'];
        return view('banquet::edit-day', compact('order', 'day', 'eventStatuses', 'eventTypes', 'setupStyles', 'locations'));
    }

    /**
     * Update an existing event day.
     */
    public function updateDay(Request $request, $order_id, $day_id)
    {
        $request->validate([
            'event_date' => 'required|date',
            'event_description' => 'nullable|string',
            'guest_count' => 'required|integer|min:1',
            'event_status' => 'required|in:Pending,Confirmed,Cancelled',
            'event_type' => 'required|in:Wedding,Conference,Meeting,Banquet,Other',
            'room' => 'required|string|max:255',
            'setup_style' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            $day = BanquetOrderDay::findOrFail($day_id);
            $day->update([
                'event_date' => $request->event_date,
                'event_description' => $request->event_description,
                'guest_count' => $request->guest_count,
                'event_status' => $request->event_status,
                'event_type' => $request->event_type,
                'room' => $request->room,
                'setup_style' => $request->setup_style,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $this->calculateDuration($request->start_time, $request->end_time),
            ]);

            return redirect()->route('banquet.orders.show', $order_id)
                ->with('success', 'Event day updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update event day: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an existing menu item.
     */
    public function editMenuItem($order_id, $day_id, $menu_item_id)
    {
        $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
        $day = BanquetOrderDay::findOrFail($day_id);
        $menuItem = BanquetOrderMenuItem::findOrFail($menu_item_id);
        $mealTypes = ['Breakfast', 'Lunch', 'Dinner', 'Snack'];
        return view('banquet::edit-menu-item', compact('order', 'day', 'menuItem', 'mealTypes'));
    }



    /**
     * Update the status of an event day quickly.
     */
    public function updateDayStatus(Request $request, $order_id, $day_id)
    {
        $request->validate([
            'event_status' => 'required|in:Pending,Confirmed,Cancelled',
        ]);

        try {
            $day = BanquetOrderDay::findOrFail($day_id);
            $day->update([
                'event_status' => $request->event_status,
            ]);

            return back()->with('success', 'Event day status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update event day status: ' . $e->getMessage());
        }
    }
    /**
     * Generate a unique order ID with a dash instead of a slash.
     */
    private function generateOrderId()
    {
        $latestOrder = BanquetOrder::latest('id')->first();
        $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
        return sprintf("%04d-%d", $nextId, now()->year); // Changed from / to -
    }

    /**
     * Calculate duration in minutes between start and end times.
     */
    private function calculateDuration($startTime, $endTime)
    {
        if (!$startTime || !$endTime) return null;
        $start = \DateTime::createFromFormat('H:i', $startTime);
        $end = \DateTime::createFromFormat('H:i', $endTime);
        return $end->diff($start)->i + ($end->diff($start)->h * 60);
    }
    /**
     * Generate pdf function sheet.
     */
    public function generatePdf($order_id)
    {
        // Fetch the order with related data
        $order = BanquetOrder::with(['customer', 'eventDays.menuItems'])
            ->where('order_id', $order_id)
            ->firstOrFail();

        // Generate the PDF from a Blade view
        $pdf = Pdf::loadView('banquet::pdf.function-sheet', compact('order'));
        $pdf->setOptions(['defaultFont' => 'Brown Sugar']);
        // Stream the PDF to the browser
        return $pdf->stream('function-sheet-' . $order->order_id . '.pdf');
    }
    /**
     * Display the form for selecting the report date range.
     */
    public function eventReportForm()
    {
        return view('banquet::reports.event-report-form');
    }

    /**
     * Generate a PDF report of events based on the selected date range.
     */
    // public function generateEventReport(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');

    //     // Fetch orders with event days and customer within the date range
    //     $orders = BanquetOrder::with(['customer', 'eventDays'])
    //         ->whereBetween('preparation_date', [$startDate, $endDate])
    //         ->get();

    //     // Prepare report data
    //     $reportData = [];
    //     $totalEvents = 0;
    //     $statusCounts = ['Confirmed' => 0, 'Cancelled' => 0, 'Completed' => 0, 'Pending' => 0];
    //     $locationCounts = [];

    //     foreach ($orders as $order) {
    //         if ($order->eventDays->isEmpty()) {
    //             $eventDateRange = 'No event days';
    //             $eventType = 'N/A';
    //             $location = 'N/A';
    //             $guestCount = 0;
    //         } else {
    //             $totalEvents += $order->eventDays->count();
    //             $dates = $order->eventDays->sortBy('event_date');
    //             $eventDateRange = $dates->first()->event_date->format('M d, Y') . ' - ' .
    //                 $dates->last()->event_date->format('M d, Y');
    //             $eventType = $order->eventDays->first()->event_type;
    //             $location = $order->eventDays->first()->room;
    //             $guestCount = $order->eventDays->sum('guest_count');

    //             // Count statuses (assuming order status applies to all event days)
    //             if (isset($statusCounts[$order->status])) {
    //                 $statusCounts[$order->status]++;
    //             }

    //             // Count locations
    //             $locationCounts[$location] = ($locationCounts[$location] ?? 0) + 1;
    //         }

    //         $reportData[] = [
    //             'event_date_range' => $eventDateRange,
    //             'customer_name' => $order->customer ? $order->customer->name : 'N/A',
    //             'event_type' => $eventType,
    //             'location' => $location,
    //             'guest_count' => $guestCount,
    //         ];
    //     }

    //     // Sort report data by event_date_range
    //     usort($reportData, function ($a, $b) {
    //         return strcmp($a['event_date_range'], $b['event_date_range']);
    //     });

    //     // Determine most used location
    //     $mostUsedLocation = !empty($locationCounts) ? array_search(max($locationCounts), $locationCounts) : 'N/A';

    //     // Prepare summary data
    //     $summary = [
    //         'total_events' => $totalEvents,
    //         'confirmed' => $statusCounts['Confirmed'],
    //         'cancelled' => $statusCounts['Cancelled'],
    //         'completed' => $statusCounts['Completed'],
    //         'pending' => $statusCounts['Pending'],
    //         'most_used_location' => $mostUsedLocation,
    //     ];

    //     // Generate PDF
    //     $pdf = Pdf::loadView('banquet::reports.event-report', [
    //         'reportData' => $reportData,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'summary' => $summary,
    //     ]);

    //     return $pdf->stream("event-report-{$startDate}-to-{$endDate}.pdf");
    // }

    /**
     * Display an HTML report of events based on the selected date range.
     */
    public function generateEventReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch orders with event days and customer within the date range
        $orders = BanquetOrder::with(['customer', 'eventDays'])
            ->whereBetween('preparation_date', [$startDate, $endDate])
            ->get();

        // Prepare report data
        $reportData = [];
        $statusCounts = ['Confirmed' => 0, 'Cancelled' => 0, 'Completed' => 0, 'Pending' => 0];
        $locationCounts = [];

        // Count total orders (events)
        $totalEvents = $orders->count();

        foreach ($orders as $order) {
            if ($order->eventDays->isEmpty()) {
                $eventDateRange = 'No event days';
                $eventType = 'N/A';
                $location = 'N/A';
                $guestCount = 0;
            } else {
                $dates = $order->eventDays->sortBy('event_date');
                $eventDateRange = $dates->first()->event_date->format('M d, Y') . ' - ' .
                    $dates->last()->event_date->format('M d, Y');
                $eventType = $order->eventDays->first()->event_type;
                $location = $order->eventDays->first()->room;
                $guestCount = $order->eventDays->sum('guest_count');

                // Count locations (still based on event days)
                $locationCounts[$location] = ($locationCounts[$location] ?? 0) + 1;
            }

            // Count statuses based on orders
            if (isset($statusCounts[$order->status])) {
                $statusCounts[$order->status]++;
            }

            $reportData[] = [
                'event_date_range' => $eventDateRange,
                'customer_name' => $order->customer ? $order->customer->name : 'N/A',
                'event_type' => $eventType,
                'location' => $location,
                'guest_count' => $guestCount,
                'status' => $order->status,
            ];
        }

        // Sort report data by event_date_range
        usort($reportData, function ($a, $b) {
            return strcmp($a['event_date_range'], $b['event_date_range']);
        });

        // Determine most used location
        $mostUsedLocation = !empty($locationCounts) ? array_search(max($locationCounts), $locationCounts) : 'N/A';

        // Prepare summary data
        $summary = [
            'total_events' => $totalEvents,
            'confirmed' => $statusCounts['Confirmed'],
            'cancelled' => $statusCounts['Cancelled'],
            'completed' => $statusCounts['Completed'],
            'pending' => $statusCounts['Pending'],
            'most_used_location' => $mostUsedLocation,
        ];

        // Return HTML view instead of PDF
        return view('banquet::reports.event-report', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
        ]);
    }
    /**
     * Display datatable for index.
     */
    public function datatable(Request $request)
    {
        $orders = BanquetOrder::with(['customer', 'eventDays.menuItems'])
            ->select('id', 'order_id', 'expenses', 'hall_rental_fees', 'status', 'customer_id', 'profit_margin') // Add profit_margin
            ->latest();

        return DataTables::of($orders)
            ->addColumn('customer', function ($order) {
                return [
                    'name' => $order->customer->name ?? null,
                    'contact_person_name' => $order->contact_person_name
                ];
            })
            ->addColumn('organization', function ($order) {
                return $order->customer->organization ?? 'N/A'; // New organization column
            })
            ->addColumn('event_dates', function ($order) {
                if ($order->eventDays->isEmpty()) return 'No event days';
                $dates = $order->eventDays->sortBy('event_date');
                return $dates->first()->event_date->format('M d, Y') . ' - ' .
                    $dates->last()->event_date->format('M d, Y');
            })
            ->addColumn('total_guests', function ($order) {
                return $order->eventDays->max('guest_count') ?? 0;
            })->addColumn('total_revenue', function ($order) {
                $menuRevenue = $order->eventDays->flatMap->menuItems->sum('total_price') ?? 0;
                $hallFees = $order->hall_rental_fees ?? 0;
                return $menuRevenue + $hallFees;
            })
            ->addColumn('profit_margin', function ($order) {
                return $order->profit_margin !== null ? number_format($order->profit_margin, 2) . '%' : 'N/A';
            })
            ->addColumn('actions', function ($order) {
                return [
                    'order_id' => $order->order_id,
                    'view' => route('banquet.orders.show', $order->order_id),
                    'edit' => route('banquet.orders.edit', $order->order_id),
                    'pdf' => route('banquet.orders.pdf', $order->order_id)
                ];
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', $keyword);
            })
            ->make(true);
    }
    /**
     * Helper Functions to Calculate Profit margin
     */
    private function calculateProfitMargin($totalRevenue, $expenses)
    {
        if ($totalRevenue <= 0) {
            return null; // Avoid division by zero
        }
        $profit = $totalRevenue - ($expenses ?? 0);
        return ($profit / $totalRevenue) * 100; // Percentage
    }
}
