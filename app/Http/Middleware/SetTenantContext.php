<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $context = app(TenantContext::class);

        if (! $user) {
            $context->reset();

            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            $context->bypass();

            return $next($request);
        }

        if ($user->business_id) {
            $context->set((int) $user->business_id);

            return $next($request);
        }

        $context->reset();

        return $next($request);
    }
}
