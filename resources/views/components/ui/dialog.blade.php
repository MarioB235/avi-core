@props([
    'title' => null,
    'labelledby' => null,
])

@php
    $titleId = $labelledby ?? 'dialog-title-'.uniqid();
    $wireModel = $attributes->wire('model')->value();
@endphp

<div
    x-data="{
        open: @if ($wireModel) @entangle($wireModel).live @else false @endif,
        previousFocus: null,
        focusPanel() {
            this.$nextTick(() => {
                const panel = this.$refs.dialogPanel;
                const initial = panel?.querySelector('input:not([disabled]), textarea:not([disabled]), select:not([disabled])')
                    ?? panel?.querySelector('[data-dialog-initial-focus]');
                (initial ?? panel)?.focus?.();
            });
        },
        restoreFocus() {
            const triggerFocus = this.$refs.trigger?.querySelector('button, a, [href]');
            const target = this.previousFocus ?? triggerFocus;
            this.previousFocus = null;
            this.$nextTick(() => target?.focus?.());
        },
        applyOpenSideEffects(isOpen) {
            document.body.style.overflow = isOpen ? 'hidden' : '';
            if (isOpen) {
                if (this.previousFocus === null) {
                    this.previousFocus = document.activeElement;
                }
                this.focusPanel();
            } else if (this.previousFocus !== null) {
                this.restoreFocus();
            }
        },
        openDialog() {
            this.open = true;
        },
        closeDialog() {
            this.open = false;
        },
        trapTab(event) {
            if (! this.open) {
                return;
            }

            const panel = this.$refs.dialogPanel;
            if (! panel) {
                return;
            }

            const focusable = [...panel.querySelectorAll(
                'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])'
            )].filter((element) => element.offsetParent !== null);

            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }"
    x-effect="applyOpenSideEffects(open)"
    x-on:keydown.escape.window="if (open) closeDialog()"
    x-on:keydown.tab.window="trapTab($event)"
    x-on:alpine:destroy="document.body.style.overflow = ''"
    {{ $attributes->except('wire:model')->class('contents') }}
>
    @unless ($wireModel)
        <div x-ref="trigger" x-on:click="openDialog()">
            {{ $trigger }}
        </div>
    @endunless

    <template x-teleport="body">
        <div x-show="open" x-cloak class="avicore-dialog" role="presentation">
            <button
                type="button"
                class="avicore-dialog__backdrop"
                x-show="open"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="closeDialog()"
                aria-label="Cerrar diálogo"
            ></button>

            <div class="avicore-dialog__stage">
                <div
                    x-ref="dialogPanel"
                    role="dialog"
                    aria-modal="true"
                    @if ($title) aria-labelledby="{{ $titleId }}" @endif
                    tabindex="-1"
                    class="avicore-dialog__panel"
                    x-show="open"
                    x-transition:enter="transition ease-out duration-250 motion-reduce:transition-none"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-on:click.stop
                >
                    @if ($title)
                        <header class="avicore-dialog__header">
                            <h2 id="{{ $titleId }}" class="avicore-dialog__title">{{ $title }}</h2>
                            <button
                                type="button"
                                class="avicore-dialog__close"
                                data-dialog-initial-focus
                                x-on:click="closeDialog()"
                                aria-label="Cerrar"
                            >
                                <x-ui.icon name="circle-x" class="size-5" />
                            </button>
                        </header>
                    @endif

                    <div class="avicore-dialog__body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
