<?php

namespace Modules\Staff\Helpers;

class DepartmentHelper
{
    private static array $map = [
        'accounts' => 'Finance',
        'admin and finance' => 'Finance',
        'finance' => 'Finance',
        'finace' => 'Finance',
        'house keeping' => 'Housekeeping',
        'housekeeping' => 'Housekeeping',
        'housekeeeping' => 'Housekeeping',
        'housekkeping' => 'Housekeeping',
        'house_keeping' => 'Housekeeping',
        'front desk' => 'Front Office',
        'front desk office' => 'Front Office',
        'front office' => 'Front Office',
        'frontdesk' => 'Front Office',
        'frontoffice' => 'Front Office',
        'food & beverage' => 'Food & Beverage',
        'food and beverage' => 'Food & Beverage',
        'food&beverage' => 'Food & Beverage',
        'foodand beverage' => 'Food & Beverage',
        'food and beverage kitchen' => 'Food & Beverage',
        'food & beverage kitchen' => 'Food & Beverage',
        'kitchen; food and beverage' => 'Food & Beverage',
        'kitchen' => 'Food & Beverage',
        'f&b' => 'Food & Beverage',
        'banquet (f&b)' => 'Banquet',
        'banquet' => 'Banquet',
        'facility (maintenance)' => 'Maintenance',
        'facility' => 'Maintenance',
        'maintenance' => 'Maintenance',
        'hr' => 'Human Resources',
        'human resources' => 'Human Resources',
        'business devlopment' => 'Sales & Marketing',
        'business development' => 'Sales & Marketing',
        'marketing' => 'Sales & Marketing',
        'social media and marketing' => 'Sales & Marketing',
        'sales and marketing' => 'Sales & Marketing',
        'it' => 'IT',
        'information technology' => 'IT',
        'laundry' => 'Laundry',
        'logistics' => 'Logistics',
        'management' => 'Management',
        'management operations' => 'Management',
        'procurement' => 'Procurement',
        'purchasing' => 'Procurement',
        'quality control' => 'Quality Control',
        'security' => 'Security',
    ];

    public static function normalize(?string $department): string
    {
        $department = trim($department ?? '');
        if (empty($department)) {
            return 'Unassigned';
        }
        $key = strtolower($department);

        return self::$map[$key] ?? $department;
    }

    public static function all(): array
    {
        return [
            'Housekeeping',
            'Front Office',
            'Finance',
            'Food & Beverage',
            'Banquet',
            'Maintenance',
            'Human Resources',
            'Sales & Marketing',
            'IT',
            'Laundry',
            'Logistics',
            'Management',
            'Procurement',
            'Quality Control',
            'Security',
            'Spa & Wellness',
            'Engineering',
        ];
    }

    public static function consolidate(iterable $records, string $countField = 'total'): array
    {
        $grouped = [];
        foreach ($records as $record) {
            $name = self::normalize($record->department);
            $count = (int) ($record->{$countField} ?? 1);
            if (isset($grouped[$name])) {
                $grouped[$name] += $count;
            } else {
                $grouped[$name] = $count;
            }
        }
        arsort($grouped);

        return array_map(fn ($name, $total) => (object) [
            'department' => $name,
            $countField => $total,
        ], array_keys($grouped), $grouped);
    }
}
