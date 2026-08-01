#!/usr/bin/env node
/**
 * Verifica que el repo AviCore cumple requisitos mínimos para Laravel Cloud
 * (sin conectar al dashboard ni ejecutar deploy).
 *
 * Uso: node scripts/check-cloud-readiness.cjs
 *      pnpm run check:cloud-readiness
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.join(__dirname, '..');

function read(relPath) {
  return fs.readFileSync(path.join(root, relPath), 'utf8').replace(/\r\n/g, '\n');
}

function exists(relPath) {
  return fs.existsSync(path.join(root, relPath));
}

let failed = false;

function fail(message) {
  console.error(`✗ ${message}`);
  failed = true;
}

function ok(message) {
  console.log(`✓ ${message}`);
}

function requireFile(relPath, label) {
  if (!exists(relPath)) {
    fail(`Falta ${label}: ${relPath}`);
    return false;
  }
  ok(`${label} presente (${relPath})`);
  return true;
}

console.log('AviCore — check cloud readiness\n');

requireFile('public/index.php', 'Front controller');
requireFile('pnpm-lock.yaml', 'Lockfile pnpm');
requireFile('composer.lock', 'Lockfile Composer');

const packageJson = JSON.parse(read('package.json'));
if (!packageJson.packageManager?.startsWith('pnpm@')) {
  fail('package.json debe declarar packageManager pnpm (Corepack en Cloud)');
} else {
  ok(`packageManager: ${packageJson.packageManager}`);
}

const bootstrap = read('bootstrap/app.php');
if (!bootstrap.includes('trustProxies')) {
  fail('bootstrap/app.php sin trustProxies (HTTPS detrás del proxy de Cloud)');
} else {
  ok('trustProxies configurado en bootstrap/app.php');
}

if (!bootstrap.includes("health: '/up'") && !bootstrap.includes('health: \'/up\'')) {
  fail('Ruta health /up no declarada en bootstrap/app.php');
} else {
  ok('Health check /up declarado');
}

const envExample = read('.env.example');
if (!envExample.includes('TRUSTED_PROXIES')) {
  fail('.env.example sin TRUSTED_PROXIES');
} else {
  ok('TRUSTED_PROXIES documentado en .env.example');
}

if (!envExample.includes('AVICORE_DEMO_LOGIN')) {
  fail('.env.example sin AVICORE_DEMO_LOGIN (staging debe ser false)');
} else {
  ok('AVICORE_DEMO_LOGIN documentado en .env.example');
}

if (!envExample.includes('AVICORE_PWA_ENABLED')) {
  fail('.env.example sin AVICORE_PWA_ENABLED');
} else {
  ok('AVICORE_PWA_ENABLED documentado en .env.example');
}

if (!envExample.includes('AVICORE_PWA_INSTALL_PROMPT')) {
  fail('.env.example sin AVICORE_PWA_INSTALL_PROMPT');
} else {
  ok('AVICORE_PWA_INSTALL_PROMPT documentado en .env.example');
}

const pwaIcons = [
  'public/images/brand/pwa-192.png',
  'public/images/brand/pwa-512.png',
  'public/images/brand/pwa-512-maskable.png',
];

for (const iconPath of pwaIcons) {
  requireFile(iconPath, `Icono PWA (${path.basename(iconPath)})`);
}

const deployDoc = read('.cursor/skills/avicore-contexto/references/deploy-laravel-cloud.md');
const expectedBuild = 'pnpm install --frozen-lockfile';
if (!deployDoc.includes(expectedBuild)) {
  fail(`deploy-laravel-cloud.md no documenta build con ${expectedBuild}`);
} else {
  ok('Guía deploy con build pnpm alineada');
}

if (!deployDoc.includes('npm install -g pnpm')) {
  fail('deploy-laravel-cloud.md debe documentar npm install -g pnpm (Cloud sin corepack)');
} else {
  ok('Guía deploy: pnpm vía npm global (Cloud)');
}

const ci = read('.github/workflows/ci.yml');
if (!ci.includes("php-version: '8.3'")) {
  fail('CI sin PHP 8.3 (alinear con Laravel Cloud)');
} else {
  ok('CI: PHP 8.3');
}

if (!ci.includes("node-version: '22'")) {
  fail('CI sin Node 22 (alinear con Laravel Cloud)');
} else {
  ok('CI: Node 22');
}

try {
  execSync('composer validate --strict', { cwd: root, stdio: 'pipe' });
  ok('composer.json válido (validate --strict)');
} catch {
  fail('composer validate --strict falló — corregir antes de Cloud');
}

console.log('\n--- Simulación build (opcional local) ---');
console.log('  npm install -g pnpm@10.32.1');
console.log('  pnpm install --frozen-lockfile');
console.log('  pnpm run build');
console.log('\n--- Antes del dashboard ---');
console.log('  php artisan key:generate --show   (guardar en gestor de contraseñas; no commitear)');

if (failed) {
  console.error('\nCloud readiness: FALLO — corregir ítems marcados con ✗');
  process.exit(1);
}

console.log('\nCloud readiness: OK — podés abrir cloud.laravel.com y seguir Fase 1 en deploy-laravel-cloud.md');
