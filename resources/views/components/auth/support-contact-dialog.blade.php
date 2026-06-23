{{-- Recuperación de contraseña: panel inferior móvil vía x-ui.sheet (no x-ui.dialog). --}}
@props([
    'trigger' => '¿Olvidaste tu contraseña?',
    'dialogTitle' => 'Recuperar contraseña',
    'intro' => 'En AviCore la recuperación la realiza tu administrador o encargado autorizado. Contactanos indicando tu número de documento registrado.',
    'footer' => 'También podés pedirle directamente a quien administra usuarios en tu empresa.',
])

@php
    $contact = app(\App\Services\SupportContactService::class)->contacts();
@endphp

<p {{ $attributes->merge(['class' => 'mt-6 text-center']) }}>
    <x-ui.sheet :title="$dialogTitle">
        <x-slot:trigger>
            <button type="button" class="avicore-auth-forgot-link">
                {{ $trigger }}
            </button>
        </x-slot:trigger>

        <p class="text-avicore-muted">
            {{ $intro }}
        </p>

        @if ($contact['has_whatsapp'] || $contact['has_email'])
            <div class="space-y-3">
                @if ($contact['has_whatsapp'])
                    <a
                        href="{{ $contact['whatsapp_url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="avicore-dialog__contact-link"
                    >
                        <x-ui.icon name="message-circle-check" class="text-avicore-primary" />
                        <span>
                            <span class="avicore-dialog__contact-link-label">WhatsApp</span>
                            <span class="avicore-dialog__contact-link-value">{{ $contact['whatsapp_display'] }}</span>
                        </span>
                    </a>
                @endif

                @if ($contact['has_email'])
                    <a
                        href="{{ $contact['mailto_url'] }}"
                        class="avicore-dialog__contact-link"
                    >
                        <x-ui.icon name="mail" class="text-avicore-primary" />
                        <span>
                            <span class="avicore-dialog__contact-link-label">Correo</span>
                            <span class="avicore-dialog__contact-link-value">{{ $contact['email'] }}</span>
                        </span>
                    </a>
                @endif
            </div>
        @else
            <p class="rounded-lg border border-avicore-border/80 bg-avicore-surface px-4 py-3 text-sm text-avicore-muted">
                Contactá a tu administrador de empresa para recuperar el acceso.
            </p>
        @endif

        @if ($footer)
            <p class="text-xs text-avicore-muted">
                {{ $footer }}
            </p>
        @endif
    </x-ui.sheet>
</p>
