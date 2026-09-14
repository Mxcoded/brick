<?php

namespace Modules\Banquet\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Normalizes lone JSON strings (e.g. stored as '"Jollof Rice"' by older
 * clients) into a proper array so consumers can always implode/iterate.
 */
class AsStringArray implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded) && $decoded !== '') {
            return [$decoded];
        }

        return trim((string) $value) === '' ? [] : [(string) $value];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode(array_values(is_array($value) ? $value : [$value]));
    }
}
