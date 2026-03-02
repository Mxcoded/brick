<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Models\RoomTypeImage;
use Modules\Website\Models\Amenity;

class RoomTypeController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display list of room types with unit counts.
     */
    public function index()
    {
        $roomTypes = RoomType::withCount('units')
            ->with(['units' => function ($q) {
                $q->select('id', 'room_type_id', 'status');
            }])
            ->ordered()
            ->paginate(10);

        return view('website::admin.room-types.index', compact('roomTypes'));
    }

    /**
     * Show form for creating a new room type.
     */
    public function create()
    {
        $amenities = Amenity::all();
        return view('website::admin.room-types.create', compact('amenities'));
    }

    /**
     * Store a new room type with optional initial units.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'video_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
            'image' => 'required|image|max:20480',
            'gallery_images.*' => 'nullable|image|max:20480',
            // Units
            'units' => 'nullable|array',
            'units.*.room_number' => 'required_with:units|string|max:50',
            'units.*.floor' => 'nullable|string|max:50',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['display_order'] = $validated['display_order'] ?? 0;

        // Upload & Compress Primary Image
        if ($request->hasFile('image')) {
            $result = $this->imageService->compressAndStore($request->file('image'), 'room_types');
            $validated['image_url'] = $result['url'];
        }

        $roomType = RoomType::create($validated);

        // Sync amenities
        if (!empty($validated['amenities'])) {
            $roomType->amenities()->sync($validated['amenities']);
        }

        // Handle Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $result = $this->imageService->compressAndStore($file, 'room_type_gallery');
                RoomTypeImage::create([
                    'room_type_id' => $roomType->id,
                    'image_url' => $result['url'],
                    'path' => $result['path']
                ]);
            }
        }

        // Create initial units
        if (!empty($request->units)) {
            foreach ($request->units as $unitData) {
                if (!empty($unitData['room_number'])) {
                    RoomUnit::create([
                        'room_type_id' => $roomType->id,
                        'room_number' => $unitData['room_number'],
                        'floor' => $unitData['floor'] ?? null,
                        'status' => 'available',
                    ]);
                }
            }
        }

        return redirect()->route('website.admin.room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Display a room type with all its units.
     */
    public function show($id)
    {
        $roomType = RoomType::with(['images', 'amenities', 'units'])
            ->withCount('bookings')
            ->findOrFail($id);

        return view('website::admin.room-types.show', compact('roomType'));
    }

    /**
     * Show form for editing a room type.
     */
    public function edit($id)
    {
        $roomType = RoomType::with(['images', 'amenities', 'units'])->findOrFail($id);
        $amenities = Amenity::all();

        return view('website::admin.room-types.edit', compact('roomType', 'amenities'));
    }

    /**
     * Update a room type.
     */
    public function update(Request $request, $id)
    {
        $roomType = RoomType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name,' . $id,
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'video_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:20480',
            'gallery_images.*' => 'nullable|image|max:20480',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        // Upload & Compress Primary Image
        if ($request->hasFile('image')) {
            if ($roomType->image_url) {
                $this->imageService->deleteByUrl($roomType->image_url);
            }
            $result = $this->imageService->compressAndStore($request->file('image'), 'room_types');
            $validated['image_url'] = $result['url'];
        }

        $roomType->update($validated);

        // Sync amenities
        if (isset($validated['amenities'])) {
            $roomType->amenities()->sync($validated['amenities']);
        } else {
            $roomType->amenities()->detach();
        }

        // Handle Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $result = $this->imageService->compressAndStore($file, 'room_type_gallery');
                RoomTypeImage::create([
                    'room_type_id' => $roomType->id,
                    'image_url' => $result['url'],
                    'path' => $result['path']
                ]);
            }
        }

        return redirect()->back()->with('success', 'Room type updated successfully.');
    }

    /**
     * Delete a room type (soft delete).
     */
    public function destroy($id)
    {
        $roomType = RoomType::withCount(['bookings', 'units'])->findOrFail($id);

        // Prevent deletion if there are active bookings
        if ($roomType->bookings_count > 0) {
            return back()->with('error', 'Cannot delete room type with existing bookings.');
        }

        // Clean up images
        if ($roomType->image_url) {
            $this->imageService->deleteByUrl($roomType->image_url);
        }
        foreach ($roomType->images as $img) {
            if ($img->path) {
                $this->imageService->delete($img->path);
            }
        }

        $roomType->delete();
        return back()->with('success', 'Room type deleted.');
    }

    /**
     * Delete a gallery image.
     */
    public function deleteImage($id)
    {
        $image = RoomTypeImage::findOrFail($id);
        if ($image->path) {
            $this->imageService->delete($image->path);
        }
        $image->delete();
        return back()->with('success', 'Gallery image deleted.');
    }

    // ==========================================
    // UNIT MANAGEMENT
    // ==========================================

    /**
     * Add a new unit to a room type.
     */
    public function storeUnit(Request $request, $roomTypeId)
    {
        $roomType = RoomType::findOrFail($roomTypeId);

        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:room_units,room_number',
            'floor' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['room_type_id'] = $roomType->id;
        $validated['status'] = 'available';

        RoomUnit::create($validated);

        return back()->with('success', 'Room unit added successfully.');
    }

    /**
     * Update a unit's details.
     */
    public function updateUnit(Request $request, $unitId)
    {
        $unit = RoomUnit::findOrFail($unitId);

        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:room_units,room_number,' . $unitId,
            'floor' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,maintenance,blocked',
            'notes' => 'nullable|string|max:500',
        ]);

        $unit->update($validated);

        return back()->with('success', 'Room unit updated.');
    }

    /**
     * Delete a unit.
     */
    public function destroyUnit($unitId)
    {
        $unit = RoomUnit::withCount('bookings')->findOrFail($unitId);

        // Prevent deletion if there are bookings
        if ($unit->bookings_count > 0) {
            return back()->with('error', 'Cannot delete unit with existing bookings. Set to maintenance instead.');
        }

        $unit->delete();
        return back()->with('success', 'Room unit deleted.');
    }

    /**
     * Bulk add multiple units.
     */
    public function bulkStoreUnits(Request $request, $roomTypeId)
    {
        $roomType = RoomType::findOrFail($roomTypeId);

        $validated = $request->validate([
            'units' => 'required|array|min:1',
            'units.*.room_number' => 'required|string|max:50|distinct',
            'units.*.floor' => 'nullable|string|max:50',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['units'] as $unitData) {
            // Check if room number already exists
            if (RoomUnit::where('room_number', $unitData['room_number'])->exists()) {
                $errors[] = "Room number '{$unitData['room_number']}' already exists.";
                continue;
            }

            RoomUnit::create([
                'room_type_id' => $roomType->id,
                'room_number' => $unitData['room_number'],
                'floor' => $unitData['floor'] ?? null,
                'status' => 'available',
            ]);
            $created++;
        }

        $message = "{$created} unit(s) created successfully.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(', ', $errors);
            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }
}
