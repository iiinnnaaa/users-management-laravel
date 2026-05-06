<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthOtpFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_email_verification_before_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonMissingPath('token');
        $response->assertJsonPath('user.is_verified', false);
        Mail::assertSent(EmailVerificationOtpMail::class);
    }

    public function test_login_rejects_unverified_users(): void
    {
        $user = User::factory()->create([
            'email' => 'u@example.com',
            'password' => 'secret123',
            'is_verified' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertForbidden();
    }

    public function test_forgot_password_returns_same_message_when_user_missing(): void
    {
        Mail::fake();

        $a = $this->postJson('/api/auth/forgot-password', ['email' => 'none@example.com']);
        $user = User::factory()->create(['email' => 'real@example.com', 'is_verified' => true]);
        $b = $this->postJson('/api/auth/forgot-password', ['email' => $user->email]);

        $a->assertOk();
        $b->assertOk();
        $this->assertSame($a->json('message'), $b->json('message'));
    }
}
