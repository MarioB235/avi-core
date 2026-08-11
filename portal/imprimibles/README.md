# Imprimibles del portal AviCore

Documentos A4 con portada. Se abren en pestaña nueva desde el menú **Documentos**.

## Informes

`informes/` — un documento por carpeta (`index.html` + `documento.css` opcional):

- `sintesis-mercado-uruguay/` — síntesis estratégica mercado avícola Uruguay

## Estilos

**Plantilla ejecutiva (recomendada):** un solo archivo autocontenido:

- `../imprimibles/_plantillas-ejecutivas/documento-ejecutivo-avicore.css` — portada + interiores + `@page` impresión

**Legacy DNGR** (solo si el documento no usa la plantilla ejecutiva):

- `../_sistema-documental/estilos/base.css`, `paginas-a4.css`, `componentes.css`, `portada.css`

Plantilla HTML: `../_sistema-documental/plantilla/index.html`

## Impresión / PDF

1. Chrome → **Márgenes: Ninguno** + **Gráficos de fondo** (obligatorio; si usás márgenes del navegador el contenido se recorta).
2. Portada: `@page ejecutivo-portada { margin: 0 }`; interiores usan encabezado/pie `position: fixed` + padding interno.
3. Recargá la pestaña del imprimible con `Ctrl+Shift+R` (no el portal principal).

## Registro en menú

Agregar entrada en `portal/js/site.nav.js` con `printable: true` y `external: true`.
