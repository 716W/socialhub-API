<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology'],
            ['name' => 'Health', 'slug' => 'health'],
            ['name' => 'Travel', 'slug' => 'travel'],
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Education', 'slug' => 'education'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
