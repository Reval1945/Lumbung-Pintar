<?php

namespace App\Http\Requests\Petani;

use Illuminate\Foundation\Http\FormRequest;

class UploadListingPhotoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_primary' => ['sometimes', 'boolean'],
            'use_ai' => ['sometimes', 'boolean'],
            // Hasil dari model offline di aplikasi (opsional). Jika ada, server tidak memanggil AI.
            'captured_offline' => ['sometimes', 'boolean'],
            'predicted_vegetable_type_id' => ['nullable', 'exists:vegetable_types,id'],
            'type_confidence' => ['nullable', 'numeric', 'between:0,1'],
            'predicted_grade' => ['nullable', 'in:A,B,C'],
            'grade_confidence' => ['nullable', 'numeric', 'between:0,1'],
        ];
    }
}