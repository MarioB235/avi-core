<?php

namespace App\Livewire\Admin\Comercial;

use App\Services\AdminHomeService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Comercial · AviCore')]
class Index extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null || ! $user->rol->canViewComercial()) {
            throw new AuthorizationException;
        }
    }

    public function render(AdminHomeService $adminHome): View
    {
        $user = auth()->user();

        return view('livewire.admin.comercial.index', [
            'contextLabel' => $adminHome->contextLabel($user),
            'items' => $adminHome->comercialPreviewItems(),
        ]);
    }
}
