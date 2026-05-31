<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-avicore-primary text-lg font-bold text-white">
        A
    </div>
    <div>
        <p class="text-lg font-semibold text-avicore-primary">AviCore</p>
        @isset($subtitle)
            <p class="text-sm text-avicore-muted">{{ $subtitle }}</p>
        @endisset
    </div>
</div>
