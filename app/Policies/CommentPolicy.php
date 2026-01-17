<?php

namespace App\Policies;

use App\Models\User;

class CommentPolicy
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

    public function update(User $user, \App\Models\Comment $comment)
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, \App\Models\Comment $comment)
    {
        return $user->id === $comment->user_id;
    }
}
