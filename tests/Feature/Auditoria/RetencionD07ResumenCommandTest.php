<?php

namespace Tests\Feature\Auditoria;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetencionD07ResumenCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_comando_muestra_politica_configurada(): void
    {
        $this->artisan('avicore:retencion-d07')
            ->assertSuccessful()
            ->expectsOutputToContain('Operativa')
            ->expectsOutputToContain('Auditoría')
            ->expectsOutputToContain('Documentos emitidos')
            ->expectsOutputToContain('Disco documentos:');
    }
}
