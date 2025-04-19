<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('website::admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('website::admin.rooms.create');
    }

    public function store(Request $request)
    {
        Log::info('Room store request:', $request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'required|string|max:50',
            'featured' => 'required|boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'primary_image' => 'required|image|max:5048',
            'images.*' => 'nullable|image|max:10248',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        $roomData = $validated;
        unset($roomData['primary_image'], $roomData['images'], $roomData['video']);
        $room = Room::create($roomData);

        if ($request->hasFile('primary_image')) {
            $room->image = $request->file('primary_image')->store('rooms', 'public');
            $room->save();
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('rooms', 'public');
                $room->images()->create([
                    'path' => $path,
                    'order' => $index,
                    'caption' => $request->input('captions')[$index] ?? null,
                ]);
            }
        }

        if ($request->hasFile('video')) {
            $room->video = $request->file('video')->store('rooms', 'public');
            $room->save();
        }

        if (!empty($validated['amenities'])) {
            $room->amenities()->sync($validated['amenities']);
        }

        Log::info('Room created:', $room->toArray());
        return redirect()->route('website.admin.rooms.index')->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        return view('website::admin.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        return view('website::admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        Log::info('Room update request:', $request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'size' => 'required|string|max:50',
            'featured' => 'required|boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'primary_image' => 'nullable|image|max:5048',
            'images.*' => 'nullable|image|max:10248',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        $roomData = $validated;
        unset($roomData['primary_image'], $roomData['images'], $roomData['video']);
        $room->update($roomData);

        if ($request->hasFile('primary_image')) {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $room->image = $request->file('primary_image')->store('rooms', 'public');
            $room->save();
        }

        if ($request->hasFile('images')) {
            $currentImageCount = $room->images()->count();
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('rooms', 'public');
                $room->images()->create([
                    'path' => $path,
                    'order' => $currentImageCount + $index,
                    'caption' => $request->input('captions')[$index] ?? null,
                ]);
            }
        }

        if ($request->hasFile('video')) {
            if ($room->video) {
                Storage::disk('public')->delete($room->video);
            }
            $room->video = $request->file('video')->store('rooms', 'public');
            $room->save();
        }

        $room->amenities()->sync($validated['amenities'] ?? []);
        Log::info('Room updated:', $room->toArray());
        return redirect()->route('website.admin.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        Log::warning('Room deletion triggered:', ['room_id' => $room->id, 'request' => request()->all()]);
        // Safeguard against accidental deletion from image form
        if (request()->hasAny(['name', 'description', 'price_per_night', 'capacity', 'size', 'featured'])) {
            Log::error('Invalid room deletion attempt with update fields:', ['room_id' => $room->id, 'request' => request()->all()]);
            return redirect()->route('website.admin.rooms.edit', $room)->with('error', 'Invalid deletion request.');
        }
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }
        foreach ($room->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
        if ($room->video) {
            Storage::disk('public')->delete($room->video);
        }
        $room->delete();
        Log::info('Room deleted:', ['room_id' => $room->id]);
        return redirect()->route('website.admin.rooms.index')->with('success', 'Room deleted successfully.');
    }

    public function destroyImage(Room $room, RoomImage $image)
    {
        Log::info('Attempting to delete image:', ['room_id' => $room->id, 'image_id' => $image->id, 'request' => request()->all()]);
        // Ensure image belongs to room (handled by route binding, but double-check)
        if ($image->room_id !== $room->id) {
            Log::warning('Image does not belong to room:', ['room_id' => $room->id, 'image_id' => $image->id]);
            return redirect()->route('website.admin.rooms.edit', $room)->with('error', 'Image not found.');
        }
        Storage::disk('public')->delete($image->path);
        $image->delete();
        Log::info('Room image deleted:', ['room_id' => $room->id, 'image_id' => $image->id]);
        return redirect()->route('website.admin.rooms.edit', $room)->with('success', 'Image deleted successfully.');
    }

    public function destroyVideo(Room $room)
    {
        Log::info('Attempting to delete video:', ['room_id' => $room->id, 'request' => request()->all()]);
        if (!$room->video) {
            Log::warning('No video to delete', ['room_id' => $room->id]);
            return redirect()->route('website.admin.rooms.edit', $room)->with('error', 'No video to delete.');
        }
        Storage::disk('public')->delete($room->video);
        $room->video = null;
        $room->save();
        Log::info('Room video deleted:', ['room_id' => $room->id]);
        return redirect()->route('website.admin.rooms.edit', $room)->with('success', 'Video deleted successfully.');
    }
}
