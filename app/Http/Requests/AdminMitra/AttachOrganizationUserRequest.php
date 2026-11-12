<?php

namespace App\Http\Requests\AdminMitra;

use Illuminate\Foundation\Http\FormRequest;

class AttachOrganizationUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['user_id' => ['required', 'integer', 'exists:users,id']];
    }
}