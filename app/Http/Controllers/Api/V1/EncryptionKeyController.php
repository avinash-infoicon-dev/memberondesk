<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EncryptionKeyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'encrypted_key' => ['required', 'string', 'max:20000'],
        ]);

        $request->user()->forceFill([
            'encrypted_key' => $data['encrypted_key'],
        ])->save();

        return ApiResponse::success(null, 'Encrypted key saved');
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'encrypted_key' => $request->user()->encrypted_key,
        ], 'Data fetched successfully');
    }
}
