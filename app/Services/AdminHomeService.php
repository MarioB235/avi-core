<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Support\HuevosUnidad;
use Carbon\CarbonImmutable;
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

        return [
            'show' => $pulso['galpones_activos'] > 0,
            ...$pulso,
            'delta_label' => $this->formatDeltaHuevos($pulso),
            'unidades_hoy' => $unidades->etiquetaCompacta($pulso['huevos_hoy']),
            'unidades_cajas_maples' => $unidades->etiquetaSoloCajasMaples($pulso['huevos_hoy']),
            'resumen_route' => $user->rol->panelRouteName('resumen.index'),
        ];
    }

    /**
     * Vista previa de stock en cámara y demanda (datos ficticios hasta módulo comercial/stock).
     *
     * @return array{
     *     show: bool,
     *     preview: bool,
     *     items: list<array{label: string, value: string, hint: string, icon?: string, tone?: string}>
     * }
     */
    public function stockPreviewFor(User $user): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return [
                'show' => false,
                'preview' => false,
                'items' => [],
            ];
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return [
                'show' => false,
                'preview' => false,
                'items' => [],
            ];
        }

        $pulso = $this->pulsoForUser($user);

        if ($pulso === null || $pulso['galpones_activos'] < 1) {
            return [
                'show' => false,
                'preview' => false,
                'items' => [],
            ];
        }

        $salidaHoy = $pulso['huevos_hoy'];
        $empresa = Empresa::query()->findOrFail($empresaId);
        $unidades = HuevosUnidad::para($empresa);
        // avicore-defer: módulo stock/comercial real — reemplazar al persistir reserva y demanda
        $reservaHuevos = 4_320;
        $demandaHuevos = 1_800;

        return [
            'show' => true,
            'preview' => true,
            'items' => [
                [
                    'label' => 'En reserva (cámara)',
                    'value' => $unidades->etiquetaSoloCajasMaples($reservaHuevos),
                    'hint' => $unidades->etiquetaCompacta($reservaHuevos).' almacenados',
                    'icon' => 'warehouse',
                    'tone' => 'huevos',
                ],
                [
                    'label' => 'En demanda',
                    'value' => $unidades->etiquetaSoloCajasMaples($demandaHuevos),
                    'hint' => 'Comprometidos con clientes esta semana',
                    'icon' => 'truck',
                    'tone' => 'huevos',
                ],
                [
                    'label' => 'Salida hoy',
                    'value' => $salidaHoy > 0
                        ? $unidades->etiquetaSoloCajasMaples($salidaHoy)
                        : 'Sin carga aún',
                    'hint' => $salidaHoy > 0
                        ? $unidades->etiquetaCompacta($salidaHoy).' juntados en galpón'
                        : 'Cuando operarios carguen huevos, verás el total acá',
                    'icon' => 'egg',
                    'tone' => 'huevos',
                ],
                [
                    'label' => 'Disponible estimado',
                    'value' => $unidades->etiquetaSoloCajasMaples(max(0, $reservaHuevos + $salidaHoy - $demandaHuevos)),
                    'hint' => 'Reserva + producción de hoy − demanda (vista previa)',
                    'icon' => 'layers',
                ],
            ],
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

        if ($ayer < 1 && $pulso['huevos_hoy'] < 1) {
            return 'Sin cargas de huevos hoy ni ayer';
        }

        if ($ayer < 1) {
            return 'Primera carga del día en campo';
        }

        if ($delta === 0) {
            return 'Igual que ayer ('.number_format($ayer, 0, ',', '.').' huevos)';
        }

        $signo = $delta > 0 ? '+' : '';
        $etiqueta = $signo.number_format($delta, 0, ',', '.').' vs ayer';

        if ($pulso['delta_huevos_pct'] !== null) {
            $etiqueta .= ' ('.$signo.number_format($pulso['delta_huevos_pct'], 1, ',', '.').'%)';
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
            'delta_label' => 'Sin cargas de huevos hoy ni ayer',
            'unidades_hoy' => '0 huevos',
            'unidades_cajas_maples' => '0 maples',
            'muertes_hoy' => 0,
            'alertas_count' => 0,
            'galpones_activos' => 0,
            'galpones_sin_carga' => [],
            'alertas' => [],
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
     * Lista plana de equipo con segmentos para filtros (Dueño, solo lectura).
     *
     * @return array{
     *     summary: array{total: int, campo: int, supervision: int, oficina: int},
     *     filters: list<array{key: string, label: string, count: int}>,
     *     items: list<array{user: User, segment: string}>
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
            ];
        }

        $members = $this->teamMembers($user);

        /** @var list<array{user: User, segment: string}> $items */
        $items = $members
            ->map(fn (User $member): array => [
                'user' => $member,
                'segment' => $this->teamSegmentFor($member->rol),
            ])
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
            ->get(['id', 'name', 'documento', 'email', 'rol']);
    }

    /**
     * Datos orientativos del módulo comercial (post-MVP).
     *
     * @return list<array{label: string, value: string, hint: string, icon?: string, illustration?: string, tone?: string}>
     */
    public function comercialPreviewItems(): array
    {
        // avicore-defer: módulo comercial real — KPIs y montos desde ventas/pedidos persistidos
        return [
            [
                'label' => 'Clientes',
                'value' => (string) count($this->comercialClientMap()['clients']),
                'hint' => 'Negocios que te compran seguido (vista previa)',
                'icon' => 'users',
            ],
            [
                'label' => 'Última venta',
                'value' => '$ 48.500',
                'hint' => 'Monto del último despacho (vista previa)',
                'icon' => 'truck',
            ],
            [
                'label' => 'Pedido de mañana',
                'value' => '1.200 huevos',
                'hint' => 'Entrega programada a las 08:00 (vista previa)',
                'illustration' => 'operario-huevo',
                'tone' => 'huevos',
            ],
            [
                'label' => 'Huevos reservados',
                'value' => '3.600 huevos',
                'hint' => 'Comprometidos con clientes esta semana (vista previa)',
                'illustration' => 'operario-huevo',
                'tone' => 'huevos',
            ],
        ];
    }

    /**
     * Clientes demo con ubicación y última compra para el mapa comercial (post-MVP).
     *
     * @return array{
     *     clients: list<array{
     *         id: string,
     *         name: string,
     *         zona: string,
     *         lat: float,
     *         lng: float,
     *         ultima_compra_fecha: string,
     *         ultima_compra_cantidad: int,
     *         ultima_compra_fecha_label: string,
     *         ultima_compra_cantidad_label: string,
     *         ultima_compra_resumen: string
     *     }>
     * }
     */
    public function comercialClientMap(): array
    {
        $clients = [
            [
                'id' => 'demo-pando',
                'name' => 'Almacén El Progreso',
                'zona' => 'Pando',
                'lat' => -34.717,
                'lng' => -55.958,
                'ultima_compra_fecha' => '2026-08-21',
                'ultima_compra_cantidad' => 600,
            ],
            [
                'id' => 'demo-las-piedras',
                'name' => 'Carnicería San José',
                'zona' => 'Las Piedras',
                'lat' => -34.730,
                'lng' => -56.220,
                'ultima_compra_fecha' => '2026-08-19',
                'ultima_compra_cantidad' => 360,
            ],
            [
                'id' => 'demo-costa',
                'name' => 'Mini market Rivera',
                'zona' => 'Ciudad de la Costa',
                'lat' => -34.823,
                'lng' => -55.992,
                'ultima_compra_fecha' => '2026-08-22',
                'ultima_compra_cantidad' => 480,
            ],
            [
                'id' => 'demo-paso-carrasco',
                'name' => 'Distribuidora Norte',
                'zona' => 'Paso Carrasco',
                'lat' => -34.838,
                'lng' => -56.052,
                'ultima_compra_fecha' => '2026-08-18',
                'ultima_compra_cantidad' => 900,
            ],
            [
                'id' => 'demo-centro',
                'name' => 'Restaurante La Granja',
                'zona' => 'Montevideo Centro',
                'lat' => -34.906,
                'lng' => -56.191,
                'ultima_compra_fecha' => '2026-08-20',
                'ultima_compra_cantidad' => 240,
            ],
            [
                'id' => 'demo-malvin',
                'name' => 'Panadería del Este',
                'zona' => 'Malvín',
                'lat' => -34.890,
                'lng' => -56.105,
                'ultima_compra_fecha' => '2026-08-17',
                'ultima_compra_cantidad' => 720,
            ],
        ];

        return [
            'clients' => array_map(
                fn (array $client): array => $this->normalizeComercialClient($client),
                $clients,
            ),
        ];
    }

    /**
     * @param  array{
     *     id: string,
     *     name: string,
     *     zona: string,
     *     lat: float,
     *     lng: float,
     *     ultima_compra_fecha: string,
     *     ultima_compra_cantidad: int
     * }  $client
     * @return array{
     *     id: string,
     *     name: string,
     *     zona: string,
     *     lat: float,
     *     lng: float,
     *     ultima_compra_fecha: string,
     *     ultima_compra_cantidad: int,
     *     ultima_compra_fecha_label: string,
     *     ultima_compra_cantidad_label: string,
     *     ultima_compra_resumen: string
     * }
     */
    private function normalizeComercialClient(array $client): array
    {
        $fechaLabel = $this->formatComercialFecha($client['ultima_compra_fecha']);
        $cantidadLabel = number_format($client['ultima_compra_cantidad'], 0, ',', '.').' huevos';

        return [
            ...$client,
            'ultima_compra_fecha_label' => $fechaLabel,
            'ultima_compra_cantidad_label' => $cantidadLabel,
            'ultima_compra_resumen' => "{$fechaLabel} · {$cantidadLabel}",
        ];
    }

    private function formatComercialFecha(string $fecha): string
    {
        $meses = [
            1 => 'ene',
            2 => 'feb',
            3 => 'mar',
            4 => 'abr',
            5 => 'may',
            6 => 'jun',
            7 => 'jul',
            8 => 'ago',
            9 => 'sep',
            10 => 'oct',
            11 => 'nov',
            12 => 'dic',
        ];

        $date = CarbonImmutable::parse($fecha);

        return $date->format('j').' '.$meses[(int) $date->format('n')].' '.$date->format('Y');
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
