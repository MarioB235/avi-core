#!/usr/bin/env node
/**
 * Verifica que cada SKILL.md enlace a references/ existentes.
 *
 * Uso: node scripts/check-skill-references.cjs
 */

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const skillsRoot = path.join(root, '.cursor', 'skills');

let failed = false;

function fail(message) {
  console.error(message);
  failed = true;
}

if (!fs.existsSync(skillsRoot)) {
  console.error('No existe .cursor/skills/');
  process.exit(1);
}

const skillDirs = fs.readdirSync(skillsRoot, { withFileTypes: true })
  .filter((d) => d.isDirectory() && fs.existsSync(path.join(skillsRoot, d.name, 'SKILL.md')));

for (const dir of skillDirs) {
  const skillPath = path.join(skillsRoot, dir.name, 'SKILL.md');
  const content = fs.readFileSync(skillPath, 'utf8');
  const refs = [...content.matchAll(/\[`([^`]+)`\]\(references\/([^)]+)\)/g)];

  for (const [, , refFile] of refs) {
    const refPath = path.join(skillsRoot, dir.name, 'references', refFile);
    if (!fs.existsSync(refPath)) {
      fail(`${dir.name}/SKILL.md enlaza references/${refFile} pero no existe`);
    }
  }
}

if (failed) {
  process.exit(1);
}

console.log(`OK: references enlazadas en ${skillDirs.length} skills existen.`);
