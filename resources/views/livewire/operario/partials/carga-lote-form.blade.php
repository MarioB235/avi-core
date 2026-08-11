<form wire:submit="guardarLote" class="space-y-4">
    @php
        $galponOptions = $galponesDisponibles->mapWithKeys(
            fn ($galpon): array => [$galpon->id => $galpon->displayName()],
        )->all();
    @endphp

    <x-ui.select
        label="¿En qué galpón ingresás el lote?"
        name="loteGalponId"
        wire:model.defer="loteGalponId"
        placeholder="Elegí un galpón"
        :options="$galponOptions"
        required
    />

    <fieldset class="space-y-2">
        <legend class="block text-sm font-medium text-avicore-text">Tipo de ave / huevo</legend>

        <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-avicore-border-strong bg-avicore-card px-3 py-2.5">
            <input
                type="checkbox"
                wire:model.live="tipoBlanco"
                class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
            />
            <span class="text-sm font-medium text-avicore-text">Blanca</span>
        </label>

        <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-avicore-border-strong bg-avicore-card px-3 py-2.5">
            <input
                type="checkbox"
                wire:model.live="tipoColor"
                class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
            />
            <span class="text-sm font-medium text-avicore-text">Colorada</span>
        </label>

        @error('tiposHuevo')
            <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
        @enderror
    </fieldset>

    @if ($tipoBlanco)
        <x-ui.input
            label="Cantidad — Blanca"
            name="cantidadBlanco"
            type="number"
            min="1"
            inputmode="numeric"
            wire:model.defer="cantidadBlanco"
            placeholder="Ej. 5000"
            required
        />
    @endif

    @if ($tipoColor)
        <x-ui.input
            label="Cantidad — Colorada"
            name="cantidadColor"
            type="number"
            min="1"
            inputmode="numeric"
            wire:model.defer="cantidadColor"
            placeholder="Ej. 3000"
            required
        />
    @endif

    <x-ui.input
        label="Nº de lote SMA (opcional)"
        name="codigoSma"
        type="text"
        wire:model.defer="codigoSma"
        placeholder="Ej. L-2024-089"
        autocomplete="off"
    />

    <x-ui.input
        label="Fecha aproximada de nacimiento"
        name="fechaNacimiento"
        type="date"
        wire:model.defer="fechaNacimiento"
        required
    />

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base !transition-none"
        wire:loading.attr="disabled"
        wire:target="guardarLote"
    >
        <span wire:loading.remove wire:target="guardarLote">Guardar</span>
        <span wire:loading wire:target="guardarLote">Guardando…</span>
    </x-ui.button>
</form>
