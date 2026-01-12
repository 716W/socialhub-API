<?php

namespace App\Http\Controllers;

use App\Services\LikeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(protected LikeService $likeService) {}
    
    /**
     * Toggle Like on Post
     * 
     * Like or unlike a post. If the user has already liked the post, it will be unliked. If not, it will be liked.
     * 
     * @tag Likes
     * @response 200 {"message": "Post Like successfully.", "status": {"attached": [1], "detached": []}}
     * @response 200 {"message": "Post unlike successfully.", "status": {"attached": [], "detached": [1]}}
     * @response 404 {"message": "Post not found"}
     */
    public function __invoke(int $postId)
    {
        $result = $this->likeService->toggleLiked(
            Auth::id() ,
            $postId
        );
        $status = count($result['attached']) > 0 ? 'Like' : 'unlike';

        return response()->json([
            'message' => "Post $status successfully.",
            'status' => $result
        ]);
    }
}
