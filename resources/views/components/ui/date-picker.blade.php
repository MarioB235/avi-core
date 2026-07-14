@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
    'placeholder' => 'Elegí un día',
    'min' => null,
    'max' => null,
    'today' => null,
    'panelTitle' => 'Elegí un día',
])

@php
    $wireDirective = $attributes->wire('model');
    $wireModel = $wireDirective->value();
    $wireModelLive = $wireDirective->hasModifier('live');
    $bagError = ($name && isset($errors) && $errors->has($name))
        ? (string) $errors->first($name)
        : null;
    $resolvedError = $error ?: $bagError;
    $hasError = filled($resolvedError);
    $pickerId = $attributes->get('id') ?? $name ?? 'date-picker-'.uniqid();
    $isRequired = $attributes->has('required');
    $todayIso = $today ?? now()->toDateString();
    $maxIso = $max;
    $minIso = $min;
    $titleId = $pickerId.'-title';
@endphp

<div
    {{ $attributes->only('class')->merge(['class' => 'avicore-date-picker-field space-y-1.5']) }}
    x-data="{
        open: false,
        previousFocus: null,
        value: @if ($wireModel)
            @if ($wireModelLive)
                @entangle($wireModel).live
            @else
                @entangle($wireModel).defer
            @endif
        @else
            @js((string) $attributes->get('value', ''))
        @endif,
        today: @js($todayIso),
        min: @js($minIso),
        max: @js($maxIso),
        viewYear: {{ (int) now()->year }},
        viewMonth: {{ (int) now()->month - 1 }},
        placeholderText: @js($placeholder),
        weekdays: ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'],
        monthNames: [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ],
        monthNamesShort: [
            'ene', 'feb', 'mar', 'abr', 'may', 'jun',
            'jul', 'ago', 'sep', 'oct', 'nov', 'dic',
        ],
        init() {
            this.syncViewFromValue();
            this.$watch('value', () => this.syncViewFromValue());
            this.$watch('open', (isOpen) => this.syncBodyScroll(isOpen));
        },
        syncBodyScroll(isOpen) {
            document.body.style.overflow = isOpen ? 'hidden' : '';
        },
        pad(value) {
            return String(value).padStart(2, '0');
        },
        toIso(year, monthIndex, day) {
            return `${year}-${this.pad(monthIndex + 1)}-${this.pad(day)}`;
        },
        parseIso(iso) {
            if (! iso || typeof iso !== 'string') {
                return null;
            }

            const parts = iso.split('-');
            if (parts.length !== 3) {
                return null;
            }

            const year = Number(parts[0]);
            const month = Number(parts[1]);
            const day = Number(parts[2]);

            if (! year || ! month || ! day) {
                return null;
            }

            return { year, monthIndex: month - 1, day };
        },
        syncViewFromValue() {
            const parsed = this.parseIso(this.value) ?? this.parseIso(this.today);
            if (! parsed) {
                return;
            }

            this.viewYear = parsed.year;
            this.viewMonth = parsed.monthIndex;
        },
        triggerLabel() {
            const parsed = this.parseIso(this.value);
            if (! parsed) {
                return this.placeholderText;
            }

            return `${parsed.day} ${this.monthNamesShort[parsed.monthIndex]} ${parsed.year}`;
        },
        monthLabel() {
            return `${this.monthNames[this.viewMonth]} ${this.viewYear}`;
        },
        dayAriaLabel(cell) {
            if (! cell || cell.empty) {
                return '';
            }

            let label = `${cell.day} de ${this.monthNames[this.viewMonth]} de ${this.viewYear}`;

            if (cell.today) {
                label += ', hoy';
            }

            if (cell.selected) {
                label += ', seleccionado';
            }

            if (cell.disabled) {
                label += ', no disponible';
            }

            return label;
        },
        isDisabled(iso) {
            if (this.min && iso < this.min) {
                return true;
            }

            if (this.max && iso > this.max) {
                return true;
            }

            return false;
        },
        canGoPrev() {
            if (! this.min) {
                return true;
            }

            const prev = new Date(this.viewYear, this.viewMonth, 0);
            const lastIso = this.toIso(prev.getFullYear(), prev.getMonth(), prev.getDate());

            return lastIso >= this.min;
        },
        canGoNext() {
            if (! this.max) {
                return true;
            }

            const next = new Date(this.viewYear, this.viewMonth + 1, 1);
            const firstIso = this.toIso(next.getFullYear(), next.getMonth(), 1);

            return firstIso <= this.max;
        },
        prevMonth() {
            if (! this.canGoPrev()) {
                return;
            }

            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear -= 1;

                return;
            }

            this.viewMonth -= 1;
        },
        nextMonth() {
            if (! this.canGoNext()) {
                return;
            }

            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear += 1;

                return;
            }

            this.viewMonth += 1;
        },
        cells() {
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const startPad = (first.getDay() + 6) % 7;
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const list = [];

            for (let i = 0; i < startPad; i++) {
                list.push({ key: `e-${i}`, empty: true });
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const iso = this.toIso(this.viewYear, this.viewMonth, day);
                list.push({
                    key: iso,
                    empty: false,
                    day,
                    iso,
                    disabled: this.isDisabled(iso),
                    selected: String(this.value ?? '') === iso,
                    today: iso === this.today,
                });
            }

            return list;
        },
        openPicker() {
            this.previousFocus = document.activeElement;
            this.syncViewFromValue();
            this.open = true;
            this.$nextTick(() => {
                this.$refs.panel?.focus?.();
            });
        },
        closePicker() {
            this.open = false;
            this.$nextTick(() => {
                (this.previousFocus ?? this.$refs.trigger)?.focus?.();
                this.previousFocus = null;
            });
        },
        selectDay(iso) {
            if (this.isDisabled(iso)) {
                return;
            }

            this.value = iso;
            this.closePicker();
        },
        selectToday() {
            if (this.isDisabled(this.today)) {
                return;
            }

            this.selectDay(this.today);
        },
        trapTab(event) {
            if (! this.open) {
                return;
            }

            const panel = this.$refs.panel;
            if (! panel) {
                return;
            }

            const focusable = [...panel.querySelectorAll(
                'button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])'
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
    x-on:keydown.escape.window="if (open) closePicker()"
    x-on:keydown.tab.window="trapTab($event)"
    x-on:alpine:destroy="document.body.style.overflow = ''"
>
    @if ($label)
        <label for="{{ $pickerId }}" class="block text-sm font-medium text-avicore-text">
            {{ $label }}
        </label>
    @endif

    <button
        type="button"
        id="{{ $pickerId }}"
        x-ref="trigger"
        @if ($name) name="{{ $name }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($isRequired) aria-required="true" @endif
        aria-haspopup="dialog"
        :aria-expanded="open"
        aria-controls="{{ $pickerId }}-panel"
        x-on:click="openPicker()"
        @class([
            'avicore-date-picker-trigger',
            'avicore-date-picker-trigger--error' => $hasError,
        ])
    >
        <span
            class="min-w-0 flex-1 truncate text-left"
            :class="(value === '' || value === null) ? 'text-avicore-muted' : 'text-avicore-text'"
            x-text="triggerLabel()"
        ></span>
        <span class="pointer-events-none flex size-10 shrink-0 items-center justify-center text-avicore-primary" aria-hidden="true">
            <x-ui.icon name="calendar" class="size-5" />
        </span>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="avicore-date-picker-overlay"
            role="presentation"
        >
            <button
                type="button"
                class="avicore-date-picker-backdrop"
                x-show="open"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="closePicker()"
                aria-label="Cerrar calendario"
            ></button>

            <div
                id="{{ $pickerId }}-panel"
                x-ref="panel"
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $titleId }}"
                tabindex="-1"
                class="avicore-date-picker-panel"
                x-show="open"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0"
                x-on:click.stop
            >
                <div class="avicore-date-picker-panel__handle" aria-hidden="true"></div>

                <header class="avicore-date-picker-panel__header">
                    <h2 id="{{ $titleId }}" class="avicore-date-picker-panel__title">{{ $panelTitle }}</h2>
                    <button
                        type="button"
                        class="avicore-date-picker-panel__close"
                        x-on:click="closePicker()"
                        aria-label="Cerrar"
                    >
                        <x-ui.icon name="circle-x" class="size-5" />
                    </button>
                </header>

                <div class="avicore-date-picker-panel__body">
                    <div class="avicore-date-picker-nav">
                        <button
                            type="button"
                            class="avicore-date-picker-nav__btn"
                            x-on:click="prevMonth()"
                            x-bind:disabled="! canGoPrev()"
                            aria-label="Mes anterior"
                        >
                            <x-ui.icon name="chevron-left" class="size-5" />
                        </button>
                        <p class="avicore-date-picker-nav__label" x-text="monthLabel()" aria-live="polite"></p>
                        <button
                            type="button"
                            class="avicore-date-picker-nav__btn"
                            x-on:click="nextMonth()"
                            x-bind:disabled="! canGoNext()"
                            aria-label="Mes siguiente"
                        >
                            <x-ui.icon name="chevron-right" class="size-5" />
                        </button>
                    </div>

                    <div class="avicore-date-picker-weekdays" aria-hidden="true">
                        <template x-for="weekday in weekdays" :key="weekday">
                            <span class="avicore-date-picker-weekday" x-text="weekday"></span>
                        </template>
                    </div>

                    <div class="avicore-date-picker-grid" role="grid" aria-label="Calendario">
                        <template x-for="cell in cells()" :key="cell.key">
                            <div class="avicore-date-picker-cell" role="gridcell">
                                <span
                                    x-show="cell.empty"
                                    class="avicore-date-picker-day avicore-date-picker-day--empty"
                                    aria-hidden="true"
                                ></span>
                                <button
                                    type="button"
                                    x-show="! cell.empty"
                                    class="avicore-date-picker-day"
                                    x-bind:class="{
                                        'avicore-date-picker-day--selected': cell.selected,
                                        'avicore-date-picker-day--today': cell.today && ! cell.selected,
                                        'avicore-date-picker-day--disabled': cell.disabled,
                                    }"
                                    x-bind:disabled="cell.disabled"
                                    x-bind:aria-pressed="cell.selected ? 'true' : 'false'"
                                    x-bind:aria-label="dayAriaLabel(cell)"
                                    x-on:click="selectDay(cell.iso)"
                                    x-text="cell.day"
                                ></button>
                            </div>
                        </template>
                    </div>

                    <div class="avicore-date-picker-footer">
                        <button
                            type="button"
                            class="avicore-date-picker-today"
                            x-on:click="selectToday()"
                            x-bind:disabled="isDisabled(today)"
                        >
                            Hoy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @if ($hint && ! $hasError)
        <p class="text-xs text-avicore-muted">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="text-sm text-avicore-danger" role="alert">{{ $resolvedError }}</p>
    @endif
</div>
