<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

// ──────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────

function verifiedUser(): User
{
    return User::factory()->create(['email_verified_at' => now()]);
}

// ──────────────────────────────────────────────
// Index – visibility rules
// ──────────────────────────────────────────────

it('returns only published posts to a regular user', function () {
    $user    = verifiedUser();
    $other   = verifiedUser();

    Post::factory()->published()->count(3)->create(['user_id' => $other->id]);
    Post::factory()->draft()->count(2)->create(['user_id' => $other->id]);

    $response = actingAs($user)->getJson('/api/posts');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

it('includes the authors own draft posts in the listing', function () {
    $user  = verifiedUser();
    $other = verifiedUser();

    // 2 published by another user, 1 draft owned by $user
    Post::factory()->published()->count(2)->create(['user_id' => $other->id]);
    Post::factory()->draft()->create(['user_id' => $user->id]);

    $response = actingAs($user)->getJson('/api/posts');

    $response->assertStatus(200);
    // user sees 2 published + 1 own draft = 3
    expect($response->json('data'))->toHaveCount(3);
});

it('does not expose another users draft posts in the listing', function () {
    $user  = verifiedUser();
    $other = verifiedUser();

    Post::factory()->draft()->count(3)->create(['user_id' => $other->id]);

    $response = actingAs($user)->getJson('/api/posts');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(0);
});

it('returns paginated results', function () {
    $user = verifiedUser();
    Post::factory()->published()->count(15)->create(['user_id' => $user->id]);

    $response = actingAs($user)->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'meta', 'links']);
    expect($response->json('data'))->toHaveCount(10); // default page size
});

// ──────────────────────────────────────────────
// Index – searching
// ──────────────────────────────────────────────

it('can search posts by title', function () {
    $user = verifiedUser();
    Post::factory()->published()->create(['user_id' => $user->id, 'title' => 'Laravel tips and tricks']);
    Post::factory()->published()->create(['user_id' => $user->id, 'title' => 'Vue.js guide']);

    $response = actingAs($user)->getJson('/api/posts?search=laravel');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.post_content'))->not->toBeNull(); // post_content key must exist
});

it('can search posts by content', function () {
    $user = verifiedUser();
    Post::factory()->published()->create(['user_id' => $user->id, 'content' => 'Deep dive into Eloquent ORM']);
    Post::factory()->published()->create(['user_id' => $user->id, 'content' => 'CSS grid tricks']);

    $response = actingAs($user)->getJson('/api/posts?search=eloquent');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

it('returns empty results when search has no matches', function () {
    $user = verifiedUser();
    Post::factory()->published()->count(3)->create(['user_id' => $user->id]);

    $response = actingAs($user)->getJson('/api/posts?search=xyzunmatchable99');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(0);
});

// ──────────────────────────────────────────────
// Index – category filtering
// ──────────────────────────────────────────────

it('can filter posts by category_id', function () {
    $user      = verifiedUser();
    $category  = Category::factory()->create();
    $otherCat  = Category::factory()->create();

    Post::factory()->published()->count(2)->create(['user_id' => $user->id, 'category_id' => $category->id]);
    Post::factory()->published()->create(['user_id' => $user->id, 'category_id' => $otherCat->id]);

    $response = actingAs($user)->getJson("/api/posts?category_id={$category->id}");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

it('can combine search and category_id filters', function () {
    $user     = verifiedUser();
    $category = Category::factory()->create();

    Post::factory()->published()->create([
        'user_id'     => $user->id,
        'title'       => 'Filtering with Laravel',
        'category_id' => $category->id,
    ]);
    Post::factory()->published()->create([
        'user_id'     => $user->id,
        'title'       => 'Filtering with Vue',
        'category_id' => Category::factory()->create()->id,
    ]);

    $response = actingAs($user)->getJson("/api/posts?search=filtering&category_id={$category->id}");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

// ──────────────────────────────────────────────
// Show – draft security
// ──────────────────────────────────────────────

it('can show a published post', function () {
    $user = verifiedUser();
    $post = Post::factory()->published()->create(['user_id' => $user->id]);

    actingAs($user)->getJson("/api/posts/{$post->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.id', $post->id);
});

it('author can view their own draft post', function () {
    $user = verifiedUser();
    $post = Post::factory()->draft()->create(['user_id' => $user->id]);

    actingAs($user)->getJson("/api/posts/{$post->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.id', $post->id);
});

it('returns 403 when a non-author requests a draft post', function () {
    $author   = verifiedUser();
    $visitor  = verifiedUser();
    $post     = Post::factory()->draft()->create(['user_id' => $author->id]);

    actingAs($visitor)->getJson("/api/posts/{$post->id}")
        ->assertStatus(403);
});

it('returns 404 for a non-existent post', function () {
    $user = verifiedUser();

    actingAs($user)->getJson('/api/posts/99999')
        ->assertStatus(404);
});

// ──────────────────────────────────────────────
// Store – validation
// ──────────────────────────────────────────────

it('requires title when creating a post', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', ['content' => 'No title here'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('requires content when creating a post', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', ['title' => 'No content here'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['content']);
});

it('validates that category_id exists in categories table', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', [
        'title'       => 'Post title',
        'content'     => 'Post content',
        'category_id' => 99999,
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['category_id']);
});

it('validates that tag ids exist in tags table', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', [
        'title'   => 'Post title',
        'content' => 'Post content',
        'tags'    => [99999],
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['tags.0']);
});

it('validates status must be draft or published', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', [
        'title'   => 'Post title',
        'content' => 'Post content',
        'status'  => 'invalid_status',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['status']);
});

// ──────────────────────────────────────────────
// Store – success cases
// ──────────────────────────────────────────────

it('can create a post with required fields', function () {
    $user = verifiedUser();

    $response = actingAs($user)->postJson('/api/posts', [
        'title'   => 'My first post',
        'content' => 'Some great content',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.post_content', 'Some great content');

    $this->assertDatabaseHas('posts', [
        'title'   => 'My first post',
        'user_id' => $user->id,
    ]);
});

it('creates a post with draft status by default', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', [
        'title'   => 'Draft post',
        'content' => 'Content',
    ])->assertStatus(201);

    $this->assertDatabaseHas('posts', [
        'title'  => 'Draft post',
        'status' => 'draft',
    ]);
});

