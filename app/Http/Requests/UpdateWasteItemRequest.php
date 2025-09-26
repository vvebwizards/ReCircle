<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWasteItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization as needed
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*' => ['string'],
            'estimated_weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'condition' => ['sometimes', 'nullable', 'in:good,fixable,scrap'],
            'location' => ['sometimes', 'nullable', 'array'],
            'location.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
