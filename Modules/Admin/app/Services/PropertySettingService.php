<?php

namespace Modules\Admin\Services;

use Modules\Admin\Models\PropertySetting;

class PropertySettingService
{
    /**
     * Get a setting value by group and key.
     */
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return PropertySetting::getValue($group, $key, $default);
    }

    /**
     * Set a setting value by group and key.
     */
    public static function set(string $group, string $key, mixed $value): void
    {
        PropertySetting::setValue($group, $key, $value);
    }

    /**
     * Get all settings for a group.
     */
    public static function getGroup(string $group): array
    {
        return PropertySetting::getGroup($group);
    }

    /**
     * Set multiple settings for a group.
     */
    public static function setGroup(string $group, array $settings): void
    {
        PropertySetting::setGroup($group, $settings);
    }

    /**
     * Get all settings for the current property.
     */
    public static function getAll(): array
    {
        return PropertySetting::getAll();
    }

    /**
     * Clear cache for a specific group.
     */
    public static function clearCache(string $group): void
    {
        PropertySetting::clearCache($group);
    }

    /**
     * Migrate settings from RestaurantSetting to PropertySetting.
     */
    public static function migrateRestaurantSettings(): int
    {
        $migrated = 0;

        $settings = \DB::table('restaurant_settings')
            ->whereNotNull('property_id')
            ->get();

        foreach ($settings as $setting) {
            PropertySetting::updateOrCreate(
                [
                    'property_id' => $setting->property_id,
                    'group' => 'restaurant',
                    'key' => $setting->key,
                ],
                [
                    'value' => $setting->value,
                    'type' => 'string',
                ]
            );
            $migrated++;
        }

        return $migrated;
    }

    /**
     * Migrate settings from StaffSetting to PropertySetting.
     */
    public static function migrateStaffSettings(): int
    {
        $migrated = 0;

        $settings = \DB::table('staff_settings')
            ->whereNotNull('property_id')
            ->get();

        foreach ($settings as $setting) {
            PropertySetting::updateOrCreate(
                [
                    'property_id' => $setting->property_id,
                    'group' => 'staff',
                    'key' => $setting->key,
                ],
                [
                    'value' => $setting->value,
                    'type' => 'string',
                ]
            );
            $migrated++;
        }

        return $migrated;
    }

    /**
     * Migrate settings from Property.settings JSON to PropertySetting.
     */
    public static function migratePropertySettings(): int
    {
        $migrated = 0;

        $properties = \DB::table('properties')
            ->whereNotNull('settings')
            ->get();

        foreach ($properties as $property) {
            $settings = json_decode($property->settings, true) ?? [];

            foreach ($settings as $key => $value) {
                PropertySetting::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'group' => 'general',
                        'key' => $key,
                    ],
                    [
                        'value' => is_string($value) ? $value : json_encode($value),
                        'type' => gettype($value),
                    ]
                );
                $migrated++;
            }
        }

        return $migrated;
    }
}
