<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null) {
            $user = $request->user();

            if ($user->must_change_password) {
                return redirect()->route('password.change');
            }

            return redirect()->route($user->homeRouteName());
        }

        return $next($request);
    }
}
