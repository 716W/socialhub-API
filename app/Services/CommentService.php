<?php

namespace App\Services;

use App\Models\Comment ;
// use App\Models\Post;

class CommentService
{
    public function getCommentById(int $commentId)
    {
        return Comment::with('user')
                    ->findOrFail($commentId);
    }

    public function getCommentsForPost(int $postId, int $pageSize = 10)
    {
        return Comment::where('post_id' , $postId)
                ->with('user')
                ->latest()
                ->paginate($pageSize);
    }
    public function createComment(string $content , int $userId , int $postId)
    {
        return Comment::create([
            'content' => $content ,
            'user_id'=> $userId,
            'post_id'=> $postId,
        ]);
    }

    public function updateComment(int $commentId , array $arrData)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update([
            'content'=> $arrData['content'],
        ]);
        return $comment->fresh();
    }

    public function deleteComment(int $commentId)
    {
        return (bool) Comment::destroy($commentId);
    }
}
