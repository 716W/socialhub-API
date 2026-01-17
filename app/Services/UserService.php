<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers()
    {
        return User::all();
    }

    public function getUserById(int $userId) {
        return User::findOrNew( $userId );
    }

    public function getUserByEmail(string $email) {
        return User::where('email', $email)->first();
    }

    public function updateUser(int $userId , User $user)
    {
        $currentUser = User::findOrFail($userId);
        if (!$currentUser == null) {
            $currentUser->update([
                'name' => $user->name,
                'email' => $user->email,
                'password' => Hash::make($user->password)
            ]);
            return 1 ;
        }
        return 0 ;
    }

    public function deleteUser(int $userId) {
        $currentUser = User::findOrFail($userId);

        if (!$currentUser->delete()) {
            return 0 ;
        }
        return 1;
    }

}