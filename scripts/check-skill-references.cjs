#!/usr/bin/env node
/**
 * Verifica enlaces relativos en documentación del agente (.md, .mdc, .html).
 *
 * Uso: node scripts/check-skill-references.cjs
 */

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

const SCAN_ROOTS = [
  path.join(root, '.cursor'),
  path.join(root, 'docs'),
  path.join(root, 'AGENTS.md'),
  path.join(root, 'README.md'),
];

const EXTENSIONS = new Set(['.md', '.mdc', '.html']);

/** Rutas que no son archivos (Blade, anclas, protocolos). */
function isSkippableTarget(raw) {
  if (!raw || raw.startsWith('#')) {
    return true;
  }
  if (/^(https?:|mailto:|tel:|data:)/i.test(raw)) {
    return true;
  }
  if (raw.includes('{{') || raw.includes('}}')) {
    return true;
  }
  if (raw.startsWith('javascript:')) {
    return true;
  }
  return false;
}

function collectFiles(entry, out = []) {
  if (!fs.existsSync(entry)) {
    return out;
  }
  const stat = fs.statSync(entry);
  if (stat.isFile()) {
    if (EXTENSIONS.has(path.extname(entry).toLowerCase())) {
      out.push(entry);
    }
    return out;
  }
  for (const name of fs.readdirSync(entry)) {
    if (name === 'node_modules' || name === 'vendor' || name === '.git') {
      continue;
    }
    collectFiles(path.join(entry, name), out);
  }
  return out;
}

function extractLinks(content, filePath) {
  const links = [];
  const ext = path.extname(filePath).toLowerCase();

  if (ext === '.html') {
    for (const match of content.matchAll(/\bhref\s*=\s*["']([^"']+)["']/gi)) {
      links.push(match[1].trim());
    }
    return links;
  }

  // Markdown: [text](url) y [`code`](url)
  for (const match of content.matchAll(/\[[^\]]*\]\(([^)]+)\)/g)) {
    let target = match[1].trim();
    // Title opcional: url "title"
    const spaceIdx = target.search(/\s+/);
    if (spaceIdx > 0) {
      target = target.slice(0, spaceIdx);
    }
    target = target.replace(/^<|>$/g, '');
    links.push(target);
  }

  return links;
}

function resolveTarget(fromFile, rawTarget) {
  const hashIdx = rawTarget.indexOf('#');
  const withoutHash = hashIdx >= 0 ? rawTarget.slice(0, hashIdx) : rawTarget;
  if (!withoutHash) {
    return null; // solo ancla en mismo archivo
  }
  if (path.isAbsolute(withoutHash) && !/^[A-Za-z]:[\\/]/.test(withoutHash)) {
    // Unix absolute path outside repo — treat as broken unless under root
    return withoutHash;
  }
  return path.normalize(path.join(path.dirname(fromFile), withoutHash));
}

let failed = false;

function fail(message) {
  console.error(message);
  failed = true;
}

const files = [];
for (const entry of SCAN_ROOTS) {
  collectFiles(entry, files);
}

let checked = 0;

for (const filePath of files) {
  const content = fs.readFileSync(filePath, 'utf8');
  const links = extractLinks(content, filePath);
  const relFile = path.relative(root, filePath).replace(/\\/g, '/');

  for (const raw of links) {
    if (isSkippableTarget(raw)) {
      continue;
    }

    const resolved = resolveTarget(filePath, raw);
    if (resolved === null) {
      continue;
    }

    checked += 1;

    if (!fs.existsSync(resolved)) {
      fail(`${relFile}: enlace roto → ${raw}`);
    }
  }
}

// Compat: SKILL.md → references/ con patrón histórico (doble red)
const skillsRoot = path.join(root, '.cursor', 'skills');
if (fs.existsSync(skillsRoot)) {
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
}

if (failed) {
  process.exit(1);
}

console.log(`OK: ${checked} enlaces relativos verificados en ${files.length} archivos.`);
