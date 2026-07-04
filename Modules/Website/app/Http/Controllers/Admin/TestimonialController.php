<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::latest()->get();

        return view('website::admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('website::admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'guest_image' => 'nullable|string|max:255',
            'stay_type' => 'nullable|string|max:255',
            'approved' => 'nullable|boolean',
        ]);

        $validated['approved'] = $request->boolean('approved');

        Testimonial::create($validated);

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function show(Testimonial $testimonial)
    {
        return view('website::admin.testimonials.show', compact('testimonial'));
    }

    public function edit(Testimonial $testimonial)
    {
        return view('website::admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'guest_image' => 'nullable|string|max:255',
            'stay_type' => 'nullable|string|max:255',
            'approved' => 'nullable|boolean',
        ]);

        $validated['approved'] = $request->boolean('approved');

        $testimonial->update($validated);

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }
}
