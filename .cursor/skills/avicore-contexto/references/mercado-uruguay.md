# Mercado avícola Uruguay — validación y oportunidad

> **Fuente maestra** de investigación de mercado, normativa de trazabilidad y coeficientes técnicos de referencia para Uruguay.  
> Contrato de producto MVP: [`producto.md`](producto.md). Roadmap: [`plan-desarrollo.md`](plan-desarrollo.md).  
> Umbrales operativos en dashboard/alertas: [`reglas.md`](../../avicore-negocio/references/reglas.md) §15.

---

## 1. Resumen

Uruguay produce cerca de **1.000 millones de huevos anuales** con consumo y producción en máximos históricos. La gran mayoría de establecimientos productores se concentra en el **sur** (Canelones, Montevideo, San José), lo que facilita venta y soporte en una zona compacta.

Las encuestas oficiales (DIEA / MGAP) muestran rezago en digitalización: muchos productores medianos y pequeños usan **Excel** o **papel**; carecen de software especializado accesible. AviCore apunta exactamente a la **recolección en campo** y el **análisis** de los parámetros que el sector y el Ministerio ya miden.

**Encuesta comercial DIEA:** la producción comercial se releva en explotaciones desde **500 aves** en adelante.

---

## 2. Realidad digital del sector

| Situación | Implicación para AviCore |
|-----------|---------------------------|
| Excel como «sistema» dominante | Oportunidad: movilidad, alertas, consistencia multiusuario |
| Registro en papel / recolección manual | Vista operario móvil simple en galpón |
| Software especializado costoso o inaccesible | Posicionamiento SaaS por empresa, sin ERP completo |

**Distribución de sistemas de alojamiento (referencia sector):** tradicional no automatizada ~52% de aves; galpón automático ~36%; piso confinado ~12%. AviCore no depende del tipo de galpón para el MVP, pero el roadmap debe contemplar **jaula vs. campo / Free Range** (INIA y tendencia creciente).

---

## 3. Parámetros críticos (coeficientes técnicos MGAP / DIEA)

Para que un productor uruguayo valore el software frente a planillas gratuitas, AviCore debe **automatizar** (no solo almacenar) los índices que el sector y las encuestas oficiales ya usan. Estos valores son **referencia nacional** para gráficos, desvíos y alertas — no sustituyen metas configurables por empresa.

| Módulo AviCore | Parámetro | Referencia Uruguay | Qué debe hacer el sistema |
|----------------|-----------|-------------------|----------------------------|
| Producción / dashboard | **Postura** | ~269–278 huevos por gallina al año | Curva de postura; gráfico de desvío vs. referencia |
| Alimento | **Conversión alimenticia** | ~121–125 g de alimento por ave y día | Índice de eficiencia alimentaria (huevos / kg alimento) |
| Mortalidad | **Mortalidad y descartes** | ~1,0%–1,1% (tasa aceptada en encuestas) | Alertas tempranas ante picos; tendencia por galpón/lote |
| Lotes | **Ciclo de vida del lote** | Postura desde semana **19–20**; descarte semana **86–87** | Trazar lote desde ingreso a cierre; edad en semanas |

### Alineación con datos que AviCore ya recolecta

| Dato operativo MVP | Uso en coeficientes |
|--------------------|---------------------|
| Huevos por galpón (diario) | Postura acumulada y curva vs. referencia |
| Muertes por galpón | Mortalidad %; alertas |
| Alimento en kg (MVP parcial) | Conversión alimenticia cuando alimento esté operativo |
| Lotes (ingreso, estado, tipo huevo) | Ciclo de vida; edad del lote en semanas |
| Movimientos / traslados / cierre (planificado) | Salidas y consistencia de aves vivas |

**Estado implementación:** recolección parcial (huevos, muertes, vacunación, lotes). Cálculo automático de coeficientes, curvas y alertas → **post-MVP** (ver [`plan-desarrollo.md`](plan-desarrollo.md) §13).

---

## 4. Trazabilidad obligatoria — SMA / SNIG (MGAP)

### Qué es

En Uruguay la avicultura comercial está sujeta al **Sistema de Monitoreo Avícola (SMA)** del MGAP, en el marco del **Sistema Nacional de Información Ganadera (SNIG)**. Los productores deben **registrar eventos por lotes**: ingresos, movimientos y salidas de aves, además de cumplir buenas prácticas documentadas en GUB.UY.

AviCore **no reemplaza** el portal oficial del MGAP en el MVP, pero **debe diseñarse** para que los datos operativos del sistema coincidan con lo que exige la trazabilidad estatal.

### Marco normativo (referencia)

**GBPEA §5** cita el marco legal completo. Para AviCore importan los que definen **habilitación**, **registros obligatorios** y **SMA/SNIG**; el resto es contexto sanitario general.

