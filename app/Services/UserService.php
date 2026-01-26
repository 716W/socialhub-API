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
        return User::findOrFail($userId);
    }

    public function getUserByEmail(string $email) {
        return User::where('email', $email)->first();
    }

    public function updateUser(int $userId, array $data)
    {
        $currentUser = User::findOrFail($userId);
        
        $updateData = [
            'name' => $data['name'] ?? $currentUser->name,
            'email' => $data['email'] ?? $currentUser->email,
        ];

        // Only update password if provided
        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // Only update role if provided (Controller handles authorization)
        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }

        $currentUser->update($updateData);
        
        return $currentUser;
    }

    public function deleteUser(int $userId) {
        $currentUser = User::findOrFail($userId);

        if (!$currentUser->delete()) {
            return 0 ;
        }
        return 1;
    }

}