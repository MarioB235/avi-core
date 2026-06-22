@props([
    'context' => 'default',
    'duration' => 4500,
])

@php
    $flashMessage = session('status');
    $flashVariant = session('status_variant', 'success');
@endphp

<div
    @class([
        'avicore-snackbar-host',
        'avicore-snackbar-host--operario' => $context === 'operario',
        'avicore-snackbar-host--default' => $context === 'default',
    ])
    x-data="{
        visible: false,
        message: '',
        variant: 'success',
        timer: null,
        duration: {{ (int) $duration }},
        init() {
            @if ($flashMessage)
                this.open(@js($flashMessage), @js($flashVariant));
            @endif
        },
        open(message, variant = 'success') {
            if (! message) {
                return;
            }

            this.message = message;
            this.variant = variant;
            this.visible = true;
            this.scheduleClose();
        },
        scheduleClose() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.close(), this.duration);
        },
        pauseAutoClose() {
            clearTimeout(this.timer);
        },
        resumeAutoClose() {
            if (this.visible) {
                this.scheduleClose();
            }
        },
        close() {
            this.visible = false;
            clearTimeout(this.timer);
        },
    }"
    @snackbar-show.window="open($event.detail.message, $event.detail.variant ?? 'success')"
    @keydown.escape.window="if (visible) close()"
>
    <div
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="avicore-snackbar"
        :class="`avicore-snackbar--${variant}`"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        @mouseenter="pauseAutoClose()"
        @mouseleave="resumeAutoClose()"
        @focusin="pauseAutoClose()"
        @focusout="resumeAutoClose()"
    >
        <span class="avicore-snackbar__icon" aria-hidden="true">
            <span x-show="variant === 'success'">
                <x-ui.icon name="check-circle" class="size-5" />
            </span>
            <span x-show="variant === 'danger'" x-cloak>
                <x-ui.icon name="shield" class="size-5" />
            </span>
            <span x-show="variant !== 'success' && variant !== 'danger'" x-cloak>
                <x-ui.icon name="bell" class="size-5" />
            </span>
        </span>

        <p class="avicore-snackbar__message" x-text="message"></p>

        <button
            type="button"
            class="avicore-snackbar__close"
            @click="close()"
            aria-label="Cerrar notificación"
        >
            <x-ui.icon name="circle-x" class="size-4" />
        </button>
    </div>
</div>
