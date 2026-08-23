# 01 — Producto AviCore

> **Visión de producto:** alcance y objetivos del MVP. El detalle operativo (pantallas, campos, flujos) vive en [`pantallas-flujos.md`](../../avicore-ui/references/pantallas-flujos.md) y [`reglas.md`](../../avicore-negocio/references/reglas.md) **solo para lo implementado**; módulos futuros en [`plan-desarrollo.md`](plan-desarrollo.md).

## 1. Definición general

**AviCore** es una plataforma de gestión operativa para avícolas de gallinas ponedoras.

Su objetivo es permitir que una empresa avícola registre, controle y consulte información diaria de producción por galpón, con foco en:

- Producción de huevos.
- Mortalidad.
- Alimento entregado.
- Aves vivas.
- Galpones.
- Lotes.
- Movimientos de aves.
- Reportes.
- Dashboard.
- Trazabilidad de cargas, correcciones y anulaciones.
- Actualización en tiempo real.

AviCore no inicia como ERP completo ni sistema contable. Su primera versión será operativa, simple, responsiva y orientada al uso real en campo.

---

## 2. Nombre del sistema

El nombre definido es:

```text
AviCore
```

Se mantiene este nombre porque es corto, profesional, tecnológico, no queda limitado solo a huevos y permite crecer hacia otros módulos avícolas.

---

## 3. Objetivo del MVP

El MVP debe resolver correctamente este flujo:

```text
Seleccionar galpón → cargar dato operativo → guardar → actualizar dashboard/reportes en tiempo real
```

El operario carga datos desde celular. El encargado, administrativo o dueño consulta la información desde PC o móvil.

---

## 4. Alcance del MVP

### Incluye

- Gestión de empresas.
- Gestión de granjas.
- Gestión de galpones.
- Gestión de lotes.
- Carga operativa por galpón.
- Producción de huevos.
- Mortalidad.
- Alimento entregado en kilos.
- Movimientos de aves.
- Traslado de lotes.
- Cierre de lotes.
- Dashboard.
- Reportes.
- Exportación PDF y Excel.
- Usuarios y roles.
- Auditoría de registros.
- Diseño responsivo.
- Empresa demo.
- Identidad visual verde/agro.
- Logo AviCore.
- Tiempo real con WebSockets.

### No incluye en el MVP

- Facturación electrónica.
- Integración DGI.
- Integración MGAP / SMA / export SNIG (trazabilidad oficial; ver [`mercado-uruguay.md`](mercado-uruguay.md) §4).
- Cálculo automático de coeficientes técnicos con curvas y alertas (postura, conversión, mortalidad vs. referencia Uruguay; ver [`mercado-uruguay.md`](mercado-uruguay.md) §3 y [`reglas.md`](../../avicore-negocio/references/reglas.md) §15).
- Stock avanzado de alimento.
- Ventas.
- Logística.
- Packing industrial.
- MOBA.
- RFID.
- Inteligencia artificial.
- App nativa separada.

---

## 5. Enfoque multiempresa

AviCore será multiempresa.

Cada empresa ve únicamente sus datos.

```text
AviCore
 ├── Empresa A
 │    ├── Granjas
 │    ├── Galpones
 │    ├── Lotes
 │    └── Registros
 │
 └── Empresa B
      ├── Granjas
      ├── Galpones
      ├── Lotes
      └── Registros
```

La estrategia será:

```text
Un sistema base + configuración por empresa
```

---

## 6. Roles principales

- Admin AviCore.
- **Dueño** — persona de referencia del **panel admin** en MVP (estructura, usuarios, futuro dashboard/reportes).
- Administrativo — mismo alcance que Dueño en código MVP; diferenciación de permisos y pantallas → post-MVP (`permisos.md` §10).
- Encargado — supervisión; panel admin parcial (sin usuarios ni estructura).
- Operario — vista móvil `/operario` (campo).

---

## 7. Flujo operativo principal

1. Operario inicia sesión.
2. Cambia contraseña si es primer ingreso.
3. Ingresa a vista móvil.
4. Selecciona galpón.
5. Carga huevos, muertes o alimento.
6. Guarda.
7. El sistema actualiza indicadores.
8. El dashboard recibe actualización en tiempo real.
9. Encargado o dueño consulta reportes.

---

## 8. Decisiones cerradas

| Tema | Decisión |
|---|---|
| Nombre | AviCore |
| Sistema | Multiempresa |
| Carga | Por galpón |
| Unidad principal | Huevo |
| Maple | 30 huevos |
| Cajón | Configurable por empresa |
| Operario | Vista móvil simplificada — **módulo prioritario** (origen de datos en galpón) |
| Orden de desarrollo | Operario → admin estructura → dashboard/reportes; ver [`estrategia-implementacion.md`](estrategia-implementacion.md) |
| Reportes | Manuales; Excel planilla productiva antes que PDF |
| Dashboard | Tiempo real (Reverb post-MVP); coeficientes MGAP en dashboard fase 3 |
| Stack | Laravel + PostgreSQL + Livewire + Tailwind CSS + Alpine.js + PWA + Laravel Reverb + Echo |
| Diseño | Verde/agro moderno |
| Mercado objetivo | Uruguay — sur; productores medianos sin ERP; ver [`mercado-uruguay.md`](mercado-uruguay.md) |
| DICOSE | Campo en `granjas` (un E.A. por granja en MVP) |
| Código de lote | Trazable; alineable al SMA desde el alta |
| Trazabilidad normativa | Datos operativos alineados a lotes/eventos SMA/SNIG; export manual post-MVP; API SMA fuera MVP |
| Investigación pendiente (humano) | Instructivo SMA, planilla real, anexos GBPEA — ver [`estrategia-implementacion.md`](estrategia-implementacion.md) §3 |
