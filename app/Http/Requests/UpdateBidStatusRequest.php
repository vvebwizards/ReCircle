<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBidStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy enforced in controller
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:accepted,rejected'],
        ];
    }
}
