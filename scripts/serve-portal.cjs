/**
 * Sirve portal/ en http://127.0.0.1:5500 (misma URL que Live Server con root /portal).
 * Uso: pnpm run portal:dev
 */
const http = require("http");
const fs = require("fs");
const path = require("path");

const PORT = 5500;
const HOST = "127.0.0.1";
const ROOT = path.join(__dirname, "..", "portal");

const MIME = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "text/javascript; charset=utf-8",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".svg": "image/svg+xml",
  ".webp": "image/webp",
  ".ico": "image/x-icon",
  ".json": "application/json",
};

function safePath(urlPath) {
  const decoded = decodeURIComponent(urlPath.split("?")[0]);
  const relative = decoded === "/" ? "/index.html" : decoded;
  const file = path.normalize(path.join(ROOT, relative));
  if (!file.startsWith(ROOT)) return null;
  return file;
}

const server = http.createServer((req, res) => {
  const file = safePath(req.url || "/");
  if (!file) {
    res.writeHead(403);
    res.end("Forbidden");
    return;
  }

  fs.readFile(file, (err, data) => {
    if (err) {
      res.writeHead(404);
      res.end(`Cannot GET ${req.url}`);
      return;
    }
    const ext = path.extname(file).toLowerCase();
    res.writeHead(200, {
      "Content-Type": MIME[ext] || "application/octet-stream",
      // Los imprimibles se abren en pestaña nueva: sin esto Chrome reusa CSS viejo
      "Cache-Control": "no-store, max-age=0",
    });
    res.end(data);
  });
});

server.listen(PORT, HOST, () => {
  console.log(`Portal AviCore → http://${HOST}:${PORT}/`);
  console.log("Cierra con Ctrl+C");
});
