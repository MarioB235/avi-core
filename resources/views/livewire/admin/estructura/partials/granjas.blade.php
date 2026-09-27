<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2">
            <x-ui.input
                label="Buscar"
                name="busqueda"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Nombre, código, DICOSE o ubicación"
            />

            <x-ui.select
                label="Estado"
                name="filtroGranjaActiva"
                wire:model.live="filtroGranjaActiva"
                placeholder="Todas"
                :options="$granjaActivaOptions"
            />
        </div>

        @if ($canManageEstructura)
            <x-ui.button type="button" wire:click="abrirCrearGranja" class="w-full shrink-0 sm:w-auto">
                <x-ui.icon name="plus" class="size-4" />
                Nueva granja
            </x-ui.button>
        @endif
    </div>

    <x-ui.card padding="none" class="overflow-hidden">
        @if ($granjas->isEmpty())
            <div class="p-8">
                <x-ui.empty-state
                    title="No hay granjas para mostrar"
                    :description="$emptyListadoMensaje"
                    icon="building"
                />
                @if ($filtrosActivos)
                    <div class="mt-4 text-center">
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="limpiarFiltros">
                            Limpiar filtros
                        </x-ui.button>
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="avicore-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th scope="col">Granja</th>
                            <th scope="col">DICOSE</th>
                            <th scope="col" class="hidden md:table-cell">Ubicación</th>
                            <th scope="col">Estado</th>
                            @if ($canManageEstructura)
                                <th scope="col" class="text-right"><span class="sr-only">Acciones</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($granjas as $granja)
                            <tr wire:key="granja-{{ $granja->id }}" class="md:hover:bg-avicore-soft/60">
                                <td>
                                    <p class="font-medium text-avicore-text">{{ $granja->nombre }}</p>
                                    @if ($granja->codigo)
                                        <p class="text-xs text-avicore-muted">{{ $granja->codigo }}</p>
                                    @endif
                                </td>
                                <td class="text-avicore-muted">{{ $granja->dicose ?? '—' }}</td>
                                <td class="hidden md:table-cell text-avicore-muted">{{ $granja->ubicacion ?? '—' }}</td>
                                <td>
                                    @if ($granja->activa)
                                        <x-ui.badge variant="success">Activa</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">Inactiva</x-ui.badge>
                                    @endif
                                </td>
                                @if ($canManageEstructura)
                                    <td class="text-right">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirEditarGranja({{ $granja->id }})">
                                            Editar
                                        </x-ui.button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-avicore-border px-4 py-3">
                {{ $granjas->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
