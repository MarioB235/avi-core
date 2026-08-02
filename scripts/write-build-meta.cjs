#!/usr/bin/env node
/**
 * Genera public/build/avicore-build.json en cada vite build (fecha + commit corto).
 * Usado en Perfil para soporte («¿qué versión tenés?»).
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.join(__dirname, '..');
const outDir = path.join(root, 'public', 'build');
const outFile = path.join(outDir, 'avicore-build.json');

function gitShortCommit() {
    try {
        return execSync('git rev-parse --short HEAD', {
            cwd: root,
            encoding: 'utf8',
        }).trim();
    } catch {
        return null;
    }
}

const payload = {
    built_at: new Date().toISOString(),
};

const commit = gitShortCommit();

if (commit) {
    payload.commit = commit;
}

fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(outFile, `${JSON.stringify(payload, null, 2)}\n`, 'utf8');

console.log(`avicore-build.json → ${payload.built_at}${commit ? ` (${commit})` : ''}`);
