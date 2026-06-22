#!/usr/bin/env node
/**
 * OBSOLETO para regenerar contenido: editar docs/plantillas/*.html directamente.
 * Este script ya no debe ejecutarse salvo migración desde HTML monolítico legacy.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'docs/02-avicore-mensajes-reutilizables.html');
const outDir = path.join(root, 'docs/plantillas');

const NAV_ITEMS = [
  { id: 'index', href: '../02-avicore-mensajes-reutilizables.html', label: 'Inicio' },
  { id: 'desarrollo', href: 'desarrollo.html', label: 'Desarrollo' },
  { id: 'fondos', href: 'chatgpt-fondos.html', label: 'ChatGPT fondos' },
  { id: 'pantallas', href: 'chatgpt-pantallas.html', label: 'ChatGPT pantallas' },
];

const FONDOS_DETAILS = `
    <details open>
      <summary>
        <span class="summary-label">ChatGPT — Fondo login móvil <span class="badge">wallpaper · 9:19</span></span>
        <button type="button" class="btn btn-primary btn-copy-header" aria-label="Copiar prompt fondo login móvil">Copiar</button>
      </summary>
      <div class="panel">
        <p class="when">Solo imagen de fondo (sin UI, sin texto). Usar en login móvil y mockups de pantalla login.</p>
        <pre data-copy>Genera un wallpaper vertical para la app AviCore (login móvil). El resultado debe ser una fotografía agro serena, lista para poner tarjetas blancas encima.

Sujeto y acción: Campo de cultivos verdes al amanecer; horizonte bajo; un solo elemento sutil (surco o hilera) en tercio inferior; cielo claro ocupa la mitad superior.

Entorno y atmósfera: Luz dorada suave, neblina leve, mood confiable y moderno. Sensación de campo productivo sin dramatismo.

Estilo visual: Fotografía realista con desenfoque suave (bokeh medio); paleta verdes #1F5E3B #3A7D44 y cielo claro. Proporción 9:19 vertical, borde a borde.

Excluir: personas, animales, galpones de primer plano, texto, logos, UI, marcos de teléfono, morado, saturación extrema, composición recargada, más de un elemento protagonista.</pre>
      </div>
    </details>

    <details>
      <summary>
        <span class="summary-label">ChatGPT — Fondo operario móvil <span class="badge">wallpaper · 9:19</span></span>
        <button type="button" class="btn btn-primary btn-copy-header" aria-label="Copiar prompt fondo operario">Copiar</button>
      </summary>
      <div class="panel">
        <p class="when">Fondo compartido del módulo operario (Inicio, Galpón, Cargar, Historial).</p>
        <pre data-copy>Genera un wallpaper vertical para AviCore módulo operario. El resultado debe ser un paisaje agro minimalista que no compita con cards blancas ni con el dock inferior.

Sujeto y acción: Paisaje verde amplio y limpio; galpón o estructura rural muy lejana y desenfocada en el horizonte; sin elementos en primer plano.

Entorno y atmósfera: Día claro, luz natural difusa, mood eficiente y tranquilo. Sensación de trabajo en campo.

Estilo visual: Fotografía suave, blur ligero en todo el plano; verdes dominantes #1F5E3B #EAF5EC. Proporción 9:19 vertical.

Excluir: personas, gallinas, texto, logos, UI, marcos de dispositivo, morado, detalles nítidos en primer plano, composición saturada.</pre>
      </div>
    </details>

    <details>
      <summary>
        <span class="summary-label">ChatGPT — Fondo login escritorio <span class="badge">wallpaper · 16:9</span></span>
        <button type="button" class="btn btn-primary btn-copy-header" aria-label="Copiar prompt fondo login desktop">Copiar</button>
      </summary>
      <div class="panel">
        <p class="when">Fondo pantalla login en viewport ≥1024px (<code>background-desktop.jpg</code>).</p>
        <pre data-copy>Genera un wallpaper horizontal para login web de AviCore. El resultado debe ser un paisaje agro amplio con espacio visual para panel de login a la derecha o centro.

Sujeto y acción: Campo verde en perspectiva suave; cielo despejado; un solo punto de interés lejano (línea de árboles o galpones diminutos).

Entorno y atmósfera: Mañana clara, luz natural, mood profesional agroindustrial.

Estilo visual: Fotografía 16:9, desenfoque suave en zonas donde iría UI; paleta verde agro #1F5E3B #3A7D44.

Excluir: personas, texto, logos, UI, morado, primer plano recargado, marcos de monitor.</pre>
      </div>
    </details>
`.trim();

function navHtml(activeId) {
  const items = NAV_ITEMS.map((item) => {
    const finalHref = item.id === 'index' ? '../02-avicore-mensajes-reutilizables.html' : path.basename(item.href);
    const active = item.id === activeId ? ' class="active" aria-current="page"' : '';
    return `<a href="${finalHref}"${active}>${item.label}</a>`;
  });
  return `<nav class="site-nav" aria-label="Secciones plantillas">\n    ${items.join('\n    ')}\n  </nav>`;
}

function pageShell({ title, lead, hint, activeId, accordionContent }) {
  const navigation = navHtml(activeId);

  return `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${title}</title>
  <link rel="stylesheet" href="plantillas.css" />
</head>
<body>
  ${navigation}
  <header>
    <h1>${title.replace('AviCore — ', 'AviCore — ')}</h1>
    <p class="lead">${lead}</p>
    ${hint ? `<p class="hint">${hint}</p>` : ''}
    <div class="toolbar">
      <button type="button" class="btn" id="expand-all">Expandir todos</button>
      <button type="button" class="btn" id="collapse-all">Colapsar todos</button>
    </div>
  </header>

  <div class="accordion" id="accordion">
${accordionContent}
  </div>

  <div class="toast" id="toast">Copiado al portapapeles</div>
  <script src="plantillas.js"></script>
</body>
</html>
`;
}

function parseDetails(lines, accordionStart, accordionEnd) {
  const details = [];
  let current = [];
  let inDetails = false;
  for (const line of lines.slice(accordionStart + 1, accordionEnd)) {
    if (/^\s*<details/.test(line)) {
      if (current.length) {
        details.push(current.join('\n'));
      }
      current = [line];
      inDetails = true;
    } else if (inDetails) {
      current.push(line);
      if (/^\s*<\/details>/.test(line)) {
        details.push(current.join('\n'));
        current = [];
        inDetails = false;
      }
    }
  }
  return details;
}

function patchPantallasFondoRef(content, fondoLabel) {
  return content.replace(
    /Entorno y atmósfera: ([^\n]+)/,
    `Entorno y atmósfera: $1 Usar wallpaper «${fondoLabel}» (generar en chatgpt-fondos.html) desenfocado detrás del contenido.`
  );
}

// --- main ---
console.error('Script obsoleto: editar docs/plantillas/*.html y docs/02-avicore-mensajes-reutilizables.html directamente.');
process.exit(0);
  console.error('No existe', srcPath);
  process.exit(1);
}

const src = fs.readFileSync(srcPath, 'utf8').replace(/\r\n/g, '\n');
const lines = src.split('\n');

const cssStart = lines.findIndex((l) => l.includes(':root {'));
const cssEnd = lines.findIndex((l, i) => i > cssStart && l.trim() === '</style>');
const jsStart = lines.findIndex((l) => l.trim() === '<script>');
const jsEnd = lines.findIndex((l) => l.trim() === '</script>');

fs.mkdirSync(outDir, { recursive: true });

let css = lines.slice(cssStart, cssEnd).join('\n');
css += `
    .site-nav {
      display: flex;
      flex-wrap: wrap;
      gap: 0.35rem;
      margin-bottom: 1rem;
      padding: 0.5rem;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 8px;
    }
    .site-nav a {
      font-size: 0.82rem;
      padding: 0.35rem 0.65rem;
      border-radius: 6px;
      text-decoration: none;
      color: var(--green-700);
      border: 1px solid transparent;
    }
    .site-nav a:hover { background: var(--green-100); }
    .site-nav a.active {
      background: var(--green-700);
      color: #fff;
      font-weight: 600;
    }
    .hub-grid {
      display: grid;
      gap: 0.75rem;
      margin-top: 1rem;
    }
    .hub-card {
      display: block;
      padding: 1rem 1.1rem;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 8px;
      text-decoration: none;
      color: inherit;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .hub-card:hover {
      border-color: var(--green-500);
      box-shadow: 0 2px 8px rgba(31, 94, 59, 0.08);
    }
    .hub-card h2 {
      font-size: 1rem;
      margin: 0 0 0.35rem;
      color: var(--green-900);
    }
    .hub-card p {
      margin: 0;
      font-size: 0.88rem;
      color: var(--muted);
    }
`;

fs.writeFileSync(path.join(outDir, 'plantillas.css'), css.trim() + '\n');
fs.writeFileSync(path.join(outDir, 'plantillas.js'), lines.slice(jsStart + 1, jsEnd).join('\n').trim() + '\n');

const accordionStart = lines.findIndex((l) => l.includes('<div class="accordion"'));
const accordionEnd = lines.findIndex(
  (l, i) => i > accordionStart && l.trim() === '</div>' && lines[i + 1]?.trim() === '' && lines[i + 2]?.includes('toast')
);

const details = parseDetails(lines, accordionStart, accordionEnd);
const devMsgs = details.filter((d) => /summary-label">[1-5] —/.test(d));
const chatgpt = details.filter((d) => d.includes('ChatGPT —'));
const refs = details.filter(
  (d) => d.includes('Referencia —') || d.includes('Comandos locales') || d.includes('Usuarios demo')
);

let pantallas = chatgpt
  .map((d) => {
    if (d.includes('Login móvil')) {
      return patchPantallasFondoRef(d, 'Fondo login móvil');
    }
    return patchPantallasFondoRef(d, 'Fondo operario móvil');
  })
  .join('\n\n');

const desarrolloContent = `${devMsgs.join('\n\n')}\n\n${refs.join('\n\n')}`.replace(
  '→ <code>docs/02-avicore-mensajes-reutilizables.html</code> + <code>CHANGELOG [cursor]</code>',
  '→ <code>docs/plantillas/</code> (índice <code>docs/02-avicore-mensajes-reutilizables.html</code>) + <code>CHANGELOG [cursor]</code>'
);

fs.writeFileSync(
  path.join(outDir, 'desarrollo.html'),
  pageShell({
    title: 'AviCore — Desarrollo (mensajes 1–5)',
    lead:
      'Plantillas para <strong>/avicore-architect-direct</strong> y cierre 2→5. El agente elige el skill interno (sin <code>@skills</code>).',
    hint:
      '<strong>Cierre 2→5:</strong> mensaje <strong>2</strong> con <code>@rutas</code> al final → <strong>3</strong> corregir → <strong>4</strong> docs → <strong>5</strong> PR. Acordeones de referencia: skills, qué documentar, comandos locales y usuarios demo.',
    activeId: 'desarrollo',
    accordionContent: desarrolloContent,
  })
);

fs.writeFileSync(
  path.join(outDir, 'chatgpt-fondos.html'),
  pageShell({
    title: 'AviCore — ChatGPT fondos',
    lead: 'Wallpapers sin UI ni texto — generálos primero y usalos en las pantallas o en el código (<code>resources/images/brand/</code>).',
    hint:
      'Un acordeón = un prompt completo. Proporción <strong>9:19</strong> móvil · <strong>16:9</strong> login escritorio. Luego generá las pantallas en <a href="chatgpt-pantallas.html">ChatGPT pantallas</a> referenciando el fondo elegido.',
    activeId: 'fondos',
    accordionContent: FONDOS_DETAILS,
  })
);

fs.writeFileSync(
  path.join(outDir, 'chatgpt-pantallas.html'),
  pageShell({
    title: 'AviCore — ChatGPT pantallas móvil',
    lead: 'Mockups UI dentro de mockup de smartphone — un acordeón = un mensaje para ChatGPT (modo imagen).',
    hint:
      'Generá antes el wallpaper en <a href="chatgpt-fondos.html">ChatGPT fondos</a>. Cada prompt ya indica qué fondo incorporar desenfocado detrás del contenido.',
    activeId: 'pantallas',
    accordionContent: pantallas,
  })
);

const indexHtml = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AviCore — Mensajes y plantillas</title>
  <link rel="stylesheet" href="plantillas/plantillas.css" />
</head>
<body>
  <nav class="site-nav" aria-label="Secciones plantillas">
    <a href="02-avicore-mensajes-reutilizables.html" class="active" aria-current="page">Inicio</a>
    <a href="plantillas/desarrollo.html">Desarrollo</a>
    <a href="plantillas/chatgpt-fondos.html">ChatGPT fondos</a>
    <a href="plantillas/chatgpt-pantallas.html">ChatGPT pantallas</a>
  </nav>
  <header>
    <h1>AviCore — Mensajes y plantillas para copiar</h1>
    <p class="lead">
      Punto de entrada único. Elegí la sección según lo que necesites: desarrollo con Cursor, fondos para diseño, o mockups de pantallas móvil.
    </p>
    <p class="hint">
      <strong>Cursor:</strong> <code>/avicore-architect-direct</code> en la primera línea del chat + plantilla de <a href="plantillas/desarrollo.html">Desarrollo</a>.
      <strong>Diseño:</strong> primero <a href="plantillas/chatgpt-fondos.html">fondos</a>, después <a href="plantillas/chatgpt-pantallas.html">pantallas</a> en ChatGPT.
      Catálogo agente: <code>.cursor/skills/README.md</code>.
    </p>
  </header>

  <div class="hub-grid">
    <a class="hub-card" href="plantillas/desarrollo.html">
      <h2>Desarrollo — mensajes 1–5</h2>
      <p>Preparar entorno, auditoría, correcciones, documentación y PR. Referencias de skills, comandos y usuarios demo.</p>
    </a>
    <a class="hub-card" href="plantillas/chatgpt-fondos.html">
      <h2>ChatGPT — fondos</h2>
      <p>Wallpapers agro sin UI: login móvil, operario móvil y login escritorio. Paisaje simple, un elemento, listo para overlay.</p>
    </a>
    <a class="hub-card" href="plantillas/chatgpt-pantallas.html">
      <h2>ChatGPT — pantallas móvil</h2>
      <p>Mockups de login y módulo operario dentro de un smartphone. Textos reales de AviCore y paleta Refined Agro.</p>
    </a>
  </div>
</body>
</html>
`;

fs.writeFileSync(path.join(root, 'docs/02-avicore-mensajes-reutilizables.html'), indexHtml);

// cleanup extract temps if present
for (const f of ['_extract-dev.txt', '_extract-chatgpt.txt', '_extract-refs.txt']) {
  const p = path.join(outDir, f);
  if (fs.existsSync(p)) {
    fs.unlinkSync(p);
  }
}

console.log('OK: index + desarrollo + chatgpt-fondos + chatgpt-pantallas');
