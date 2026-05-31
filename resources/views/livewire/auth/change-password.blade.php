<div class="space-y-6">
    <x-ui.logo subtitle="Cambio obligatorio de contraseña" class="justify-center" />

    <x-ui.alert variant="warning">
        Por seguridad debés definir una contraseña nueva antes de continuar.
    </x-ui.alert>

    <x-ui.card>
        <form wire:submit="save" class="space-y-5">
            <x-ui.input
                label="Contraseña actual"
                name="current_password"
                type="password"
                wire:model="current_password"
                autocomplete="current-password"
                required
            />

            <x-ui.input
                label="Nueva contraseña"
                name="password"
                type="password"
                wire:model="password"
                autocomplete="new-password"
                required
            />

            <x-ui.input
                label="Confirmar nueva contraseña"
                name="password_confirmation"
                type="password"
                wire:model="password_confirmation"
                autocomplete="new-password"
                required
            />

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Guardar contraseña</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
