<?php

namespace App\Livewire\Admin\Equipo;

use App\Services\AdminHomeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Equipo · AviCore')]
class Index extends Component
{
    #[Url(as: 'grupo', except: 'todos', history: true)]
    public string $filtroSegmento = 'todos';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null || ! $user->rol->canViewEquipo()) {
            throw new AuthorizationException;
        }
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
