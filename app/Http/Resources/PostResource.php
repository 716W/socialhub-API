<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id , 
            'post_content' => $this->content ,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'author' => [
                'id' => $this->user->id ,
                'name' => $this->user->name ,
                'avatar_url' => $this->user->profile && $this->user->profile->avatar ? asset('storage/' . $this->user->profile->avatar) : null,
            ] ,
            'count_like' => $this->count_like ?? 0 ,
            'posted_at' => $this->created_at->format('Y-m-d') ,
        ];
    }
}
