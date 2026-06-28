<?php

namespace Modules\Frontdeskcrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRateCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $rateCode = $this->route('rate_code');

        return [
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:20', Rule::unique('rate_codes', 'code')->ignore($rateCode)],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'prices' => 'nullable|array',
            'prices.*.room_type_id' => 'required|exists:room_types,id',
            'prices.*.price' => 'required|numeric|min:0|max:999999.99',
        ];
    }
}
