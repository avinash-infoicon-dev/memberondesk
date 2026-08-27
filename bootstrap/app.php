<?php

use App\Http\Middleware\EnsureBusinessActive;
use App\Http\Middleware\EnsureBusinessOwner;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => SetTenantContext::class,
            'active.user' => EnsureUserIsActive::class,
            'super_admin' => EnsureSuperAdmin::class,
            'business_owner' => EnsureBusinessOwner::class,
            'business.active' => EnsureBusinessActive::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->isSuperAdmin()
                ? route('super-admin.dashboard')
                : route('business.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();

$views = $app->storagePath('framework/views');

if (! is_dir($views) || ! is_writable($views)) {
    $fallback = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'member-on-desk-storage';

    foreach ([
        'app/public',
        'framework/cache/data',
        'framework/sessions',
        'framework/testing',
        'framework/views',
        'logs',
    ] as $directory) {
        $path = $fallback.DIRECTORY_SEPARATOR.$directory;

        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        @chmod($path, 0777);
    }

    $app->useStoragePath($fallback);
}

return $app;
