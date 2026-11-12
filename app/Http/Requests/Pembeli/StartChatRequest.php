<?php

namespace App\Http\Requests\Pembeli;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer', 'exists:users,id'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
        ];
    }
}