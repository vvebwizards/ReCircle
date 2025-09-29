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
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'estimated_weight' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'in:good,fixable,scrap'],
            'location' => ['required', 'array'],
            'location.lat' => ['required', 'numeric', 'between:-90,90'],
            'location.lng' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A title is required.',
            'title.max' => 'Title may not be greater than :max characters.',
            'images.required' => 'Please upload at least one image (up to 10).',
            'images.array' => 'Images must be an array of files.',
            'images.max' => 'You may not upload more than :max images.',
            'images.*.required' => 'Each selected image is invalid.',
            'images.*.image' => 'Every file must be a valid image.',
            'images.*.mimes' => 'Images must be a type of: :values.',
            'images.*.max' => 'Images must not exceed 2MB each.',
            'estimated_weight.required' => 'Provide an estimated weight (0 or greater).',
            'estimated_weight.numeric' => 'Estimated weight must be numeric.',
            'estimated_weight.min' => 'Estimated weight can\'t be negative.',
            'condition.required' => 'Select a condition (good, fixable, or scrap).',
            'condition.in' => 'Condition must be one of good, fixable, or scrap.',
            'location.required' => 'Location details are required.',
            'location.array' => 'Location must be an object containing latitude and longitude.',
            'location.lat.required' => 'Latitude is required.',
            'location.lat.numeric' => 'Latitude must be numeric.',
            'location.lat.between' => 'Latitude must be between -90 and 90.',
            'location.lng.required' => 'Longitude is required.',
            'location.lng.numeric' => 'Longitude must be numeric.',
            'location.lng.between' => 'Longitude must be between -180 and 180.',
            'notes.max' => 'Notes may not exceed :max characters.',
        ];
    }
}
