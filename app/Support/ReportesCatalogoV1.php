<?php

namespace App\Support;

/**
 * Catálogo canónico de reportes v1 (REP-01).
 * Narrativa: `.cursor/skills/avicore-reportes/references/reportes.md`.
 */
final class ReportesCatalogoV1
{
    public const VERSION = 'v1-operativo-2026-09-29';

    /**
     * @return array<string, array{
     *     titulo: string,
     *     necesidad: string,
     *     destinatarios: list<string>,
     *     filtros: string,
     *     contenido: string,
     *     fuentes_consulta: list<string>,
     *     formatos: list<string>,
     *     d05: string,
     *     implementacion: string,
     * }>
     */
    public static function definiciones(): array
    {
        return [
            'produccion_diaria' => [
                'titulo' => 'Producción diaria',
                'necesidad' => 'Dueño o encargado necesita el mismo detalle que Resumen/Historial en Excel o PDF para reunión o archivo, sin rearmar totales a mano.',
                'destinatarios' => ['Dueño', 'Administrativo', 'Encargado'],
                'filtros' => 'Empresa, granja, galpón, período (día o rango de días operativos).',
                'contenido' => 'Huevos aptos, huevos de descarte, muertes, descarte de aves y kg entregados (remito); totales alineados con consulta en pantalla.',
                'fuentes_consulta' => [
                    'ReporteConsultaService',
                    'TotalesCapturaDiaService',
                    'AlimentoEntregaSemantica',
                ],
                'formatos' => ['pdf', 'excel'],
                'd05' => 'Reporte interno v1; no sustituye planilla MGAP oficial (REP-12/13).',
                'implementacion' => 'ReporteConsultaService + Excel/PDF producción diaria (REP-02–04).',
            ],
            'movimientos_existencias' => [
                'titulo' => 'Movimientos y existencias de aves',
                'necesidad' => 'Supervisor audita saldo inicial, entradas, traslados, ajustes, faenas/cierres y saldo final por galpón o lote.',
                'destinatarios' => ['Dueño', 'Administrativo', 'Encargado'],
                'filtros' => 'Empresa, granja, galpón, lote cuando aplique, período.',
                'contenido' => 'Ledger de movimientos con tipo, cantidades, origen/destino, reversiones identificadas y conciliación aritmética.',
                'fuentes_consulta' => [
                    'MovimientoAvesVistaPreviaService',
                    'RegistrarTrasladoAvesAction',
                    'RegistrarCierreLoteAction',
                ],
                'formatos' => ['pdf', 'excel'],
                'd05' => 'Trazabilidad interna; sin envío SMA automático.',
                'implementacion' => 'ReporteConsultaService::movimientosExistencias + Excel/PDF (REP-05).',
            ],
            'historia_lote' => [
                'titulo' => 'Historia de lote',
                'necesidad' => 'Responder trazabilidad de un lote: alta, ubicaciones, movimientos y producción atribuible sin mezclar todo el galpón.',
                'destinatarios' => ['Dueño', 'Administrativo', 'Encargado'],
                'filtros' => 'Lote, rango de fechas; galpón derivado del lote.',
                'contenido' => 'Alta del lote, traslados, cierres/reaperturas, vacunas vinculadas y producción con límite explícito por atribución.',
                'fuentes_consulta' => [
                    'EstructuraFichaService',
                    'MortalidadVentanaGalpon',
                ],
                'formatos' => ['pdf', 'excel'],
                'd05' => 'Interno; atribución multi-lote en galpón sigue reglas RES-05 (no tasa por lote inventada).',
                'implementacion' => 'ReporteConsultaService::historiaLote + Excel (REP-06).',
            ],
            'sanidad_basica' => [
                'titulo' => 'Sanidad básica (vacunaciones)',
                'necesidad' => 'Listar vacunaciones aplicadas y anulaciones autorizadas para control sanitario operativo, sin módulo veterinario completo.',
                'destinatarios' => ['Dueño', 'Administrativo', 'Encargado'],
                'filtros' => 'Galpón, lote, período.',
                'contenido' => 'Fecha, producto/vacuna registrada, operario, estado activo/anulado; sin diagnóstico ni prescripción.',
                'fuentes_consulta' => [
                    'Vacunacion',
                    'AnularVacunacionAction',
                ],
                'formatos' => ['pdf', 'excel'],
                'd05' => 'No reemplaza planilla GBPEA completa (post-MVP).',
                'implementacion' => 'ReporteConsultaService::sanidadBasica + Excel (REP-06).',
            ],
            'auditoria_operativa' => [
                'titulo' => 'Auditoría operativa',
                'necesidad' => 'Dueño o administración revisa quién cambió qué (correcciones, anulaciones, movimientos sensibles) con motivo.',
                'destinatarios' => ['Dueño', 'Administrativo'],
                'filtros' => 'Actor, tipo de evento, período.',
                'contenido' => 'Eventos de auditoría ya persistidos; permiso restringido respecto a operario.',
                'fuentes_consulta' => [
                    'SoporteEmpresaService',
                    'CorreccionRegistroOperativo',
                ],
                'formatos' => ['pdf', 'excel'],
                'd05' => 'Interno; sin certificación externa.',
                'implementacion' => 'Pendiente REP-08.',
            ],
        ];
    }

    /**
     * Salidas normativas fuera del catálogo operativo v1 (decisión D05).
     *
     * @return array<string, array{titulo: string, estado: string, motivo: string}>
     */
    public static function exclusionesNormativasD05(): array
    {
        return [
            'mgap_anexo2_ponedoras' => [
                'titulo' => 'Registro productivo ponedoras (Anexo Nº 2)',
                'estado' => 'bloqueado_hasta_REP-12',
                'motivo' => 'Fuente, layout y validación competente pendientes (D05); v1 entrega reportes internos completos primero.',
            ],
            'mgap_anexo2_reproductoras' => [
                'titulo' => 'Registro productivo reproductoras (misma plantilla)',
                'estado' => 'bloqueado_hasta_REP-12',
                'motivo' => 'Misma puerta D05; no etiquetar como certificación sin REP-13.',
            ],
            'control_sanitario_gbpea' => [
                'titulo' => 'Control sanitario GBPEA completo',
                'estado' => 'post_mvp',
                'motivo' => 'ATB, tiempos de espera y bloque D fuera del alcance operario actual.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function idsOperativosV1(): array
    {
        return array_keys(self::definiciones());
    }

    public static function cantidadOperativosV1(): int
    {
        return count(self::definiciones());
    }
}
