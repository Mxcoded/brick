<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::withCount('users')->latest()->get();

        return view('admin::properties.index', compact('properties'));
    }

    public function create()
    {
        $activeProperties = Property::active()->get();

        return view('admin::properties.create', compact('activeProperties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:properties,slug',
            'code' => 'required|string|max:10|unique:properties,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $property = Property::create($data);

        Auth::user()->properties()->attach($property->id, ['is_default' => true]);

        if ($request->filled('clone_from')) {
            $source = Property::find($request->clone_from);
            if ($source) {
                $this->clonePropertyData($source, $property);
            }
        }

        return redirect()->route('admin.properties.index')
            ->with('success', "Property \"{$property->name}\" created successfully.");
    }

    public function show(Property $property)
    {
        $property->loadCount('users');
        $recentUsers = $property->users()->latest('property_user.created_at')->take(10)->get();

        return view('admin::properties.show', compact('property', 'recentUsers'));
    }

    public function edit(Property $property)
    {
        return view('admin::properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:properties,slug,'.$property->id,
            'code' => 'required|string|max:10|unique:properties,code,'.$property->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $property->update($data);

        return redirect()->route('admin.properties.index')
            ->with('success', "Property \"{$property->name}\" updated.");
    }

    public function destroy(Property $property)
    {
        if ($property->is_headquarters) {
            return back()->with('error', 'Cannot delete the headquarters property.');
        }

        $property->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property deleted.');
    }

    public function setHeadquarters(Property $property)
    {
        if ($property->is_headquarters) {
            return back()->with('info', 'This property is already the headquarters.');
        }

        Property::where('is_headquarters', true)->update(['is_headquarters' => false]);

        $property->update(['is_headquarters' => true]);

        return back()->with('success', "\"{$property->name}\" is now the headquarters.");
    }

    public function switch(Property $property, PropertyService $service)
    {
        if (! Auth::user()->properties()->where('property_id', $property->id)->exists()) {
            return back()->with('error', 'You are not assigned to this property.');
        }

        $service->setCurrent($property);

        return redirect()->back()->with('success', "Switched to \"{$property->name}\".");
    }

    public function switchAll(PropertyService $service)
    {
        $service->setAll();

        return redirect()->back()->with('success', 'Viewing all properties.');
    }

    public function manageUsers(Property $property)
    {
        $assignedUserIds = $property->users->pluck('id')->toArray();
        $users = User::whereDoesntHave('properties', fn ($q) => $q->where('property_id', $property->id))
            ->orderBy('name')->get();

        return view('admin::properties.users', compact('property', 'users', 'assignedUserIds'));
    }

    public function assignUser(Request $request, Property $property)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_default' => 'boolean',
        ]);

        if (! $property->users()->where('user_id', $data['user_id'])->exists()) {
            $property->users()->attach($data['user_id'], ['is_default' => $request->boolean('is_default')]);
        }

        return back()->with('success', 'User assigned to property.');
    }

    public function removeUser(Property $property, User $user)
    {
        if ($property->users()->count() <= 1) {
            return back()->with('error', 'Cannot remove the last user from a property.');
        }

        $property->users()->detach($user->id);

        return back()->with('success', 'User removed from property.');
    }

    private function clonePropertyData(Property $source, Property $target): void
    {
        DB::transaction(function () use ($source, $target) {
            foreach (DB::table('room_types')->where('property_id', $source->id)->get() as $row) {
                $uniqueName = $row->name.' ('.$target->slug.')';
                DB::table('room_types')->insert([
                    'name' => $uniqueName,
                    'slug' => Str::slug($uniqueName),
                    'description' => $row->description,
                    'capacity' => $row->capacity,
                    'size' => $row->size,
                    'bed_type' => $row->bed_type,
                    'price' => $row->price,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('charge_types')->where('property_id', $source->id)->get() as $row) {
                DB::table('charge_types')->insert([
                    'name' => $row->name,
                    'code' => $row->code.'-'.$target->slug,
                    'description' => $row->description,
                    'icon' => $row->icon,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('rate_codes')->where('property_id', $source->id)->get() as $row) {
                DB::table('rate_codes')->insert([
                    'name' => $row->name,
                    'code' => $row->code.'-'.$target->slug,
                    'description' => $row->description,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('booking_sources')->where('property_id', $source->id)->get() as $row) {
                DB::table('booking_sources')->insert([
                    'name' => $row->name.' ('.$target->slug.')',
                    'description' => $row->description,
                    'type' => $row->type,
                    'commission_rate' => $row->commission_rate,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('guest_types')->where('property_id', $source->id)->get() as $row) {
                DB::table('guest_types')->insert([
                    'name' => $row->name.' ('.$target->slug.')',
                    'description' => $row->description,
                    'color' => $row->color,
                    'discount_rate' => $row->discount_rate,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('channels')->where('property_id', $source->id)->get() as $row) {
                DB::table('channels')->insert([
                    'name' => $row->name,
                    'provider' => $row->provider,
                    'is_active' => $row->is_active,
                    'property_id' => $target->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
