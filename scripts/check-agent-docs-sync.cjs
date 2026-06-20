#!/usr/bin/env node
/**
 * Verifica invariantes del tooling del agente AviCore (anti-drift).
 * Inspirado en docs/ponytail/scripts/check-rule-copies.js — adaptado a la jerarquía AviCore.
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
const skills03 = read('docs/cursor/03-skills-avicore.md');
const config00 = read('docs/cursor/00-configuracion-cursor.md');
const index01 = read('docs/cursor/01-indice-agente.md');

const actualSkillCount = countSkillDirs();

const countMatch03 = skills03.match(/\((\d+)\s+internos/);
const documentedCount03 = countMatch03 ? parseInt(countMatch03[1], 10) : null;

if (documentedCount03 !== actualSkillCount) {
  fail(
    `docs/cursor/03-skills-avicore.md dice ${documentedCount03} internos pero hay ${actualSkillCount} carpetas con SKILL.md`
  );
}

for (const file of ['docs/cursor/00-configuracion-cursor.md', 'docs/cursor/01-indice-agente.md']) {
  const text = file === 'docs/cursor/00-configuracion-cursor.md' ? config00 : index01;
  const m = text.match(/Skills internos\s*\|\s*(\d+)/) || text.match(/\((\d+)\s+internos\)/);
  if (m && parseInt(m[1], 10) !== actualSkillCount) {
    fail(`${file}: inventario dice ${m[1]} skills, hay ${actualSkillCount}`);
  }
}

const POINTER_CHECKS = [
  ['AGENTS.md', agents, '01-indice-agente.md'],
  ['AGENTS.md', agents, '/avicore-architect-direct'],
  ['comando', comando, '03-skills-avicore.md'],
  ['comando', comando, 'Cierre 2→5 en un solo chat'],
  ['comando', comando, 'solo al final del mensaje 2'],
  ['03-skills', skills03, 'Única tabla mensaje → skill'],
  ['03-skills', skills03, 'avicore-deuda-tecnica'],
  ['00-config', config00, 'check-agent-docs-sync'],
  ['05 vía index', index01, '05-evolucion-skills-y-docs.md'],
];

for (const [label, text, needle] of POINTER_CHECKS) {
  if (!text.includes(needle)) {
    fail(`${label}: falta invariante "${needle}"`);
  }
}

const skillNamesIn03 = [...skills03.matchAll(/`avicore-[\w-]+`/g)].map((m) => m[0].slice(1, -1));
const NON_SKILL_MARKERS = new Set(['avicore-defer']);
const uniqueSkills = [...new Set(skillNamesIn03)].filter(
  (n) => n.startsWith('avicore-') && !NON_SKILL_MARKERS.has(n)
);

for (const skillName of uniqueSkills) {
  const skillPath = path.join(root, '.cursor', 'skills', skillName, 'SKILL.md');
  if (!fs.existsSync(skillPath)) {
    fail(`03-skills referencia ${skillName} pero no existe ${skillPath}`);
  }
}

const ROUTING_INTENTS = [
  'avicore-nuevo-modulo',
  'avicore-ui',
  'avicore-design-system',
  'avicore-deuda-tecnica',
];

for (const skill of ROUTING_INTENTS) {
  if (!comando.includes(skill)) {
    fail(`comando: falta enrutamiento mensaje 1 para ${skill}`);
  }
  if (!skills03.includes(skill)) {
    fail(`03-skills: falta ${skill} en enrutamiento o catálogo`);
  }
}

if (failed) {
  console.error('\nCorregí el drift según docs/cursor/05-evolucion-skills-y-docs.md § Matriz.');
  process.exit(1);
}

console.log(
  `OK: ${actualSkillCount} skills, punteros y enrutamiento alineados (scripts/check-agent-docs-sync.cjs).`
);
