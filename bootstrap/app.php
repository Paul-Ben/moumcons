<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie RBAC middleware aliases (Module 3).
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'redirect-auth' => RedirectIfAuthenticated::class,
        ]);

        // Unauthenticated users hitting protected routes are sent to the
        // named login route instead of the default /login assumption.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // M13 hardening (PRD §32): security headers everywhere, and accounts
        // deactivated mid-session are signed out on their next request.
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [EnsureAccountIsActive::class]);

        // Behind a load balancer / reverse proxy (common on managed hosting)
        // the original scheme comes from X-Forwarded-*; trust it so HTTPS is
        // detected and secure cookies / URLs are generated correctly.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