| Prioridad AviCore | Norma | Rol resumido |
|-------------------|-------|--------------|
| **Alta** | **Decreto 396/2019** (23 dic 2019) | Habilitación y refrendación de E.A. comerciales |
| **Alta** | **Res. DGSG N° 22/022** (26 ene 2022) | Bioseguridad; Manual de Buenas Prácticas Avícolas + POES |
| **Alta** | **Res. N° 1.684/025** | Aprueba GBPEA (jul 2025) |
| **Media** | Res. DGSG N° 78/024 (28 feb 2024) | Actualización normativa reciente — verificar alcance |
| **Media** | Res. DGSG N° 325/024 (19 nov 2024) | Certificación sanitaria aves a faena **exportación** — §8.3–8.4 |
| **Media** | Res. DGSG N° 246/019 (28 ago 2019) | Envío aves a plantas de faena habilitadas |
| **Media** | Res. DGSG N° 149/018 (26 abr 2018) | Importación huevos fértiles / pollitos reproductoras |
| **Media** | Res. DGSG N° 341/2024 (6 dic 2024) | **Manual de Contingencia Influenza Aviar** — §7.11 eventos adversos |
| **Media** | Res. DGSG N° 246/019 (28 ago 2019) | Marco previo a Dec. 396/019 — posible SMA/SNIG |
| **Baja (contexto)** | Ley N° 16.736 art. 285 (redacción Ley 19.535 art. 87) | Marco legal sector |
| **Baja** | Ley N° 19.355 art. 290 | Marco legal sector |
| **Baja** | Decreto 315/994 y modificativas | Marco histórico sanitario |
| **Baja** | Decreto 360/003 (3 set 2003) | Marco histórico |
| **Baja** | Res. DGSG 224/012 (13 dic 2012) | Normativa anterior |
| **Baja** | Res. DGSG 149/018 (26 abr 2018) | Normativa anterior |

**Lista íntegra GBPEA §5** (como cita la guía):

1. Artículo 285 de la Ley Nº 16.736 (5 ene 1996), redacción art. 87 Ley Nº 19.535 (25 set 2017).
2. Ley N° 19.355 (19 dic 2015), artículo 290.
3. Decreto Nº 315/994 (5 jul 1994), modificativas y concordantes.
4. Decreto N° 360/003 (3 set 2003).
5. Decreto N° 396/2019 (23 dic 2019).
6. Resolución DGSG N° 224/012 (13 dic 2012).
7. Resolución DGSG N° 149/018 (26 abr 2018).
8. Resolución DGSG N° 246/019 (28 ago 2019).
9. Resolución DGSG N° 22/022 (26 ene 2022).
10. Resolución DGSG N° 78/024 (28 feb 2024).
11. Resolución DGSG N° 325/024 (nov 2024).
12. Resolución DGSG N° 341/2024 (6 dic 2024).

| Documento transversal | Rol |
|-----------------------|-----|
| **GBPEA §7.12** | Procedimiento de trazabilidad (manual de cada E.A.) |
| **Portal SNIG** | Registro en SMA + instructivo de movimientos por tipo de E.A. |

### Documentos a profundizar (pendiente de contenido)

Si podés compartir texto o PDF, priorizar en este orden:

| # | Documento | Por qué lo necesitamos |
|---|-----------|------------------------|
| 1 | **Instructivo SNIG / SMA** (movimientos por tipo de E.A.) | Define campos y eventos exactos para export post-MVP |
| 2 | **Res. DGSG 78/024, 325/024 y 341/2024** | Cambios recientes; pueden afectar registros o habilitación |
| 3 | **Decreto 396/2019** (artículos sobre registros productivos) | Plazos de conservación y qué debe quedar documentado |
| 4 | **Res. DGSG 246/019** | Posible vínculo con monitoreo avícola previo al decreto actual |
| 5 | **GBPEA §8** (buenas prácticas por tipo de establecimiento) | Diferencias jaula / piso / Free Range para roadmap |
| 6 | **GBPEA §7.4–7.11 y anexos** (alimento, agua, POES, plagas, residuos, planillas ejemplo) | Registros restantes del manual BPA |
| 7 | **Anexos GBPEA** (planillas A de §7.4, §7.9, §7.10, §7.3) | Formato exacto de export PDF/Excel |

No hace falta por ahora: leyes marco (16.736, 19.355) ni decretos históricos (315/994, 360/003) salvo que mencionen **registros de producción** o **trazabilidad** explícita.

La GBPEA **no es** el manual operativo de cada granja: es el **marco técnico** que orienta qué debe incluir el manual que cada establecimiento avícola (E.A.) redacta con su VLE (Veterinario de Libre Ejercicio). AviCore puede **alimentar** esos registros y exportarlos.

### GBPEA — introducción, objetivo y alcance (§1–3)

| Sección GBPEA | Contenido oficial | Relevancia AviCore |
|---------------|-------------------|-------------------|
| **§1 Introducción** | Plan Avícola MGAP: fortalecer salud/bienestar animal e inocuidad para mercados exigentes | Posicionamiento: datos operativos + trazabilidad como habilitación exportadora |
| | Res. DGSG 22/022 obliga **Manual de Buenas Prácticas Avícolas** para habilitación/refrendación de cada E.A. | AviCore alimenta registros del manual (no sustituye el documento firmado por VLE) |
| | BPAs = acciones en toda la cadena para salud de aves y productos inocuos | Registros sanitarios (vacunación MVP) + operativos (huevos, muertes) |
| | Elaborada por DGSG + DIGEBIA + AMEVEA; orienta al VLE | Canal comercial: veterinarios acreditados como aliados de adopción |
| **§2 Objetivo** | Orientar al VLE en la redacción del manual adaptado al E.A. | Export de registros adaptables al manual de cada granja |
| | Lineamientos como **requisito reglamentario** u **recomendación** voluntaria | Separar en producto: obligatorio (SMA, trazabilidad) vs. recomendado (KPIs avanzados) |
| **§3 Alcance** | Aplica a E.A. de cría, recría, reproducción, **postura**, incubación y **engorde** | MVP AviCore = **postura** (ponedoras); roadmap puede extender a otros rubros |

### GBPEA §6 — Definiciones clave (AviCore)

Solo las definiciones con impacto directo en modelo de datos, roles o integración SMA. El listado completo está en la GBPEA.

