<?php

namespace App\Http\Controllers;

use App\Services\LikeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(protected LikeService $likeService) {}
    /**
     * Handle the incoming request.
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
