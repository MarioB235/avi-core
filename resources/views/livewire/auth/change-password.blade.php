<div>
    <div class="avicore-auth-card">
        <header class="avicore-auth-card__header">
            <h2 class="avicore-auth-card__title">Nueva contraseña</h2>
            <p class="avicore-auth-card__subtitle">Actualizá tu acceso para continuar</p>
        </header>

        <x-ui.alert variant="warning" class="mb-5">
            Por seguridad debés definir una contraseña nueva antes de continuar.
        </x-ui.alert>

        <form wire:submit="save" class="space-y-5">
            <x-ui.input
                label="Contraseña actual"
                name="current_password"
                icon="lock-keyhole"
                toggle-password
                wire:model="current_password"
                autocomplete="current-password"
                required
            />

            <x-ui.input
                label="Nueva contraseña"
                name="password"
                icon="lock-keyhole"
                toggle-password
                wire:model="password"
                autocomplete="new-password"
                required
            />

            <x-ui.input
                label="Confirmar nueva contraseña"
                name="password_confirmation"
                icon="lock-keyhole"
                toggle-password
                wire:model="password_confirmation"
                autocomplete="new-password"
                required
            />

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Guardar contraseña</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </x-ui.button>
        </form>
    </div>
</div>
