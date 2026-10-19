<?php

use App\Support\TenantContext;

if (! function_exists('tenant')) {
    function tenant(): TenantContext
    {
        return app(TenantContext::class);
    }
}

if (! function_exists('format_inr')) {
    function format_inr(int|float|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }
}

if (! function_exists('status_badge_class')) {
    function status_badge_class(string $color): string
    {
        return match ($color) {
            'success' => 'badge-success',
            'warning' => 'badge-warning',
            'danger' => 'badge-danger',
            'info' => 'badge-info',
            default => 'badge-muted',
        };
    }
}

if (! function_exists('public_storage_url')) {
    function public_storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = 'storage/'.ltrim($path, '/');

        if (app()->runningInConsole() || ! app()->bound('request') || ! request()) {
            return asset($relative);
        }

        return rtrim(request()->getSchemeAndHttpHost().request()->getBasePath(), '/').'/'.$relative;
    }
}
