<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\MediaService;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected PostService $postService , protected MediaService $mediaService)
    {
        
    }
    
    /**
     * Get All Posts
     * 
     * Retrieve a paginated list of all posts with user and comment information.
     * 
     * @tag Posts
     * @response 200 {"data": [{"id": 1, "content": "Sample post", "user_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}]}
     */
    public function index()
    {
        $posts = $this->postService->GetAllPosts();

        // Transform paginated items to resource arrays so pagination helper can wrap them
        $posts->getCollection()->transform(fn ($post) => (new PostResource($post))->toArray(request()));

        return $this->paginatedResponse($posts, 'Posts retrieved successfully');
    }

    /**
     * Create New Post
     * 
     * Create a new post for the authenticated user.
     * 
     * @tag Posts
     * @bodyParam content string required The content of the post.
     * @response 201 {"data": {"id": 1, "content": "My new post", "user_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}}
     * @response 422 {"message": "Validation errors", "errors": {"content": ["The content field is required."]}}
     */
    
    public function store(PostRequest $request)
    {
        $data = $this->postService->CreatePost(
            $request->validated(),
            Auth::id()
        );

        return $this->createResponse(new PostResource($data), 'Post created successfully');
    }

    /**
     * Get Post by ID
     * 
     * Retrieve a specific post by its ID with user and comment details.
     * 
     * @tag Posts
     * @response 200 {"data": {"id": 1, "content": "Sample post", "user_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T12:00:00.000000Z"}}
     * @response 404 {"message": "Post not found"}
     */
    public function show(string $id)
    {
        $post = $this->postService->GetPostById($id);

        return $this->successResponse(new PostResource($post), 'Post retrieved successfully');
    }

    /**
     * Update Post
     * 
     * Update an existing post. Only the post owner can update it.
     * 
     * @tag Posts
     * @bodyParam content string required The updated content of the post.
     * @response 200 {"data": {"id": 1, "content": "Updated post content", "user_id": 1, "created_at": "2026-01-10T12:00:00.000000Z", "updated_at": "2026-01-10T13:00:00.000000Z"}}
     * @response 403 {"message": "Unauthorized! You can only update your own posts."}
     * @response 404 {"message": "Post not found"}
     */
    public function update(PostRequest $request, string $id)
    {
        $post = Post::findOrFail($id);

        \Illuminate\Support\Facades\Gate::authorize('update', $post);

        $data = $this->postService->UpdatePost(
            $post,
            $request->validated()
        );

        return $this->successResponse(new PostResource($data), 'Post updated successfully');
    }

    /**
     * Delete Post
     * 
     * Delete a post. Only the post owner can delete it.
     * 
     * @tag Posts
     * @response 204
     * @response 403 {"message": "Unauthorized! You can only delete your own posts."}
     * @response 404 {"message": "Post not found"}
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        
        \Illuminate\Support\Facades\Gate::authorize('delete', $post);

        $this->postService->DeletePost($id);
        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
