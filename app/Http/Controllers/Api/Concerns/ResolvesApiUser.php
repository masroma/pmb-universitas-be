<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesApiUser
{
    private function userFromBearerToken(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (! $token || ! str_contains($token, '|')) {
            return null;
        }

        [$userId, $plainToken] = explode('|', $token, 2);
        $user = User::query()->find($userId);

        if (! $user || ! $user->api_token) {
            return null;
        }

        return hash_equals($user->api_token, hash('sha256', $plainToken)) ? $user : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'profile_photo_url' => $user->profile_photo_url,
        ];
    }
}
