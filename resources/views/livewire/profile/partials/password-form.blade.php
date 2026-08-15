<section
    id="perfil-panel-password"
    role="tabpanel"
    aria-labelledby="perfil-tab-password"
    class="avicore-operario-perfil__section"
>
    <form wire:submit="guardarContrasena" class="space-y-4">
        <x-ui.input
            label="Contraseña actual"
            name="current_password"
            icon="lock-keyhole"
            toggle-password
            wire:model="current_password"
            autocomplete="current-password"
            placeholder="Tu contraseña actual"
            required
        />

        <x-ui.input
            label="Nueva contraseña"
            name="password"
            icon="key-round"
            toggle-password
            wire:model="password"
            autocomplete="new-password"
            placeholder="Mínimo 8 caracteres"
            hint="8+ caracteres, mayúsculas, minúsculas y números."
            required
        />

        <x-ui.input
            label="Confirmar nueva contraseña"
            name="password_confirmation"
            icon="shield-check"
            toggle-password
            wire:model="password_confirmation"
            autocomplete="new-password"
            placeholder="Repetí la nueva contraseña"
            required
        />

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="guardarContrasena">Guardar contraseña</span>
            <span wire:loading wire:target="guardarContrasena">Guardando…</span>
        </x-ui.button>
    </form>

    <x-auth.support-contact-dialog
        trigger="¿Olvidaste tu contraseña?"
        intro="Si no recordás tu contraseña, contactá a tu administrador o a soporte indicando tu documento registrado."
        class="mt-4"
    />
</section>
