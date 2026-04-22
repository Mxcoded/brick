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
use Modules\Website\Models\Booking;

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
        $validated['is_active'] = $request->boolean('is_active');

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
    public function deleteImage($imageId)
    {
        $image = RoomTypeImage::findOrFail($imageId);
        
        // Get the room type for reference
        $roomTypeId = $image->room_type_id;
        
        // Delete the file
        if ($image->path) {
            $this->imageService->delete($image->path);
        }
        
        // Delete the database record
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
        try {
            $roomType = RoomType::findOrFail($roomTypeId);

            $validated = $request->validate([
                'room_number' => [
                    'required',
                    'string',
                    'max:50',
                    \Illuminate\Validation\Rule::unique('room_units', 'room_number')->whereNull('deleted_at'),
                ],
                'floor' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:500',
            ], [
                'room_number.required' => 'Room number is required.',
                'room_number.unique' => 'This room number already exists. Please use a different one.',
            ]);

            $validated['room_type_id'] = $roomType->id;
            $validated['status'] = 'available';

            $unit = RoomUnit::create($validated);

            \Log::info('Room unit created', [
                'unit_id' => $unit->id,
                'room_number' => $unit->room_number,
                'room_type' => $roomType->name,
                'created_by' => auth()->id(),
            ]);

            return back()->with('success', "Room unit '{$unit->room_number}' added successfully.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exception to let Laravel handle it
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to create room unit', [
                'room_type_id' => $roomTypeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to create room unit: ' . $e->getMessage())->withInput();
        }
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

    /**
     * Move a unit to a different room type.
     * This also moves all associated bookings to the new room type.
     * The inventory calendar updates automatically since availability is calculated dynamically.
     */
    public function moveUnit(Request $request, $unitId)
    {
        $unit = RoomUnit::with(['roomType', 'bookings'])->findOrFail($unitId);
        $oldRoomType = $unit->roomType;

        $validated = $request->validate([
            'new_room_type_id' => 'required|exists:room_types,id|different:current_room_type_id',
        ], [
            'new_room_type_id.different' => 'Please select a different room type.',
        ]);

        $newRoomType = RoomType::findOrFail($validated['new_room_type_id']);

        // Check if unit has active registrations (Frontdesk) - these cannot be moved
        if (class_exists(\Modules\Frontdeskcrm\Models\Registration::class)) {
            $activeRegistrationsCount = $unit->registrations()
                ->whereIn('stay_status', ['checked_in', 'reserved', 'draft_by_guest'])
                ->where('check_out', '>=', now())
                ->count();

            if ($activeRegistrationsCount > 0) {
                return back()->with('error', 
                    "Cannot move unit '{$unit->room_number}': It has {$activeRegistrationsCount} active registration(s). " .
                    "Please complete check-out first."
                );
            }
        }

        // Use a transaction to ensure atomicity
        \DB::beginTransaction();
        try {
            // Get all bookings assigned to this unit (regardless of status)
            $bookingsToMove = Booking::where('room_unit_id', $unit->id)->get();
            $movedBookingsCount = 0;

            // Update each booking's room_type_id to match the new room type
            foreach ($bookingsToMove as $booking) {
                $oldBookingRoomTypeId = $booking->room_type_id;
                $booking->room_type_id = $newRoomType->id;
                $booking->save();

                $movedBookingsCount++;

                \Log::info('Booking moved with unit', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'from_room_type_id' => $oldBookingRoomTypeId,
                    'to_room_type_id' => $newRoomType->id,
                    'unit_id' => $unit->id,
                ]);
            }

            // Move the unit
            $unit->room_type_id = $newRoomType->id;
            $unit->save();

            \DB::commit();

            // Log the change for audit purposes
            \Log::info('Room unit moved between types', [
                'unit_id' => $unit->id,
                'room_number' => $unit->room_number,
                'from_room_type' => $oldRoomType->name,
                'to_room_type' => $newRoomType->name,
                'bookings_moved' => $movedBookingsCount,
                'moved_by' => auth()->id(),
            ]);

            $message = "Unit '{$unit->room_number}' moved from '{$oldRoomType->name}' to '{$newRoomType->name}'.";
            if ($movedBookingsCount > 0) {
                $message .= " {$movedBookingsCount} booking(s) also moved to the new room type.";
            }
            $message .= " Inventory calendar has been updated automatically.";

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Failed to move room unit', [
                'unit_id' => $unitId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to move unit: ' . $e->getMessage());
        }
    }
}
