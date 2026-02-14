<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'username' => ['nullable','string','max:20' , 'unique:user_profiles,username,' . $userId . ',user_id'],
            'bio'      => ['nullable','string','max:500' ],
            'website'  => ['nullable','url','max:255' ],
            'avatar'   => ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048']
        ];
    }
    public function messages(): array
    {
        return [
            'username.unique'  => 'The username is already used , please choose other !',
            'website.url'      => 'You must enter valid url start with (http or https) .'
        ];
    }
}
