<?php 

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TagService
{
    /**
     * Get all tags.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags(): Collection
    {
        return Tag::orderBy("name", "asc")->get();
    }

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return \App\Models\Tag
     */
    public function createTag(array $data): Tag
    {
        $data['slug'] = Str::slug($data['name']);

        return Tag::create($data);
    }
}