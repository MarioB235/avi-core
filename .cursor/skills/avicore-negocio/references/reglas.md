# 05 — Reglas de negocio

## 1. Multiempresa

1. Cada empresa ve solamente sus datos.
2. Toda tabla operativa debe tener empresa_id.
3. El Admin AviCore no accede libremente a datos productivos reales.
4. El modo soporte requiere motivo y auditoría.

---

## 2. Usuarios

1. El login se realiza con documento y contraseña.
2. El documento es único dentro de cada empresa (`empresa_id` + `documento`).
3. Admin AviCore (`empresa_id` null) tiene documento único a nivel global.
4. Los usuarios son creados por administrador o perfil autorizado.
5. Todo usuario nuevo puede tener contraseña temporal (`must_change_password`).
6. El primer ingreso exige cambio obligatorio de contraseña antes de usar el sistema.
7. La nueva contraseña debe cumplir política mínima: 8+ caracteres, letras, mayúsculas/minúsculas y números; no puede repetir la actual.
8. Tras 5 intentos fallidos de login por documento e IP en 60 segundos, se bloquea temporalmente el acceso; en login demo (`AVICORE_DEMO_LOGIN=true`) el mensaje va en el campo `demoRole`.
9. Si un documento resuelve más de una cuenta activa con credenciales válidas, se rechaza el login (ambigüedad).
10. Usuario inactivo o empresa no activa impiden el acceso (Admin AviCore exceptuado de validación de empresa).
11. Usuario no Admin AviCore sin `empresa_id` asignado no puede iniciar sesión.
12. La recuperación de contraseña en MVP la realiza administrador o encargado autorizado. En login y cambio obligatorio de contraseña, el enlace «¿Olvidaste tu contraseña?» abre un diálogo con contacto de soporte (WhatsApp y/o correo desde `config/avicore.php` / `.env`, URLs validadas en `SupportContactService`); no hay reset automático por correo.
13. Login demo MVP (`AVICORE_DEMO_LOGIN=true`): un solo usuario (`000000000`); el selector asigna el rol al entrar (sin credenciales en pantalla). Desactivar antes de go-live. Detalle: [`demo.md`](../../avicore-datos-demo/references/demo.md) § 4.
14. **Autogestión de perfil:** todo usuario autenticado puede editar su nombre y correo, y cambiar su contraseña voluntariamente (`/perfil` o `/operario/perfil`). No puede cambiar documento, rol ni empresa; eso lo hace un administrador.

---

## 3. Galpones

1. La carga operativa se realiza por galpón.
2. El operario puede elegir cualquier galpón **disponible para carga** de su empresa (`activo` y `estado = activo`).
3. El sistema recuerda el último galpón seleccionado (`users.ultimo_galpon_id`).
4. Si el galpón recordado deja de estar disponible (inactivo, mantenimiento, etc.), el operario debe elegir otro en el selector de **Inicio**; las pantallas de carga redirigen a `/operario` con el selector abierto (flash `abrirSelectorGalpon` desde `CargarHub`/`CargaHuevos`; `Home` también abre el selector con `?abrir_galpon=1` desde enlaces del hero).
5. Un galpón puede tener uno o varios lotes.
6. Si tiene varios lotes, se muestra aviso informativo.
7. El aviso no bloquea la carga.

---

## 4. Lotes

1. El lote conserva información histórica.
2. El lote puede trasladarse.
3. El lote cerrado no permite cargas normales.
4. La reapertura de lote cerrado requiere perfil superior y auditoría.
5. El tipo de huevo se define en el lote.
6. No se debe usar solamente fecha de nacimiento como identificador.
7. **Alta de lote (hub Cargar):** solo perfiles con permiso «Crear lote» (dueño, administrativo, encargado; **no** operario). `fecha_ingreso` = día del registro (hoy). `codigo` generado en servidor: `{codigo_galpon}-{YYYYMMDD}-{B|C}-{secuencia}` (B=blanco, C=color; secuencia por galpón + día + tipo). Índice único `(empresa_id, codigo)`. Si el usuario marca ambos tipos (Blanca y Colorada), se crea **un lote por tipo**. `cantidad_inicial` suma a `aves_actuales` del galpón (transacción + `lockForUpdate`). Estado inicial: `activo`. Validación vía `LotePolicy::create` + `RegistrarLoteAction`.

