<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Settings;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = Settings::all();
        return view('website::admin.settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new setting.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('website::admin.settings.create');
    }

    /**
     * Store a newly created setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::info('Settings store request:', $request->all());
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings',
            'type' => 'required|in:string,image,video,json',
            'value' => 'required_if:type,string,json',
            'image' => 'required_if:type,image|image|max:2048',
            'video' => 'required_if:type,video|mimes:mp4,mov,avi|max:10240',
        ]);
        Log::info('Validation passed:', $validated);

        $data = [
            'key' => $validated['key'],
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'image' && $request->hasFile('image')) {
            $data['value'] = $request->file('image')->store('settings', 'public');
            Log::info('Image stored at:', ['path' => $data['value']]);
        } elseif ($validated['type'] === 'video' && $request->hasFile('video')) {
            $data['value'] = $request->file('video')->store('settings', 'public');
            Log::info('Video stored at:', ['path' => $data['value']]);
        } else {
            $data['value'] = $validated['value'];
        }

        $setting = Settings::create($data);
        Log::info('Setting created:', $setting->toArray());

        return redirect()->route('website.admin.settings.index')->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified setting.
     *
     * @param  \Modules\Website\Models\Setting  $setting
     * @return \Illuminate\View\View
     */
    public function show(Settings $setting)
    {
        return view('website::admin.settings.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified setting.
     *
     * @param  \Modules\Website\Models\Setting  $setting
     * @return \Illuminate\View\View
     */
    public function edit(Settings $setting)
    {
        return view('website::admin.settings.edit', compact('setting'));
    }

    /**
     * Update the specified setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Website\Models\Setting  $setting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Settings $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'type' => 'required|in:string,image,video,json',
            'value' => 'required_if:type,string,json',
            'image' => 'nullable|image|max:2048', // Nullable since updating might not include a new image
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240', // Nullable for the same reason
        ]);

        $data = [
            'key' => $validated['key'],
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'image' && $request->hasFile('image')) {
            $data['value'] = $request->file('image')->store('settings', 'public');
        } elseif ($validated['type'] === 'video' && $request->hasFile('video')) {
            $data['value'] = $request->file('video')->store('settings', 'public');
        } elseif ($validated['type'] !== 'image' && $validated['type'] !== 'video') {
            $data['value'] = $validated['value'];
        }

        $setting->update($data);

        return redirect()->route('website.admin.settings.index')->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified setting from storage.
     *
     * @param  \Modules\Website\Models\Setting  $setting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Settings $setting)
    {
        $setting->delete();
        return redirect()->route('website.admin.settings.index')->with('success', 'Setting deleted successfully.');
    }
}