| Termino GBPEA | Definición resumida | AviCore |
|---------------|---------------------|---------|
| **Lote** | Conjunto de aves/huevos identificado en el **SMA** con número **único e irrepetible**; unidad básica del SMA | Entidad `lotes` + `codigo`; alinear ID/código con SMA en export post-MVP |
| **SMA** | Sistema informático de registros de actores y **movimientos**; órbita del **SNIG** | Integración post-MVP; datos operativos deben mapear eventos SMA |
| **E.A. de Postura** | Producción de huevos para consumo; puede incluir cría/recría | **MVP AviCore** — empresa → granja → galpón → lote |
| **OCA** | Operario/a Cuidador Avícola — personal en contacto con aves | Rol **operario** en vista móvil |
| **Vacío sanitario** | Intervalo entre salida de un lote y entrada del siguiente; limpieza/desinfección | **Post-MVP:** estado galpón / bloqueo de carga durante vacío |
| **VLE habilitado / acreditado** | Veterinario autorizado por DGSG; refrenda habilitación anual | Canal adopción; export para manual BPA |
| **POES** | Procedimientos de limpieza y desinfección documentados | Fuera del MVP operativo; no mezclar con carga diaria |
| **Plan de respuesta a eventos adversos** | Documento del productor para habilitación | Fuera MVP; posible checklist export post-MVP |
| **Bioseguridad** | Medidas físicas y de gestión (Res. 22/022) | §7.3 registros de ingreso — módulo futuro |

### GBPEA §7 — Estructura del Manual de BPAs

Cada E.A. tiene su manual propio (procedimientos + instructivos + **registros**). Estructura mínima obligatoria:

| # | Procedimiento GBPEA | Soporte AviCore |
|---|---------------------|-----------------|
| 1 | Datos de la empresa | Empresa + granja; **DICOSE** (post-MVP en config) |
| 2 | Instalaciones / diseño E.A. | Galpones (básico); georef y bioseguridad física → manual |
| 3 | Ingreso personas y vehículos | **Post-MVP** — registro visitas/vehículos (§7.3) |
| 4 | Suministro de alimento | Carga kg entregado (parcial); **stock/insumos** §7.4 post-MVP |
| 5 | Suministro de agua | Fuera MVP (§7.5 registros 2 años) |
| 6 | Capacitación del personal | Registros capacitación post-MVP (§7.6) |
| 7 | POES (saneamiento) | Checklist vacío sanitario post-MVP (§7.7) |
| 8 | Higiene del personal | Manual + §7.3; regla 72 h sin contacto aves |
| 9 | Manejo integrado de plagas | Planilla cebos semanal post-MVP (§7.9) |
| 10 | Medicamentos veterinarios | Vacunación MVP; **planilla control sanitario** + ATB post-MVP (§7.10) |
| 11 | Manejo de residuos sólidos | PGRS + movimiento residuos post-MVP (§7.11) |
| 12 | **Trazabilidad** | **Núcleo** — lotes, movimientos, huevos, muertes, export SMA |

Cada procedimiento debe detallar: objetivo, alcance, sector, responsabilidad, desarrollo y **registros** (si aplica).

### GBPEA §7.1 — Datos de la empresa

| Requisito MGAP | Implicación AviCore |
|----------------|---------------------|
| Habilitación sanitaria DSA + **N° DICOSE** asignado por SMA/SNIG | Campo futuro `dicose` en empresa o granja; mostrar en admin |
| Registro SMA actualizado; refrendación **anual** por VLE | Recordatorio/refrendación → post-MVP (alertas admin) |
| DICOSE en cartelería al ingreso del E.A. | Dato de config exportable a manual |
| Otras certificaciones (ISO 22000, Free Range, etc.) | Campos opcionales empresa — post-MVP |
| Planos y diagrama de flujo (recorrido animales/personal) | Documento del manual; AviCore no sustituye |

### GBPEA §7.2 — Instalaciones y galpones (resumen)

Normativa de bioseguridad (Res. 22/022): ubicación, cerco perimetral, áreas limpias/sucias, filtros sanitarios, diseño de galpones, sala de huevos, almacén químicos. **La mayor parte es documentación física del manual**, no carga operaria diaria.

| Tema §7.2 | AviCore hoy / futuro |
|-----------|----------------------|
| Cantidad y diseño de **galpones** | CRUD galpones (parcial); atributos constructivos → campos opcionales post-MVP |
| **Georreferenciación** del E.A. | Campo futuro en `granjas` |
| Sala clasificación/almacenamiento huevos | Fuera MVP operario; módulo packing post-MVP |
| Cerco, filtros, vestuarios, necropsia | Manual BPA; no software MVP |

### GBPEA §7.3 — Ingreso de personas y vehículos

**Registros obligatorios/recomendados** que hoy muchas granjas llevan en planilla:

| Registro | Campos típicos | AviCore |
|----------|----------------|---------|
| Control de ingresos (personal/visitas) | Fecha, hora, nombre, motivo, último contacto con otras aves; matrícula si vehículo | **Módulo post-MVP** bioseguridad |
| Filtros sanitarios | Ubicación, producto, fecha recambio, observaciones | Post-MVP o checklist manual |

Responsable oficial: encargado/a. AviCore puede digitalizar estas planillas como extensión del panel admin/encargado, integrado a auditoría.

### GBPEA §7.4–7.11 — Registros del manual (resumen AviCore)

