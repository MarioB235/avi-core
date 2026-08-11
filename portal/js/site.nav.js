// Menú del portal AviCore (NAV_SECTIONS). Runtime en site.js.

/** Subnavegación interna de plantillas (inyectada por site.js en [data-plantilla-nav]). */
const PLANTILLA_NAV = [
  {
    id: "dev-plantillas",
    href: "contenido/desarrollo/mensajes-reutilizables.html",
    label: "Mensajes y plantillas",
    short: "Inicio",
  },
  {
    id: "dev-plantillas-cursor",
    href: "contenido/desarrollo/plantillas-cursor.html",
    label: "Plantillas Cursor",
    short: "Desarrollo",
  },
  {
    id: "dev-plantillas-chatgpt",
    href: "contenido/desarrollo/plantillas-chatgpt.html",
    label: "ChatGPT pantallas",
    short: "ChatGPT",
  },
];

const NAV_SECTIONS = [
  {
    title: "Inicio",
    items: [{ id: "inicio", label: "Presentación", href: "contenido/inicio.html" }],
  },
  {
    title: "Producto",
    items: [{ id: "producto-mvp", label: "MVP y alcance", href: "contenido/producto/mvp.html" }],
  },
  {
    title: "Mercado Uruguay",
    items: [
      { id: "mercado-resumen", label: "Síntesis estratégica", href: "contenido/mercado-uruguay/resumen.html" },
      { id: "mercado-coeficientes", label: "Coeficientes MGAP/DIEA", href: "contenido/mercado-uruguay/coeficientes.html" },
      { id: "mercado-gbpea", label: "GBPEA y trazabilidad", href: "contenido/mercado-uruguay/gbpea.html" },
      { id: "mercado-planillas", label: "Catálogo de planillas", href: "contenido/mercado-uruguay/planillas.html" },
    ],
  },
  {
    title: "Documentos",
    items: [
      {
        id: "doc-sintesis-mercado",
        label: "Síntesis mercado Uruguay",
        href: "contenido/documentos/sintesis-mercado.html",
        printable: true,
      },
    ],
  },
  {
    title: "Equipo técnico",
    items: [
      { id: "dev-contexto", label: "Contexto del proyecto", href: "contenido/desarrollo/contexto.html" },
      { id: "dev-changelog", label: "Changelog", href: "contenido/desarrollo/changelog.html" },
      { id: "dev-plantillas", label: "Mensajes y plantillas", href: "contenido/desarrollo/mensajes-reutilizables.html" },
      { id: "fuentes-docs", label: "Portal vs skills (.md)", href: "contenido/desarrollo/fuentes-documentacion.html" },
    ],
  },
];
