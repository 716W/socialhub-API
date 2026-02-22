<?php

use App\Models\Post;
use App\Models\User;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

it('can like a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = actingAs($user)->postJson("/api/posts/{$post->id}/like");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Post Like successfully.'
        ]);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

it('can unlike a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();
    
    // Like the post first
    $user->likedPosts()->attach($post->id);

    $response = actingAs($user)->postJson("/api/posts/{$post->id}/like");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Post unlike successfully.'
        ]);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});
