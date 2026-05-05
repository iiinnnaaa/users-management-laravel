<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::orderBy('id')->get())->response();
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return (new UserResource($user))->response();
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => $request->validated('role'),
        ]);

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
        ]);

        return (new UserResource($user->fresh()))->response();
    }

    public function destroy(User $user): Response
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->noContent();
    }

    public function makeAdmin(User $user): JsonResponse
    {
        $this->authorize('makeAdmin', $user);

        $user->update(['role' => User::ROLE_ADMIN]);

        return (new UserResource($user->fresh()))->response();
    }

    public function removeAdmin(User $user): JsonResponse
    {
        $this->authorize('removeAdmin', $user);

        $user->update(['role' => User::ROLE_USER]);

        return (new UserResource($user->fresh()))->response();
    }
}
