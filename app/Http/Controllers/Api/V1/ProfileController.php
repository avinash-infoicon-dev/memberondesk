<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateOwnerProfileRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(UpdateOwnerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $business = $user->business;
        $this->authorize('update', $business);

        $oldImage = $user->profile_image_path;
        $newImage = null;

        if ($request->hasFile('profile_image')) {
            $newImage = $request->file('profile_image')->store('profile-images/'.$user->id, 'public');

            abort_if($newImage === false, 500, 'Unable to store profile image.');
        }

        try {
            DB::transaction(function () use ($request, $user, $business, $newImage): void {
                if ($newImage !== null) {
                    $user->profile_image_path = $newImage;
                }

                $user->fill($request->safe()->only(['name', 'email', 'phone']));

                if ($request->filled('password')) {
                    $user->password = Hash::make($request->input('password'));
                }

                $user->save();

                $business->fill([
                    'name' => $request->input('business_name', $business->name),
                    'email' => $request->exists('business_email') ? $request->input('business_email') : $business->email,
                    'phone' => $request->exists('business_phone') ? $request->input('business_phone') : $business->phone,
                    'upi_id' => $request->exists('upi_id') ? $request->input('upi_id') : $business->upi_id,
                    'address' => $request->exists('address') ? $request->input('address') : $business->address,
                    'city' => $request->exists('city') ? $request->input('city') : $business->city,
                    'state' => $request->exists('state') ? $request->input('state') : $business->state,
                    'pincode' => $request->exists('pincode') ? $request->input('pincode') : $business->pincode,
                ]);

                if ($request->exists('whatsapp_enabled')) {
                    $business->whatsapp_enabled = $request->boolean('whatsapp_enabled');
                }

                $business->save();
            });
        } catch (\Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        $user->refresh()->load('business');

        return ApiResponse::success([
            'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'business_id', 'profile_image_url']),
            'business' => $user->business?->only([
                'id', 'name', 'type', 'status', 'email', 'phone', 'upi_id', 'account_holder_name', 'upi_qr_url', 'address', 'city', 'state', 'pincode', 'whatsapp_enabled',
            ]),
        ], 'Profile updated successfully');
    }
}
