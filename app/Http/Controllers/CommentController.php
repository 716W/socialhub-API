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
     * Get All Comments for Post
     * 
     * Retrieve all comments for a specific post.
     * 
     * @tag Comments
     * @response 200 {"data": [{"id": 1, "content": "Great post!", "user_id": 2, "post_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}]}
     */
    public function index(int $postId)
    {
        $comments = $this->commentService->getCommentsForPost($postId);
        $comments->getCollection()->transform(fn ($comment) => (new CommentResource($comment))->toArray(request()));

        return $this->paginatedResponse($comments, 'Comments retrieved successfully');
    }

    /**
     * Create New Comment
     * 
     * Add a new comment to a specific post.
     * 
     * @tag Comments
     * @bodyParam content string required The content of the comment.
     * @response 201 {"data": {"id": 1, "content": "Great post!", "user_id": 1, "post_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}}
     * @response 422 {"message": "Validation errors", "errors": {"content": ["The content field is required."]}}
     */
    public function store(CommentRequest $request , Post $post)
    {
        $data = $this->commentService->createComment(
            $request->validated('content') ,
            Auth::id(),
            $post->id
        );
        return $this->createResponse(new CommentResource($data), 'Comment created successfully');
    }

    /**
     * Get Comment by ID
     * 
     * Retrieve a specific comment by its ID.
     * 
     * @tag Comments
     * @response 200 {"data": {"id": 1, "content": "Great post!", "user_id": 2, "post_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}}
     * @response 404 {"message": "Comment not found"}
     */
    public function show(string $id)
    {
        $comment = $this->commentService->getCommentById($id);
        return $this->successResponse(new CommentResource($comment), 'Comment retrieved successfully');
    }

    /**
     * Update Comment
     * 
     * Update an existing comment. Only the comment owner can update it.
     * 
     * @tag Comments
     * @bodyParam content string required The updated content of the comment.
     * @response 200 {"data": {"id": 1, "content": "Updated comment", "user_id": 1, "post_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T13:00:00.000000Z"}}
     * @response 403 {"message": "Unauthorized! You can only update your own comments."}
     * @response 404 {"message": "Comment not found"}
     */
    public function update(CommentRequest $request, string $id)
    {
        $comment = $this->commentService->getCommentById($id);
        if ($comment->user_id != Auth::id()) {
            return $this->errorResponse('Unauthorized! You can only update your own comments.', 403);
        }
        $data = $this->commentService->updateComment(
            $id,
            $request->validated() ,
        );

        return $this->successResponse(new CommentResource($data), 'Comment updated successfully');
    }

    /**
     * Delete Comment
     * 
     * Delete a comment. Only the comment owner can delete it.
     * 
     * @tag Comments
     * @response 200 {"message": "Comment deleted successfully."}
     * @response 403 {"message": "Unauthorized! You can only delete your own comments."}
     * @response 404 {"message": "Comment not found"}
     */
    public function destroy(string $id)
    {
        $comment = $this->commentService->getCommentById($id);
        if ($comment->user_id != Auth::id()) {
            return $this->errorResponse('Unauthorized! You can only delete your own comments.', 403);
        }

        $this->commentService->deleteComment($id);
        return $this->successResponse(null, 'Comment deleted successfully.');
    }
}
