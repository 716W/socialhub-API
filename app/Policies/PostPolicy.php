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

    public function update(User $user, \App\Models\Post $post)
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, \App\Models\Post $post)
    {
        return $user->id === $post->user_id;
    }
}
