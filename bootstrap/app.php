<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke halaman tersebut.'], 403);
            }

            $user = $request->user();
            if (!$user) {
                return redirect()->route('login');
            }

            $route = 'dashboard';
            if ($user->hasRole('owner')) {
                $route = 'owner.dashboard';
            } elseif ($user->hasRole('cashier')) {
                $route = 'cashier.dashboard';
            } elseif ($user->hasRole('staff')) {
                $route = 'staff.dashboard';
            }

            return redirect()->route($route)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        });
    })->create();
