<?php

namespace App\Livewire\Admin\Resumen;

use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Models\User;
use App\Services\AdminResumenService;
use App\Support\HuevosUnidad;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Resumen · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;

    #[Url(as: 'granja', except: '', history: true)]
    public string $filtroGranjaId = '';

    #[Url(as: 'galpon', except: '', history: true)]
    public string $filtroGalponId = '';

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewResumen';
    }

    public function updatedFiltroGranjaId(): void
    {
        $this->filtroGalponId = '';
    }

    public function render(AdminResumenService $adminResumen): View
    {
        /** @var User $user */
        $user = auth()->user();

        $granjaId = $this->filtroGranjaId !== '' ? (int) $this->filtroGranjaId : null;

        $granjas = $adminResumen->granjasParaFiltro($user);
        $galponesFiltro = $adminResumen->galponesParaFiltro($user, $granjaId);

        $validGalponIds = $galponesFiltro->modelKeys();

        if ($this->filtroGalponId !== '' && ! in_array((int) $this->filtroGalponId, $validGalponIds, true)) {
            $this->filtroGalponId = '';
        }

        $galponId = $this->filtroGalponId !== '' ? (int) $this->filtroGalponId : null;
        $resumen = $adminResumen->for($user, $granjaId, $galponId);
        $posturaSemanal = $adminResumen->posturaSemanal($user, $granjaId, $galponId);

        $granjasOptions = $granjas
            ->mapWithKeys(fn ($granja): array => [
                (string) $granja->id => $granja->dicose
                    ? "{$granja->nombre} · DICOSE {$granja->dicose}"
                    : $granja->nombre,
            ])
            ->all();

        $galponesOptions = $galponesFiltro
            ->mapWithKeys(fn ($galpon): array => [
                (string) $galpon->id => $galpon->nombre,
            ])
            ->all();

        return view('livewire.admin.resumen.index', [
            'resumen' => $resumen,
            'posturaSemanal' => $posturaSemanal,
            'granjasOptions' => $granjasOptions,
            'galponesOptions' => $galponesOptions,
            'galponesFiltro' => $galponesFiltro,
            'mortalidadReferencia' => AdminResumenService::MORTALIDAD_REFERENCIA_PCT,
            'huevosHoyUnidades' => HuevosUnidad::etiquetaSoloCajasMaples($resumen->huevosHoy),
            'maplesHoy' => HuevosUnidad::maplesDesdeHuevos($resumen->huevosHoy),
        ]);
    }
}
