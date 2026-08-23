<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;

class AdminHomeService
{
    public function __construct(private AdminResumenService $adminResumen) {}

    public function for(User $user): AdminHomeViewData
    {
        return new AdminHomeViewData(
            user: $user,
            contextLabel: $this->contextLabel($user),
            activeUsersCount: $this->activeUsersCount($user),
            estructuraCount: $this->estructuraCount($user),
            operativoTeaser: $this->adminResumen->teaserFor($user),
        );
    }

    public function contextLabel(User $user): string
    {
        $empresa = $user->empresa?->nombre ?? 'AviCore';

        return "{$empresa} · {$user->rol->label()}";
    }

    public function activeUsersCount(User $user): int
    {
        $query = User::query()->where('activo', true);

        if ($user->isAdminAvicore()) {
            return $query->count();
        }

        return $query
            ->where('empresa_id', $user->empresa_id)
            ->count();
    }

    public function estructuraCount(User $user): int
    {
        if ($user->empresa_id === null) {
            return 0;
        }

        return Granja::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('activa', true)
            ->count()
            + Galpon::query()
                ->where('empresa_id', $user->empresa_id)
                ->where('activo', true)
                ->count();
    }

    /**
     * KPIs de equipo para el módulo Equipo (Dueño, solo lectura).
     *
     * @return list<array{label: string, value: string, hint: string, icon?: string}>
     */
    public function teamPreviewItems(User $user): array
    {
        if (! $user->rol->canViewEquipo() || $user->empresa_id === null) {
            return [];
        }

        $base = User::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('activo', true);

        $activos = (clone $base)->count();
        $operarios = (clone $base)->where('rol', UserRole::Operario)->count();
        $supervision = (clone $base)->whereIn('rol', [
            UserRole::Encargado,
            UserRole::Administrativo,
        ])->count();

        return [
            [
                'label' => 'Usuarios activos',
                'value' => number_format($activos, 0, ',', '.'),
                'hint' => 'Personas con acceso a AviCore en tu empresa',
                'icon' => 'users',
            ],
            [
                'label' => 'Operarios en campo',
                'value' => number_format($operarios, 0, ',', '.'),
                'hint' => 'Cuentas para carga en galpón',
                'icon' => 'smartphone',
            ],
            [
                'label' => 'Supervisión y oficina',
                'value' => number_format($supervision, 0, ',', '.'),
                'hint' => 'Encargados y administrativos activos',
                'icon' => 'clipboard-list',
            ],
        ];
    }

    /**
     * Datos orientativos del módulo comercial (post-MVP).
     *
     * @return list<array{label: string, value: string, hint: string, icon?: string, illustration?: string, tone?: string}>
     */
    public function comercialPreviewItems(): array
    {
        return [
            [
                'label' => 'Clientes',
                'value' => '12',
                'hint' => 'Negocios que te compran seguido',
                'icon' => 'users',
            ],
            [
                'label' => 'Última venta',
                'value' => '$ 48.500',
                'hint' => 'Monto del último despacho',
                'icon' => 'truck',
            ],
            [
                'label' => 'Pedido de mañana',
                'value' => '1.200 huevos',
                'hint' => 'Entrega programada a las 08:00',
                'illustration' => 'operario-huevo',
                'tone' => 'huevos',
            ],
            [
                'label' => 'Huevos reservados',
                'value' => '3.600 huevos',
                'hint' => 'Comprometidos con clientes esta semana',
                'illustration' => 'operario-huevo',
                'tone' => 'huevos',
            ],
        ];
    }
}

readonly class AdminHomeViewData
{
    /**
     * @param  array{huevos_hoy: int, muertes_hoy: int, alertas_count: int, galpones_activos: int}  $operativoTeaser
     */
    public function __construct(
        public User $user,
        public string $contextLabel,
        public int $activeUsersCount,
        public int $estructuraCount,
        public array $operativoTeaser,
    ) {}
}
