<?php

namespace App\Services\Auth;

use App\Mail\EmailVerificationOtpMail;
use App\Mail\PasswordResetOtpMail;
use App\Mail\RegistrationWelcomeMail;
use App\Models\AuthToken;
use App\Models\User;
use App\Repositories\AuthTokenRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthTokenService
{
    public function __construct(
        protected AuthTokenRepository $tokens
    ) {
    }

    public function issueRegistrationOtp(User $user): void
    {
        $this->tokens->revokeActiveForUser($user->id, AuthToken::TYPE_VERIFY_EMAIL);
        $plain = $this->generateNumericOtp();
        $this->tokens->create(
            $user->id,
            AuthToken::TYPE_VERIFY_EMAIL,
            Hash::make($plain),
            now()->addMinutes(config('auth_tokens.verify_ttl_minutes', 10)),
        );
        $this->safeSendMail(fn () => Mail::to($user->email)->send(new EmailVerificationOtpMail($user, $plain)));
    }

    public function resendRegistrationOtp(string $email): void
    {
        $user = User::query()->where('email', $email)->first();
        if (! $user || $user->is_verified) {
            return;
        }
        $this->issueRegistrationOtp($user);
    }

    public function verifyRegistrationEmail(string $email, string $otp): array
    {
        $this->assertVerifyAttemptsAllowed($email, 'registration_verify');

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('Invalid verification request.')],
            ]);
        }

        if ($user->is_verified) {
            throw ValidationException::withMessages([
                'email' => [__('This email is already verified.')],
            ]);
        }

        $record = $this->tokens->findActiveForUser($user->id, AuthToken::TYPE_VERIFY_EMAIL);
        if (! $record || ! Hash::check($otp, $record->token_hash)) {
            $this->registerVerifyFailure($email, 'registration_verify');

            throw ValidationException::withMessages([
                'otp' => [__('The OTP is invalid or has expired.')],
            ]);
        }

        $this->clearVerifyFailures($email, 'registration_verify');
        $this->tokens->markUsed($record);
        $user->forceFill(['is_verified' => true])->save();

        $plainToken = $user->createToken('auth')->plainTextToken;

        $this->safeSendMail(fn () => Mail::to($user->email)->send(new RegistrationWelcomeMail($user)));

        Log::info('auth.email_verified', ['user_id' => $user->id]);

        return ['token' => $plainToken, 'user' => $user->fresh()];
    }

    public function issuePasswordReset(string $email): void
    {
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return;
        }

        $this->tokens->revokeActiveForUser($user->id, AuthToken::TYPE_RESET_PASSWORD);
        $plain = $this->generateNumericOtp();
        $this->tokens->create(
            $user->id,
            AuthToken::TYPE_RESET_PASSWORD,
            Hash::make($plain),
            now()->addMinutes(config('auth_tokens.reset_ttl_minutes', 60)),
        );

        $this->safeSendMail(fn () => Mail::to($user->email)->send(new PasswordResetOtpMail($user, $plain)));

        Log::info('auth.password_reset_issued', ['user_id' => $user->id]);
    }

    public function resetPassword(string $email, string $otp, string $newPassword): void
    {
        $this->assertVerifyAttemptsAllowed($email, 'password_reset_verify');

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('Invalid verification request.')],
            ]);
        }

        $record = $this->tokens->findActiveForUser($user->id, AuthToken::TYPE_RESET_PASSWORD);
        if (! $record || ! Hash::check($otp, $record->token_hash)) {
            $this->registerVerifyFailure($email, 'password_reset_verify');

            throw ValidationException::withMessages([
                'otp' => [__('The reset code is invalid or has expired.')],
            ]);
        }

        $this->clearVerifyFailures($email, 'password_reset_verify');
        $this->tokens->markUsed($record);
        $user->forceFill(['password' => $newPassword])->save();
        $user->tokens()->delete();

        Log::info('auth.password_reset_completed', ['user_id' => $user->id]);
    }

    protected function generateNumericOtp(): string
    {
        $len = max(6, min(12, (int) config('auth_tokens.otp_length', 6)));

        return str_pad((string) random_int(0, 10 ** $len - 1), $len, '0', STR_PAD_LEFT);
    }

    protected function safeSendMail(callable $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            Log::error('auth_mail_failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function verifyThrottleKey(string $email, string $context): string
    {
        return 'otp_attempts:'.$context.':'.strtolower($email);
    }

    protected function registerVerifyFailure(string $email, string $context): void
    {
        $key = $this->verifyThrottleKey($email, $context);
        RateLimiter::hit($key, (int) config('auth_tokens.verify_lockout_seconds', 900));

        Log::notice('auth.otp_verify_failed', [
            'context' => $context,
        ]);
    }

    protected function clearVerifyFailures(string $email, string $context): void
    {
        RateLimiter::clear($this->verifyThrottleKey($email, $context));
    }

    protected function assertVerifyAttemptsAllowed(string $email, string $context): void
    {
        $key = $this->verifyThrottleKey($email, $context);
        $max = (int) config('auth_tokens.max_verify_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => [__('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds])],
            ])->status(429);
        }
    }
}
