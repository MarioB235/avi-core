<?php

namespace App\Livewire\Admin\Resumen;

use App\Models\User;
use App\Services\AdminResumenService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Resumen · AviCore')]
class Index extends Component
{
    #[Url(as: 'granja', except: '', history: true)]
    public string $filtroGranjaId = '';

    /** @var list<int> */
    #[Url(as: 'galpones', history: true)]
    public array $filtroGalponIds = [];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null || ! $user->rol->canViewResumen()) {
            throw new AuthorizationException;
        }
    }

    public function updatedFiltroGranjaId(): void
    {
        $this->filtroGalponIds = [];
    }

    public function toggleGalpon(int $galponId, AdminResumenService $adminResumen): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $granjaId = $this->filtroGranjaId !== '' ? (int) $this->filtroGranjaId : null;
        $validIds = $adminResumen->galponesParaFiltro($user, $granjaId)->modelKeys();

        if (! in_array($galponId, $validIds, true)) {
            return;
        }

        if (in_array($galponId, $this->filtroGalponIds, true)) {
            $this->filtroGalponIds = array_values(array_filter(
                $this->filtroGalponIds,
                fn (int $id): bool => $id !== $galponId,
            ));
        } else {
            $this->filtroGalponIds[] = $galponId;
        }
    }

    public function limpiarFiltroGalpones(): void
    {
        $this->filtroGalponIds = [];
    }

    public function render(AdminResumenService $adminResumen): View
    {
        /** @var User $user */
        $user = auth()->user();

        $granjaId = $this->filtroGranjaId !== '' ? (int) $this->filtroGranjaId : null;
        $galponIds = $this->filtroGalponIds;

        $granjas = $adminResumen->granjasParaFiltro($user);
        $galponesFiltro = $adminResumen->galponesParaFiltro($user, $granjaId);
        $resumen = $adminResumen->for($user, $granjaId, $galponIds);

        $granjasOptions = $granjas
            ->mapWithKeys(fn ($granja): array => [
                (string) $granja->id => $granja->dicose
                    ? "{$granja->nombre} · DICOSE {$granja->dicose}"
                    : $granja->nombre,
            ])
            ->all();

        return view('livewire.admin.resumen.index', [
            'resumen' => $resumen,
            'granjasOptions' => $granjasOptions,
            'galponesFiltro' => $galponesFiltro,
            'mortalidadReferencia' => AdminResumenService::MORTALIDAD_REFERENCIA_PCT,
        ]);
    }
}
