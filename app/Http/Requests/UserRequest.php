<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the user ID from the route if available (for update scenarios)
        $userId = $this->route('user');

        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name'     => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:255',
            'email'    => ($isUpdate ? 'sometimes|' : '') . 'required|email|unique:users,email' . ($userId ? ",{$userId}" : ''),
            'password' => $isUpdate ? 'nullable|string|min:5|confirmed' : 'required|string|min:5|confirmed',
        ];
    }
}
