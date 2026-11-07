<?php

namespace App\Http\Requests\Petani;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vegetable_type_id' => ['sometimes', 'exists:vegetable_types,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity_available' => ['sometimes', 'numeric', 'min:0.01'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'min_order_qty' => ['sometimes', 'numeric', 'min:0.01'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'category' => ['sometimes', 'in:ecer,non_ecer'],
            'harvest_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'storage_method' => ['sometimes', 'in:suhu_ruang,dingin,lainnya'],
            'grade' => ['nullable', 'in:A,B,C'],
            'pickup_desa' => ['nullable', 'string', 'max:255'],
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}