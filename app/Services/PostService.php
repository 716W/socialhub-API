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
    public function GetAllPosts(int $pageSize = 10, array $filters = [], ?int $userId = null)
    {
        return Post::with('user')
            ->withCount(['likes as count_like'])
            ->where(function ($query) use ($userId) {
                $query->where('status', 'published');
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('content', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(!empty($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->latest()
            ->paginate($pageSize);
    }

    public function GetPostById(int $postId, ?int $userId = null)
    {
        $post = Post::with('user')
            ->withCount(['likes as count_like'])
            ->findOrFail($postId);

        if ($post->status === 'draft' && $post->user_id !== $userId) {
            abort(403, 'This post is not available.');
        }

        return $post;
    }

    public function CreatePost(array $arrData , int $userId) {
        return DB::transaction(function () use ($arrData, $userId) {
            
            if (isset($arrData['image']) && $arrData['image'] instanceof UploadedFile) {
                $arrData['image'] = $this->mediaService->upload($arrData['image'], 'posts');
            }
            $post = Post::create([
                'user_id'     => $userId,
                'title'       => $arrData['title'],
                'category_id' => $arrData['category_id'] ?? null,
                'content'     => $arrData['content'],
                'image'       => $arrData['image'] ?? null,
                'status'      => $arrData['status'] ?? 'draft',
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