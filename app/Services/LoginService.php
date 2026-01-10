<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function Login(string $email , string $password)
    {
        // check user existence by email :-
        $user = User::where('email',$email)->first();

        // verify password :-
        if (! $user || ! Hash::check($password, $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate Token :-
        $token = $user->createToken('auth_token')->plainTextToken;

        // return DTO :-
        return [
            'user' => $user,
            'token'=> $token
        ];
    }
}