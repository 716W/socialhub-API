<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(int $postId)
    {
        return CommentResource::collection(
            $this->commentService->getCommentsForPost($postId)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentRequest $request , Post $post)
    {
        $data = $this->commentService->createComment(
            $request->validated('content') ,
            Auth::id(),
            $post->id
        );

        return new CommentResource($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new CommentResource($this->commentService->getCommentById($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommentRequest $request, string $postId)
    {
        $comment = $this->commentService->getCommentById($postId);
        if ($comment->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized! You can only update your own comments.',
            ], 403);
        }
        $data = $this->commentService->updateComment(
            $postId,
            $request->validated() ,
        );

        return new CommentResource($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = $this->commentService->getCommentById($id);
        if ($comment->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized! You can only delete your own comments.',
            ], 403);
        }
        $this->commentService->deleteComment($id);
        return response()->json([
            'message' => 'Comment deleted successfully.',
        ] , 200);
    }
}
