<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('token') && ! $this->has('otp')) {
            $this->merge([
                'otp' => $this->input('token'),
            ]);
        }
    }

    public function rules(): array
    {
        $len = (int) config('auth_tokens.otp_length', 6);

        return [
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string', 'regex:/^\d{'.$len.'}$/'],
            'new_password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }
}
