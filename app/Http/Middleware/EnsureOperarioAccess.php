<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperarioAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->rol->isOperario()) {
            return redirect()->route($user?->homeRouteName() ?? 'login');
        }

        return $next($request);
    }
}
