<?php

namespace App\Http\Requests\AdminMitra;

use Illuminate\Foundation\Http\FormRequest;

class SaveOrganizationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'name' => [$req, 'string', 'max:255'],
            'type' => [$req, 'in:sppg,katering,koperasi_kdmp,umkm_olahan,kelompok_tani'],
            'address' => ['nullable', 'string'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ];
    }
}