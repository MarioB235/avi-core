<?php

declare(strict_types=1);

use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/** @var array<string, mixed> $payload */
$payload = json_decode((string) file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);

$resultPath = (string) $payload['result_path'];

try {
    $user = User::query()->findOrFail((int) $payload['user_id']);

    match ((string) $payload['action']) {
        'traslado' => (function () use ($user, $payload): void {
            $params = $payload['params'];
            app(RegistrarTrasladoAvesAction::class)->execute(
                $user,
                Galpon::query()->findOrFail((int) $params['origen_id']),
                Galpon::query()->findOrFail((int) $params['destino_id']),
                Lote::query()->findOrFail((int) $params['lote_id']),
                (int) $params['cantidad'],
                (string) $params['motivo'],
            );
        })(),
        'cierre' => (function () use ($user, $payload): void {
            $params = $payload['params'];
            app(RegistrarCierreLoteAction::class)->execute(
                $user,
                Galpon::query()->findOrFail((int) $params['galpon_id']),
                Lote::query()->findOrFail((int) $params['lote_id']),
                (int) $params['cantidad'],
                (string) $params['motivo'],
                cerrarCicloLote: (bool) ($params['cerrar_ciclo'] ?? true),
            );
        })(),
        'muertes' => (function () use ($user, $payload): void {
            $params = $payload['params'];
            app(RegistrarCargaMuertesAction::class)->execute(
                $user,
                Galpon::query()->findOrFail((int) $params['galpon_id']),
                (int) $params['muertes'],
            );
        })(),
        default => throw new InvalidArgumentException('Acción desconocida: '.$payload['action']),
    };

    file_put_contents($resultPath, json_encode(['ok' => true], JSON_THROW_ON_ERROR));
} catch (ValidationException $exception) {
    file_put_contents($resultPath, json_encode([
        'ok' => false,
        'type' => ValidationException::class,
        'errors' => $exception->errors(),
    ], JSON_THROW_ON_ERROR));
} catch (Throwable $exception) {
    file_put_contents($resultPath, json_encode([
        'ok' => false,
        'type' => $exception::class,
        'message' => $exception->getMessage(),
    ], JSON_THROW_ON_ERROR));
}
