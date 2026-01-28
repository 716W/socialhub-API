<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

test('email verification link marks user as verified', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $this->getJson($url)
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Email verified successfully',
        ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('email verification returns already verified when user already verified', function () {
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $this->getJson($url)
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Email already verified',
        ]);
});

test('email verification rejects invalid hash even if URL is signed', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1('wrong@example.com'),
        ]
    );

    $this->getJson($url)
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid verification link',
        ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('resend verification sends notification for unverified user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson([
            'success' => true,
            'message' => 'Verification link sent',
        ]);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verification-notification endpoint sends notification for unverified user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/email/verification-notification')
        ->assertStatus(202)
        ->assertJson([
            'success' => true,
            'message' => 'Verification link sent',
        ]);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('resend verification does not send when already verified', function () {
    Notification::fake();

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/email/resend')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Email already verified',
        ]);

    Notification::assertNothingSent();
});

test('resend verification requires authentication', function () {
    $this->postJson('/api/email/resend')
        ->assertStatus(401);
});

test('unverified user cannot access verified-only routes', function () {
    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/posts')->assertStatus(403);
});
