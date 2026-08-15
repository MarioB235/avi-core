# Estrategia de implementación — operario primero

> **Corte:** 2026-08-10 · Decisiones de producto cerradas por arquitectura (Fase 0).  
> Mercado y norma: [`mercado-uruguay.md`](mercado-uruguay.md) · MVP: [`producto.md`](producto.md) · Roadmap: [`plan-desarrollo.md`](plan-desarrollo.md).

---

## 1. Veredicto: operario primero es correcto

**Sí — priorizar el módulo Operario es la estrategia acertada** para AviCore.

| Razón | Detalle |
|-------|---------|
| **Origen de la verdad** | Huevos, muertes, vacunación y lotes nacen en el galpón (OCA, GBPEA §6). Sin esos datos, dashboard y planillas MGAP no tienen base. |
| **Norma** | La GBPEA exige registros diarios del E.A.; el operario reemplaza la planilla en papel (§7.12). |
| **Ventaja competitiva** | «Un registro → muchas planillas» solo funciona si la captura en campo es simple y fiable. |
| **Estado del repo** | Operario es el bloque más maduro (Home, Cargar, Historial, PWA). Admin avícola y dashboard aún no existen. |

**No significa** ignorar admin: la segunda ola completa la estructura (granjas, galpones, DICOSE) y consume lo que el operario ya cargó.

### Flujo de valor (de abajo hacia arriba)

```text
Operario (galpón) → datos en BD → Admin (estructura + consulta) → Dashboard (coeficientes) → Reportes (planillas MGAP)
```

---

## 2. Decisiones cerradas (Fase 0 — 2026-08-10)

Decisiones que **no requieren más investigación** para avanzar en código.

| Tema | Decisión | Notas |
|------|----------|-------|
| **Estrategia de desarrollo** | **Operario primero**, luego admin estructura, luego dashboard/reportes | Ver §4 olas |
| **Segmento inicial** | Productores **medianos** sin ERP; sur (Canelones, Montevideo, San José) | Grandes automatizadas: post-MVP |
| **Rubro MVP** | **Postura** (galpones ponedores, §8.3 GBPEA) | Cría, incubación, engorde: fuera MVP |
| **Tipo de galpón** | Jaula / piso confinado / tradicional | Free Range / pastoreo: post-MVP (`avicore-defer`) |
| **Offline en galpón** | **Online** en MVP; PWA instalable sin sync offline completo | Ver `avicore-pwa/references/pwa.md` |
| **DICOSE** | Campo en **`granjas`** (`dicose`, string, único por granja en MVP) | Una granja ≈ un E.A. habilitado |
| **Código de lote** | Campo **`codigo`** en `lotes` = identificador trazable; **debe poder coincidir con SMA** | Alta desde operario: obligatorio o autogenerado con prefijo empresa; export futuro usa el mismo código |
| **Coeficientes MGAP** | Referencia **fija nacional** en MVP (`reglas.md` §15) | Umbrales configurables por empresa → `avicore-defer` |
| **Alertas MVP** | **Mortalidad acumulada del lote** > 1,1% y **postura** bajo piso de referencia (269 huevos/año prorrateado por edad) | Picos diarios extremos: segunda iteración |
| **Export planillas** | **Excel primero** (planilla productiva); PDF después | Layout v1 sin anexos exactos hasta recibir PDFs ejemplo |
| **Integración SMA** | **Export manual** (CSV/Excel pre-llenado) en fase 2; **sin API** en MVP | No reemplazar portal SNIG en MVP |
| **Canal comercial** | Venta directa + **VLE como aliado** (refrendación, manual BPA) | VLE no es usuario obligatorio del sistema |
| **Alimento en operario** | **Siguiente ítem del hub Cargar** (kg por galpón/día) antes de stock/insumos §7.4 | Conversión alimenticia cuando haya kg + aves vivas |
| **Movimientos de aves** | Tabla `movimientos_aves` **después** de cerrar alimento operario y DICOSE | Traslados, ajustes, cierre/faena |
| **Auditoría** | Anulación con motivo (ya en espíritu); tabla `auditorias` en fase dedicada | Sin delete físico en operativos |

---

## 3. Investigación que solo el equipo humano puede hacer

Lista clara para el usuario. **Sin esto no bloqueamos Fase 1**; **sí bloquea Fase 2** (exports oficiales y SMA).

### P0 — Necesario antes de exports «oficiales» y movimientos SMA

