<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::latest()->paginate(10);
        return view('website::admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('website::admin.rooms.create');
    }

    public function store(Request $request)
    {
        // 1. Validation (Updated to match New Schema)
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name',
            'price' => 'required|numeric|min:0', // Matches database 'price'
            'capacity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'description' => 'required|string',
            'amenities' => 'nullable|array', // JSON Array
            'video_url' => 'nullable|url', // YouTube Links preferred
            'is_featured' => 'boolean',
            'status' => 'required|in:available,maintenance,booked',
            'image' => 'required|image|max:5120', // Primary Image
            'gallery_images.*' => 'nullable|image|max:5120' // Extra Gallery Images
        ]);

        // 2. Generate Slug
        $validated['slug'] = Str::slug($validated['name']);

        // 3. Upload Primary Image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        // 4. Create Room
        $room = Room::create($validated);

        // 5. Handle Multiple Gallery Images (From your old controller)
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('room_gallery', 'public');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => Storage::url($path), // storing full URL
                    'path' => $path // storing raw path for deletion
                ]);
            }
        }

        return redirect()->route('website.admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function edit($id)
    {
        $room = Room::with('images')->findOrFail($id);
        return view('website::admin.rooms.edit', compact('room'));
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
            'video_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'status' => 'required|in:available,maintenance,booked',
            'image' => 'nullable|image|max:5120',
            'gallery_images.*' => 'nullable|image|max:5120'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Handle Primary Image Update
        if ($request->hasFile('image')) {
            if ($room->image_url) {
                // Try to extract path from URL for deletion
                $oldPath = str_replace('/storage/', '', $room->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $room->update($validated);

        // Handle New Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('room_gallery', 'public');
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => Storage::url($path),
                    'path' => $path
                ]);
            }
        }

        return redirect()->route('website.admin.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    // Kept your helper to delete specific gallery images
    public function deleteImage($id) // <--- Matches Route
    {
        $image = RoomImage::findOrFail($id);

        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return back()->with('success', 'Gallery image deleted.');
    }
}
