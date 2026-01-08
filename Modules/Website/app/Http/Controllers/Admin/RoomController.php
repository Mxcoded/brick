<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;
use Modules\Website\Models\Amenity; // Import Amenity
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
            'image' => 'required|image|max:5120',
            'gallery_images.*' => 'nullable|image|max:5120'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Upload Primary Image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $room = Room::create($validated);

        // SYNC AMENITIES (The Pivot Table Magic)
        if (!empty($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        }

        // Handle Gallery
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
            'status' => 'required|in:available,maintenance,booked',
            'image' => 'nullable|image|max:5120',
            'gallery_images.*' => 'nullable|image|max:5120'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($room->image_url) {
                $oldPath = str_replace('/storage/', '', $room->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $room->update($validated);

        // SYNC AMENITIES
        if (isset($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        } else {
            $room->amenities()->detach(); // If none selected, clear all
        }

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

    public function show($id)
    {
        $room = Room::with(['images', 'amenities'])->findOrFail($id);
        return view('website::admin.rooms.show', compact('room'));
    }

    public function deleteImage($id)
    {
        $image = RoomImage::findOrFail($id);
        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
        $image->delete();
        return back()->with('success', 'Gallery image deleted.');
    }

    // Add destroy method if missing
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        // Clean up images
        if ($room->image_url) {
            $path = str_replace('/storage/', '', $room->image_url);
            Storage::disk('public')->delete($path);
        }
        foreach ($room->images as $img) {
            if ($img->path) Storage::disk('public')->delete($img->path);
        }
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }
}
