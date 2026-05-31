<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div
        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-avicore-primary text-lg font-bold text-white shadow-sm ring-1 ring-avicore-primary/20"
        aria-hidden="true"
    >
        A
    </div>
    <div>
        <p class="text-lg font-semibold tracking-tight text-avicore-primary">AviCore</p>
        @isset($subtitle)
            <p class="text-sm text-avicore-muted">{{ $subtitle }}</p>
        @endisset
    </div>
</div>
