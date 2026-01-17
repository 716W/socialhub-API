<?php

namespace App\Policies;

use App\Models\User;

class PostPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before($user , $ability)
    {
        if ($user->role == 'admin') {
            return true ;
        }
    }
}
