<?php

namespace App\Repositories;

use App\Models\AuthToken;
use Illuminate\Support\Carbon;

class AuthTokenRepository
{
    public function revokeActiveForUser(int $userId, string $type): int
    {
        return AuthToken::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function create(int $userId, string $type, string $tokenHash, Carbon $expiresAt): AuthToken
    {
        return AuthToken::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findActiveForUser(int $userId, string $type): ?AuthToken
    {
        return AuthToken::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }

    public function markUsed(AuthToken $token): void
    {
        $token->forceFill(['used_at' => now()])->save();
    }
}
