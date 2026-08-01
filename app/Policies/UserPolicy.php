<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->rol->canViewUsers();
    }

    public function view(User $actor, User $target): bool
    {
        if (! $actor->rol->canViewUsers()) {
            return false;
        }

        return $this->sameTenantScope($actor, $target);
    }

    public function create(User $actor): bool
    {
        return $actor->rol->canManageUsers();
    }

    public function update(User $actor, User $target): bool
    {
        if (! $actor->rol->canManageUsers()) {
            return false;
        }

        if (! $this->sameTenantScope($actor, $target)) {
            return false;
        }

        if ($target->isAdminAvicore() && ! $actor->isAdminAvicore()) {
            return false;
        }

        return true;
    }

    public function resetPassword(User $actor, User $target): bool
    {
        if (! $actor->rol->canResetUserPassword()) {
            return false;
        }

        if ($actor->is($target)) {
            return false;
        }

        if (! $this->sameTenantScope($actor, $target)) {
            return false;
        }

        if ($target->isAdminAvicore() && ! $actor->isAdminAvicore()) {
            return false;
        }

        return true;
    }

    public function toggleActive(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return false;
        }

        return $this->update($actor, $target);
    }

    private function sameTenantScope(User $actor, User $target): bool
    {
        if ($actor->isAdminAvicore()) {
            return true;
        }

        if ($target->isAdminAvicore()) {
            return false;
        }

        return $actor->empresa_id !== null
            && $actor->empresa_id === $target->empresa_id;
    }
}
