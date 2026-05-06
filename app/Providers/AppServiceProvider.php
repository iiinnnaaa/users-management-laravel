<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('verify-email', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(30)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('resend-otp', fn (Request $request) => Limit::perMinutes(15, 3)->by(
            strtolower((string) $request->input('email', $request->ip()))
        ));

        RateLimiter::for('forgot-password', fn (Request $request) => Limit::perHour(5)->by($request->ip()));

        RateLimiter::for('reset-password', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(10)->by($request->ip().'|'.$email);
        });
    }
}