---

## 5. Producción

1. La producción se carga por galpón.
2. Si hay varios lotes en el galpón, la producción se asigna al galpón completo.
3. La unidad principal es el huevo.
4. 1 maple equivale a 30 huevos.
5. **Inicio operario — acumulado:** huevos y muertes acumuladas del galpón seleccionado se calculan desde la `fecha_ingreso` más antigua entre lotes con estado `activo` o `en_produccion` del galpón; registros anteriores a esa ventana no cuentan. Sin lotes activos, no hay ventana de acumulado.
6. **avicore-defer:** objetivo diario por galpón (KPI «Objetivo» en Inicio operario) — pendiente definir meta y umbral por empresa/galpón.
7. El cajón es configurable por empresa.
8. Los reportes del MVP muestran huevos.

---

## 6. Mortalidad

1. Las muertes se cargan por galpón.
2. Si hay varios lotes, la mortalidad se asigna al galpón completo.
3. Las muertes descuentan aves vivas (`aves_actuales` del galpón).
4. No se permite que aves vivas quede negativo; `RegistrarCargaMuertesAction` valida cantidad > 0 y ≤ aves vivas (transacción con `lockForUpdate` en el galpón).
5. Mismo criterio de permisos y empresa que huevos: `GalponPolicy::view`, `empresa_id` y galpón disponible para carga.

---

## 6.25 Descarte de aves (operario MVP)

1. **Descarte** = gallinas **vivas** que se sacan del galpón (no murieron en el piso). Distinto de mortalidad.
2. Tipo de registro `descarte`, campo `descarte_aves`.
3. Descuenta `aves_actuales` con las mismas validaciones que muertes (`RegistrarCargaDescarteAction`).

---

## 6.5 Vacunación (operario MVP)

1. La vacunación se registra **por lote** en el galpón de trabajo del operario.
2. Tipos MVP (`VacunaTipo`): Newcastle, Bronquitis, Gumboro, Encefalomielitis, Pox — catálogo fijo en enum.
3. Solo lotes con estado `activo` o `en_produccion` del galpón seleccionado.
4. El lote debe pertenecer al galpón y a la misma `empresa_id` del usuario.
5. Mismo criterio de permisos y galpón disponible que huevos/muertes: `GalponPolicy::view` vía `RegistrarVacunacionAction`.
6. Persistencia en tabla `vacunaciones` (no en `registros_operativos`).
7. Historial operario incluye vacunaciones activas del usuario, mezcladas con `registros_operativos` por `created_at` descendente (`OperarioHistorialItem`).
8. **avicore-defer:** plan sanitario completo (calendario, dosis, stock vacunas) — fuera del hub operario; ver `plan-desarrollo.md`.

---

## 7. Alimento

1. El MVP no maneja stock de alimento.
2. Solo se registra **alimento entregado** (kg del remito cuando llega el camión), no consumo diario estimado.
3. La unidad es kilos.
4. Se permiten decimales.
5. El alimento puede cargarse sin huevos ni muertes.
6. Puede haber varios días sin registro entre entregas.

---

## 8. Carga diaria

1. La carga es flexible y en tiempo real.
2. No existe cierre diario automático.
3. El operario no selecciona fecha ni hora.
4. Cada registro usa fecha y hora del momento.
5. Puede haber varias cargas del mismo galpón el mismo día.
6. Para guardar, debe existir al menos un dato cargado.

---

## 9. Anulación

1. Se usa “anular”, no eliminar.
2. Las FK de tablas operativas y de estructura avícola usan `ON DELETE RESTRICT` (no cascade): no se puede borrar físicamente un padre que tenga historial o hijos.
3. El registro anulado no cuenta en cálculos.
4. El registro anulado queda en auditoría.
5. El operario solo anula registros propios del día (desde **Historial** → detalle → motivo obligatorio).
6. Toda anulación requiere motivo obligatorio.
7. Muertes y descarte de aves anulados **restauran** `aves_actuales` del galpón.

