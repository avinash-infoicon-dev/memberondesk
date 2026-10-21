<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'type' => ['sometimes', 'nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

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

        if (! $user->isSuperAdmin() && ! $user->business) {
            throw ValidationException::withMessages([
                'email' => ['This account is not linked to a business.'],
            ]);
        }

        $businessType = $user->business?->type?->value;

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken(
            $data['device_name'] ?? 'android',
            array_filter([$businessType])
        )->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'type' => $businessType,
            'user' => [
                'id' => $user->id,
                'profile_image_url' => $user->profile_image_url,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'business_id' => $user->business_id,
                'type' => $businessType,
            ],
            'business' => $user->business ? [
                'id' => $user->business->id,
                'name' => $user->business->name,
                'type' => $user->business->type,
                'status' => $user->business->status,
                'upi_id' => $user->business->upi_id,
                'member_phone_storage' => $user->business->member_phone_storage?->value
                    ?? \App\Enums\MemberPhoneStorage::DeviceLocal->value,
            ] : null,
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
        $businessType = $user->business?->type?->value
            ?? collect($request->user()->currentAccessToken()?->abilities ?? [])->first();

        return ApiResponse::success([
            'user' => array_merge(
                $user->only(['id', 'name', 'email', 'phone', 'role', 'business_id', 'profile_image_url']),
                ['type' => $businessType],
            ),
            'business' => $user->business ? [
                'id' => $user->business->id,
                'name' => $user->business->name,
                'type' => $user->business->type,
                'status' => $user->business->status,
                'upi_id' => $user->business->upi_id,
                'member_phone_storage' => $user->business->member_phone_storage?->value
                    ?? \App\Enums\MemberPhoneStorage::DeviceLocal->value,
            ] : null,
        ], 'Data fetched successfully');
    }
}