it('can create a post as published', function () {
    $user = verifiedUser();

    actingAs($user)->postJson('/api/posts', [
        'title'   => 'Published post',
        'content' => 'Content',
        'status'  => 'published',
    ])->assertStatus(201);

    $this->assertDatabaseHas('posts', [
        'title'  => 'Published post',
        'status' => 'published',
    ]);
});

it('can create a post with category and tags', function () {
    $user     = verifiedUser();
    $category = Category::factory()->create();
    $tags     = Tag::factory()->count(2)->create();

    $response = actingAs($user)->postJson('/api/posts', [
        'title'       => 'Tagged post',
        'content'     => 'Content',
        'status'      => 'published',
        'category_id' => $category->id,
        'tags'        => $tags->pluck('id')->toArray(),
    ]);

    $response->assertStatus(201);

    $post = Post::where('title', 'Tagged post')->first();
    expect($post)->not->toBeNull();
    expect($post->category_id)->toBe($category->id);
    expect($post->tags)->toHaveCount(2);
});

it('can create a post with an image', function () {
    Storage::fake('public');
    $user = verifiedUser();
    $file = UploadedFile::fake()->image('post.jpg');

    $response = actingAs($user)->postJson('/api/posts', [
        'title'   => 'Post with image',
        'content' => 'Content',
        'image'   => $file,
    ]);

    $response->assertStatus(201);

    $post = Post::where('title', 'Post with image')->first();
    $this->assertNotNull($post->image);
    Storage::disk('public')->assertExists($post->image);
});

// ──────────────────────────────────────────────
// Update – validation
// ──────────────────────────────────────────────

it('validates status on update', function () {
    $user = verifiedUser();
    $post = Post::factory()->create(['user_id' => $user->id]);

    actingAs($user)->putJson("/api/posts/{$post->id}", [
        'title'  => 'Updated',
        'status' => 'bad_value',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['status']);
});

it('can update a post content only (partial update)', function () {
    $user = verifiedUser();
    $post = Post::factory()->create(['user_id' => $user->id]);

    actingAs($user)->putJson("/api/posts/{$post->id}", [
        'content' => 'Updated content only',
    ])->assertStatus(200)
      ->assertJsonPath('data.post_content', 'Updated content only');

    $this->assertDatabaseHas('posts', [
        'id'      => $post->id,
        'content' => 'Updated content only',
    ]);
});

it('can update a post title and status', function () {
    $user = verifiedUser();
    $post = Post::factory()->draft()->create(['user_id' => $user->id]);

    actingAs($user)->putJson("/api/posts/{$post->id}", [
        'title'  => 'Updated title',
        'status' => 'published',
    ])->assertStatus(200);

    $this->assertDatabaseHas('posts', [
        'id'     => $post->id,
        'title'  => 'Updated title',
        'status' => 'published',
    ]);
});

it('cannot update another users post', function () {
    $owner   = verifiedUser();
    $visitor = verifiedUser();
    $post    = Post::factory()->create(['user_id' => $owner->id]);

    actingAs($visitor)->putJson("/api/posts/{$post->id}", [
        'content' => 'Hacked content',
    ])->assertStatus(403);
});

// ──────────────────────────────────────────────
// Delete
// ──────────────────────────────────────────────

it('can delete a post', function () {
    $user = verifiedUser();
    $post = Post::factory()->create(['user_id' => $user->id]);

    actingAs($user)->deleteJson("/api/posts/{$post->id}")
        ->assertStatus(204);

    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});

it('cannot delete another users post', function () {
    $owner   = verifiedUser();
    $visitor = verifiedUser();
    $post    = Post::factory()->create(['user_id' => $owner->id]);

    actingAs($visitor)->deleteJson("/api/posts/{$post->id}")
        ->assertStatus(403);
});

// ──────────────────────────────────────────────
// Auth guard
// ──────────────────────────────────────────────

it('requires authentication to access posts', function () {
    getJson('/api/posts')->assertStatus(401);
    postJson('/api/posts', [])->assertStatus(401);
});
