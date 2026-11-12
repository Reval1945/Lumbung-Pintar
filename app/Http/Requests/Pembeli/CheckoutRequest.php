<?php

namespace App\Http\Requests\Pembeli;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string'],
            'needed_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}