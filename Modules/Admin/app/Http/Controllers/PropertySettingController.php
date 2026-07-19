<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Modules\Admin\Services\PropertySettingService;

class PropertySettingController extends Controller
{
    protected array $settingGroups;

    public function __construct()
    {
        $this->settingGroups = [
            'general' => [
                'label' => 'General',
                'icon' => 'fas fa-cog',
                'settings' => [
                    ['key' => 'property_name', 'label' => 'Property Name', 'type' => 'text', 'default' => ''],
                    ['key' => 'property_email', 'label' => 'Contact Email', 'type' => 'email', 'default' => ''],
                    ['key' => 'property_phone', 'label' => 'Contact Phone', 'type' => 'text', 'default' => ''],
                    ['key' => 'property_address', 'label' => 'Address', 'type' => 'textarea', 'default' => ''],
                ],
            ],
            'restaurant' => [
                'label' => 'Restaurant',
                'icon' => 'fas fa-utensils',
                'settings' => [
                    ['key' => 'enable_room_service', 'label' => 'Enable Room Service', 'type' => 'toggle', 'default' => false],
                    ['key' => 'restaurant_tax_rate', 'label' => 'Tax Rate (%)', 'type' => 'number', 'default' => 7.5],
                    ['key' => 'service_charge_rate', 'label' => 'Service Charge (%)', 'type' => 'number', 'default' => 0],
                    ['key' => 'auto_close_kitchen_orders', 'label' => 'Auto-close kitchen after hours', 'type' => 'toggle', 'default' => false],
                ],
            ],
            'frontdesk' => [
                'label' => 'Front Desk',
                'icon' => 'fas fa-concierge-bell',
                'settings' => [
                    ['key' => 'check_in_time', 'label' => 'Default Check-in Time', 'type' => 'time', 'default' => '14:00'],
                    ['key' => 'check_out_time', 'label' => 'Default Check-out Time', 'type' => 'time', 'default' => '12:00'],
                    ['key' => 'auto_no_show_hours', 'label' => 'Mark No-show After (hours)', 'type' => 'number', 'default' => 24],
                    ['key' => 'require_guest_id', 'label' => 'Require Guest ID at Check-in', 'type' => 'toggle', 'default' => true],
                ],
            ],
            'staff' => [
                'label' => 'Staff',
                'icon' => 'fas fa-users',
                'settings' => [
                    ['key' => 'attendance_grace_minutes', 'label' => 'Late Grace Period (minutes)', 'type' => 'number', 'default' => 15],
                    ['key' => 'overtime_threshold_hours', 'label' => 'Overtime Threshold (hours)', 'type' => 'number', 'default' => 8],
                    ['key' => 'enable_shift_swapping', 'label' => 'Enable Shift Swapping', 'type' => 'toggle', 'default' => false],
                ],
            ],
            'housekeeping' => [
                'label' => 'Housekeeping',
                'icon' => 'fas fa-broom',
                'settings' => [
                    ['key' => 'auto_assign_rooms', 'label' => 'Auto-assign Rooms', 'type' => 'toggle', 'default' => false],
                    ['key' => 'inspection_required', 'label' => 'Require Inspection Before Checkout', 'type' => 'toggle', 'default' => true],
                    ['key' => 'cleaning_priority', 'label' => 'Default Cleaning Priority', 'type' => 'select', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'], 'default' => 'medium'],
                ],
            ],
        ];
    }

    public function index(Request $request)
    {
        $propertyService = app(PropertyService::class);
        $currentPropertyId = $propertyService->id();
        $isViewingAll = $propertyService->isViewingAll();
        $propertyName = $propertyService->current()?->name ?? 'All Properties';

        $currentGroup = $request->get('group', 'general');

        $settings = [];
        if (isset($this->settingGroups[$currentGroup])) {
            foreach ($this->settingGroups[$currentGroup]['settings'] as $setting) {
                $settings[$setting['key']] = PropertySettingService::get(
                    $currentGroup,
                    $setting['key'],
                    $setting['default']
                );
            }
        }

        return view('admin::settings.index', [
            'settingGroups' => $this->settingGroups,
            'currentGroup' => $currentGroup,
            'settings' => $settings,
            'currentPropertyId' => $currentPropertyId,
            'isViewingAll' => $isViewingAll,
            'propertyName' => $propertyName,
        ]);
    }

    public function update(Request $request, string $group)
    {
        if (! isset($this->settingGroups[$group])) {
            return redirect()->route('admin.settings.index')->with('error', 'Invalid settings group.');
        }

        $groupSettings = $this->settingGroups[$group]['settings'];

        foreach ($groupSettings as $setting) {
            $value = $request->input($setting['key'], $setting['default']);

            if ($setting['type'] === 'toggle') {
                $value = $request->boolean($setting['key']);
            }

            if ($setting['type'] === 'number') {
                $value = (float) $value;
            }

            PropertySettingService::set($group, $setting['key'], $value);
        }

        PropertySettingService::clearCache($group);

        return redirect()->route('admin.settings.index', ['group' => $group])
            ->with('success', ucfirst($this->settingGroups[$group]['label']).' settings saved successfully.');
    }
}
