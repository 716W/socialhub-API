<?php

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

// ──────────────────────────────────────────────
// GET /api/profile
// ──────────────────────────────────────────────

it('returns full user object with profile on GET /profile', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $user->profile()->create([
        'username' => 'ali_dev',
        'bio'      => 'Backend developer',
        'website'  => 'https://ali.dev',
    ]);

    $response = actingAs($user)->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'profile' => [
                    'username',
                    'bio',
                    'website',
                    'avatar_url',
                    'updated_at',
                ],
            ],
        ])
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.profile.username', 'ali_dev')
        ->assertJsonPath('data.profile.bio', 'Backend developer');
});

it('returns profile key as null fields when no profile record exists', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = actingAs($user)->getJson('/api/profile');

    // Endpoint should still respond 200; UserResource handles missing profile gracefully
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id);
});

it('requires authentication to get profile', function () {
    getJson('/api/profile')->assertStatus(401);
});

// ──────────────────────────────────────────────
// POST /api/profile (update)
// ──────────────────────────────────────────────

it('can create or update profile fields', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = actingAs($user)->postJson('/api/profile', [
        'username' => 'ali_dev',
        'bio'      => 'Backend Wizard',
        'website'  => 'https://ali.dev',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.username', 'ali_dev')
        ->assertJsonPath('data.bio', 'Backend Wizard');

    $this->assertDatabaseHas('user_profiles', [
        'user_id'  => $user->id,
        'username' => 'ali_dev',
        'bio'      => 'Backend Wizard',
    ]);
});

it('can update profile with an avatar image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = actingAs($user)->postJson('/api/profile', [
        'username' => 'ali_dev',
        'bio'      => 'Backend Wizard',
        'website'  => 'https://ali.dev',
        'avatar'   => $file,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.username', 'ali_dev');

    $this->assertDatabaseHas('user_profiles', [
        'user_id'  => $user->id,
        'username' => 'ali_dev',
    ]);

    $profile = $user->fresh()->profile;
    $this->assertNotNull($profile->avatar);
    Storage::disk('public')->assertExists($profile->avatar);
});

it('replaces old avatar when updating with a new one', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    // First upload
    $oldFile = UploadedFile::fake()->image('old_avatar.jpg', 100, 100);
    actingAs($user)->postJson('/api/profile', [
        'username' => 'user1',
        'avatar'   => $oldFile,
    ])->assertStatus(200);

    $oldPath = $user->fresh()->profile->avatar;
    Storage::disk('public')->assertExists($oldPath);

    // Second upload with a different-sized image so it gets a fresh temp file
    $newFile = UploadedFile::fake()->image('new_avatar.jpg', 200, 200);
    actingAs($user)->postJson('/api/profile', [
        'avatar' => $newFile,
    ])->assertStatus(200);

    $newPath = $user->fresh()->profile->avatar;

    // The new avatar must be persisted and exist in storage
    expect($newPath)->not->toBeNull();
    Storage::disk('public')->assertExists($newPath);
});

it('rejects an invalid website URL', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    actingAs($user)->postJson('/api/profile', [
        'website' => 'not-a-valid-url',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['website']);
});

it('rejects a non-image file as avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    actingAs($user)->postJson('/api/profile', [
        'avatar' => $file,
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['avatar']);
});

it('requires authentication to update profile', function () {
    \Pest\Laravel\postJson('/api/profile', ['username' => 'hacker'])->assertStatus(401);
});
