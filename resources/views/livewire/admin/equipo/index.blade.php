<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Equipo"
        subtitle="Tu gente en AviCore — solo lectura. La gestión la hace administración."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Listado del equipo">
            <x-ui.section-head
                eyebrow="Personas"
                title="Tu equipo"
                :subtitle="'Personas activas en '.$contextLabel.'.'"
            />

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
                    <ul class="avicore-team-list mt-4" aria-label="Personas del equipo">
                        @foreach ($items as $item)
                            @php
                                $member = $item['user'];
                            @endphp
                            <li wire:key="team-member-{{ $member->id }}" class="avicore-team-list__item">
                                <div class="avicore-team-list__main">
                                    <p class="avicore-team-list__name">{{ $member->name }}</p>
                                    <p class="avicore-team-list__meta">
                                        <span>{{ $member->documento }}</span>
                                        @if ($member->email)
                                            <span class="avicore-team-list__meta-sep" aria-hidden="true">·</span>
                                            <span class="avicore-team-list__email">{{ $member->email }}</span>
                                        @endif
                                    </p>
                                </div>

                                <x-ui.badge variant="primary" class="avicore-team-list__badge shrink-0">
                                    {{ $member->rol->label() }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </x-ui.reveal>
    </div>
</div>
