<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Get all categories.
     *
     * @param string|array|null $include
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories(string|array|null $include = null): Collection
    {
        $query = Category::query();

        if ($include) {
            $query->with($include);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function createCategory(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);

        return Category::create($data);
    }
}