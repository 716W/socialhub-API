<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function register(string $name , string $email , string $password)
    {
        // create user :-
        $user = User::create([
            'name' => $name,
            'email'=> $email,
            'password' => Hash::make($password),
        ]);

        // Generate Token :-
        $token = $user->createToken('auth_token')->plainTextToken;

        // use event to send email verification :-
        event(new Registered($user));

        // return DTO :-
        return [
            'user'  => $user ,
            'token' => $token,
        ];
    }
}