<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy enforced in controller
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'currency' => ['sometimes', 'in:USD,EUR,TND'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
