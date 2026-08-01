#!/usr/bin/env node
/**
 * Verifica invariantes del tooling del agente AviCore (anti-drift).
 *
 * Uso: node scripts/check-agent-docs-sync.cjs
 */

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function read(relPath) {
  return fs.readFileSync(path.join(root, relPath), 'utf8').replace(/\r\n/g, '\n');
}

function countSkillDirs() {
  const skillsRoot = path.join(root, '.cursor', 'skills');
  if (!fs.existsSync(skillsRoot)) {
    return 0;
  }
  return fs.readdirSync(skillsRoot, { withFileTypes: true })
    .filter((d) => d.isDirectory() && fs.existsSync(path.join(skillsRoot, d.name, 'SKILL.md')))
    .length;
}

let failed = false;

function fail(message) {
  console.error(message);
  failed = true;
}

const agents = read('AGENTS.md');
const comando = read('.cursor/commands/avicore-architect-direct.md');
const skillsReadme = read('.cursor/skills/README.md');
const cursorReadme = read('.cursor/README.md');

const actualSkillCount = countSkillDirs();

const countMatchReadme = skillsReadme.match(/\((\d+)\s+internos/);
const documentedCount = countMatchReadme ? parseInt(countMatchReadme[1], 10) : null;

if (documentedCount !== actualSkillCount) {
  fail(
    `.cursor/skills/README.md dice ${documentedCount} internos pero hay ${actualSkillCount} carpetas con SKILL.md`
  );
}

const inventoryMatch = cursorReadme.match(/Skills \((\d+)\)/);
if (inventoryMatch && parseInt(inventoryMatch[1], 10) !== actualSkillCount) {
  fail(`.cursor/README.md: inventario dice ${inventoryMatch[1]} skills, hay ${actualSkillCount}`);
}

const POINTER_CHECKS = [
  ['AGENTS.md', agents, '/avicore-architect-direct'],
  ['AGENTS.md', agents, '.cursor/skills/README.md'],
  ['comando', comando, '.cursor/skills/README.md'],
  ['comando', comando, 'avicore-contexto'],
  ['comando', comando, 'avicore-negocio'],
  ['comando', comando, 'Arquitectura documental'],
  ['comando', comando, 'docs/02-avicore-mensajes-reutilizables.html'],
  ['comando', comando, 'solo al final del mensaje 2'],
  ['skills README', skillsReadme, 'Única tabla mensaje → skill'],
  ['skills README', skillsReadme, 'avicore-deuda-tecnica'],
  ['cursor README', cursorReadme, 'check:agent-docs'],
  ['cursor README', cursorReadme, 'check:docs-impact'],
  ['cursor README', cursorReadme, 'GOBERNANZA.md'],
];

for (const [label, text, needle] of POINTER_CHECKS) {
  if (!text.includes(needle)) {
    fail(`${label}: falta invariante "${needle}"`);
  }
}

const skillNamesInReadme = [...skillsReadme.matchAll(/`avicore-[\w-]+`/g)].map((m) => m[0].slice(1, -1));
const NON_SKILL_MARKERS = new Set(['avicore-defer']);
const uniqueSkills = [...new Set(skillNamesInReadme)].filter(
  (n) => n.startsWith('avicore-') && !NON_SKILL_MARKERS.has(n)
);

for (const skillName of uniqueSkills) {
  const skillPath = path.join(root, '.cursor', 'skills', skillName, 'SKILL.md');
  if (!fs.existsSync(skillPath)) {
    fail(`README referencia ${skillName} pero no existe ${skillPath}`);
  }
}

const ROUTING_INTENTS = [
  'avicore-nuevo-modulo',
  'avicore-ui',
  'avicore-design-system',
  'avicore-negocio',
  'avicore-deuda-tecnica',
];

for (const skill of ROUTING_INTENTS) {
  if (!skillsReadme.includes(skill)) {
    fail(`skills README: falta ${skill} en enrutamiento o catálogo`);
  }
}

if (!comando.includes('.cursor/skills/README.md') && !comando.includes('../skills/README.md')) {
  fail('comando: debe apuntar a skills/README.md como única tabla de enrutamiento');
}

if (comando.includes('| Intención del usuario |')) {
  fail('comando: no duplicar la matriz de enrutamiento (vive solo en skills/README.md)');
}

const removedPaths = [
  'docs/cursor/03-skills-avicore.md',
  'docs/README.md',
  'docs/reference/estructura-base-datos.md',
];

for (const rel of removedPaths) {
  if (fs.existsSync(path.join(root, rel))) {
    fail(`archivo obsoleto aún existe: ${rel}`);
  }
}

const desarrolloHtml = read('docs/plantillas/desarrollo.html');
const PLANTILLA_NEEDLES = [
  'Aquí te detallo la tarea:',
  'Archivos a analizar:',
  'pnpm run check:docs-impact',
  '.cursor/skills/README.md',
  'docs/00-contexto.md',
];
for (const needle of PLANTILLA_NEEDLES) {
  if (!desarrolloHtml.includes(needle)) {
    fail(`docs/plantillas/desarrollo.html: falta invariante "${needle}"`);
  }
}
if (desarrolloHtml.includes('Skills de dominio (auto-invoke)')) {
  fail(
    'docs/plantillas/desarrollo.html: no duplicar tabla de skills (vive en .cursor/skills/README.md)'
  );
}
if (desarrolloHtml.includes('Regla de una sola fuente maestra')) {
  fail(
    'docs/plantillas/desarrollo.html: no duplicar mapa de fuentes (vive en docs/00-contexto.md)'
  );
}

if (!fs.existsSync(path.join(root, 'scripts', 'check-docs-impact.cjs'))) {
  fail('falta scripts/check-docs-impact.cjs');
}

const pkg = read('package.json');
if (!pkg.includes('check:docs-impact')) {
  fail('package.json: falta script check:docs-impact');
}

if (failed) {
  console.error('\nCorregí el drift según avicore-evolucion-tooling/references/GOBERNANZA.md');
  process.exit(1);
}

console.log(
  `OK: ${actualSkillCount} skills, punteros, plantillas y enrutamiento alineados (scripts/check-agent-docs-sync.cjs).`
);
