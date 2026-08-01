@php
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $empresaNombre = $home->user->empresa?->nombre ?? 'AviCore';
@endphp

<x-layouts.admin title="Inicio · AviCore">
    <div class="avicore-operario-home">
        <x-admin.home-hero
            :saludo="$saludo"
            :subtitle="'Resumen de '.$home->contextLabel.'.'"
        >
            <x-slot:contextChip>
                <div class="avicore-admin-context">
                    <x-ui.icon name="building" class="size-4 shrink-0" />
                    <span class="truncate">{{ $empresaNombre }}</span>
                </div>
            </x-slot:contextChip>
        </x-admin.home-hero>

        <div class="avicore-operario-home-sheet">
            <section class="avicore-admin-home-kpis" aria-label="Indicadores de la empresa">
                <x-ui.kpi-card
                    label="Usuarios activos"
                    :value="number_format($home->activeUsersCount, 0, ',', '.')"
                    hint="Equipo con acceso a la empresa"
                    icon="users"
                />

                <x-ui.kpi-card
                    label="Granjas y galpones"
                    value="—"
                    hint="Se habilitan al configurar la estructura"
                    icon="layers"
                />
            </section>

            <div class="avicore-admin-home-panels">
                <section aria-label="Accesos de gestión">
                    <h2 class="avicore-section-title">¿Qué querés gestionar?</h2>
                    <p class="avicore-section-subtitle mb-4">
                        Módulos del panel para configurar y seguir la empresa.
                    </p>

                    <div class="avicore-admin-home-actions">
                        <a
                            href="{{ route('admin.usuarios.index') }}"
                            wire:navigate
                            class="avicore-admin-home-action avicore-admin-home-action--active"
                        >
                            <span class="avicore-admin-home-action__icon" aria-hidden="true">
                                <x-ui.icon name="users" class="size-5 text-avicore-primary" />
                            </span>
                            <span class="avicore-admin-home-action__title">Usuarios</span>
                            <span class="avicore-admin-home-action__meta">Invitá al equipo y asigná roles</span>
                        </a>

                        <div class="avicore-admin-home-action avicore-admin-home-action--disabled" aria-disabled="true">
                            <span class="avicore-admin-home-action__icon" aria-hidden="true">
                                <x-ui.icon name="layers" class="size-5 text-avicore-muted" />
                            </span>
                            <span class="avicore-admin-home-action__title">Estructura</span>
                            <span class="avicore-admin-home-action__meta">Granjas y galpones · Próximamente</span>
                        </div>

                        <div class="avicore-admin-home-action avicore-admin-home-action--disabled" aria-disabled="true">
                            <span class="avicore-admin-home-action__icon" aria-hidden="true">
                                <x-ui.icon name="file-bar-chart" class="size-5 text-avicore-muted" />
                            </span>
                            <span class="avicore-admin-home-action__title">Reportes</span>
                            <span class="avicore-admin-home-action__meta">Exportaciones · Próximamente</span>
                        </div>
                    </div>
                </section>

                <section aria-label="Configuración inicial">
                    <h2 class="avicore-section-title">Estado inicial</h2>
                    <p class="avicore-section-subtitle mb-4">
                        Completá estos pasos para dejar la empresa lista.
                    </p>

                    <x-ui.setup-checklist :items="$home->setupItems" />
                </section>
            </div>
        </div>
    </div>
</x-layouts.admin>
