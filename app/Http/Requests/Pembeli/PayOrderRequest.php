<?php

namespace App\Http\Requests\Pembeli;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['method' => ['required', 'in:simulasi,transfer,qris,virtual_account,cod']];
    }
}