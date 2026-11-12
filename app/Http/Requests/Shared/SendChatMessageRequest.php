<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:2000']];
    }
}