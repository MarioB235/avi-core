<?php

namespace App\Livewire\Concerns;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

trait RequiresAdminModuleAccess
{
    use AuthorizesRequests;

    abstract protected function requiredAdminModuleAbility(): string;

    public function mount(): void
    {
        $this->authorizeAdminModule();
    }

    public function hydrate(): void
    {
        $this->authorizeAdminModule();
    }

    protected function authorizeAdminModule(): void
    {
        $this->authorize($this->requiredAdminModuleAbility());
    }
}