| § | Procedimiento | Registros que pide MGAP | AviCore |
|---|---------------|------------------------|---------|
| **7.4** | Suministro de **alimento** | Ingreso insumo: fecha, cantidad, proveedor, prod./vencimiento, lote; formulación de raciones si elabora en E.A. (anexos ejemplo) | **Hoy:** kg entregado por galpón (parcial, sin hub móvil). **Post-MVP:** stock silos, ingreso insumos, conversión alimenticia |
| **7.5** | Suministro de **agua** | Limpieza tanque/distribución; control potabilización; análisis microbiológicos (**conservar 2 años**) | Fuera MVP operativo |
| **7.6** | **Capacitación** | Fecha, temario, participantes, responsable (**2 años**) | Post-MVP — módulo admin; VLE como responsable |
| **7.7** | **POES** | Limpieza operativa (mortandad diaria, cama húmeda) y no operativa en **vacío sanitario** (etapas: seco → lavado → detergente → enjuague → desinfección → secado) | Post-MVP checklist encargado; bloqueo carga en vacío |
| **7.8** | **Higiene personal** | Vestimenta E.A.; sin enfermedad; 72 h sin contacto con otras aves; carné salud | Cruza con §7.3; reglas en manual |
| **7.9** | **Plagas** | Plano de cebos/trampas; inspección **semanal** con hallazgos y correctivas (anexo planilla cebos) | Post-MVP módulo sanidad ambiental |
| **7.10** | **Medicamentos** | **Plan sanitario** (vacunas: frecuencia, vía, tipo); planilla control sanitario; ATB solo con prescripción VLE (dosis, duración, tiempos de espera) | **MVP:** `vacunaciones` por lote. **Post-MVP:** plan sanitario, ATB, export planilla control |
| **7.11** | **Residuos** | PGRS (Res. MVOTMA 1708/2013); registro movimiento cama/abono fuera del E.A.; notificar Servicio Ganadero en mortalidad masiva; Res. **341/2024** contingencia influenza | Post-MVP; **alertas** por pico de muertes alineadas a §3 coeficientes |

**§7.12 Trazabilidad** — ver bloque siguiente (SMA + registros E.A. + instructivo SNIG).

**Anexos GBPEA pendientes de recibir:** planilla ingreso alimento/insumos, formulación raciones, control de cebos, control sanitario, control de ingresos.

**Fuentes oficiales:**

- [GBPEA — Guía completa (índice)](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/publicaciones/guia-buenas-practicas-establecimientos-avicolas-version-1-julio-2025)
- [GBPEA §7.12 — Procedimiento de trazabilidad](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/publicaciones/guia-buenas-practicas-establecimientos-avicolas-version-1-julio-2025-10)
- [Noticia MGAP — aprobación GBPEA](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/noticias/mgap-aprueba-nueva-guia-buenas-practicas-para-establecimientos-avicolas)
- [Resolución N° 1.684/025](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/institucional/normativa/resolucion-n-1684025-apruebase-guia-buenas-practicas-establecimientos)

### GBPEA §7.12 — qué exige el MGAP al E.A.

| Aspecto | Requisito oficial | Implicación AviCore |
|---------|-------------------|---------------------|
| **Objetivo** | Procedimiento detallado del sistema de trazabilidad del E.A., **en el marco del SMA** | Datos operativos alineados a eventos SMA; export post-MVP |
| **Alcance** | Todos los E.A.; todos los sectores | Multiempresa + granja + galpón + lote |
| **Responsable** | Productores y propietarios de las aves | Roles dueño / encargado / operario con auditoría |
| **Desarrollo** | Describir alcance, etapas del proceso, mecanismos de identificación y registros del sistema | Modelo de lotes, movimientos, estados; código de lote |
| **Lote trazable** | El manual del E.A. debe **definir** qué es un «lote trazable» | Entidad `lotes` con `codigo`, ingreso, tipo hueo, estado — documentar definición en manual exportable |
| **Registros** | Identificar y seguir movimientos de **aves y huevos** en todo el proceso | Huevos diarios, muertes, movimientos, cierre de lote |
| **Conservación** | Registros obligatorios del **SMA** + registros propios del E.A. | Historial sin delete físico; anulación con motivo; auditoría |

**Detalle del manual BPM-POES 2025 (MGAP):** los movimientos de aves (pollitos/bebé y aves a faena) se registran por el **SMA**; la cantidad de huevos se registra en el **SMA** y en la **planilla productiva** con producción diaria. AviCore reemplaza esa planilla productiva y puede pre-llenar el SMA.

### Eventos que el SMA exige (y AviCore debe poder reflejar)

| Evento SNIG / SMA | Equivalente AviCore (plan / MVP) |
|-------------------|----------------------------------|
| Alta de tenedor / establecimiento | Empresa + granja (admin) |
| Registro de lote / ingreso de aves | Alta de lote + `cantidad_inicial` |
| Movimientos entre galpones / establecimientos | `movimientos_aves` (planificado) |
| Salidas (descarte, venta, mortalidad acumulada) | Muertes + cierre de lote + movimientos |
| Producción diaria de huevos | Carga huevos por galpón (MVP) |
| Trazabilidad histórica | Lotes con historial; anulación lógica, no delete físico |

### Portal SNIG / SMA — flujos operativos (bitácora 2026-08-11)

