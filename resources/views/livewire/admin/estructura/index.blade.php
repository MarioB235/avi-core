<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Estructura"
        subtitle="Granjas, galpones y lotes de tu empresa."
    >
        <x-slot:actions>
            <a
                href="{{ route(auth()->user()->rol->routePrefix().'.reportes.sanidad-basica') }}"
                class="avicore-btn avicore-btn--secondary avicore-btn--sm"
            >
                Exportar sanidad (Excel)
            </a>
        </x-slot:actions>
    </x-admin.page-hero>

    <div class="avicore-operario-home-sheet space-y-6">
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Secciones de estructura">
            @foreach ([
                'granjas' => 'Granjas',
                'galpones' => 'Galpones',
                'lotes' => 'Lotes',
            ] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('seccion', '{{ $value }}')"
                    class="avicore-operario-filter-chip {{ $seccion === $value ? 'avicore-operario-filter-chip--active' : 'avicore-operario-filter-chip--idle' }}"
                    role="tab"
                    aria-selected="{{ $seccion === $value ? 'true' : 'false' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($seccion === 'granjas')
            @include('livewire.admin.estructura.partials.granjas')
        @elseif ($seccion === 'galpones')
            @include('livewire.admin.estructura.partials.galpones')
        @else
            @include('livewire.admin.estructura.partials.lotes')
        @endif
    </div>

    @include('livewire.admin.estructura.partials.dialog-granja')
    @include('livewire.admin.estructura.partials.dialog-galpon')
    @include('livewire.admin.estructura.partials.dialog-lote-crear')
    @include('livewire.admin.estructura.partials.dialog-lote-editar')
    @include('livewire.admin.estructura.partials.dialog-galpon-ficha')
    @include('livewire.admin.estructura.partials.dialog-lote-ficha')
</div>
