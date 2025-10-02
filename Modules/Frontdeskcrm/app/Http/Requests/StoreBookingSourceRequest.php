<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBookingSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:booking_sources,name',
            'description' => 'nullable|string|max:500',
            'type' => 'nullable|in:online,offline,partner',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }
}
