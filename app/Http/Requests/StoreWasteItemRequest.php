<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWasteItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization as needed
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
            'estimated_weight' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'in:good,fixable,scrap'],
            'location' => ['nullable', 'array'],
            'location.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
