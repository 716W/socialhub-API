<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase; // لتصفير الداتا بيس بعد كل اختبار

    public function test_user_can_update_profile_with_avatar()
    {
        // 1. تجهيز البيئة
        Storage::fake('public'); // قرص وهمي للاختبار
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg'); // صورة وهمية

        // 2. تنفيذ الطلب (Acting As User)
        $response = $this->actingAs($user)->postJson('/api/profile', [
            'username' => 'ali_dev',
            'bio' => 'Backend Wizard',
            'website' => 'https://ali.dev',
            'avatar' => $file,
        ]);

        // 3. التحقق من النتيجة (Assert)
        $response->assertStatus(200)
                 ->assertJsonPath('data.username', 'ali_dev'); // تأكد أن الرد يحوي الاسم

        // 4. التحقق من الداتا بيس
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'bio' => 'Backend Wizard',
            'username' => 'ali_dev'
        ]);

        // 5. التحقق من وجود الصورة (في القرص الوهمي)
        // لاحظ: نستخدم hashName لأن الـ MediaService تغير الاسم
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

    public function test_profile_validation_rejects_invalid_url()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile', [
            'website' => 'not-a-url', // رابط خطأ
        ]);

        $response->assertStatus(422) // كود خطأ الـ Validation
                 ->assertJsonValidationErrors(['website']);
    }
} 