<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUpiDetailsRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UpiController extends Controller
{
    public function store(StoreUpiDetailsRequest $request): JsonResponse
    {
        $business = $request->user()->business;
        abort_unless($business, 404, 'Business not found.');
        $this->authorize('update', $business);

        $oldPath = $business->upi_qr_path;
        $path = $request->file('upi_qr')->store('upi-qr/'.$business->id, 'public');

        abort_if($path === false, 500, 'Unable to store UPI QR code.');

        try {
            $business->forceFill([
                'account_holder_name' => $request->string('account_holder_name')->toString(),
                'upi_qr_path' => $path,
            ])->save();
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return ApiResponse::success(
            $business->fresh()->upiDetails(),
            'UPI details saved'
        );
    }
}
