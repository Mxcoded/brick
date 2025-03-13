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
            'organization' => 'nullable|string|max:255', // Optional for customer
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                // Check if a customer with this email already exists
                $customer = Customer::where('email', $validated['contact_person_email'])->first();

                if (!$customer) {
                    // Create a new customer if none exists
                    $customer = Customer::create([
                        'name' => $validated['contact_person_name'],
                        'email' => $validated['contact_person_email'],
                        'phone' => $validated['contact_person_phone'],
                        'organization' => $validated['organization'] ?? null,
                    ]);
                } else {
                    // Optionally update the existing customer's details if desired
                    $customer->update([
                        'name' => $validated['contact_person_name'],
                        'phone' => $validated['contact_person_phone'],
                        'organization' => $validated['organization'] ?? $customer->organization,
                    ]);
                }

                // Create the banquet order with the customer's ID
                return BanquetOrder::create([
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
                    'status' => 'Pending',
                    'total_revenue' => 0,
                    'expenses' => $validated['expenses'] ?? 0,
                    'profit_margin' => $validated['expenses'] ? null : 0,
                ]);
            });

            return redirect()->route('banquet.orders.add-day', $order->order_id)
                ->with('success', 'Order created! Now add event days.');
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
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
            'menu_items' => 'required|array|min:1', // Ensure at least one item
            'menu_items.*' => 'string|max:255', // Each item is a string
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

            // Update total revenue
            $order->total_revenue += $totalPrice;
            $order->save();

            return redirect()->route('banquet.orders.add-menu-item', [$order->order_id, $day->id])
                ->with('success', 'Menu item added! Add another or go back to Event List.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to add menu item: ' . $e->getMessage());
        }
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
     * Update an existing banquet order.
     */
    public function update(Request $request, $order_id)
    {
        $request->validate([
            // 'preparation_date' => 'required|date',
            // 'customer_id' => 'nullable|exists:customers,id',
            'contact_person_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_person_phone' => 'required|string|max:20',
            'contact_person_email' => 'required|email|max:255',
            'referred_by' => 'nullable|string|max:255',
            'contact_person_name_ii' => 'nullable|string|max:255',
            'contact_person_phone_ii' => 'nullable|string|max:20',
            'contact_person_email_ii' => 'nullable|email|max:255',
            'expenses' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Completed',
        ]);

        try {
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $order->update([
                // 'preparation_date' => $request->preparation_date,
                // 'customer_id' => $request->customer_id,
                'contact_person_name' => $request->contact_person_name,
                'department' => $request->department,
                'contact_person_phone' => $request->contact_person_phone,
                'contact_person_email' => $request->contact_person_email,
                'referred_by' => $request->referred_by,
                'contact_person_name_ii' => $request->contact_person_name_ii,
                'contact_person_phone_ii' => $request->contact_person_phone_ii,
                'contact_person_email_ii' => $request->contact_person_email_ii,
                'expenses' => $request->expenses ?? 0,
                'status' => $request->status,
                // total_revenue is managed via menu items, not updated here
            ]);

            return redirect()->route('banquet.orders.show', $order->order_id)
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
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

            // Adjust the order's total_revenue
            $order = BanquetOrder::where('order_id', $order_id)->firstOrFail();
            $order->total_revenue = $order->total_revenue - $oldTotalPrice + $newTotalPrice;
            $order->save();

            return redirect()->route('banquet.orders.show', $order_id)
                ->with('success', 'Menu item updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
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

    public function datatable(Request $request)
    {
        $orders = BanquetOrder::with(['customer', 'eventDays'])
            ->select('id', 'order_id', 'expenses', 'total_revenue', 'status', 'customer_id')
            ->latest();

        return DataTables::of($orders)
            ->addColumn('customer', function ($order) {
                return [
                    'name' => $order->customer->name ?? null,
                    'contact_person_name' => $order->contact_person_name
                ];
            })
            ->addColumn('event_dates', function ($order) {
                if ($order->eventDays->isEmpty()) return 'No event days';
                $dates = $order->eventDays->sortBy('event_date');
                return $dates->first()->event_date->format('M d, Y') . ' - ' .
                    $dates->last()->event_date->format('M d, Y');
            })
            ->addColumn('total_guests', function ($order) {
                return $order->eventDays->max('guest_count') ?? 0;
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
            ->make(true); // Removed rawColumns since no HTML is rendered server-side
    }
}
