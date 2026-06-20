<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => $payload['password'],
            'role' => 'mahasiswa',
        ]);

        return response()->json($this->authPayload($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $payload['email'])->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password tidak sesuai.',
            ], 422);
        }

        return response()->json($this->authPayload($user));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'data' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if ($user) {
            $user->forceFill(['api_token' => null])->save();
        }

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * @return array{data: array<string, mixed>, token: string}
     */
    private function authPayload(User $user): array
    {
        $plainToken = Str::random(80);

        $user->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return [
            'data' => $this->userPayload($user),
            'token' => $user->id.'|'.$plainToken,
        ];
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
        ];
    }

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
}
