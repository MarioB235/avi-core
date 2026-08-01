#!/usr/bin/env node
/**
 * Sugiere qué documentation actualizar según rutas tocadas (no edita archivos).
 *
 * Uso:
 *   node scripts/check-docs-impact.cjs
 *   node scripts/check-docs-impact.cjs --base main
 *   git diff --name-only main...HEAD | node scripts/check-docs-impact.cjs --stdin
 *
 * Exit 0 siempre (sugerencias). Exit 1 solo si git falla sin --stdin.
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const readline = require('readline');

const root = path.join(__dirname, '..');

/** @type {{ test: (p: string) => boolean, docs: string[] }[]} */
const RULES = [
  {
    test: (p) => p.startsWith('database/migrations/') || p.startsWith('app/Models/'),
    docs: [
      '.cursor/skills/avicore-modelo-datos/references/esquema-bd.md',
      'docs/CHANGELOG.md',
    ],
  },
  {
    test: (p) => p.startsWith('app/Policies/') || p.includes('/permisos'),
    docs: ['.cursor/skills/avicore-negocio/references/permisos.md', 'docs/CHANGELOG.md'],
  },
  {
    test: (p) => p.startsWith('app/Actions/') || p.startsWith('app/Services/') || p.startsWith('app/Enums/'),
    docs: ['.cursor/skills/avicore-negocio/references/reglas.md'],
  },
  {
    test: (p) =>
      p.startsWith('resources/views/') ||
      p.startsWith('app/Livewire/') ||
      p.startsWith('resources/css/'),
    docs: [
      '.cursor/skills/avicore-ui/references/pantallas-flujos.md',
      '.cursor/skills/avicore-design-system/references/tokens-componentes.md',
    ],
  },
  {
    test: (p) => p.includes('operario'),
    docs: [
      '.cursor/skills/avicore-ui/references/patrones-mobile-operario.md',
      '.cursor/skills/avicore-ui/references/pantallas-flujos.md',
    ],
  },
  {
    test: (p) => p.includes('/admin/') || p.includes('Admin/'),
    docs: [
      '.cursor/skills/avicore-ui/references/patrones-web-admin.md',
      '.cursor/skills/avicore-ui/references/pantallas-flujos.md',
    ],
  },
  {
    test: (p) => p.startsWith('database/seeders/') || p.includes('Seeder'),
    docs: ['.cursor/skills/avicore-datos-demo/references/demo.md'],
  },
  {
    test: (p) => p.startsWith('.github/workflows/'),
    docs: ['docs/CHANGELOG.md'],
  },
  {
    test: (p) =>
      p.startsWith('.cursor/') ||
      p.startsWith('docs/plantillas/') ||
      p === 'docs/02-avicore-mensajes-reutilizables.html' ||
      p === 'AGENTS.md',
    docs: [
      '.cursor/skills/avicore-evolucion-tooling/references/GOBERNANZA.md',
      'docs/CHANGELOG.md',
      'pnpm run check:agent-docs',
    ],
  },
  {
    test: (p) => p.startsWith('app/') || p.startsWith('routes/'),
    docs: ['.cursor/skills/avicore-contexto/references/arbol-proyecto.md'],
  },
];

function parseArgs(argv) {
  const opts = { base: 'main', stdin: false };
  for (let i = 2; i < argv.length; i++) {
    if (argv[i] === '--stdin') {
      opts.stdin = true;
    } else if (argv[i] === '--base' && argv[i + 1]) {
      opts.base = argv[++i];
    }
  }
  return opts;
}

function filesFromGit(base) {
  try {
    const out = execSync(`git diff --name-only ${base}...HEAD`, {
      cwd: root,
      encoding: 'utf8',
    });
    const unstaged = execSync('git diff --name-only', { cwd: root, encoding: 'utf8' });
    const staged = execSync('git diff --name-only --cached', { cwd: root, encoding: 'utf8' });
    return [...new Set(`${out}\n${unstaged}\n${staged}`.split(/\r?\n/).filter(Boolean))];
  } catch (err) {
    console.error('No se pudo leer git diff. Usá --stdin o revisá la rama.');
    console.error(String(err.message || err));
    process.exit(1);
  }
}

async function filesFromStdin() {
  const rl = readline.createInterface({ input: process.stdin, crlfDelay: Infinity });
  const files = [];
  for await (const line of rl) {
    const t = line.trim();
    if (t) {
      files.push(t.replace(/\\/g, '/'));
    }
  }
  return files;
}

function suggest(files) {
  const map = new Map();
  for (const file of files) {
    const norm = file.replace(/\\/g, '/');
    for (const rule of RULES) {
      if (!rule.test(norm)) {
        continue;
      }
      for (const doc of rule.docs) {
        if (!map.has(doc)) {
          map.set(doc, new Set());
        }
        map.get(doc).add(norm);
      }
    }
  }
  return map;
}

async function main() {
  const opts = parseArgs(process.argv);
  const files = opts.stdin ? await filesFromStdin() : filesFromGit(opts.base);

  if (files.length === 0) {
    console.log('OK: sin archivos en el diff — no hay sugerencias de documentación.');
    return;
  }

  const map = suggest(files);
  console.log(`Archivos en alcance: ${files.length}`);
  if (map.size === 0) {
    console.log('Sin sugerencias automáticas (revisá docs/00-contexto.md si el cambio es de contrato).');
    return;
  }

  console.log('\nSugerencias (revisar; no editar a ciegas):\n');
  for (const [doc, triggers] of [...map.entries()].sort((a, b) => a[0].localeCompare(b[0]))) {
    const sample = [...triggers].slice(0, 5).join(', ');
    const more = triggers.size > 5 ? ` (+${triggers.size - 5})` : '';
    const exists =
      doc.startsWith('pnpm ') || fs.existsSync(path.join(root, doc)) ? '' : ' [FALTA EN DISCO]';
    console.log(`- ${doc}${exists}`);
    console.log(`    ← ${sample}${more}`);
  }
  console.log('\nMapa canónico: docs/00-contexto.md · mensaje 4 decide actualizar / OK / no aplica.');
}

main();
