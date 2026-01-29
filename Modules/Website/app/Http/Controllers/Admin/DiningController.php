<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Dining;
use Illuminate\Support\Facades\Storage;

class DiningController extends Controller
{
    public function index()
    {
        $diningOptions = Dining::latest()->paginate(10);
        return view('website::admin.dining.index', compact('diningOptions'));
    }

    public function create()
    {
        return view('website::admin.dining.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'opening_hours' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:8192', // Max 8MB
            'menu_link' => 'nullable|url', // Added validation for menu link
        ]);

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);

            // Remove the raw 'image' file object so it doesn't break the DB insert
            unset($validated['image']);
        }

        Dining::create($validated);

        return redirect()->route('website.admin.dining.index')
            ->with('success', 'Dining option created successfully.');
    }

    public function edit($id)
    {
        $dining = Dining::findOrFail($id);
        return view('website::admin.dining.edit', compact('dining'));
    }

    public function update(Request $request, $id)
    {
        $dining = Dining::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'opening_hours' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:8192',
            'menu_link' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            // 1. Delete old image if it exists
            if ($dining->image_url) {
                // Convert URL back to storage path
                $oldPath = str_replace('/storage/', '', $dining->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            // 2. Store new image
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);

            // 3. Remove raw file object
            unset($validated['image']);
        }

        // 4. Update Database
        $dining->update($validated);

        return redirect()->route('website.admin.dining.index')
            ->with('success', 'Dining option updated successfully.');
    }

    public function destroy($id)
    {
        $dining = Dining::findOrFail($id);

        if ($dining->image_url) {
            $oldPath = str_replace('/storage/', '', $dining->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $dining->delete();

        return redirect()->route('website.admin.dining.index')
            ->with('success', 'Dining option deleted.');
    }
}
