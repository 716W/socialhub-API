<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;

it('can list comments for a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    Comment::factory()->count(3)->create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = actingAs($user)->getJson("/api/posts/{$post->id}/comments");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'content', 'author']
            ]
        ]);
});

it('can show a specific comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = actingAs($user)->getJson("/api/comments/{$comment->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $comment->id);
});

it('can create a comment on a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = actingAs($user)->postJson("/api/posts/{$post->id}/comments", [
        'content' => 'This is a comment',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.content', 'This is a comment');

    $this->assertDatabaseHas('comments', [
        'content' => 'This is a comment',
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

it('can update a comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = actingAs($user)->putJson("/api/comments/{$comment->id}", [
        'content' => 'Updated comment',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.content', 'Updated comment');

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'content' => 'Updated comment',
    ]);
});

it('can delete a comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = actingAs($user)->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Comment deleted successfully.'
        ]);

    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});
