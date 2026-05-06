<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected AuthTokenService $authTokens
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => User::ROLE_USER,
            'is_verified' => false,
        ]);

        $this->authTokens->issueRegistrationOtp($user);

        return response()->json([
            'message' => __('Registration successful. Verify your email with the code we sent.'),
            'user' => new UserResource($user->fresh()),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $password = $request->validated('password');

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->is_verified) {
            return response()->json([
                'message' => __('Your email address is not verified.'),
            ], Response::HTTP_FORBIDDEN);
        }
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
