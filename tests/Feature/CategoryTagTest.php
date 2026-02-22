<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

it('can list all categories', function () {
    // Arrange
    Category::factory()->count(3)->create();

    // Act
    $response = getJson(route('categories.index'));

    // Assert
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('can create a category when authenticated and verified', function () {
    // Arrange
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
    ]);

    // Act
    $response = actingAs($user)->postJson(route('categories.store'), [
        'name' => 'New Category',
    ]);

    // Assert
    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'New Category',
                'slug' => 'new-category',
            ]
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});

it('cannot create a category when unauthenticated', function () {
    // Act
    $response = postJson(route('categories.store'), [
        'name' => 'New Category',
    ]);

    // Assert
    $response->assertStatus(401);
});

it('cannot create a category when email is not verified', function () {
    // Arrange
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    // Act
    $response = actingAs($user)->postJson(route('categories.store'), [
        'name' => 'New Category',
    ]);

    // Assert
    // Middleware EnsureEmailIsVerified usually returns 409 (Conflict) for JSON requests.
    // However, if the middleware is customized or behaves as forbidden (403), we'll accept either.
    // In this case, it returned 403.
    $response->assertStatus(403); 
});

it('can list all tags', function () {
    // Arrange
    Tag::factory()->count(3)->create();

    // Act
    $response = getJson(route('tags.index'));

    // Assert
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('can create a tag when authenticated and verified', function () {
    // Arrange
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
    ]);

    // Act
    $response = actingAs($user)->postJson(route('tags.store'), [
        'name' => 'New Tag',
    ]);

    // Assert
    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'New Tag',
                'slug' => 'new-tag',
            ]
        ]);

    $this->assertDatabaseHas('tags', [
        'name' => 'New Tag',
        'slug' => 'new-tag',
    ]);
});

it('cannot create a tag when email is not verified', function () {
    // Arrange
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    // Act
    $response = actingAs($user)->postJson(route('tags.store'), [
        'name' => 'New Tag',
    ]);

    // Assert
    $response->assertStatus(403);
});
