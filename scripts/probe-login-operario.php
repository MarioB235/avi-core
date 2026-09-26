<?php

declare(strict_types=1);
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;

/**
 * Prueba HTTP del login demo (perfil Operario) contra el servidor local en ejecución.
 * Uso: php scripts/probe-login-operario.php
 */

require __DIR__.'/../vendor/autoload.php';

$baseUrl = getenv('AVICORE_PROBE_URL') ?: 'http://127.0.0.1:8000';
$cookieJar = tempnam(sys_get_temp_dir(), 'avicore_probe_');

$client = new Client([
    'base_uri' => rtrim($baseUrl, '/').'/',
    'cookies' => new FileCookieJar($cookieJar, true),
    'http_errors' => false,
    'allow_redirects' => false,
]);

$loginResponse = $client->get('login');
$loginHtml = (string) $loginResponse->getBody();
$loginStatus = $loginResponse->getStatusCode();

if ($loginStatus !== 200) {
    fwrite(STDERR, "GET /login devolvió {$loginStatus}\n");
    exit(1);
}

if (! preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginHtml, $csrfMatch)) {
    fwrite(STDERR, "No se encontró csrf-token en /login\n");
    exit(1);
}

$csrf = $csrfMatch[1];

if (! preg_match('/wire:snapshot="([^"]+)"/', $loginHtml, $snapshotMatch)) {
    fwrite(STDERR, "No se encontró wire:snapshot en /login\n");
    exit(1);
}

if (! preg_match('/livewire-[a-f0-9]+\/update/', $loginHtml, $updatePathMatch)) {
    fwrite(STDERR, "No se encontró ruta livewire/update en /login\n");
    exit(1);
}

$updatePath = $updatePathMatch[0];
$snapshot = html_entity_decode($snapshotMatch[1], ENT_QUOTES);

$payload = [
    '_token' => $csrf,
    'components' => [
        [
            'snapshot' => $snapshot,
            'updates' => [
                'demoRole' => 'operario',
            ],
            'calls' => [
                [
                    'path' => '',
                    'method' => 'login',
                    'params' => [],
                ],
            ],
        ],
    ],
];

$updateResponse = $client->post($updatePath, [
    'headers' => [
        'X-Livewire' => 'true',
        'X-CSRF-TOKEN' => $csrf,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
    'json' => $payload,
]);

$updateStatus = $updateResponse->getStatusCode();
$updateBody = (string) $updateResponse->getBody();
$updateJson = json_decode($updateBody, true);

if ($updateStatus !== 200 || ! is_array($updateJson)) {
    fwrite(STDERR, "POST /livewire/update devolvió {$updateStatus}\n{$updateBody}\n");
    exit(1);
}

$redirect = $updateJson['components'][0]['effects']['redirect'] ?? null;

if ($redirect === null) {
    fwrite(STDERR, "Login no redirigió. Respuesta:\n{$updateBody}\n");
    exit(1);
}

$homeResponse = $client->get(ltrim(parse_url($redirect, PHP_URL_PATH) ?: '/operario', '/'), [
    'headers' => [
        'Accept' => 'text/html',
    ],
]);

$homeStatus = $homeResponse->getStatusCode();
$homeHtml = (string) $homeResponse->getBody();

$checks = [
    'redirect_operario' => str_contains($redirect, '/operario'),
    'home_status_200' => $homeStatus === 200,
    'operario_body' => str_contains($homeHtml, 'avicore-operario-body'),
    'operario_home' => str_contains($homeHtml, 'avicore-operario-home'),
];

echo json_encode([
    'login_status' => $loginStatus,
    'demo_role_field' => str_contains($loginHtml, 'name="demoRole"'),
    'update_status' => $updateStatus,
    'redirect' => $redirect,
    'home_status' => $homeStatus,
    'checks' => $checks,
    'success' => ! in_array(false, $checks, true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;

@unlink($cookieJar);

exit($checks['redirect_operario'] && $checks['home_status_200'] && $checks['operario_body'] ? 0 : 1);
