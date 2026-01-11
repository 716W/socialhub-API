<?php

namespace App\Services;
use App\Models\Post;

class PostService
{
    public function GetAllPosts(int $pageSize = 10)
    {
        return Post::with('user')->latest()->paginate($pageSize);
    }

    public function GetPostById(int $postId)
    {
        return Post::with('user')->findOrFail($postId);
    }

    public function CreatePost(array $arrData , int $userId) {
        return Post::create([
            'content' => $arrData['content'],
            'user_id'=> $userId,
        ]);
    }

    public function UpdatePost(Post $post, array $arrData) {
        $post->update([
            'content' => $arrData['content'] ?? $post->content,
        ]);

        return $post;
    }

    public function DeletePost(int $postId) {
        $post = Post::findOrFail($postId);
        return $post->delete();
    } 
}