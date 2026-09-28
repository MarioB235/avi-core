#!/usr/bin/env node
/**
 * Verifica artefactos INVERA del portal y que el HTML generado esté al día con el Markdown.
 *
 * Uso: pnpm run check:portal-invera
 */

const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const root = path.join(__dirname, "..");

const REQUIRED = [
  "portal/css/invera-print.css",
  "portal/assets/logo-invera.png",
  "portal/contenido/fuentes/invera-documento-maestro.md",
  "portal/contenido/documentos/documento-maestro-invera.html",
  "portal/contenido/documentos/invera-documento-maestro.generated.html",
  "scripts/build-invera-documento-maestro.cjs",
];

let failed = false;

function fail(message) {
  console.error(message);
  failed = true;
}

for (const rel of REQUIRED) {
  if (!fs.existsSync(path.join(root, rel))) {
    fail(`Falta artefacto INVERA: ${rel}`);
  }
}

const indexHtml = fs.readFileSync(path.join(root, "portal/index.html"), "utf8");
if (!indexHtml.includes("css/invera-print.css")) {
  fail("portal/index.html no enlaza css/invera-print.css");
}

const generatedPath = path.join(root, "portal/contenido/documentos/invera-documento-maestro.generated.html");
if (fs.existsSync(generatedPath)) {
  const before = fs.readFileSync(generatedPath, "utf8");
  try {
    execSync("node scripts/build-invera-documento-maestro.cjs", {
      cwd: root,
      stdio: "pipe",
    });
  } catch (error) {
    fail(`build:portal-invera falló: ${error.message}`);
  }
  const after = fs.readFileSync(generatedPath, "utf8");
  if (before !== after) {
    fail(
      "invera-documento-maestro.generated.html desactualizado. Ejecutá pnpm run build:portal-invera y commiteá el fragmento."
    );
  }
}

if (failed) {
  process.exit(1);
}

console.log("check:portal-invera OK");
