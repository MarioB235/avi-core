<?php

declare(strict_types=1);

/**
 * Arranque de desarrollo multi-proceso (serve + queue + Vite; + Pail si hay pcntl).
 * Pail requiere ext-pcntl (Linux/macOS). En Windows se omite sin tumbar el resto.
 */
$hasPcntl = function_exists('pcntl_fork');

$processes = [
    'php artisan serve',
    'php artisan queue:listen --tries=1 --timeout=0',
];

if ($hasPcntl) {
    $processes[] = 'php artisan pail --timeout=0';
}

$processes[] = 'npm run dev';

$names = $hasPcntl ? 'server,queue,logs,vite' : 'server,queue,vite';
$colors = $hasPcntl ? '#93c5fd,#c4b5fd,#fb7185,#fdba74' : '#93c5fd,#c4b5fd,#fdba74';

$quotedProcesses = array_map(
    static fn (string $command): string => '"'.str_replace('"', '\\"', $command).'"',
    $processes
);

$command = sprintf(
    'npx concurrently -c "%s" %s --names=%s --kill-others',
    $colors,
    implode(' ', $quotedProcesses),
    $names
);

if (! $hasPcntl) {
    fwrite(
        STDERR,
        "Aviso: Pail omitido (ext-pcntl no disponible en Windows). Logs en storage/logs/laravel.log\n"
    );
}

passthru($command, $exitCode);

exit($exitCode);
