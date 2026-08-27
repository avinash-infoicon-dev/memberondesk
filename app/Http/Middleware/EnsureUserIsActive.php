<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Your account is disabled.'], 403);
            }

            auth()->logout();
            $request->session()?->invalidate();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is disabled.',
            ]);
        }

        return $next($request);
    }
}
