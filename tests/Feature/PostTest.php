<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;

it('can list posts', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Post::factory()->count(3)->create(['user_id' => $user->id]);

    $response = actingAs($user)->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'post_content', 'author']
            ]
        ]);
});

it('can show a specific post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = actingAs($user)->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $post->id);
});

it('can create a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $category = Category::factory()->create();

    $response = actingAs($user)->postJson('/api/posts', [
        'content' => 'This is a new post',
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.post_content', 'This is a new post');

    $this->assertDatabaseHas('posts', [
        'content' => 'This is a new post',
        'user_id' => $user->id,
    ]);
});

it('can create a post with an image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $file = UploadedFile::fake()->image('post.jpg');

    $response = actingAs($user)->postJson('/api/posts', [
        'content' => 'Post with image',
        'image' => $file,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', [
        'content' => 'Post with image',
    ]);
    
    $post = Post::where('content', 'Post with image')->first();
    $this->assertNotNull($post->image);
    Storage::disk('public')->assertExists($post->image);
});

it('can update a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = actingAs($user)->putJson("/api/posts/{$post->id}", [
        'content' => 'Updated post content',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.post_content', 'Updated post content');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'content' => 'Updated post content',
    ]);
});

it('can delete a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = actingAs($user)->deleteJson("/api/posts/{$post->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});
