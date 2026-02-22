<?php

use App\Models\User;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;

it('can list users when authenticated and verified', function () {
    User::factory()->count(3)->create();
    $user = User::factory()->create(['email_verified_at' => now(), 'role' => 'admin']);

    $response = actingAs($user)->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'Name', 'Email']
            ]
        ]);
});

it('can show a specific user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $targetUser = User::factory()->create();

    $response = actingAs($user)->getJson("/api/users/{$targetUser->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $targetUser->id);
});

it('can update a user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = actingAs($user)->putJson("/api/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.Name', 'Updated Name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('can delete a user', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'role' => 'admin']);
    $targetUser = User::factory()->create();

    $response = actingAs($user)->deleteJson("/api/users/{$targetUser->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});
