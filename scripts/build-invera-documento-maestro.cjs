/**
 * Genera el cuerpo web del documento maestro INVERA para el portal.
 * Fuente: portal/contenido/fuentes/invera-documento-maestro.md
 * Salida: portal/contenido/documentos/invera-documento-maestro.generated.html
 */
const fs = require("fs");
const path = require("path");
const { marked } = require("marked");

const ROOT = path.resolve(__dirname, "..");
const MD_PATH = path.join(ROOT, "portal/contenido/fuentes/invera-documento-maestro.md");
const OUT_FRAGMENT = path.join(
  ROOT,
  "portal/contenido/documentos/invera-documento-maestro.generated.html"
);

function parseFrontmatter(raw) {
  const match = raw.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n/);
  if (!match) {
    return { meta: {}, body: raw };
  }
  const meta = {};
  for (const line of match[1].split("\n")) {
    const m = line.match(/^(\w+):\s*"?([^"]*)"?$/);
    if (m) {
      meta[m[1]] = m[2];
    }
  }
  return { meta, body: raw.slice(match[0].length) };
}

function slugify(text) {
  return text
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "")
    .slice(0, 64);
}

function demoteHeadings(md) {
  return md
    .split("\n")
    .map((line) => {
      const m = line.match(/^(#{1,6})\s/);
      if (!m) {
        return line;
      }
      const level = Math.min(m[1].length + 1, 6);
      return "#".repeat(level) + line.slice(m[1].length);
    })
    .join("\n");
}

function postProcessWebHtml(html) {
  return html.replace(
    /<blockquote>\s*([\s\S]*?)\s*<\/blockquote>/g,
    (_, inner) => `<aside class="doc-invera__callout">${inner.trim()}</aside>`
  );
}

function splitSections(body) {
  const start = body.search(/^# 0\./m);
  const trimmed = start >= 0 ? body.slice(start) : body;
  const parts = trimmed.split(/^# (?=\d+\.)/m).filter(Boolean);
  return parts.map((part) => {
    const nl = part.indexOf("\n");
    const titleLine = nl >= 0 ? part.slice(0, nl).trim() : part.trim();
    const rest = nl >= 0 ? part.slice(nl + 1) : "";
    const id = `sec-${slugify(titleLine)}`;
    const inner = postProcessWebHtml(marked.parse(demoteHeadings(rest), { async: false }));
    return { id, title: titleLine, html: inner };
  });
}

function buildFragment(meta, sections) {
  const version = meta.version || "2.0";
  const fecha = meta.fecha || "2026-09-28";

  const sectionsHtml = sections
    .map(
      (s) => `
  <section class="doc-invera__section" aria-labelledby="${s.id}">
    <h2 id="${s.id}">${s.title}</h2>
    ${s.html}
  </section>`
    )
    .join("\n");

  return `<!-- Generado por pnpm run build:portal-invera — no editar a mano -->
<div class="doc-invera__meta" hidden>
  <span data-version="${version}"></span>
  <time datetime="${fecha}">${fecha}</time>
</div>
${sectionsHtml}
`;
}

function buildDocument() {
  if (!fs.existsSync(MD_PATH)) {
    console.error("No se encontró:", MD_PATH);
    process.exit(1);
  }

  const raw = fs.readFileSync(MD_PATH, "utf8");
  const { meta, body } = parseFrontmatter(raw);
  const sections = splitSections(body);
  const fragment = buildFragment(meta, sections);

  fs.mkdirSync(path.dirname(OUT_FRAGMENT), { recursive: true });
  fs.writeFileSync(OUT_FRAGMENT, fragment, "utf8");
  console.log(
    `Generado: ${OUT_FRAGMENT} (${sections.length} secciones, ${(fragment.length / 1024).toFixed(0)} KB)`
  );
}

buildDocument();
