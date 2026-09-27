<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-3">
            <x-ui.select
                label="Granja"
                name="filtroGranjaId"
                wire:model.live="filtroGranjaId"
                placeholder="Todas las granjas"
                :options="$granjasOptions"
            />

            <x-ui.select
                label="Estado operativo"
                name="filtroGalponEstado"
                wire:model.live="filtroGalponEstado"
                placeholder="Todos"
                :options="$galponEstadoOptions"
            />

            <x-ui.input
                label="Buscar"
                name="busqueda"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Nombre o código"
            />
        </div>

        @if ($canManageEstructura)
            <x-ui.button type="button" wire:click="abrirCrearGalpon" class="w-full shrink-0 lg:w-auto">
                <x-ui.icon name="plus" class="size-4" />
                Nuevo galpón
            </x-ui.button>
        @endif
    </div>

    <x-ui.card padding="none" class="overflow-hidden">
        @if ($galpones->isEmpty())
            <div class="p-8">
                <x-ui.empty-state
                    title="No hay galpones para mostrar"
                    :description="$emptyListadoMensaje"
                    icon="warehouse"
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
                <table class="avicore-table min-w-[720px]">
                    <thead>
                        <tr>
                            <th scope="col">Galpón</th>
                            <th scope="col" class="hidden md:table-cell">Granja</th>
                            <th scope="col">Aves</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-right"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($galpones as $galpon)
                            <tr wire:key="galpon-{{ $galpon->id }}" class="md:hover:bg-avicore-soft/60">
                                <td>
                                    <p class="font-medium text-avicore-text">{{ $galpon->nombre }}</p>
                                    @if ($galpon->codigo)
                                        <p class="text-xs text-avicore-muted">{{ $galpon->codigo }}</p>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell text-avicore-muted">
                                    {{ $galpon->granja->nombre }}
                                    @unless ($galpon->granja->activa)
                                        <x-ui.badge variant="neutral" class="ml-1">Granja inactiva</x-ui.badge>
                                    @endunless
                                </td>
                                <td class="text-avicore-muted">{{ number_format($galpon->aves_actuales, 0, ',', '.') }}</td>
                                <td>
                                    @if ($galpon->disponibleParaCargaOperativa())
                                        <x-ui.badge variant="success">{{ $galpon->estado->label() }}</x-ui.badge>
                                    @else
                                        <x-ui.badge
                                            variant="neutral"
                                            title="{{ $galpon->granja->activa ? 'No disponible para carga operativa' : 'Granja inactiva: no admite carga' }}"
                                        >
                                            {{ $galpon->estado->label() }}
                                        </x-ui.badge>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirFichaGalpon({{ $galpon->id }})">
                                            Ver ficha
                                        </x-ui.button>
                                        @if ($canManageEstructura)
                                            <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirEditarGalpon({{ $galpon->id }})">
                                                Editar
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-avicore-border px-4 py-3">
                {{ $galpones->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
