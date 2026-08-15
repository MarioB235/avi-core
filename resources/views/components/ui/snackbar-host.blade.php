@props([
    'context' => 'default',
    'duration' => null,
])

@php
    $flashMessage = session('status');
    $flashVariant = session('status_variant', 'success');
    $resolvedDuration = $duration ?? ($context === 'operario' ? 2500 : 3500);
@endphp

<div
    @class([
        'avicore-snackbar-host',
        'avicore-snackbar-host--operario' => $context === 'operario',
        'avicore-snackbar-host--default' => $context === 'default',
    ])
    x-data="{
        context: @js($context),
        visible: false,
        message: '',
        variant: 'success',
        actionLabel: null,
        actionKey: null,
        timer: null,
        duration: {{ (int) $resolvedDuration }},
        remainingMs: {{ (int) $resolvedDuration }},
        closeAt: null,
        progressKey: 0,
        progressPaused: false,
        init() {
            @if ($flashMessage)
                this.open(@js($flashMessage), @js($flashVariant));
            @endif
        },
        isCompact() {
            return this.context === 'operario'
                && this.variant === 'success'
                && ! this.actionLabel;
        },
        open(message, variant = 'success', actionLabel = null, actionKey = null) {
            if (! message) {
                return;
            }

            this.message = message;
            this.variant = variant;
            this.actionLabel = actionLabel;
            this.actionKey = actionKey;
            this.visible = true;
            this.remainingMs = this.duration;
            this.scheduleClose(true);
        },
        scheduleClose(resetRemaining = false) {
            clearTimeout(this.timer);

            if (this.actionLabel) {
                return;
            }

            if (resetRemaining) {
                this.remainingMs = this.duration;
            }

            this.closeAt = Date.now() + this.remainingMs;
            this.progressPaused = false;
            this.progressKey += 1;
            this.timer = setTimeout(() => this.close(), this.remainingMs);
            this.$nextTick(() => this.syncProgressDuration());
        },
        pauseAutoClose() {
            clearTimeout(this.timer);

            if (! this.visible || this.actionLabel || this.isCompact()) {
                return;
            }

            this.remainingMs = Math.max(0, (this.closeAt ?? Date.now()) - Date.now());
            this.progressPaused = true;
        },
        resumeAutoClose() {
            if (this.visible && ! this.actionLabel && ! this.isCompact()) {
                this.scheduleClose(false);
            }
        },
        close() {
            this.visible = false;
            this.actionLabel = null;
            this.actionKey = null;
            this.remainingMs = this.duration;
            this.progressPaused = false;
            clearTimeout(this.timer);
        },
        syncProgressDuration() {
            const bar = this.$refs.progressBar;

            if (bar) {
                bar.style.setProperty('--snackbar-duration', `${this.remainingMs}ms`);
            }
        },
        runAction() {
            if (this.actionKey === 'pwa-update') {
                window.__avicorePwaUpdate?.();
            }

            this.close();
        },
    }"
    @snackbar-show.window="open(
        $event.detail.message,
        $event.detail.variant ?? 'success',
        $event.detail.actionLabel ?? null,
        $event.detail.actionKey ?? null
    )"
    @keydown.escape.window="if (visible && ! isCompact()) close()"
>
    <div
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 translate-y-2 lg:translate-y-0 lg:translate-x-2"
        x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0"
        x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 translate-y-0 lg:translate-x-0"
        x-transition:leave-end="opacity-0 translate-y-2 lg:translate-y-0 lg:translate-x-2"
        class="avicore-snackbar"
        :class="{
            [`avicore-snackbar--${variant}`]: true,
            'avicore-snackbar--compact': isCompact(),
        }"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        @mouseenter="pauseAutoClose()"
        @mouseleave="resumeAutoClose()"
        @focusin="pauseAutoClose()"
        @focusout="resumeAutoClose()"
    >
        <div
            class="avicore-snackbar__progress"
            x-show="! actionLabel && ! isCompact()"
            x-cloak
            aria-hidden="true"
        >
            <div
                class="avicore-snackbar__progress-bar"
                x-ref="progressBar"
                :class="{ 'avicore-snackbar__progress-bar--paused': progressPaused }"
                :key="progressKey"
            ></div>
        </div>

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
            class="avicore-snackbar__action"
            x-show="actionLabel"
            x-cloak
            x-on:click="runAction()"
            x-text="actionLabel"
        ></button>

        <button
            type="button"
            class="avicore-snackbar__close"
            x-show="! isCompact()"
            x-cloak
            @click="close()"
            aria-label="Cerrar notificación"
        >
            <x-ui.icon name="circle-x" class="size-4" />
        </button>
    </div>
</div>
