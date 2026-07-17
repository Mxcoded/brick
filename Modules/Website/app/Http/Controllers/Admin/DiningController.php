<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Models\Dining;

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
            'menu_pdf' => 'nullable|mimes:pdf|max:20480',
            'image' => 'nullable|image|max:8192',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);
            unset($validated['image']);
        }

        if ($request->hasFile('menu_pdf')) {
            $path = $request->file('menu_pdf')->store('dining/menus', 'public');
            $validated['menu_pdf'] = Storage::url($path);
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
            'menu_pdf' => 'nullable|mimes:pdf|max:20480',
            'image' => 'nullable|image|max:8192',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            if ($dining->image_url) {
                $oldPath = str_replace('/storage/', '', $dining->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('dining', 'public');
            $validated['image_url'] = Storage::url($path);
            unset($validated['image']);
        }

        if ($request->hasFile('menu_pdf')) {
            if ($dining->menu_pdf) {
                $oldPath = str_replace('/storage/', '', $dining->menu_pdf);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('menu_pdf')->store('dining/menus', 'public');
            $validated['menu_pdf'] = Storage::url($path);
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
        if ($dining->menu_pdf) {
            $oldPath = str_replace('/storage/', '', $dining->menu_pdf);
            Storage::disk('public')->delete($oldPath);
        }

        $dining->delete();

        return redirect()->route('website.admin.dining.index')
            ->with('success', 'Dining option deleted.');
    }

    public function deletePdf($id)
    {
        $dining = Dining::findOrFail($id);
        if ($dining->menu_pdf) {
            $oldPath = str_replace('/storage/', '', $dining->menu_pdf);
            Storage::disk('public')->delete($oldPath);
            $dining->update(['menu_pdf' => null]);
        }

        return redirect()->route('website.admin.dining.edit', $dining->id)
            ->with('success', 'Menu PDF removed.');
    }

    public function qrCode($id)
    {
        $dining = Dining::findOrFail($id);
        $url = route('website.dining.menu', $dining);

        return view('website::admin.dining.qr', compact('dining', 'url'));
    }
}
