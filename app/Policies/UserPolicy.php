<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role === User::ROLE_ADMIN && $user->id !== $model->id;
    }

    public function makeAdmin(User $user, User $model): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function removeAdmin(User $user, User $model): bool
    {
        return $user->role === User::ROLE_ADMIN && $user->id !== $model->id;
    }
}
