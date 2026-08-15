@props([
    'user',
    'buildLabel' => null,
])

<section
    id="perfil-panel-datos"
    role="tabpanel"
    aria-labelledby="perfil-tab-datos"
    class="avicore-operario-perfil__section"
>
    <form wire:submit="guardarDatos" class="space-y-4">
        <x-ui.input
            label="Nombre"
            name="name"
            icon="users"
            wire:model="name"
            autocomplete="name"
            placeholder="Tu nombre"
            required
        />

        <x-ui.input
            label="Correo"
            name="email"
            type="email"
            wire:model="email"
            autocomplete="email"
            placeholder="correo@ejemplo.com"
        />

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="guardarDatos">Guardar datos</span>
            <span wire:loading wire:target="guardarDatos">Guardando…</span>
        </x-ui.button>
    </form>

    <dl class="avicore-operario-perfil__readonly">
        <div>
            <dt>Documento</dt>
            <dd>{{ $user->documento }}</dd>
        </div>
        @if ($user->empresa)
            <div>
                <dt>Empresa</dt>
                <dd>{{ $user->empresa->nombre }}</dd>
            </div>
        @endif
        <div>
            <dt>Rol</dt>
            <dd>{{ $user->rol->label() }}</dd>
        </div>
        @if ($buildLabel)
            <div>
                <dt>Versión</dt>
                <dd>{{ $buildLabel }}</dd>
            </div>
        @endif
    </dl>
</section>
