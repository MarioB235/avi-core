<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Historial operativo"
        subtitle="Cargas de todo el equipo — filtros por granja, galpón, operario, tipo, estado y período."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Filtros del historial">
            <x-ui.section-head
                eyebrow="Filtros"
                title="Refinar listado"
                subtitle="Los registros de todos los operarios de tu empresa."
            />

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <x-ui.select
                    label="Granja"
                    name="filtroGranjaId"
                    wire:model.live="filtroGranjaId"
                    placeholder="Todas las granjas"
                    :options="$granjasOptions"
                />

                @if ($galponesFiltro->isNotEmpty())
                    <x-ui.select
                        label="Galpón"
                        name="filtroGalponId"
                        wire:model.live="filtroGalponId"
                        placeholder="Todos los galpones"
                        :options="$galponesOptions"
                    />
                @endif

                @if ($operariosOptions !== [])
                    <x-ui.select
                        label="Operario"
                        name="filtroOperarioId"
                        wire:model.live="filtroOperarioId"
                        placeholder="Todos los operarios"
                        :options="$operariosOptions"
                    />
                @endif

                <x-ui.select
                    label="Tipo"
                    name="filtroTipo"
                    wire:model.live="filtroTipo"
                    placeholder="Todos los tipos"
                    :options="$tipoOptions"
                />

                <x-ui.select
                    label="Estado"
                    name="filtroEstado"
                    wire:model.live="filtroEstado"
                    placeholder="Activos y anulados"
                    :options="$estadoOptions"
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

        <x-ui.reveal as="section" class="mt-8" aria-label="Registros operativos">
            <x-ui.section-head
                eyebrow="Operación"
                title="Registros"
                subtitle="Del más reciente al más antiguo."
            />

            @if ($registros->isEmpty())
                <x-ui.empty-state
                    class="mt-4"
                    title="Sin registros"
                    :description="$hayFiltrosActivos
                        ? 'No hay cargas que coincidan con los filtros elegidos.'
                        : 'Cuando el equipo registre cargas en campo, las verás acá.'"
                    icon="clipboard-list"
                />
            @else
                <ul class="avicore-team-list mt-4" aria-label="Historial operativo del equipo">
                    @foreach ($registros as $item)
                        <li wire:key="supervisor-historial-{{ $item->key }}">
                            <button
                                type="button"
                                wire:click="abrirDetalle('{{ $item->key }}')"
                                class="avicore-team-list__item w-full text-left {{ $item->anulado ? 'opacity-70' : '' }}"
                            >
                                <div class="avicore-team-list__main min-w-0">
                                    <p class="avicore-team-list__name">
                                        {{ $item->label }}
                                        @if ($item->anulado)
                                            <x-ui.badge variant="muted" class="ml-2">Anulado</x-ui.badge>
                                        @endif
                                    </p>
                                    <p class="avicore-team-list__meta">
                                        <span>{{ $item->tipoEtiqueta }}</span>
                                        <span class="avicore-team-list__meta-sep" aria-hidden="true">·</span>
                                        <span>{{ $item->galponEtiqueta }}</span>
                                        <span class="avicore-team-list__meta-sep" aria-hidden="true">·</span>
                                        <span>{{ $item->operarioNombre }}</span>
                                    </p>
                                </div>
                                <time
                                    class="shrink-0 text-sm text-avicore-muted"
                                    datetime="{{ $item->createdAt->toIso8601String() }}"
                                >
                                    {{ $item->createdAt->format('d/m/Y H:i') }}
                                </time>
                            </button>
                        </li>
                    @endforeach
                </ul>

                @if ($registros->hasPages())
                    <div class="mt-6">
                        {{ $registros->links() }}
                    </div>
                @endif
            @endif
        </x-ui.reveal>
    </div>

    @include('livewire.admin.historial-operativo.partials.detalle-dialog', ['detalleItem' => $detalleItem])
</div>
