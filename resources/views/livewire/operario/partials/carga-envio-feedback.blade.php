@if ($cargaEnvioError)
    <div
        role="alert"
        aria-live="assertive"
        class="rounded-lg border border-avicore-danger/40 bg-red-50 px-3 py-3 text-sm text-avicore-danger"
    >
        <p class="font-medium text-avicore-text">No pudimos confirmar el guardado</p>
        <p class="mt-1">{{ $cargaEnvioError }}</p>
    </div>
@endif
