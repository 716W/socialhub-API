<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|digits:6',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The verification code is required.',
            'code.digits'   => 'The verification code must be exactly 6 digits.',
        ];
    }
}