| # | Qué conseguir | Estado (2026-08-11) | Falta |
|---|---------------|---------------------|-------|
| **1** | **Instructivo SNIG/SMA avícola postura** | Parcial: flujos remito→lote→movimiento→faena documentados; instructivo engorde I1071501 como referencia | PDF/capturas **ponedoras**; campos exactos de pantalla |
| **2** | **Planilla Anexo 2 ponedoras** | **Cubierto** (estructura + campos diarios + 9 sem pre-faena) | PDF oficial GUB.UY solo para validar layout; capturas SMA opcional |
| **3** | **Anexos GBPEA** (planillas ejemplo) | Pendiente | PDFs Anexo A desde guía o BPM-POES 2025 |

### P1 — Recomendado para producto y ventas (no bloquea código)

| # | Qué conseguir | Cómo | Para qué |
|---|---------------|------|----------|
| **4** | **3–5 entrevistas** a productores del sur | 30 min: ¿Excel o papel? ¿duplican carga en SMA? ¿pagarían? | Prioridades UX y pricing |
| **5** | **1 contacto VLE** acreditado | Presentar AviCore como «backend del manual» | Canal de adopción y feedback normativo |

### P2 — Solo si priorizamos faena / exportación / contingencia

| # | Qué conseguir | Para qué |
|---|---------------|----------|
| **6** | Texto **Res. 325/024** (faena exportación) | Remitos y cierre de lote export |
| **7** | Texto **Res. 341/2024** (influenza) | Alertas por mortalidad masiva |
| **8** | **Decreto 396/2019** — plazos de conservación de registros | Política de retención en BD |

**Plantilla para registrar hallazgos:** agregar fila en [`mercado-uruguay.md`](mercado-uruguay.md) §11 (bitácora).

---

## 4. Olas de implementación (orden acordado)

### Ola 1 — Completar operario (sin investigación extra)

| Ítem | Entregable |
|------|------------|
| Carga **alimento** (kg por entrega de camión) | Hub Cargar + validaciones + historial |
| Pulir **lotes** desde operario | Código trazable, edad en semanas visible |
| Tests + demo seed | Escenario galpón con huevos/muertes/alimento |

### Ola 2 — Admin estructura mínima

| Ítem | Entregable |
|------|------------|
| CRUD **granjas** + campo **DICOSE** | `/admin` |
| CRUD **galpones** y **lotes** | Vinculado a operario |
| Permisos Dueño / Encargado | Policies existentes |

### Ola 3 — Valor «profesional» (consulta + coeficientes)

| Ítem | Entregable |
|------|------------|
| **Dashboard** KPIs | Postura, mortalidad vs referencia MGAP |
| **Export Excel** planilla productiva v1 | Empresa + DICOSE + datos del período |
| Alertas básicas | Mortalidad > 1,1% acumulada |

### Ola 4 — Trazabilidad avanzada (requiere P0 investigación)

| Ítem | Entregable |
|------|------------|
| `movimientos_aves` | Traslados, cierre |
| Export formato SMA | CSV pre-llenado según instructivo |
| Planillas Anexo A fieles | PDF/Excel con layout oficial |

### Ola 5 — Post-MVP normativo

Bioseguridad §7.3, stock alimento §7.4, plagas, residuos, tiempo real Reverb, API SMA si existe.

---

## 5. Brecha actual (referencia rápida)

| Capa | Hecho | Siguiente (Ola 1–2) |
|------|-------|---------------------|
| Operario | Huevos, muertes, descarte, vacunación, alimento, lotes (SMA), historial (detalle + anulación), perfil, PWA | **Ola 2** — admin granjas/galpones/DICOSE |
| Admin | Usuarios | Granjas, galpones, DICOSE |
| Análisis | — | Dashboard coeficientes |
| Norma | Doc GBPEA completa | Export planilla + SMA manual |

---

## 6. Qué puede hacer el agente sin esperar al usuario

- Implementar Olas 1–3 con las decisiones de §2.
- Documentar contrato en `esquema-bd.md`, `pantallas-flujos.md`, `reglas.md` al tocar cada ítem.
- Preparar `ReporteService` con layout **aproximado** hasta recibir planilla real (P0 #2).
- No implementar integración API SMA ni remitos faena hasta P0 #1.

---

## 7. Referencias

- Operario UX: [`pantallas-flujos.md`](../../avicore-ui/references/pantallas-flujos.md) §8
- Coeficientes: [`reglas.md`](../../avicore-negocio/references/reglas.md) §15
- Reportes: [`reportes.md`](../../avicore-reportes/references/reportes.md)
- Esquema futuro: [`esquema-bd.md`](../../avicore-modelo-datos/references/esquema-bd.md)
