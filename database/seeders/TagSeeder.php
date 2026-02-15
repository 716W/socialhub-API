<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'VueJS', 'slug' => 'vuejs'],
            ['name' => 'React', 'slug' => 'react'],
        ];

        foreach ($tags as $tag) {
            \App\Models\Tag::create($tag);
        }
    }
}
