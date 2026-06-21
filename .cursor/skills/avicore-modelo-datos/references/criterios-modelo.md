# 04 — Modelo de datos inicial

> **Esquema canónico (tablas y campos):** [`esquema-bd.md`](esquema-bd.md) — actualizar primero al cambiar BD y registrar en [`docs/CHANGELOG.md`](../../../../docs/CHANGELOG.md).

## 1. Criterio general

El modelo debe soportar:

- Multiempresa.
- Granjas.
- Galpones.
- Lotes.
- Cargas operativas.
- Aves vivas.
- Auditoría.
- Reportes.
- Tiempo real.
- Datos demo.

---

## 2. Entidades y reglas (resumen)

Detalle de columnas en [`esquema-bd.md`](esquema-bd.md).

| Entidad | Reglas clave |
|---------|----------------|
| **empresas** | Cliente multiempresa; estados activa/suspendida/inactiva |
| **users** | Login por documento; único por `(empresa_id, documento)`; documento único global si `empresa_id` null (Admin AviCore); contraseña temporal obliga cambio |
| **granjas / galpones / lotes** | Jerarquía empresa → granja → galpón → lote; carga por galpón |
| **registros_operativos** | Fecha/hora = `created_at`; anulación lógica con motivo |
| **movimientos_aves** | *Planificado* — traslados, ajustes, cierres; ver `avicore-contexto/references/plan-desarrollo.md` |
| **auditorias** | *Planificado* — acciones críticas; ver `avicore-contexto/references/plan-desarrollo.md` |
| **alertas** | *Planificado* — dashboard y supervisión; ver `avicore-contexto/references/plan-desarrollo.md` |
| **configuraciones_empresa** | *Planificado* — maple/cajón, logos, módulos; ver `avicore-contexto/references/plan-desarrollo.md` |

---

## 3. Índices recomendados

- empresa_id en tablas principales.
- galpon_id en registros_operativos.
- created_at en registros_operativos.
- estado en lotes.
- tipo en registros_operativos.
- documento + empresa_id en users.
