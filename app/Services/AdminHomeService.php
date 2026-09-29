<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Support\ComparacionHonestaPulso;
use App\Support\EquipoLectura;
use App\Support\HuevosUnidad;
use App\Support\InicioExcepcionesPulso;
use Illuminate\Database\Eloquent\Collection;

class AdminHomeService
{
    /** @var array<string, mixed>|null */
    private ?array $cachedPulso = null;

    private ?int $cachedPulsoUserId = null;

    public function __construct(
        private AdminResumenService $adminResumen,
        private EmpresaOnboardingService $onboarding,
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
    ) {}

    public function for(User $user): AdminHomeViewData
    {
        return new AdminHomeViewData(
            user: $user,
            contextLabel: $this->contextLabel($user),
            activeUsersCount: $this->activeUsersCount($user),
            estructuraCount: $this->estructuraCount($user),
            operativoTeaser: $this->adminResumen->teaserFor($user),
            inicio: $this->inicioPanel($user),
            pulso: $this->pulsoPanel($user),
            stockPreview: $this->stockPreviewFor($user),
            onboarding: $this->onboarding->panelFor($user),
        );
    }

    /**
     * Contexto estructural del tab Inicio — sin KPIs operativos ni datos de otros módulos.
     *
     * @return array{granjas: int, galpones: int, show_estructura: bool}
     */
    public function inicioPanel(User $user): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return [
                'granjas' => 0,
                'galpones' => 0,
                'show_estructura' => false,
            ];
        }

        return [
            'granjas' => $this->granjasActivasCount($user),
            'galpones' => $this->galponesActivosCount($user),
            'show_estructura' => true,
        ];
    }

    /**
     * Pulso ejecutivo del día para Inicio admin (Dueño y roles con resumen).
     *
     * @return array{
     *     show: bool,
     *     estado: string,
     *     estado_label: string,
     *     estado_hint: string,
     *     huevos_hoy: int,
     *     huevos_ayer: int,
     *     delta_huevos: int,
     *     delta_huevos_pct: ?float,
     *     delta_label: string,
     *     unidades_hoy: string,
     *     unidades_cajas_maples: string,
     *     muertes_hoy: int,
     *     alertas_count: int,
     *     galpones_activos: int,
     *     galpones_sin_carga: list<array{id: int, nombre: string, granja: string}>,
     *     alertas: list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>,
     *     excepciones: list<array{tipo: string, prioridad: int, titulo: string, detalle: string, accion_label: string, accion_url: string, galpon_id: int}>,
     *     resumen_route: ?string
     * }
     */
    public function pulsoPanel(User $user): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return $this->pulsoPanelVacio();
        }

        $pulso = $this->pulsoForUser($user);

        if ($pulso === null) {
            return $this->pulsoPanelVacio();
        }

        $user->loadMissing('empresa');
        $unidades = HuevosUnidad::para($user->empresa);
        $resumenRoute = $user->rol->panelRouteName('resumen.index');

        return [
            'show' => $pulso['galpones_activos'] > 0,
            ...$pulso,
            'delta_label' => $this->formatDeltaHuevos($pulso),
            'unidades_hoy' => $unidades->etiquetaCompacta($pulso['huevos_hoy']),
            'unidades_cajas_maples' => $unidades->etiquetaSoloCajasMaples($pulso['huevos_hoy']),
            'excepciones' => InicioExcepcionesPulso::construir(
                $pulso['alertas'],
                $pulso['galpones_sin_carga'],
                $resumenRoute,
            ),
            'resumen_route' => $resumenRoute,
        ];
    }

    /**
     * Stock en cámara y demanda — deshabilitado en v1 productiva (RES-01); etapa comercial/stock.
     *
     * @return array{
     *     show: bool,
     *     preview: bool,
     *     items: list<array{label: string, value: string, hint: string, icon?: string, tone?: string}>
     * }
     */
    public function stockPreviewFor(User $user): array
    {
        return [
            'show' => false,
            'preview' => false,
            'items' => [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function pulsoForUser(User $user): ?array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return null;
        }

        if ($this->empresaContext->empresaIdFor($user) === null) {
            return null;
        }

        if ($this->cachedPulsoUserId === $user->id && $this->cachedPulso !== null) {
            return $this->cachedPulso;
        }

        $this->cachedPulsoUserId = $user->id;
        $this->cachedPulso = $this->adminResumen->pulsoFor($user);

        return $this->cachedPulso;
    }

    private function formatDeltaHuevos(array $pulso): string
    {
        $delta = $pulso['delta_huevos'];
        $ayer = $pulso['huevos_ayer'];
        $motivo = $pulso['delta_huevos_pct_motivo'] ?? null;

        if ($motivo === ComparacionHonestaPulso::MOTIVO_SIN_HUEVOS_AMBOS) {
            return 'Sin cargas de huevos hoy ni ayer';
        }

        if ($motivo === ComparacionHonestaPulso::MOTIVO_SIN_BASE_AYER) {
            return 'Primera carga del día en campo';
        }

        if ($delta === 0 && $motivo === null) {
            return 'Igual que ayer ('.number_format($ayer, 0, ',', '.').' huevos)';
        }

        $signo = $delta > 0 ? '+' : '';
        $etiqueta = $signo.number_format($delta, 0, ',', '.').' vs ayer';

        if ($pulso['delta_huevos_pct'] !== null) {
            $etiqueta .= ' ('.$signo.number_format($pulso['delta_huevos_pct'], 1, ',', '.').'%)';
        } elseif ($motivo === ComparacionHonestaPulso::MOTIVO_DIA_EN_CURSO) {
            $etiqueta .= ' (día en curso; % al cerrar capturas)';
        }

        return $etiqueta;
    }

    /**
     * @return array{
     *     show: bool,
     *     estado: string,
     *     estado_label: string,
     *     estado_hint: string,
     *     huevos_hoy: int,
     *     huevos_ayer: int,
     *     delta_huevos: int,
     *     delta_huevos_pct: ?float,
     *     delta_label: string,
     *     unidades_hoy: string,
     *     unidades_cajas_maples: string,
     *     muertes_hoy: int,
     *     alertas_count: int,
     *     galpones_activos: int,
     *     galpones_sin_carga: list<array{id: int, nombre: string, granja: string}>,
     *     alertas: list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>,
     *     resumen_route: ?string
     * }
     */
    private function pulsoPanelVacio(): array
    {
        return [
            'show' => false,
            'estado' => 'ok',
            'estado_label' => 'Sin datos operativos',
            'estado_hint' => 'Cuando haya galpones activos, verás el pulso del día aquí.',
            'huevos_hoy' => 0,
            'huevos_ayer' => 0,
            'delta_huevos' => 0,
            'delta_huevos_pct' => null,
            'delta_huevos_pct_motivo' => ComparacionHonestaPulso::MOTIVO_SIN_HUEVOS_AMBOS,
            'delta_label' => 'Sin cargas de huevos hoy ni ayer',
            'unidades_hoy' => '0 huevos',
            'unidades_cajas_maples' => '0 maples',
            'muertes_hoy' => 0,
            'alertas_count' => 0,
            'galpones_activos' => 0,
            'galpones_sin_carga' => [],
            'alertas' => [],
            'excepciones' => [],
            'resumen_route' => null,
        ];
    }

    public function granjasActivasCount(User $user): int
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return 0;
        }

        return Granja::query()
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->count();
    }

    public function galponesActivosCount(User $user): int
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return 0;
        }

        return Galpon::query()
            ->where('empresa_id', $empresaId)
            ->disponiblesParaCarga()
            ->count();
    }

    public function contextLabel(User $user): string
    {
        if ($user->isAdminAvicore()) {
            $sesion = $this->soporte->activeSesion();
            $empresa = $sesion?->empresa->nombre ?? 'AviCore';

            return $sesion !== null
                ? "{$empresa} · Soporte"
                : "{$empresa} · {$user->rol->label()}";
        }

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
        return $this->granjasActivasCount($user) + $this->galponesActivosCount($user);
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
                'hint' => 'Cuentas habilitadas (sin métricas de rendimiento)',
                'icon' => 'users',
            ],
            [
                'label' => 'Operarios en campo',
                'value' => number_format($operarios, 0, ',', '.'),
                'hint' => 'Rol operario en campo',
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
     * Lista plana de equipo con segmentos para filtros (Dueño, solo lectura).
     *
     * @return array{
     *     summary: array{total: int, campo: int, supervision: int, oficina: int},
     *     filters: list<array{key: string, label: string, count: int}>,
     *     items: list<array{id: int, nombre: string, rol_label: string, segment: string, segment_label: string, documento: string, estado_acceso: string, estado_label: string}>,
     *     aviso: string
     * }
     */
    public function teamList(User $user): array
    {
        $emptySummary = [
            'total' => 0,
            'campo' => 0,
            'supervision' => 0,
            'oficina' => 0,
        ];

        if (! $user->rol->canViewEquipo() || $user->empresa_id === null) {
            return [
                'summary' => $emptySummary,
                'filters' => [],
                'items' => [],
                'aviso' => EquipoLectura::AVISO_SIN_PRODUCTIVIDAD,
            ];
        }

        $members = $this->teamMembers($user);

        /** @var list<array{id: int, nombre: string, rol_label: string, segment: string, segment_label: string, documento: string, estado_acceso: string, estado_label: string}> $items */
        $items = $members
            ->map(fn (User $member): array => EquipoLectura::fila(
                $user,
                $member,
                $this->teamSegmentFor($member->rol),
            ))
            ->values()
            ->all();

        $summary = [
            'total' => $members->count(),
            'campo' => collect($items)->where('segment', 'campo')->count(),
            'supervision' => collect($items)->where('segment', 'supervision')->count(),
            'oficina' => collect($items)->where('segment', 'oficina')->count(),
        ];

        $filters = [
            ['key' => 'todos', 'label' => 'Todos', 'count' => $summary['total']],
        ];

        if ($summary['campo'] > 0) {
            $filters[] = ['key' => 'campo', 'label' => 'Campo', 'count' => $summary['campo']];
        }

        if ($summary['supervision'] > 0) {
            $filters[] = ['key' => 'supervision', 'label' => 'Supervisión', 'count' => $summary['supervision']];
        }

        if ($summary['oficina'] > 0) {
            $filters[] = ['key' => 'oficina', 'label' => 'Oficina', 'count' => $summary['oficina']];
        }

        return [
            'summary' => $summary,
            'filters' => $filters,
            'items' => $items,
            'aviso' => EquipoLectura::AVISO_SIN_PRODUCTIVIDAD,
        ];
    }

    private function teamSegmentFor(UserRole $rol): string
    {
        return match ($rol) {
            UserRole::Operario, UserRole::Reparto => 'campo',
            UserRole::Encargado => 'supervision',
            UserRole::Administrativo, UserRole::Dueno => 'oficina',
            default => 'oficina',
        };
    }

    /**
     * Directorio de equipo agrupado por función (Dueño, solo lectura).
     *
     * @return array{
     *     summary: array{total: int, campo: int, supervision: int, oficina: int},
     *     groups: list<array{key: string, title: string, tone: string, members: Collection<int, User>}>
     * }
     */
    public function teamDirectory(User $user): array
    {
        $emptySummary = [
            'total' => 0,
            'campo' => 0,
            'supervision' => 0,
            'oficina' => 0,
        ];

        if (! $user->rol->canViewEquipo() || $user->empresa_id === null) {
            return [
                'summary' => $emptySummary,
                'groups' => [],
            ];
        }

        $members = $this->teamMembers($user);

        $campo = $members->whereIn('rol', [UserRole::Operario, UserRole::Reparto])->values();
        $supervision = $members->where('rol', UserRole::Encargado)->values();
        $oficina = $members->where('rol', UserRole::Administrativo)->values();
        $direccion = $members->where('rol', UserRole::Dueno)->values();

        $groups = [];

        foreach ([
            ['key' => 'campo', 'title' => 'En campo', 'tone' => 'campo', 'members' => $campo],
            ['key' => 'supervision', 'title' => 'Supervisión', 'tone' => 'supervision', 'members' => $supervision],
            ['key' => 'oficina', 'title' => 'Oficina', 'tone' => 'oficina', 'members' => $oficina],
            ['key' => 'direccion', 'title' => 'Dirección', 'tone' => 'direccion', 'members' => $direccion],
        ] as $group) {
            if ($group['members']->isNotEmpty()) {
                $groups[] = $group;
            }
        }

        return [
            'summary' => [
                'total' => $members->count(),
                'campo' => $campo->count(),
                'supervision' => $supervision->count(),
                'oficina' => $oficina->count() + $direccion->count(),
            ],
            'groups' => $groups,
        ];
    }

    /**
     * Listado de personas activas para la tabla de Equipo (Dueño, solo lectura).
     *
     * @return Collection<int, User>
     */
    public function teamMembers(User $user): Collection
    {
        if (! $user->rol->canViewEquipo() || $user->empresa_id === null) {
            return new Collection;
        }

        return User::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name', 'documento', 'rol', 'must_change_password']);
    }

    /**
     * KPIs comerciales — vacío en v1 productiva (RES-01).
     *
     * @return list<array{label: string, value: string, hint: string, icon?: string, illustration?: string, tone?: string}>
     */
    public function comercialPreviewItems(): array
    {
        return [];
    }

    /**
     * Mapa de clientes — vacío en v1 productiva (RES-01).
     *
     * @return array{clients: list<array<string, mixed>>}
     */
    public function comercialClientMap(): array
    {
        return ['clients' => []];
    }
}

