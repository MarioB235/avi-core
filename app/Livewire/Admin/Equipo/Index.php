<?php

namespace App\Livewire\Admin\Equipo;

use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Services\AdminHomeService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Equipo · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;

    #[Url(as: 'grupo', except: 'todos', history: true)]
    public string $filtroSegmento = 'todos';

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewEquipo';
    }

    public function filtrarEquipo(string $segmento): void
    {
        $this->filtroSegmento = $segmento;
    }

    public function render(AdminHomeService $adminHome): View
    {
        $user = auth()->user();
        $list = $adminHome->teamList($user);

        $items = collect($list['items']);

        if ($this->filtroSegmento !== 'todos') {
            $items = $items->where('segment', $this->filtroSegmento);
        }

        return view('livewire.admin.equipo.index', [
            'contextLabel' => $adminHome->contextLabel($user),
            'summary' => $list['summary'],
            'filters' => $list['filters'],
            'items' => $items->values()->all(),
        ]);
    }
}
