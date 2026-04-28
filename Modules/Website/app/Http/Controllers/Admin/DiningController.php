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
            'cuisine_type' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:255',
            'menu_link' => 'nullable|url',
            'image' => 'nullable|image|max:8192',
        ]);

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);
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
            'cuisine_type' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:255',
            'menu_link' => 'nullable|url',
            'image' => 'nullable|image|max:8192',
        ]);

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($dining->image_url) {
                $oldPath = str_replace('/storage/', '', $dining->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new image
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);
            unset($validated['image']);
        }

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
