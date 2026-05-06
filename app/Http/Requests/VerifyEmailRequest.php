<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $len = (int) config('auth_tokens.otp_length', 6);

        return [
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string', 'regex:/^\d{'.$len.'}$/'],
        ];
    }
}
