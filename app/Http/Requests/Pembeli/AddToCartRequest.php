<?php

namespace App\Http\Requests\Pembeli;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}