readonly class AdminHomeViewData
{
    /**
     * @param  array{huevos_hoy: int, muertes_hoy: int, alertas_count: int, galpones_activos: int}  $operativoTeaser
     * @param  array{granjas: int, galpones: int, show_estructura: bool}  $inicio
     * @param  array{
     *     show: bool,
     *     estado: string,
     *     estado_label: string,
     *     estado_hint: string,
     *     huevos_hoy: int,
     *     huevos_ayer: int,
     *     delta_huevos: int,
     *     delta_huevos_pct: ?float,
     *     delta_label: string,
     *     unidades_hoy: string,
     *     unidades_cajas_maples: string,
     *     muertes_hoy: int,
     *     alertas_count: int,
     *     galpones_activos: int,
     *     galpones_sin_carga: list<array{id: int, nombre: string, granja: string}>,
     *     alertas: list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>,
     *     resumen_route: ?string
     * }  $pulso
     * @param  array{
     *     show: bool,
     *     preview: bool,
     *     items: list<array{label: string, value: string, hint: string, icon?: string, tone?: string}>
     * }  $stockPreview
     * @param  array{
     *     show: bool,
     *     title: string,
     *     subtitle: string,
     *     pending_count: int,
     *     total_count: int,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         description: string,
     *         icon: string,
     *         status: string,
     *         href: ?string
     *     }>
     * }  $onboarding
     */
    public function __construct(
        public User $user,
        public string $contextLabel,
        public int $activeUsersCount,
        public int $estructuraCount,
        public array $operativoTeaser,
        public array $inicio,
        public array $pulso,
        public array $stockPreview,
        public array $onboarding,
    ) {}
}
