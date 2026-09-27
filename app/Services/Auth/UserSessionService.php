<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserSessionService
{
    public function invalidateAllForUser(User $user, ?string $exceptSessionId = null): void
    {
        // avicore-defer: invalidación multi-sesión requiere SESSION_DRIVER=database (ver arranque-local.md)
        if (config('session.driver') !== 'database') {
            return;
        }

        $query = DB::table($this->sessionTable())
            ->where('user_id', $user->id);

        if ($exceptSessionId !== null) {
            $query->where('id', '!=', $exceptSessionId);
        }

        $query->delete();
    }

    public function invalidateOtherSessionsForUser(User $user, ?string $currentSessionId): void
    {
        if ($currentSessionId === null) {
            $this->invalidateAllForUser($user);

            return;
        }

        $this->invalidateAllForUser($user, $currentSessionId);
    }

    public function invalidateAllForEmpresa(int $empresaId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $userIds = User::query()
            ->where('empresa_id', $empresaId)
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        DB::table($this->sessionTable())
            ->whereIn('user_id', $userIds)
            ->delete();
    }

    private function sessionTable(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
