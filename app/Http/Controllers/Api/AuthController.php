<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $deviceName = $request->device_name ?? ($request->header('User-Agent') ?: 'api-token');

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName, $this->getTokenAbilities($user));

        $defaultProperty = $user->properties()->wherePivot('is_default', true)->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
            ],
            'property_id' => $defaultProperty?->id,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'type' => 'guest',
            'status' => User::STATUS_ACTIVE,
        ]);

        $token = $user->createToken('api-token');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
            ],
            'token' => $token->plainTextToken,
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $properties = $user->properties()->get(['properties.id', 'properties.name']);
        $defaultProperty = $user->properties()->wherePivot('is_default', true)->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
                'status' => $user->status,
            ],
            'properties' => $properties,
            'current_property_id' => $defaultProperty?->id,
        ]);
    }

    public function switchProperty(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $user = $request->user();
        $hasAccess = $user->properties()->where('properties.id', $request->property_id)->exists();

        if (! $hasAccess) {
            return response()->json(['message' => 'You do not have access to this property.'], 403);
        }

        return response()->json([
            'message' => 'Property context switched.',
            'property_id' => $request->property_id,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $oldToken = $request->user()->currentAccessToken();
        $newToken = $request->user()->createToken(
            $oldToken->name,
            $this->getTokenAbilities($request->user())
        );

        $oldToken->delete();

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->expires_at,
        ]);
    }

    private function getTokenAbilities(User $user): array
    {
        $abilities = ['read', 'write'];

        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            $abilities[] = 'admin';
        }

        return $abilities;
    }
}
