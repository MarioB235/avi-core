<?php

namespace App\Livewire\Admin\Comercial;

use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Services\AdminHomeService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Comercial · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewComercial';
    }

    public function render(AdminHomeService $adminHome): View
    {
        $user = auth()->user();

        return view('livewire.admin.comercial.index', [
            'contextLabel' => $adminHome->contextLabel($user),
            'items' => $adminHome->comercialPreviewItems(),
            'clients' => $adminHome->comercialClientMap()['clients'],
        ]);
    }
}