---

## 10. Corrección

1. Toda corrección requiere motivo.
2. Debe guardarse valor anterior y valor nuevo.
3. Encargado o superior puede corregir.
4. Las correcciones deben auditarse.

---

## 11. Aves vivas

1. Se calculan principalmente por galpón.
2. Se actualizan con muertes, salidas, traslados y ajustes.
3. El ajuste manual solo lo puede hacer encargado o superior.
4. El ajuste impacta desde ese momento.
5. El ajuste no modifica reportes históricos ya generados.
6. Todo ajuste requiere motivo.

---

## 12. Reportes

1. Los reportes se generan manualmente.
2. PDF incluye logo empresa y marca AviCore.
3. Excel debe ser limpio para análisis.
4. Observaciones del operario no van en PDF principal.
5. Observaciones quedan en detalle operativo.

---

## 13. Tiempo real

1. Dashboard se actualiza con eventos relevantes.
2. Las alertas importantes aparecen sin recargar.
3. No se usa tiempo real para CRUD simples.
4. Los canales deben respetar empresa_id.

---

## 14. Demo

1. Debe existir empresa demo.
2. Debe tener 2 granjas.
3. Debe tener 8 galpones.
4. Debe tener 30 días de datos.
5. Debe incluir escenarios variados.

---

## 15. Coeficientes técnicos de referencia (Uruguay)

Valores de **referencia nacional** (MGAP / DIEA) para gráficos, desvíos y alertas. No son metas fijas por empresa hasta que exista configuración explícita (`avicore-defer`: umbrales por galpón/empresa).

| Coeficiente | Referencia sector Uruguay | Fuente de datos AviCore |
|-------------|---------------------------|-------------------------|
| Postura | ~269–278 huevos por gallina al año | Huevos diarios + aves vivas + edad del lote |
| Conversión alimenticia | ~121–125 g alimento por ave y día | Alimento (kg) + aves vivas |
| Mortalidad | ~1,0%–1,1% (tasa aceptada en encuestas) | Muertes acumuladas vs. aves |
| Ciclo de lote | Postura semana 19–20; descarte semana 86–87 | `fecha_ingreso` del lote + estado |

**Implementación:** recolección operativa en MVP; cálculo automático, curvas y alertas → post-MVP (dashboard fase 17). Detalle mercado y SMA/SNIG: [`mercado-uruguay.md`](../../avicore-contexto/references/mercado-uruguay.md).

---

## 16. Planilla MGAP Anexo Nº 2 (ponedoras — ciclo largo)

Referencia: [`mercado-uruguay.md`](../../avicore-contexto/references/mercado-uruguay.md) §4 · export: [`reportes.md`](../../avicore-reportes/references/reportes.md).

1. **Rubro MVP:** gallinas **ponedoras** (aves de ciclo largo); no usar planilla de pollos parrilleros (engorde).
2. **Registro diario obligatorio:** mortalidad, **descarte de aves** (tipo `descarte`), **huevos aptos** y **huevos de descarte** (rotos/sucios), alimento (kg por entrega) y agua (cuando esté operativo).
3. **Huevos:** `huevos` = aptos/comerciales; `huevos_descarte` = rotos/sucios (puede ser 0). Al menos un total > 0 por registro.
4. **Pre-faena:** al exportar o cerrar lote hacia faena, incluir historial de las **últimas 9 semanas** de producción (norma DGSG).
5. **Cabecera export:** DICOSE, **lote SMA** (`lotes.codigo_sma`, opcional al crear), lote interno, fecha ingreso/nacimiento, línea genética, población inicial, establecimiento.
6. **Agua:** `avicore-defer` — en granjas con bebederos automáticos el operario **no** registra consumo diario; lectura de medidor o módulo técnico queda para encargado/admin o integración futura.
7. **Certificación VLE:** texto y espacio de firma en PDF; no sustituye al manual BPA firmado.
