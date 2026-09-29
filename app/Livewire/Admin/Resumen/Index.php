<?php

namespace App\Livewire\Admin\Resumen;

use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Models\User;
use App\Services\AdminResumenService;
use App\Services\SoporteEmpresaService;
use App\Support\HuevosUnidad;
use App\Support\ResumenMetricasCatalog;
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

    public function mount(SoporteEmpresaService $soporte): void
    {
        $this->authorizeAdminModule();

        $user = auth()->user();

        if ($user instanceof User && $user->isAdminAvicore() && $soporte->isActive()) {
            $soporte->recordAccionForActiveSession('consulta_resumen', [
                'ruta' => $soporte->entryRouteName(),
            ]);
        }
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

        $user->loadMissing('empresa');
        $unidades = HuevosUnidad::para($user->empresa);

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
            'referenciaMortalidad' => ResumenMetricasCatalog::referenciaMortalidad(),
            'huevosHoyUnidades' => $unidades->etiquetaSoloCajasMaples($resumen->huevosHoy),
            'maplesHoy' => $unidades->maplesDesdeHuevos($resumen->huevosHoy),
            'huevosHoyDesglose' => $unidades->desgloseDesdeHuevos($resumen->huevosHoy),
        ]);
    }
}
