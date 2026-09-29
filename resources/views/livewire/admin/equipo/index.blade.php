<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Equipo"
        subtitle="Solo lectura: roles y estado de acceso. La gestión de usuarios la hace administración."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Listado del equipo">
            <x-ui.section-head
                eyebrow="Personas"
                title="Tu equipo"
                :subtitle="'Usuarios activos en '.$contextLabel.'.'"
            />

            <p class="mt-3 text-xs leading-relaxed text-avicore-muted">
                {{ $aviso }}
            </p>

            @if ($summary['total'] === 0)
                <x-ui.empty-state
                    class="mt-4"
                    title="Sin personas activas"
                    description="Cuando administración dé de alta usuarios, los verás aquí."
                    icon="users"
                />
            @else
                <p class="avicore-team-list-summary mt-4" aria-label="Resumen del equipo">
                    <span class="font-semibold text-avicore-primary">{{ number_format($summary['total'], 0, ',', '.') }}</span>
                    {{ $summary['total'] === 1 ? 'persona activa' : 'personas activas' }}
                </p>

                @if (count($filters) > 1)
                    <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Filtrar por área">
                        @foreach ($filters as $filter)
                            <button
                                type="button"
                                wire:key="equipo-filter-{{ $filter['key'] }}"
                                wire:click="filtrarEquipo('{{ $filter['key'] }}')"
                                class="avicore-operario-filter-chip {{ $filtroSegmento === $filter['key'] ? 'avicore-operario-filter-chip--active' : 'avicore-operario-filter-chip--idle' }}"
                                aria-pressed="{{ $filtroSegmento === $filter['key'] ? 'true' : 'false' }}"
                            >
                                {{ $filter['label'] }} ({{ $filter['count'] }})
                            </button>
                        @endforeach
                    </div>
                @endif

                @if ($items === [])
                    <x-ui.empty-state
                        class="mt-4"
                        title="Sin personas en este filtro"
                        description="Probá con otro área o elegí Todos."
                        icon="users"
                    />
                @else
                    <div class="avicore-table-wrap mt-4 hidden md:block">
                        <table class="avicore-table avicore-table--compact min-w-[32rem]">
                            <caption class="sr-only">Equipo activo por rol y estado de acceso</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Rol</th>
                                    <th scope="col">Área</th>
                                    <th scope="col">Documento</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr wire:key="team-row-{{ $item['id'] }}">
                                        <th scope="row" class="font-medium">{{ $item['nombre'] }}</th>
                                        <td>{{ $item['rol_label'] }}</td>
                                        <td>{{ $item['segment_label'] }}</td>
                                        <td class="tabular-nums">{{ $item['documento'] }}</td>
                                        <td>{{ $item['estado_label'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <ul class="avicore-team-list mt-4 md:hidden" aria-label="Personas del equipo">
                        @foreach ($items as $item)
                            <li wire:key="team-member-{{ $item['id'] }}" class="avicore-team-list__item">
                                <div class="avicore-team-list__main">
                                    <p class="avicore-team-list__name">{{ $item['nombre'] }}</p>
                                    <p class="avicore-team-list__meta">
                                        {{ $item['rol_label'] }}
                                        <span class="avicore-team-list__meta-sep" aria-hidden="true">·</span>
                                        {{ $item['segment_label'] }}
                                        <span class="avicore-team-list__meta-sep" aria-hidden="true">·</span>
                                        <span class="tabular-nums">{{ $item['documento'] }}</span>
                                    </p>
                                </div>

                                <x-ui.badge
                                    :variant="$item['estado_acceso'] === 'activo' ? 'primary' : 'muted'"
                                    class="avicore-team-list__badge shrink-0"
                                >
                                    {{ $item['estado_label'] }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </x-ui.reveal>
    </div>
</div>
