<?php

namespace App\Http\Requests\Petani;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vegetable_type_id' => ['required', 'exists:vegetable_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity_available' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:20'],
            'min_order_qty' => ['nullable', 'numeric', 'min:0.01'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'in:ecer,non_ecer'],
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
            'storage_method' => ['required', 'in:suhu_ruang,dingin,lainnya'],
            'grade' => ['nullable', 'in:A,B,C'],
            'pickup_desa' => ['nullable', 'string', 'max:255'],
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}