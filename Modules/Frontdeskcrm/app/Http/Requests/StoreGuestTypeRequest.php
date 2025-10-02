<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreGuestTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:guest_types,name',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7', // Hex color
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }
}
