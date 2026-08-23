<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-3">
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

            <x-ui.input
                label="Buscar"
                name="busqueda"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Código o SMA"
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
                    description="Registrá un lote en un galpón disponible."
                    icon="layers"
                />
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
                            @if ($canManageLotes)
                                <th scope="col" class="text-right"><span class="sr-only">Acciones</span></th>
                            @endif
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
                                <td class="hidden md:table-cell text-avicore-muted">{{ $lote->galpon->displayName() }}</td>
                                <td class="text-avicore-muted">{{ number_format($lote->cantidad_inicial, 0, ',', '.') }}</td>
                                <td>
                                    <x-ui.badge variant="primary">{{ $lote->estado->label() }}</x-ui.badge>
                                </td>
                                @if ($canManageLotes)
                                    <td class="text-right">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirEditarLote({{ $lote->id }})">
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
                {{ $lotes->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
