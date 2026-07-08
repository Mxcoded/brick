<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\ImageService;
use App\Services\RoomCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Website\Models\Amenity;
use Modules\Website\Models\RoomImage;

class RoomController extends Controller
{
    protected ImageService $imageService;

    protected RoomCalendarService $calendarService;

    public function __construct(ImageService $imageService, RoomCalendarService $calendarService)
    {
        $this->imageService = $imageService;
        $this->calendarService = $calendarService;
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
            'gallery_images.*' => 'nullable|image|max:20480',
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
        if (! empty($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        }

        // Handle Gallery Images with Compression
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $result = $this->imageService->compressAndStore($file, 'room_gallery');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => $result['url'],
                    'path' => $result['path'],
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
            'name' => 'required|string|max:255|unique:rooms,name,'.$id,
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
            'gallery_images.*' => 'nullable|image|max:20480',
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
                    'path' => $result['path'],
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
            ? Carbon::parse($request->date)
            : Carbon::now();

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $daysInMonth = $date->daysInMonth;

        // 2. Fetch Rooms with Bookings overlapping this month
        // We eagerly load bookings to avoid "N+1" query performance issues
        $rooms = Room::with(['bookings' => function ($query) use ($startOfMonth, $endOfMonth) {
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
     * API: Live Room Rack Data (uses RoomCalendarService)
     */
    public function getRoomStatus()
    {
        return response()->json($this->calendarService->getRoomStatusData());
    }

    /**
     * API: Calendar/Density Chart Data (uses RoomCalendarService)
     */
    public function getCalendarData(Request $request)
    {
        $start = $request->input('start') ? Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? Carbon::parse($request->input('end')) : now()->endOfMonth();

        return response()->json($this->calendarService->getCalendarData($start, $end));
    }

    /**
     * API: Get occupancy statistics for dashboard
     */
    public function getOccupancyStats()
    {
        return response()->json($this->calendarService->getOccupancyStats());
    }
}
