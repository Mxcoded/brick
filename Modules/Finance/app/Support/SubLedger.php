<?php

namespace Modules\Finance\Support;

use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\Guest;

/**
 * The people and organisations a journal line can be booked against.
 *
 * A sub-ledger is the "who" behind a control-account line: the guest or the
 * company an accounts-receivable balance belongs to. Finance does not own either
 * model, so the register is built here and every class is probed with
 * class_exists() before use - the whole point of a modular monolith is that the
 * front desk can be switched off without Finance falling over, and the reports
 * still have to run.
 *
 * The stored party_type is the model's own class name (no global morph map is
 * registered, so nothing else in the app changes how it writes polymorphic
 * types). Options are keyed "Class:id" so a single Select can carry both kinds
 * without a second lookup column.
 */
final class SubLedger
{
    /**
     * Model class => [attribute holding the display name, human group prefix].
     *
     * @return array<class-string, array{label: string, prefix: string}>
     */
    public static function map(): array
    {
        $candidates = [
            Guest::class => ['label' => 'full_name', 'prefix' => 'Guest'],
            CityLedgerAccount::class => ['label' => 'name', 'prefix' => 'Company'],
        ];

        return array_filter(
            $candidates,
            fn (string $class): bool => class_exists($class),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Search options for the journal-line sub-ledger select, keyed "Class:id".
     *
     * @return array<string, string>
     */
    public static function options(?string $search = null, int $limit = 25): array
    {
        $search = trim((string) $search);
        $options = [];

        foreach (self::map() as $class => $meta) {
            $query = $class::query();

            if ($search !== '') {
                $query->where($meta['label'], 'like', '%'.$search.'%');
            }

            foreach ($query->orderBy($meta['label'])->limit($limit)->get() as $model) {
                $options[$class.':'.$model->getKey()] = $meta['prefix'].' — '.$model->{$meta['label']};
            }
        }

        return $options;
    }

    /**
     * The readable label for a stored "Class:id" value, for re-hydrating an
     * edit form whose current party is outside the first page of search results.
     */
    public static function labelFor(?string $value): ?string
    {
        if (! $value || ! str_contains($value, ':')) {
            return null;
        }

        [$class, $id] = explode(':', $value, 2);

        return self::label($class, (int) $id);
    }

    /**
     * The readable label for a party, given the two stored columns. Returns null
     * when there is no party or the owning module is disabled, so callers can
     * fall back instead of showing a broken reference.
     */
    public static function label(?string $type, mixed $id): ?string
    {
        if (! $type || ! $id) {
            return null;
        }

        $meta = self::map()[$type] ?? null;

        if (! $meta) {
            return null;
        }

        $model = $type::find($id);

        return $model?->{$meta['label']} ?: null;
    }

    /**
     * Splits a "Class:id" option value into its two parts.
     *
     * @return array{type: string, id: int}|null
     */
    public static function parse(?string $value): ?array
    {
        if (! $value || ! str_contains($value, ':')) {
            return null;
        }

        [$type, $id] = explode(':', $value, 2);

        if (! array_key_exists($type, self::map())) {
            return null;
        }

        return ['type' => $type, 'id' => (int) $id];
    }
}
