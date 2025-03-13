<?php

namespace Modules\Banquet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBanquetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer.name' => 'required|string|max:255',
            'customer.contact_person' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.email' => 'required|email',
            'event_date' => 'required|date',
            'guest_count' => 'required|integer|min:1',
            'location_times' => 'required|array|min:1',
            'location_times.*.room' => 'required|string',
            'location_times.*.setup_style' => 'required|string',
            'location_times.*.start_time' => 'required|date',
            'location_times.*.end_time' => 'required|date|after:location_times.*.start_time',
            'menu_selections' => 'required|array|min:1',
            'menu_selections.*.day_number' => 'required|integer|between:1,5',
            'menu_selections.*.meal_type' => 'required|string',
            'menu_selections.*.quantity' => 'required|integer|min:1',
            'menu_selections.*.price' => 'required|numeric|min:0',
            'locations' => 'required|array',
            'locations.*' => 'exists:banquet_locations,id',
            'menu_items' => 'required|array',
            'menu_items.*' => 'exists:banquet_menu_items,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
