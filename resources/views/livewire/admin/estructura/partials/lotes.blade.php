<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <x-ui.select
                label="Granja"
                name="filtroGranjaId"
                wire:model.live="filtroGranjaId"
                placeholder="Todas"
                :options="$granjasOptions"
            />

            <x-ui.select
                label="Galpón"
                name="filtroGalponId"
                wire:model.live="filtroGalponId"
                placeholder="Todos"
                :options="$galponesOptions"
            />

            <x-ui.select
                label="Estado"
                name="filtroLoteEstado"
                wire:model.live="filtroLoteEstado"
                placeholder="Todos"
                :options="$loteEstadoOptions"
            />

            <x-ui.select
                label="Tipo de ave"
                name="filtroLoteTipo"
                wire:model.live="filtroLoteTipo"
                placeholder="Todos"
                :options="$tipoHuevoOptions"
            />

            <x-ui.input
                label="Buscar"
                name="busqueda"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Código, SMA o raza"
            />
        </div>

        @if ($canManageLotes)
            <x-ui.button type="button" wire:click="abrirCrearLote" class="w-full shrink-0 lg:w-auto">
                <x-ui.icon name="plus" class="size-4" />
                Nuevo lote
            </x-ui.button>
        @endif
    </div>

    <x-ui.card padding="none" class="overflow-hidden">
        @if ($lotes->isEmpty())
            <div class="p-8">
                <x-ui.empty-state
                    title="No hay lotes para mostrar"
                    :description="$emptyListadoMensaje"
                    icon="layers"
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
                <table class="avicore-table min-w-[800px]">
                    <thead>
                        <tr>
                            <th scope="col">Lote</th>
                            <th scope="col" class="hidden md:table-cell">Galpón</th>
                            <th scope="col">Aves</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-right"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lotes as $lote)
                            <tr wire:key="lote-{{ $lote->id }}" class="md:hover:bg-avicore-soft/60">
                                <td>
                                    <p class="font-medium text-avicore-text">{{ $lote->codigo }}</p>
                                    @if ($lote->codigo_sma)
                                        <p class="text-xs text-avicore-muted">SMA {{ $lote->codigo_sma }}</p>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell text-avicore-muted">
                                    <p>{{ $lote->galpon->displayName() }}</p>
                                    @unless ($lote->galpon->disponibleParaCargaOperativa())
                                        <p class="text-xs text-avicore-muted">
                                            {{ $lote->galpon->granja->activa ? 'Galpón no disponible para carga' : 'Granja inactiva' }}
                                        </p>
                                    @endunless
                                </td>
                                <td class="text-avicore-muted">{{ number_format($lote->cantidad_inicial, 0, ',', '.') }}</td>
                                <td>
                                    <x-ui.badge variant="{{ $lote->estado->permiteCargaNormal() ? 'primary' : 'neutral' }}">
                                        {{ $lote->estado->label() }}
                                    </x-ui.badge>
                                    <p class="mt-1 text-xs text-avicore-muted">{{ $lote->tipo_huevo->labelUi() }}</p>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirFichaLote({{ $lote->id }})">
                                            Ver ficha
                                        </x-ui.button>
                                        @if ($canManageLotes)
                                            <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirEditarLote({{ $lote->id }})">
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
                {{ $lotes->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
