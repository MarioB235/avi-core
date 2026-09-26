<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserManagementGuard
{
    public function assertCompanyRetainsActiveManager(User $target, UserRole $newRole, bool $newActive): void
    {
        if ($target->empresa_id === null) {
            return;
        }

        $wasManager = $target->activo && $target->rol->canManageUsers();
        $willBeManager = $newActive && $newRole->canManageUsers();

        if (! $wasManager || $willBeManager) {
            return;
        }

        if ($this->activeManagersCount((int) $target->empresa_id, $target->id) === 0) {
            $field = $newActive ? 'rol' : 'activo';

            throw ValidationException::withMessages([
                $field => 'No podés dejar la empresa sin un administrativo activo.',
            ]);
        }
    }

    private function activeManagersCount(int $empresaId, ?int $excludeUserId = null): int
    {
        return User::query()
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($excludeUserId !== null, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->get()
            ->filter(fn (User $user): bool => $user->rol->canManageUsers())
            ->count();
    }
}
