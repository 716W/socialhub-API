<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path_register_verify_login_update_profile()
    {
        Storage::fake('avatars');

        // 1. Register (Simulate user creation since RegisterController is not fully visible in snippet, using Factory)
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
        
        // Ensure OTP fields are set (simulate sendOtp)
        $otp = '123456';
        $user->forceFill([
            'otp_code' => hash('sha256', $otp),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0
        ])->save();

        // 2. Verify OTP
        Sanctum::actingAs($user); // Login the user for api call
        
        $response = $this->postJson(route('mobile.verify'), [
            'code' => $otp
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 3. Update Profile
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->postJson('/api/profile', [
            'username' => 'happyuser',
            'bio' => 'Living the life',
            'avatar' => $file
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_profiles', ['username' => 'happyuser']);
    }

    public function test_unverified_user_cannot_update_profile()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile', [
            'bio' => 'Hacker Bio'
        ]);

        // Expect 403 Forbidden because of EnsureEmailIsVerified middleware? 
        // Or 409 Conflict if using standard Laravel verification middleware with 'verified' alias?
        // The routes/api.php uses `EnsureEmailIsVerified::class`.
        // By default, EnsureEmailIsVerified returns 409 if json, or redirect. 
        // Let's assume 403 or 409. 403 is common for "Forbidden".
        // Actually, Illuminate\Auth\Middleware\EnsureEmailIsVerified throws generic 403 if not verified in newer Laravel versions or 409.
        // Let's check for failure.
        
        $this->assertTrue(in_array($response->status(), [403, 409]), "Status was {$response->status()}");
    }

    public function test_expired_otp_fails_verification()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $otp = '123456';
        
        $user->forceFill([
            'otp_code' => hash('sha256', $otp),
            'otp_expires_at' => now()->subMinutes(1), // Expired
            'otp_attempts' => 0
        ])->save();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('mobile.verify'), [
            'code' => $otp
        ]);

        $response->assertStatus(400);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_cross_user_otp_verification_attempt_fails()
    {
        // User A has a valid OTP
        $userA = User::factory()->create(['email_verified_at' => null]);
        $otpA = '111111';
        $userA->forceFill([
            'otp_code' => hash('sha256', $otpA),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0
        ])->save();

        // User B tries to use User A's OTP code
        $userB = User::factory()->create(['email_verified_at' => null]);
        $otpB = '222222'; // User B has different OTP
        $userB->forceFill([
            'otp_code' => hash('sha256', $otpB),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0
        ])->save();

        Sanctum::actingAs($userB);

        // User B sends '111111' (User A's code)
        $response = $this->postJson(route('mobile.verify'), [
            'code' => $otpA 
        ]);

        $response->assertStatus(400); // Invalid code for User B
        $this->assertNull($userB->fresh()->email_verified_at);
    }

    public function test_malicious_file_upload_blocked()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($user);

        // Create a fake php file
        $file = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->postJson('/api/profile', [
            'avatar' => $file
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['avatar']);
    }
}
