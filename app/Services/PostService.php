<?php

namespace App\Services;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\post;

class PostService
{
    public function __construct(protected MediaService $mediaService)
    {
    }
    public function GetAllPosts(int $pageSize = 10)
    {
        return Post::with('user')
            ->withCount(['likes as count_like'])
            ->latest()
            ->paginate($pageSize);
    }

    public function GetPostById(int $postId)
    {
        return Post::with('user')
            ->withCount(['likes as count_like'])
            ->findOrFail($postId);
    }

    public function CreatePost(array $arrData , int $userId) {
        return DB::transaction(function () use ($arrData, $userId) {
            
            if (isset($arrData['image']) && $arrData['image'] instanceof UploadedFile) {
                $arrData['image'] = $this->mediaService->upload($arrData['image'], 'posts');
            }
            $post = Post::create([
                'user_id'     => $userId,
                'category_id' => $arrData['category_id'] ?? null,
                'content'     => $arrData['content'],
                'image'       => $arrData['image'] ?? null,
            ]);

            //check if tags are provided and sync them with the post
            if (!empty($arrData['tags'])) {
                $post->tags()->sync($arrData['tags']);
            }

            return $post;
        });
    }

    public function UpdatePost(Post $post, array $arrData) {
       return DB::transaction(function () use ($post , $arrData){
            if(isset($arrData['image']) && $arrData['image'] instanceof UploadedFile) {
                // delete old image if exists
                if ($post->image){
                    $this->mediaService->delete($post->image);
                }
                $arrData['image'] = $this->mediaService->upload($arrData['image'], 'posts');
            }

            $post->update($arrData);
            // check for tags and sync them with the post
            if (isset($arrData['tags'])) {
                $post->tags()->sync($arrData['tags']);
            }
            return $post->fresh(['tags','category']);
       });
    }

    public function DeletePost(int $postId) {
        $post = Post::findOrFail($postId);
        if ($post->image)
            $this->mediaService->delete($post->image);
        return $post->delete();
    } 
}