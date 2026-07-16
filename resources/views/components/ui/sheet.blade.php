@props([
    'title' => null,
    'labelledby' => null,
])

@php
    $titleId = $labelledby ?? 'sheet-title-'.uniqid();
    $wireModel = $attributes->wire('model')->value();
@endphp

<div
    x-data="{
        open: @if ($wireModel) @entangle($wireModel).live @else false @endif,
        previousFocus: null,
        syncBodyScroll(isOpen) {
            document.body.style.overflow = isOpen ? 'hidden' : '';
        },
        openSheet() {
            this.previousFocus = document.activeElement;
            this.open = true;
            this.syncBodyScroll(true);
            this.$nextTick(() => {
                const panel = this.$refs.sheetPanel;
                const initial = panel?.querySelector('[data-sheet-initial-focus]');
                (initial ?? panel)?.focus?.();
            });
        },
        closeSheet() {
            this.open = false;
            this.syncBodyScroll(false);
            this.$nextTick(() => {
                const triggerFocus = this.$refs.trigger?.querySelector('button, a, [href]');
                (this.previousFocus ?? triggerFocus)?.focus?.();
                this.previousFocus = null;
            });
        },
        trapTab(event) {
            if (! this.open) {
                return;
            }

            const panel = this.$refs.sheetPanel;
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
    x-init="syncBodyScroll(open)"
    x-effect="syncBodyScroll(open)"
    x-on:keydown.escape.window="if (open) closeSheet()"
    x-on:keydown.tab.window="trapTab($event)"
    x-on:alpine:destroy="document.body.style.overflow = ''"
    {{ $attributes->except('wire:model')->class('contents') }}
>
    @unless ($wireModel)
        <div x-ref="trigger" x-on:click="openSheet()">
            {{ $trigger }}
        </div>
    @endunless

    <template x-teleport="body">
        <div x-show="open" x-cloak class="avicore-sheet" role="presentation">
            <button
                type="button"
                class="avicore-sheet__backdrop"
                x-show="open"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="closeSheet()"
                aria-label="Cerrar panel"
            ></button>

            <div
                x-ref="sheetPanel"
                role="dialog"
                aria-modal="true"
                @if ($title) aria-labelledby="{{ $titleId }}" @endif
                tabindex="-1"
                class="avicore-sheet__panel"
                x-show="open"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="translate-y-full opacity-0 lg:translate-y-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0 lg:translate-y-0"
                x-on:click.stop
            >
                <div class="avicore-sheet__handle" aria-hidden="true"></div>

                @if ($title)
                    <header class="avicore-sheet__header">
                        <h2 id="{{ $titleId }}" class="avicore-sheet__title">{{ $title }}</h2>
                        <button
                            type="button"
                            class="avicore-sheet__close"
                            data-sheet-initial-focus
                            x-on:click="closeSheet()"
                            aria-label="Cerrar"
                        >
                            <x-ui.icon name="circle-x" class="size-5" />
                        </button>
                    </header>
                @endif

                <div class="avicore-sheet__body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>
