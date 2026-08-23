<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRolePanelAccess
{
    public function handle(Request $request, Closure $next, string $panel): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->rol->routePrefix() !== $panel) {
            return redirect()->route($user->homeRouteName());
        }

        return $next($request);
    }
}
