# Retención D07 (AUD-08)

**Alcance:** política operativa acordada para historial, auditoría y documentos emitidos. **No** constituye asesoramiento legal ni plazos normativos MGAP/SMA (ver D05).

## 1. Principios

1. **No borrar** registros operativos, correcciones ni auditoría; anulación lógica cuando aplique.
2. **Documentos emitidos** (PDF/Excel) se almacenan como copia inmutable al generarse (REP-11).
3. **Purga automática** deshabilitada en MVP (`purge_habilitado = false`); cualquier depuración futura requiere OPS-06 y acuerdo explícito.

## 2. Plazos acordados (meses)

Configuración: `config/avicore.php` → `retencion.d07`.

| Categoría | Clave config | Default MVP |
|-----------|--------------|-------------|
| Historial operativo y anulaciones | `operativa_meses` | 60 |
| Bitácora `auditorias` | `auditoria_meses` | 60 |
| `correcciones_registro_operativo` | `correcciones_meses` | 60 |
| `documentos_emitidos` | `documentos_emitidos_meses` | 60 |
| Datos de usuarios | `usuarios_meses` | 60 |

Helper: `App\Support\PoliticaRetencionD07`.

## 3. Documentos emitidos

| Pieza | Rol |
|-------|-----|
| Tabla `documentos_emitidos` | Metadatos, filtros, fecha de corte, checksum |
| `RegistrarDocumentoEmitidoAction` | Persiste archivo en disco privado + fila (sin sobrescribir) |
| `DocumentoEmitido` | `PreventsHardDelete` — no borrado desde app |

Almacenamiento: disco `local` (privado) por defecto; ruta `empresas/{codigo}/documentos-emitidos/{tipo}/{Y/m}/…`.

## 4. Inmutabilidad en código

`PreventsHardDelete` en: `RegistroOperativo`, `Vacunacion`, `CorreccionRegistroOperativo`, `Auditoria`, `DocumentoEmitido` (+ estructura EST-09).

## 5. Verificación

```bash
php artisan test tests/Feature/Auditoria/RetencionD07Test.php tests/Unit/Support/PoliticaRetencionD07Test.php
php artisan avicore:retencion-d07
```
