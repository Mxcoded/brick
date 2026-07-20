<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'code',
        'name',
        'driver',
        'is_active',
        'is_default',
        'credentials',
        'settings',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Convenience accessor for a single credential value.
     */
    public function credential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }
}
