<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BusinessType;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in([BusinessType::Gym->value, BusinessType::Library->value])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $loginType = BusinessType::from($data['type']);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is disabled.'],
            ]);
        }

        $user->load('business');

        if (! $user->business?->type?->supports($loginType)) {
            throw ValidationException::withMessages([
                'type' => ['This account does not belong to a '.$loginType->value.'.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken($data['device_name'] ?? 'android', [$loginType->value])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'type' => $loginType->value,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'business_id' => $user->business_id,
                'type' => $loginType->value,
            ],
            'business' => [
                'id' => $user->business->id,
                'name' => $user->business->name,
                'type' => $user->business->type,
                'status' => $user->business->status,
                'upi_id' => $user->business->upi_id,
            ],
        ], 'Logged in successfully');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('business');
        $loginType = collect($request->user()->currentAccessToken()?->abilities ?? [])
            ->first(fn (string $ability) => in_array($ability, [BusinessType::Gym->value, BusinessType::Library->value], true));

        return ApiResponse::success([
            'user' => array_merge(
                $user->only(['id', 'name', 'email', 'phone', 'role', 'business_id']),
                ['type' => $loginType ?? $user->business?->type],
            ),
            'business' => $user->business?->only(['id', 'name', 'type', 'status', 'upi_id']),
        ], 'Data fetched successfully');
    }
}
