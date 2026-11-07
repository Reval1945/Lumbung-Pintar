<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveVegetableTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('vegetable_types', 'name')->ignore($this->route('id'))],
            'category' => ['nullable', 'string', 'max:255'],
            'default_unit' => ['sometimes', 'string', 'max:20'],
            'fresh_days' => ['required', 'integer', 'min:1', 'max:60'],
            'storage_notes' => ['nullable', 'string', 'max:255'],
            'pihps_commodity_code' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'tiers' => ['sometimes', 'array'],
            'tiers.*.days_after_fresh' => ['required', 'integer', 'min:1'],
            'tiers.*.discount_percent' => ['required', 'integer', 'between:10,40'],
            'tiers.*.label' => ['required', Rule::in(['fresh', 'segera_diskon', 'diskon_kesegaran', 'tidak_layak_jual'])],
        ];
    }
}