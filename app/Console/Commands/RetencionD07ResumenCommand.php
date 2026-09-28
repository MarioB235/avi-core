<?php

namespace App\Console\Commands;

use App\Support\PoliticaRetencionD07;
use Illuminate\Console\Command;

class RetencionD07ResumenCommand extends Command
{
    protected $signature = 'avicore:retencion-d07';

    protected $description = 'Muestra la política de retención D07 configurada (AUD-08)';

    public function handle(PoliticaRetencionD07 $politica): int
    {
        $resumen = $politica->resumenOperativo();

        $this->line($resumen['nota']);
        $this->newLine();
        $this->table(
            ['Categoría', 'Meses'],
            [
                ['Operativa', $resumen['operativa_meses']],
                ['Auditoría', $resumen['auditoria_meses']],
                ['Correcciones', $resumen['correcciones_meses']],
                ['Documentos emitidos', $resumen['documentos_emitidos_meses']],
                ['Usuarios', $resumen['usuarios_meses']],
            ],
        );
        $this->line('Purga habilitada: '.($resumen['purge_habilitado'] ? 'sí' : 'no'));
        $this->line('Disco documentos: '.$resumen['documentos_disk']);

        return self::SUCCESS;
    }
}
