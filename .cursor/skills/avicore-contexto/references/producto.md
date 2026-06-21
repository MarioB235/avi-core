# 01 — Producto AviCore

> **Visión de producto:** alcance y objetivos del MVP. El detalle operativo (pantallas, campos, flujos) vive en [`02-pantallas-y-flujos.md`](02-pantallas-y-flujos.md) y [`05-reglas-de-negocio.md`](05-reglas-de-negocio.md) **solo para lo implementado**; módulos futuros en [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md).

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
- Integración MGAP / SMA.
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
- Dueño.
- Administrativo.
- Encargado.
- Operario.

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
| Operario | Vista móvil simplificada |
| Reportes | Manuales |
| Dashboard | Tiempo real |
| Stack | Laravel + PostgreSQL + Livewire + Tailwind CSS + Alpine.js + PWA + Laravel Reverb + Echo |
| Diseño | Verde/agro moderno |
