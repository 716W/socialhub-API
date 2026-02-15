<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username ?? null,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'website' => $this->website,
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
