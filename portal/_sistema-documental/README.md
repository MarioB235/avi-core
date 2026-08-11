# Sistema documental AviCore

Infraestructura compartida para documentos HTML imprimibles (A4). Los documentos viven en `portal/imprimibles/informes/`, etc.

## Estilos

| Archivo | Contenido |
|---------|-----------|
| `imprimibles/_plantillas-ejecutivas/documento-ejecutivo-avicore.css` | **Plantilla ejecutiva** — único CSS (portada + interiores + impresión) |
| `estilos/base.css` | Legacy DNGR — tipografía, tablas, `@page` genérico |
| `estilos/portada.css` | Legacy DNGR — portada con banda lateral |
| `estilos/paginas-a4.css` | Legacy DNGR — páginas interiores |
| `estilos/componentes.css` | Legacy DNGR — firmas, notas, listas |

**`!important` en legacy:** los cuatro CSS de `estilos/` usan `!important` masivo dentro de `@media print` para forzar layout A4 al imprimir desde el portal de ejemplo (DNGR/ATLAS). Es una **excepción documentada** — no aplicar ese patrón en la app Laravel ni en `documento-ejecutivo-avicore.css` (plantilla ejecutiva sin `!important`).

## Plantilla

Copiar `plantilla/index.html` al crear un documento nuevo. Ajustar metadatos de portada; agregar `documento.css` solo si hace falta CSS exclusivo.

## Import — plantilla ejecutiva

Desde un documento en `portal/imprimibles/informes/<nombre>/`:

```html
<link rel="stylesheet" href="../../_plantillas-ejecutivas/documento-ejecutivo-avicore.css" />
<!-- opcional: <link rel="stylesheet" href="documento.css" /> -->
```

No cargar `base.css`, `paginas-a4.css` ni `componentes.css` en documentos ejecutivos (evita conflictos de márgenes y tipografía).

## Portada — metadatos obligatorios

- Tipo de documento
- Título
- Fecha de emisión
- Responsable
- Destinatario
- Asunto (cuando corresponda)

Logo: `../../../assets/logo-avicore.png` desde `imprimibles/informes/<doc>/`.
