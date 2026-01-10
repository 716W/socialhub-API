<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected PostService $postService)
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->postService->GetAllPosts());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $data = $this->postService->CreatePost(
            $request->validated(),
            Auth::id()
        );
        return response()->json($data , 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json($this->postService->GetPostById((int) $id), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, string $id)
    {
        $post = Post::findOrFail($id);
        // check if the authenticated user is the owner of the post
        if ($post->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized! You can only update your own posts.', 
            ], 403);
        }
        $data = $this->postService->UpdatePost($post, $request->validated());

        return response()->json($data , 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id != Auth::id()) {
            return response()->json([
                'message'=> 'Unauthorized! You can only delete your own posts.',
            ], 403);
        }
        $this->postService->DeletePost((int) $id);
        return response()->json(null , 204);
    }
}
