<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class LikeService{

    public function toggleLiked(int $userId , int $postId)
    {
        $user = User::findOrFail($userId);
        $post = Post::findOrFail($postId);

        // if user liked the post , toggle like off , else toggle like on
        return $user->likedPosts()->toggle($post->id);
    }
}