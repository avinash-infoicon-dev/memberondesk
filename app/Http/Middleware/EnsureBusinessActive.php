<?php

namespace App\Http\Middleware;

use App\Enums\BusinessStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->user()?->business;

        if (! $business || $business->status !== BusinessStatus::Active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This business is not active. Contact the platform administrator.',
                ], 403);
            }

            if ($request->isMethod('GET')) {
                return $next($request);
            }

            return back()->with('error', 'This business is not active. Contact the platform administrator.');
        }

        return $next($request);
    }
}
