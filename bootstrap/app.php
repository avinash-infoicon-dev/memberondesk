<?php

use App\Http\Middleware\EnsureBusinessActive;
use App\Http\Middleware\EnsureBusinessOwner;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SetTenantContext;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

        // Tenant context must be set before route-model binding, or scoped
        // models (members, plans, etc.) resolve as missing and return 404.
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: SetTenantContext::class,
        );

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

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error($e->getMessage(), $e->errors(), $e->status);
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::error('Unauthenticated.', null, 401);
            }

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return ApiResponse::error($e->getMessage() ?: 'This action is unauthorized.', null, 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return ApiResponse::error('Resource not found.', null, 404);
            }

            if ($e instanceof UnauthorizedHttpException) {
                return ApiResponse::error($e->getMessage() ?: 'Unauthorized.', null, 401);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return ApiResponse::error(
                config('app.debug') ? ($e->getMessage() ?: 'Something went wrong.') : 'Something went wrong.',
                null,
                $status
            );
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
