<?php

namespace Modules\Admin\Filament\Resources\AuditTrails;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Modules\Admin\Filament\Resources\AuditTrails\Pages\ListAuditTrails;
use Modules\Admin\Filament\Resources\AuditTrails\Tables\AuditTrailsTable;
use OwenIt\Auditing\Models\Audit;

class AuditTrailResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Audit Trails';

    protected static ?string $modelLabel = 'audit trail';

    protected static ?string $pluralModelLabel = 'audit trails';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $slug = 'audit-trails';

    public static function eventLabel(string $event): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $event));
    }

    public static function eventColor(string $event): string
    {
        return match ($event) {
            'created', 'attached', 'role-assigned' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'restored' => 'info',
            'role-detached', 'detached', 'sync' => 'warning',
            default => 'dark',
        };
    }

    /**
     * Compact per-field change summary, e.g. "name: Old Name → New Name".
     */
    public static function changesSummary(Audit $audit): string
    {
        $old = $audit->old_values ?? [];
        $new = $audit->new_values ?? [];

        $lines = [];

        foreach (array_keys(array_merge($old, $new)) as $field) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $oldValue = static::formatValue($field, $old[$field] ?? null);
            $newValue = static::formatValue($field, $new[$field] ?? null);

            if ($audit->event === 'created') {
                $lines[] = "{$field}: {$newValue}";
            } elseif ($audit->event === 'deleted') {
                $lines[] = "{$field}: {$oldValue}";
            } elseif ($oldValue !== $newValue) {
                $lines[] = "{$field}: {$oldValue} → {$newValue}";
            } else {
                $lines[] = "{$field}: {$newValue}";
            }
        }

        return implode(PHP_EOL, $lines);
    }

    protected static function formatValue(string $field, $value): string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if (in_array(substr($trimmed, 0, 1), ['[', '{'], true)) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
        }

        if (! is_array($value)) {
            return $value ?? '—';
        }

        if ($field === 'roles') {
            return collect($value)
                ->map(fn ($role) => is_array($role) ? ($role['name'] ?? '') : $role)
                ->filter()
                ->implode(', ');
        }

        return json_encode($value);
    }

    /**
     * Revert a model to the state captured in an "updated" or "deleted" audit.
     *
     * @return array{0: bool, 1: string}
     */
    public static function restoreAudit(Audit $audit): array
    {
        if (! in_array($audit->event, ['updated', 'deleted'], true)) {
            return [false, 'Only "updated" or "deleted" records can be restored.'];
        }

        $modelClass = $audit->auditable_type;

        if (! class_exists($modelClass)) {
            return [false, 'The audited model class no longer exists.'];
        }

        try {
            $instance = new $modelClass;
            $table = $instance->getTable();
            $keyName = $instance->getKeyName();

            $columns = Schema::getColumnListing($table);
            $old = collect($audit->old_values)
                ->only($columns)
                ->toArray();

            $existing = static::findAuditable($modelClass, $audit->auditable_id);
            $label = class_basename($modelClass).' #'.$audit->auditable_id;

            if ($existing) {
                $existing->forceFill($old)->save();

                if (method_exists($existing, 'trashed') && $existing->trashed()) {
                    $existing->restore();
                }
            } else {
                $model = new $modelClass;
                $model->forceFill($old);
                $model->{$keyName} = $audit->auditable_id;
                $model->save();
            }

            return [true, "Restored {$label} to its previous state."];
        } catch (\Throwable $e) {
            return [false, 'Unable to restore record: '.$e->getMessage()];
        }
    }

    protected static function findAuditable(string $modelClass, $id)
    {
        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            return $modelClass::withTrashed()->find($id);
        }

        return $modelClass::find($id);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access_admin_dashboard') ?? false;
    }

    public static function table(Table $table): Table
    {
        return AuditTrailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditTrails::route('/'),
        ];
    }
}
