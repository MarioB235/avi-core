<?php

namespace App\Livewire\Admin\Equipo;

use App\Services\AdminHomeService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Equipo · AviCore')]
class Index extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null || ! $user->rol->canViewEquipo()) {
            throw new AuthorizationException;
        }
    }

    public function render(AdminHomeService $adminHome): View
    {
        $user = auth()->user();

        return view('livewire.admin.equipo.index', [
            'contextLabel' => $adminHome->contextLabel($user),
            'items' => $adminHome->teamPreviewItems($user),
        ]);
    }
}
