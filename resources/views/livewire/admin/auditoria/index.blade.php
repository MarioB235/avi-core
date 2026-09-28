<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Auditoría"
        subtitle="Bitácora de acciones críticas — solo consulta, sin edición ni borrado."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Filtros de auditoría">
            <x-ui.section-head
                eyebrow="Filtros"
                title="Refinar listado"
                subtitle="Eventos registrados en tu empresa."
            />

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <x-ui.select
                    label="Categoría"
                    name="filtroCategoria"
                    wire:model.live="filtroCategoria"
                    placeholder="Todas las categorías"
                    :options="$categoriaOptions"
                />

                @if ($actoresOptions !== [])
                    <x-ui.select
                        label="Actor"
                        name="filtroActorId"
                        wire:model.live="filtroActorId"
                        placeholder="Todos los actores"
                        :options="$actoresOptions"
                    />
                @endif

                <x-ui.input
                    label="Acción"
                    name="filtroAccion"
                    wire:model.live.debounce.400ms="filtroAccion"
                    placeholder="Ej. creado, anulado…"
                />

                <x-ui.date-picker
                    label="Desde"
                    name="fechaDesde"
                    wire:model.live="fechaDesde"
                    placeholder="Sin límite inferior"
                    panel-title="Fecha desde"
                    :max="$fechaMaxima"
                    :error="$errors->first('fechaDesde')"
                />

                <x-ui.date-picker
                    label="Hasta"
                    name="fechaHasta"
                    wire:model.live="fechaHasta"
                    placeholder="Sin límite superior"
                    panel-title="Fecha hasta"
                    :max="$fechaMaxima"
                    :error="$errors->first('fechaHasta')"
                />
            </div>

            @if ($hayFiltrosActivos)
                <div class="mt-3">
                    <button
                        type="button"
                        wire:click="limpiarFiltros"
                        class="text-sm font-medium text-avicore-primary underline-offset-2 hover:underline"
                    >
                        Limpiar filtros
                    </button>
                </div>
            @endif
        </x-ui.reveal>

        <x-ui.reveal as="section" class="mt-8" aria-label="Eventos de auditoría">
            <x-ui.section-head
                eyebrow="Bitácora"
                title="Eventos"
                subtitle="Del más reciente al más antiguo."
            />

            @if ($auditorias->isEmpty())
                <x-ui.empty-state
                    class="mt-4"
                    title="Sin eventos"
                    :description="$hayFiltrosActivos
                        ? 'No hay eventos que coincidan con los filtros elegidos.'
                        : 'Cuando ocurran acciones críticas en tu empresa, las verás acá.'"
                    icon="shield"
                />
            @else
                <ul class="avicore-team-list mt-4" aria-label="Eventos de auditoría">
                    @foreach ($auditorias as $auditoria)
                        <li wire:key="auditoria-{{ $auditoria->id }}">
                            <button
                                type="button"
                                wire:click="abrirDetalle({{ $auditoria->id }})"
                                class="avicore-team-list__item w-full text-left"
                            >
                                <div class="avicore-team-list__main min-w-0">
                                    <p class="avicore-team-list__name">
                                        {{ $tituloResumen($auditoria) }}
                                    </p>
                                    <p class="avicore-team-list__meta">
                                        {{ $auditoria->categoria->label() }}
                                        · {{ $subtituloResumen($auditoria) }}
                                    </p>
                                </div>
                                <x-ui.icon name="chevron-right" class="avicore-team-list__chevron size-5 shrink-0" />
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4">
                    {{ $auditorias->links() }}
                </div>
            @endif
        </x-ui.reveal>
    </div>

    @include('livewire.admin.auditoria.partials.detalle-dialog')
</div>
