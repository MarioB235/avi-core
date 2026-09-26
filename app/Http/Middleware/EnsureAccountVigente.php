<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccountAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountVigente
{
    public function __construct(
        private readonly AccountAccessService $accountAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $user->refresh();
        $user->loadMissing('empresa');

        if ($this->accountAccess->mayUseApplication($user)) {
            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Tu sesión ya no es válida. Iniciá sesión de nuevo.')
            ->with('status_variant', 'error');
    }
}
