<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Data fetched successfully', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::normalize($data),
        ], $status);
    }

    public static function error(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => self::normalize($data),
        ], $status);
    }

    private static function normalize(mixed $data): mixed
    {
        if ($data instanceof ResourceCollection || $data instanceof JsonResource) {
            return $data->resolve();
        }

        return $data;
    }
}
