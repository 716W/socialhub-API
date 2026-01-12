<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory(10)->create();

        // Randomly create posts for the users :-
        $post = Post::factory(10)
            ->recycle($user)
            ->create();

        // Randomly create comments for the posts :-
        Comment::factory(30)
            ->recycle($user)
            ->recycle($post)
            ->create();

        // Create an admin user :-
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);
    }
}
