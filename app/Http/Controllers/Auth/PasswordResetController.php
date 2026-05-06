<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\Auth\AuthTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PasswordResetController extends Controller
{
    public function __construct(
        protected AuthTokenService $authTokens
    ) {
    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authTokens->issuePasswordReset($request->validated('email'));

        return response()->json([
            'message' => __('If an account matches this email, further instructions were sent.'),
        ]);
    }

    public function reset(ResetPasswordRequest $request): Response
    {
        $this->authTokens->resetPassword(
            $request->validated('email'),
            $request->validated('otp'),
            $request->validated('new_password'),
        );

        return response()->noContent();
    }
}
