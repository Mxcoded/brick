<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\Booking;
use Modules\Frontdeskcrm\Models\Registration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index()
    {
        $rooms = Room::latest()->paginate(10);
        return view('website::admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        // Fetch dynamic amenities from DB
        $amenities = Amenity::all();
        return view('website::admin.rooms.create', compact('amenities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id', // Validate IDs
            'video_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'status' => 'required|in:available,maintenance,booked',
            'image' => 'required|image|max:20480', // 20MB - will be compressed to <5MB
            'gallery_images.*' => 'nullable|image|max:20480'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Upload & Compress Primary Image
        if ($request->hasFile('image')) {
            $result = $this->imageService->compressAndStore(
                $request->file('image'),
                'rooms'
            );
            $validated['image_url'] = $result['url'];
        }

        $room = Room::create($validated);

        // SYNC AMENITIES (The Pivot Table Magic)
        if (!empty($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        }

        // Handle Gallery Images with Compression
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $result = $this->imageService->compressAndStore($file, 'room_gallery');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => $result['url'],
                    'path' => $result['path']
                ]);
            }
        }

        return redirect()->route('website.admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function edit($id)
    {
        $room = Room::with('images', 'amenities')->findOrFail($id);
        $amenities = Amenity::all(); // Pass all available options
        return view('website::admin.rooms.edit', compact('room', 'amenities'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name,' . $id,
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'video_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'status' => 'required|in:available,maintenance,Booked',
            'image' => 'nullable|image|max:20480', // 20MB - will be compressed to <5MB
            'gallery_images.*' => 'nullable|image|max:20480'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Upload & Compress Primary Image
        if ($request->hasFile('image')) {
            // Delete old image
            if ($room->image_url) {
                $this->imageService->deleteByUrl($room->image_url);
            }
            // Compress and store new image
            $result = $this->imageService->compressAndStore(
                $request->file('image'),
                'rooms'
            );
            $validated['image_url'] = $result['url'];
        }

        $room->update($validated);

        // SYNC AMENITIES
        if (isset($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        } else {
            $room->amenities()->detach();
        }

        // Handle Gallery Images with Compression
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $result = $this->imageService->compressAndStore($file, 'room_gallery');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => $result['url'],
                    'path' => $result['path']
                ]);
            }
        }

        // ✅ UX IMPROVEMENT: Redirect back to the edit page instead of the index
        return redirect()->back()->with('success', 'Room updated successfully.');
    }

    public function show($id)
    {
        $room = Room::with(['images', 'amenities'])->findOrFail($id);
        return view('website::admin.rooms.show', compact('room'));
    }

    public function deleteImage($id)
    {
        $image = RoomImage::findOrFail($id);
        if ($image->path) {
            $this->imageService->delete($image->path);
        }
        $image->delete();
        return back()->with('success', 'Gallery image deleted.');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        
        // Clean up primary image
        if ($room->image_url) {
            $this->imageService->deleteByUrl($room->image_url);
        }
        
        // Clean up gallery images
        foreach ($room->images as $img) {
            if ($img->path) {
                $this->imageService->delete($img->path);
            }
        }
        
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }


    /**
     * Display the Monthly Tape Chart (Calendar).
     */
    public function calendar(Request $request)
    {
        // 1. Determine Month/Year (Default to current)
        $date = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)
            : \Carbon\Carbon::now();

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $daysInMonth = $date->daysInMonth;

        // 2. Fetch Rooms with Bookings overlapping this month
        // We eagerly load bookings to avoid "N+1" query performance issues
        $rooms = \Modules\Website\Models\Room::with(['bookings' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('check_in_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('check_out_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->where('check_in_date', '<', $startOfMonth)
                                ->where('check_out_date', '>', $endOfMonth);
                        });
                });
        }])->get();

        // 3. Prepare View Data
        return view('website::admin.rooms.calendar', compact('rooms', 'date', 'daysInMonth', 'startOfMonth'));
    }
    /**
     * ✅ API 1: Live Room Rack Data (Merged & Prioritized)
     */
    public function getRoomStatus()
    {
        // 1. Get all rooms
        $rooms = Room::select('id', 'name', 'capacity', 'status')->get();
        $today = now()->format('Y-m-d');

        // 2. Fetch Active FRONTDESK Registrations (Priority 1)
        // We grab entries that are physically in-house
        $activeRegistrations = collect();
        if (class_exists(Registration::class)) {
            $activeRegistrations = Registration::whereIn('stay_status', ['checked_in', 'draft_by_guest'])
                ->get();
        }

        // 3. Fetch Active WEBSITE Bookings (Priority 2)
        $activeBookings = Booking::where('status', '!=', 'cancelled')
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->get()
            ->keyBy('room_id');

        // 4. Map Status
        $data = $rooms->map(function ($room) use ($activeRegistrations, $activeBookings) {

            // A. Maintenance Check
            if ($room->status === 'maintenance') {
                return $this->formatStatus($room, 'maintenance', 'secondary', 'Maintenance');
            }

            // B. Frontdesk Priority Check (The "Think Deep" Logic)
            // 1. Try strict ID match
            $registration = $activeRegistrations->firstWhere('room_id', $room->id);

            // 2. Fallback: If room_id is missing, match name string (Legacy Support)
            if (!$registration) {
                $registration = $activeRegistrations->firstWhere('room_allocation', $room->name);
            }

            if ($registration) {
                return $this->formatStatus(
                    $room,
                    'occupied',
                    'danger', // Red = Physically Occupied
                    $registration->full_name,
                    $registration->check_out
                );
            }

            // C. Website Booking Check
            $booking = $activeBookings->get($room->id);
            if ($booking) {
                $isCheckingOut = $booking->check_out_date === now()->format('Y-m-d');
                return $this->formatStatus(
                    $room,
                    'occupied',
                    $isCheckingOut ? 'warning' : 'primary', // Blue/Orange
                    $booking->guest_name,
                    $booking->check_out_date
                );
            }

            // D. Available
            return $this->formatStatus($room, 'available', 'success', 'Vacant');
        });

        return response()->json($data);
    }

    private function formatStatus($room, $status, $color, $guest, $checkout = null)
    {
        return [
            'id' => $room->id,
            'name' => $room->name,
            'status' => $status,
            'color' => $color,
            'guest' => $guest,
            'checkout' => $checkout ? \Carbon\Carbon::parse($checkout)->format('M d') : null,
        ];
    }

    /**
     * ✅ API: Calendar Data (Merged & Color Coded for Uniformity)
     */
    public function getCalendarData(Request $request)
    {
        $start = $request->input('start') ? \Carbon\Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? \Carbon\Carbon::parse($request->input('end')) : now()->endOfMonth();

        $rooms = Room::all();

        // Get Website Bookings
        $bookings = Booking::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in_date', [$start, $end])
                    ->orWhereBetween('check_out_date', [$start, $end]);
            })->get();

        // Get Frontdesk Registrations
        $registrations = collect();
        if (class_exists(Registration::class)) {
            $registrations = Registration::where('stay_status', '!=', 'cancelled')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('check_in', [$start, $end])
                        ->orWhereBetween('check_out', [$start, $end]);
                })->get();
        }

        $roomData = $rooms->map(function ($room) use ($bookings, $registrations, $start, $end) {
            $events = [];

            // 1. Maintenance Status -> MAGENTA (#FF00FF)
            if ($room->status === 'maintenance') {
                $events[] = [
                    'id' => 'maint-' . $room->id,
                    'title' => 'Maintenance',
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                    'color' => '#FF00FF',
                    'status' => 'maintenance'
                ];
            }

            // 2. Frontdesk Events (Prioritized for physically in-house guests)
            foreach ($registrations as $reg) {
                if ($reg->room_id == $room->id || $reg->room_allocation == $room->name) {

                    // Match Frontdesk CRM Schedule Legend colors and wording
                    $color = '#6c757d'; // Default Grey
                    $statusLabel = 'Stay';

                    switch ($reg->stay_status) {
                        case 'checked_in':
                            $color = '#32CD32'; // Light Green
                            $statusLabel = 'In-House';
                            break;
                        case 'checked_out':
                            $color = '#006400'; // Dark Green
                            $statusLabel = 'Checked Out';
                            break;
                        case 'reserved':
                            $color = '#0DCAF0'; // Cyan
                            $statusLabel = 'Reserved';
                            break;
                        case 'maintenance':
                            $color = '#FF00FF'; // Magenta
                            $statusLabel = 'Maintenance';
                            break;
                        case 'draft_by_guest':
                            $color = '#ffc107'; // Yellow
                            $statusLabel = 'Pending Check-in';
                            break;
                    }

                    $events[] = [
                        'id' => 'reg-' . $reg->id,
                        'title' => "{$reg->full_name} ({$statusLabel})",
                        'start' => $reg->check_in instanceof \Carbon\Carbon ? $reg->check_in->format('Y-m-d') : substr($reg->check_in, 0, 10),
                        'end' => $reg->check_out instanceof \Carbon\Carbon ? $reg->check_out->format('Y-m-d') : substr($reg->check_out, 0, 10),
                        'color' => $color,
                        'status' => $reg->stay_status,
                    ];
                }
            }

            // 3. Website Events -> PRIMARY BLUE (#0d6efd)
            foreach ($bookings as $booking) {
                // Only add web booking if room isn't already marked as checked-in via Frontdesk
                if ($booking->room_id == $room->id) {
                    $events[] = [
                        'id'          => 'bk-' . $booking->id,
                        'title'       => "{$booking->guest_name} (Online Booking)",
                        'start'       => $booking->check_in_date instanceof \Carbon\Carbon
                            ? $booking->check_in_date->format('Y-m-d')
                            : substr($booking->check_in_date, 0, 10),
                        'end'         => $booking->check_out_date instanceof \Carbon\Carbon
                            ? $booking->check_out_date->format('Y-m-d')
                            : substr($booking->check_out_date, 0, 10),
                        'color'       => '#0d6efd', // Standard Blue for Web Bookings
                        'status'      => 'online_booking',
                        'details_url' => route('website.admin.bookings.edit', $booking->id),
                    ];
                }
            }

            return [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'events' => $events
            ];
        });

        return response()->json([
            'rooms' => $roomData,
            'days' => $this->generateDateHeaders($start, $end)
        ]);
    }

    private function generateDateHeaders($start, $end)
    {
        $dates = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dates[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'weekday' => $current->format('D'),
                'is_weekend' => $current->isWeekend(),
                'is_today' => $current->isToday(),
            ];
            $current->addDay();
        }
        return $dates;
    }
}
