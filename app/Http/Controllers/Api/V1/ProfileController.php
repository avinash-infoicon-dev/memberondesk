<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateOwnerProfileRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(UpdateOwnerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $business = $user->business;
        $this->authorize('update', $business);

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

        $user->refresh()->load('business');

        return ApiResponse::success([
            'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'business_id']),
            'business' => $user->business?->only([
                'id', 'name', 'type', 'status', 'email', 'phone', 'upi_id', 'address', 'city', 'state', 'pincode', 'whatsapp_enabled',
            ]),
        ], 'Profile updated successfully');
    }
}