> Fuente: [SNIG — página principal](https://www.snig.gub.uy/) · instructivo engorde I1071501 (referencia estructural) · planilla campo sur (usuario). **Verificar** en portal SMA avícola antes de implementar export.

**Acceso:** [snig.gub.uy](https://www.snig.gub.uy/) → login con **Usuario gub.uy** o **DICOSE + contraseña** → perfil Productor / VLE → establecimiento en REUNE. SMA avícola: menú **Otros sistemas** → *Sistema de Monitoreo Avícola* (documentación, materiales de apoyo). Mesa SMA: **2410 2790** (lun–vie 9–17, sáb 9–14).

| Flujo SMA | Pasos resumidos (secuencial) | Equivalente AviCore (futuro) |
|-----------|------------------------------|------------------------------|
| **Alta de lote** | Remitos Recibidos → Aceptar remito (incubadora/origen) → lote **Disponible** | Alta lote + `codigo` SMA + remito origen |
| **Movimiento** | Guías / Nuevo movimiento → DICOSE destino + transportista → lote + cantidad → Verificar → Confirmar → preembarque | `movimientos_aves` + export pre-llenado |
| **Faena** | Planta: Certificación a faena / Remitos recibidos → contrastar cantidades → Aceptar e imprimir PDF | Cierre lote + remito SMA |

**Caveat rubro:** el instructivo citado (I1071501, set 2021) es **granja de engorde**; AviCore MVP = **ponedoras** (§8.3). La secuencia remito→lote→movimiento→faena aplica igual; los campos de **producción diaria de huevos** no figuran en ese instructivo — validar en materiales SMA avícola / GBPEA Anexo A postura.

**Portal SNIG (contexto DICOSE, no bloquea MVP operario):** Nuevo Portal Productores (desde 08/12/2025), Declaración Jurada DICOSE, alta DICOSE (requisitos oct 2024). Despacho de tropa digital obligatorio (may 2026) aplica más a **semovientes** que a aves SMA; no confundir con remito avícola.

### Planilla MGAP oficial — Anexo Nº 2 «Registros productivos» (aves de ciclo largo)

> **Corte 2026-08-11 (usuario):** el PDF titula «Reproductoras», pero la DGSG usa el **mismo Anexo Nº 2** para **aves de ciclo largo**, incluidas **ponedoras** (MVP AviCore §8.3). No es la planilla de pollos parrilleros (engorde). Fuente normativa: Procedimiento de Certificación Sanitaria de Aves (DGSG) en [gub.uy](https://www.gub.uy/) · GBPEA §7.12 · manual BPM-POES 2025.

**Regla MGAP clave (ponedoras):** registro **diario de huevos** + conservar historial de las **últimas 9 semanas** antes de enviar el lote a faena.

#### Cabecera obligatoria (ponedoras)

| Campo planilla | AviCore hoy | Notas |
|----------------|-------------|-------|
| N° DICOSE | `granjas.dicose` | Ola 2 |
| Nº lote SMA | `lotes.codigo` | Alinear con SMA al aceptar remito |
| Fecha de nacimiento / ingreso | `lotes.fecha_ingreso` | Planilla pide «nacimiento»; en postura suele ser fecha de alojamiento de pollitas — `avicore-defer`: `fecha_nacimiento` opcional si difiere |
| Línea genética (Hy-Line, Lohmann, etc.) | — | `avicore-defer`: `lotes.linea_genetica` |
| Cantidad inicial de aves | `lotes.cantidad_inicial` | ✓ |
| Nom. comercial / Propietario | `empresas` | Export |
| N° Remitos / Galpón / Enfermedad | parcial | Ver bloque A abajo |

#### Producción diaria (ponedoras — núcleo operario)

| Campo planilla | AviCore hoy | Gap / decisión |
|----------------|-------------|----------------|
| **Mortalidad** (día + acumulado) | `registros_operativos.muertes` | ✓ |
| **Descarte** de aves (separado) | — | `avicore-defer`: campo `descarte_aves` o tipo registro |
| **Huevos aptos** (comerciales) | `registros_operativos.huevos` (total) | Hoy un solo número — **split** aptos vs descarte huevos |
| **Huevos descarte** (rotos, sucios, fecales) | — | `avicore-defer`: `huevos_descarte` en registro o segundo campo |
| **Alimento** (kg/día) | — | **Ola 1** hub Cargar |
| **Agua** (medidor) | — | Post-MVP |
| Historial **9 semanas** pre-faena | Historial en BD | Export debe filtrar últimas 9 sem. al cierre/faena |

#### Historial sanitario

| Campo | AviCore |
|-------|---------|
| Vacunas (fecha, cepa, serie, vencimiento) | `vacunaciones` parcial — ver bloque D |
| Productos veterinarios / ATB / tiempo espera | Post-MVP §7.10 |
| Certificación VLE (Salmonella, influenza, etc.) | Leyenda + firma en PDF impreso; checklist post-MVP |

#### Bloques del PDF recibido (misma plantilla física)

El PDF compartido incluye además: **origen de lotes** (remito, galpón), **semana de producción**, **grilla mortalidad** 7 días × semanas con % acumulado, firmas VLE y responsable.

**Planilla operativa de galpón (campo sur)** — complemento UX papel/Excel antes de digitar SMA; columnas alineadas a la tabla «Producción diaria» arriba.

**Cabecera papel/Excel (referencia sur)**

| Campo | Ejemplo | AviCore |
|-------|---------|---------|
| N° galpón | Galpón 2 | `galpones` |
| N° lote SMA | Código al aceptar remito | `lotes.codigo` |
| Fecha alojamiento | DD/MM/AAAA | `lotes.fecha_ingreso` |
| Población inicial | 15.000 | `lotes.cantidad_inicial` |
| Línea genética | Cobb / Ross | Campo opcional lote (`avicore-defer`) |
| DICOSE | 020XXXXXXXX | `granjas.dicose` |

**Registro diario (engorde / recría — referencia UX)**

| Columna | Notas | AviCore MVP postura |
|---------|-------|---------------------|
| Día / edad (días) | Edad del lote | Edad en semanas (operario) |
| Mortalidad | Aves | **Carga muertes** ✓ |
| Descarte | Separado de mortalidad | `avicore-defer` — hoy solo muertes |
| Alimento (kg) | Diario | Ola 1 — hub Cargar |
| Agua (L) | Termómetro sanitario | Post-MVP |
| Peso promedio (g) | Muestra semanal | Post-MVP |
| Observaciones / vacunas / T° | Libre | Vacunación ✓; notas `avicore-defer` |

**Totales semanales:** mortalidad acumulada %, consumo acumulado — base para dashboard coeficientes.

#### Detalle bloques PDF (origen, sanitario, grilla mortalidad)

| Campo planilla | AviCore hoy | Notas export |
|----------------|-------------|--------------|
| Nom. comercial | `empresas.nombre` | |
| N° de Remitos | — | `avicore-defer`: `lotes.remito_origen` o tabla remitos |
| Nº Lote de SMA | `lotes.codigo` | Obligatorio alinear con SMA |
| FECHA de INGRESO | `lotes.fecha_ingreso` | |
| GALPÓN | `galpones.nombre` / número | |
| Enfermedad | — | `avicore-defer`: campo sanitario lote o observación |
| DICOSE | `granjas.dicose` | Ola 2 |
| Propietario | `empresas` / titular | |
| Firma / Marca / Remito | — | Solo PDF impreso (no dato en BD) |

Texto VLE: certificación de veracidad — export PDF incluye leyenda; firma manual post-impresión.

#### Bloque B — Registro de producción

| Campo planilla | AviCore hoy | Notas |
|----------------|-------------|-------|
| SEM. DE PRODUCCIÓN | Edad lote en semanas | Calcular desde `fecha_ingreso` |
| ESTABLECIMIENTO | `granjas.nombre` + DICOSE | |

Incluye en export ponedoras: columnas **huevos aptos** y **huevos descarte** por día (ver tabla producción diaria arriba).

#### Bloque C — Productos veterinarios (ATB y similares)

| Campo planilla | AviCore hoy |
|----------------|-------------|
| Fecha | — |
| Forma de administración | — |
| Duración / dosis | — |
| Tiempo de espera | — |
| Firma | — |

**Post-MVP** §7.10 — hoy solo `vacunaciones` (vacunas, no ATB con tiempo de espera).

#### Bloque D — Control sanitario / Vacunas

| Campo planilla | AviCore hoy | Gap |
|----------------|-------------|-----|
| Fecha | `vacunaciones.created_at` | ✓ |
| Dosis / Cepa | `vacunaciones.vacuna` (tipo) | Falta cepa/dosis detalle |
| Vencimiento | — | `avicore-defer` |
| Serie | — | `avicore-defer` |

#### Bloque E — Mortalidad diaria (grilla)

Estructura: filas = **semanas**; columnas = días **1–7** + total **SEM** + acumulado **ACU** + **MORTALIDAD %**.

| Dato | AviCore hoy |
|------|-------------|
| Muertes por día | `registros_operativos` tipo muerte, agrupar por fecha |
| Total semanal SEM | Suma 7 días |
| Acumulado ACU | Suma desde ingreso lote |
| Mortalidad % | Muertes ACU / `cantidad_inicial` × 100 |

Firma VLE + responsable establecimiento: solo en PDF impreso.

**Conclusión Anexo 2 ponedoras:** el PDF recibido + normativa usuario **cubren el contrato de export MVP** (cabecera, mortalidad grilla, vacunas, huevos diarios, 9 semanas pre-faena). Pendiente solo **PDF oficial GUB.UY** para validar layout pixel-perfect y capturas SMA.

**Otras planillas (no Anexo 2):** pollos **parrilleros** (engorde), control de accesos, RENAPAG — otros rubros.

### Argumento de venta

Si AviCore permite **exportar** datos en formato compatible o **facilitar el llenado** de formularios de tenedores/lotes del SNIG (sin duplicar carga manual en Excel), el cumplimiento normativo se convierte en diferencial frente a planillas.

**MVP:** fuera de alcance explícito ([`producto.md`](producto.md) §4). **Roadmap:** integración MGAP / SMA / export SNIG ([`plan-desarrollo.md`](plan-desarrollo.md) §13).

### Buenas prácticas y habilitación

La habilitación y **refrendación sanitaria anual** del E.A. exige manual de Buenas Prácticas + POES verificados por VLE y DGSG. La GBPEA cubre bioseguridad, registros productivos/sanitarios y **trazabilidad integrada con sistemas oficiales**. AviCore debe mantener **auditoría** de cargas, correcciones y anulaciones alineada a ese espíritu.

### GBPEA §8 — Buenas prácticas por tipo de E.A.

| § | Rubro | Relevancia AviCore |
|---|-------|-------------------|
| **8.1** | Granjas **reproductoras** | Fuera MVP; importación genética (Res. 149/018); huevos fértiles |
| **8.2** | **Plantas de incubación** | Fuera MVP; alta bioseguridad |
| **8.3** | Granjas **ponedoras** | **MVP AviCore** — pollitas → huevos consumo → descarte |
| **8.4** | Aves a **faena** + registros | Salida de lote; **remito SMA**; Res. 246/019, **325/024**, 341/2024 |

**§8.3 Granjas ponedoras (núcleo producto):** recepción pollitas (mismo origen/edad por galpón); huevos en maples sin tocar el piso; **huevos no aptos** (rotos, incubados, sucios, fecales, etc. — no lavar); sala de almacenamiento separada; al fin de ciclo → faena MGAP con registro de **fecha y número de aves** retiradas.

**§8.4 Faena:** captura y transporte con bienestar animal; lotes sin enfermedades de denuncia obligatoria → planta habilitada; **generar remito en SMA** según protocolo de envío a faena; exportación → Res. **325/024** (nov 2024).

### Catálogo de planillas MGAP (§8.4 + §9 Anexo A)

Todas llevan **nombre empresa + N° DICOSE**. AviCore puede **generar** estas planillas desde datos ya cargados (export PDF/Excel post-MVP).

| Planilla oficial | Campos principales | AviCore hoy / futuro |
|------------------|-------------------|----------------------|
| **Registro productivo** | Modificaciones de aves y desempeño del **lote** | **Núcleo** — huevos, muertes, lotes, aves vivas |
| **Control sanitario** | Fecha, medicamento, **tiempo de espera** | Vacunación MVP; ATB + tiempos post-MVP |
| **Ingreso de alimento** | Fecha, toneladas, proveedor, lote, destino galpones | Carga kg parcial; stock/insumos post-MVP |
| **Formulación de raciones** | Insumos, lotes, cantidades, destino galpones | Post-MVP |
| **Control de cloro** | Punto, ppm (3–5), correctivas | Post-MVP §7.5 |
| **Control de ingresos** | Fecha/hora, nombre, matrícula, motivo, contacto con otras aves | Post-MVP §7.3 |
| **Recambio desinfectantes filtros** | Ubicación, producto, dosis, fecha | Post-MVP |
| **Limpieza y desinfección** | Sector, superficie, detergente/desinfectante, frecuencia | Post-MVP POES |
| **Control de cebos/plagas** | Trampa, hallazgos, roedor muerto, correctivas | Post-MVP §7.9 |
| **Salida de abono/cama** | Destino, cantidad estimada | Post-MVP §7.11 |
| **Remitos SMA** | Movimientos del establecimiento (incl. faena) | Integración SMA post-MVP |

**Normativa faena/exportación vinculada:** Res. DGSG **246/019** (faena); **325/024** (certificación exportación, nov 2024); Decreto 396/019.

### GBPEA §10 — Referencias bibliográficas (selección útil)

Guías de línea genética (Arbor Acres, Cobb, Ross), Codex huevos, Manual BUMV MGAP 2017, Guía Bienestar Animal faena 2019, UNIT agua potable. AviCore no implementa estas guías; sirven para **KPIs por línea** y contenido de capacitación (§7.6) en roadmap.

---

## 5. Oportunidades diferenciadas

| Oportunidad | Notas |
|-------------|-------|
| **Cumplimiento SMA / SNIG** | Export o pre-llenado de registros oficiales |
| **Sistemas Free Range / pastoreo** | Variables distintas a jaula tradicional; nicho INIA y alto margen |
| **Zona sur compacta** | Go-to-market y soporte en Canelones / Montevideo / San José |
| **Alertas vs. Excel** | Mortalidad, postura y conversión en tiempo real (Reverb post-MVP) |

---

## 6. Segmento objetivo

| Pregunta comercial | Decisión (2026-08-10) |
|--------------------|------------------------|
| ¿Grandes o medianos? | **Medianos sin ERP** primero; sur (Canelones, Montevideo, San José) |
| ¿Offline en galpón? | **Online** en MVP; PWA sin sync offline ([`pwa.md`](../../avicore-pwa/references/pwa.md)) |
| ¿Encuesta productores? | **Pendiente humano** — 3–5 entrevistas; ver [`estrategia-implementacion.md`](estrategia-implementacion.md) §3 |

---

## 7. Validación pendiente (solo equipo humano)

Lo que **no** puede cerrar el agente sin insumos externos:

| Prioridad | Tarea | Bloquea |
|-----------|-------|---------|
| **P0** | Instructivo SNIG/SMA (movimientos postura) | Export SMA y remitos faena |
| **P0** | 1 planilla productiva real (Excel/papel) | Layout export Excel fiel |
| **P0** | Anexos GBPEA (planillas ejemplo) | PDF/Excel Anexo A exactos |
| **P1** | 3–5 entrevistas productores sur | Pricing y prioridades UX |
| **P1** | 1 contacto VLE acreditado | Canal adopción |
| **P2** | Res. 325/024, 341/2024, plazos Dec. 396/2019 | Faena export, contingencia, retención |

**Decisiones ya cerradas** (DICOSE, alertas, export Excel primero, operario primero, código lote): [`estrategia-implementacion.md`](estrategia-implementacion.md) §2.

Registrar hallazgos en la **bitácora** (§11).

---

## 10. Síntesis estratégica — oportunidad AviCore

> Opinión de producto basada en toda la investigación recopilada (mercado DIEA, GBPEA jul 2025, coeficientes MGAP).

### Veredicto

La información **valida fuerte** la propuesta AviCore y define con claridad **qué digitalizar primero** y **cómo vender**.

### Por qué ayuda

1. **Demanda real:** sector en crecimiento, digitalización rezagada (Excel/papel), concentración geográfica sur.
2. **Norma como aliada:** habilitación exige manual BPA + registros; AviCore puede ser la **planilla productiva** y el **backend de trazabilidad** sin reemplazar al VLE.
3. **Catálogo de planillas explícito** (§9): diseño de export y módulos futuros casi definido campo por campo.
4. **Coeficientes DIEA/MGAP** (§3): dashboard y alertas con referencia nacional ya documentada.
5. **SMA/SNIG:** integración futura es el diferencial frente a Excel; lote = unidad SMA.

### Ventajas competitivas a explotar

| Ventaja | Cómo |
|---------|------|
| **Un solo registro → muchas planillas** | Carga operario alimenta registro productivo, parcialmente sanitario y trazabilidad |
| **Cumplimiento habilitación** | DICOSE + datos listos para refrendación anual |
| **Operario en galpón (OCA)** | Reemplaza planilla en papel con PWA |
| **Alertas vs. planilla estática** | Mortalidad, postura, picos → Res. 341/2024 contingencia |
| **Canal VLE** | Export para manual BPA firmado por veterinario acreditado |

### Alineación con MVP actual

| Ya encaminado | Falta para «excelente» |
|---------------|------------------------|
| Huevos, muertes, vacunación, lotes, operario móvil | Alimento operativo completo + conversión alimenticia |
| Multiempresa, auditoría, histórico | Dashboard coeficientes + curvas postura |
| | Export planillas Anexo A + remitos SMA |
| | Movimientos de aves, cierre/faena con remito |
| | Validación con 3–5 productores del sur |

### Qué sigue investigando (bloqueantes)

Ver lista priorizada en [`estrategia-implementacion.md`](estrategia-implementacion.md) §3 (P0–P2). Resumen: instructivo SMA, planilla real, anexos GBPEA.

---

## 11. Notas de investigación (bitácora)

| Fecha | Fuente / tema | Nota |
|-------|---------------|------|
| 2026-08-05 | Investigación mercado Uruguay (usuario) | Creación de este documento; datos MGAP/DIEA, coeficientes, SMA/SNIG |
| 2026-08-05 | GBPEA §7.12 + prólogo MGAP (gub.uy) | Trazabilidad E.A. en marco SMA; lote trazable; registros aves/huevos; Res. DGSG 22/022 y 1684/025 |
| 2026-08-05 | GBPEA §1–3 (introducción, objetivo, alcance) | Plan Avícola; BPAs obligatorias; alcance postura + otros rubros; VLE como canal |
| 2026-08-05 | GBPEA §5 marco normativo | Lista íntegra leyes/decretos/resoluciones DGSG; prioridad AviCore; docs a profundizar |
| 2026-08-08 | GBPEA §6–7.3 | Definiciones (lote SMA, OCA, vacío sanitario); mapa manual BPA→AviCore; DICOSE; ingreso personas/vehículos |
| 2026-08-08 | GBPEA §7.4–7.12 | Registros alimento/agua/capacitación/POES/plagas/medicamentos/residuos; Res. 341/2024 influenza; planilla control sanitario |
| 2026-08-08 | GBPEA §8–10 + Anexo A | Granjas ponedoras MVP; catálogo planillas; remitos SMA faena; síntesis estratégica §10 |
| 2026-08-10 | Estrategia operario primero + Fase 0 | Decisiones producto cerradas; olas 1–5; checklist investigación humana P0–P2 — `estrategia-implementacion.md` |
| 2026-08-11 | SNIG portal + flujos SMA (usuario) | Alta lote (remito→disponible), movimiento (guía/preembarque), faena (certificación); instructivo engorde I1071501 como referencia estructural — **falta instructivo postura** |
| 2026-08-11 | Planilla galpón sur + Anexo reproductoras (usuario) | Cabecera DICOSE/lote/galpón; columnas diarias mortalidad/alimento/agua/peso; grilla mortalidad semanal VLE — **falta PDF oficial ponedoras** |
| 2026-08-11 | Anexo Nº 2 PDF reproductoras — detalle campos (usuario) | Bloques A–E mapeados a AviCore; gaps: remito origen, serie/vencimiento vacuna, ATB, huevos (ponedoras) — ver §4 planilla |
| 2026-08-11 | Anexo Nº 2 **ponedoras** / ciclo largo (usuario) | Mismo Anexo 2 DGSG; huevos aptos+descarte diarios; 9 semanas pre-faena; gaps AviCore: split huevos, descarte aves, agua, línea genética |
| — | TUTORIAL SAVCO / COASGROP | Bitácora operario — referencia UX competencia regional |
| — | Avinews / Colibrí | Crecimiento sector; distribución tipos de galpón |

*(Agregar filas al recibir nueva información del usuario.)*

---

## 12. Referencias externas (no vinculantes)

- MGAP — Ministerio de Ganadería, Agricultura y Pesca (Uruguay)
- DIEA — Dirección de Estadísticas Agropecuarias (censos y encuestas postura comercial)
- SNIG / SMA — trazabilidad avícola y registro de tenedores/lotes ([snig.gub.uy](https://www.snig.gub.uy/); SMA en «Otros sistemas»)
- INIA — investigación sistemas Free Range / libre de jaula
- [GBPEA — Guía completa (índice)](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/publicaciones/guia-buenas-practicas-establecimientos-avicolas-version-1-julio-2025) — §1–3 contexto; §7.12 trazabilidad
- [GBPEA §7.12 — Procedimiento de trazabilidad](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/publicaciones/guia-buenas-practicas-establecimientos-avicolas-version-1-julio-2025-10)
- [Resolución N° 1.684/025 — GBPEA](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/institucional/normativa/resolucion-n-1684025-apruebase-guia-buenas-practicas-establecimientos)
- [Manual BPM-POES 2025 (PDF)](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/sites/ministerio-ganaderia-agricultura-pesca/files/2025-09/MANUAL%20BPM-POES%202025.pdf) — §15 trazabilidad; huevos en SMA + planilla productiva

Verificar URLs y normativa vigente antes de implementar integración oficial.
