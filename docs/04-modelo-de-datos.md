# 04 — Modelo de datos inicial

> **Esquema canónico (tablas y campos):** [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md) — actualizar primero al cambiar BD y registrar en [`CHANGELOG.md`](CHANGELOG.md).

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

Detalle de columnas en [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md).

| Entidad | Reglas clave |
|---------|----------------|
| **empresas** | Cliente multiempresa; estados activa/suspendida/inactiva |
| **users** | Login por documento; único por `empresa_id`; contraseña temporal obliga cambio |
| **granjas / galpones / lotes** | Jerarquía empresa → granja → galpón → lote; carga por galpón |
| **registros_operativos** | Fecha/hora = `created_at`; anulación lógica con motivo |
| **movimientos_aves** | Traslados, ajustes, cierres; impactan aves vivas |
| **auditorias** | Acciones críticas; modo soporte auditado |
| **alertas** | Dashboard y supervisión |
| **configuraciones_empresa** | Maple/cajón, logos, módulos por empresa |

---

## 3. Índices recomendados

- empresa_id en tablas principales.
- galpon_id en registros_operativos.
- created_at en registros_operativos.
- estado en lotes.
- tipo en registros_operativos.
- documento + empresa_id en users.
