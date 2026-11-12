<?php

namespace App\Http\Requests\Pembeli;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer', 'exists:users,id'],
            'score' => ['required', 'integer', 'between:1,5'],
            'quality_score' => ['nullable', 'integer', 'between:1,5'],
            'punctuality_score' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}