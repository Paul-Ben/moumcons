<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect authenticated users away from guest-only pages (login).
 * Registered as alias 'guest' — Laravel ships a built-in one, but we keep
 * an explicit app-level middleware so behavior stays predictable.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
