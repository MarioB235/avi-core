<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->must_change_password && ! $request->routeIs('password.change')) {
            return redirect()->route('password.change');
        }

        if (! $user->must_change_password && $request->routeIs('password.change')) {
            return redirect()->route($user->homeRouteName());
        }

        return $next($request);
    }
}
