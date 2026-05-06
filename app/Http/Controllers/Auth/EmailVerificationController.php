<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected AuthTokenService $authTokens
    ) {
    }

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $data = $this->authTokens->verifyRegistrationEmail(
            $request->validated('email'),
            $request->validated('otp'),
        );

        return response()->json([
            'message' => __('Email verified.'),
            'user' => new UserResource($data['user']),
            'token' => $data['token'],
        ]);
    }

    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $this->authTokens->resendRegistrationOtp($request->validated('email'));

        return response()->json([
            'message' => __('If this email is registered and unverified, a new code has been sent.'),
        ]);
    }
}
