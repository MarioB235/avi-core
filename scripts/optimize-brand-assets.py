#!/usr/bin/env python3
"""Comprime fondos de marca (JPEG) y sincroniza copias en public/images/brand/."""

from __future__ import annotations

import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
BRAND_RESOURCES = ROOT / "resources" / "images" / "brand"
BRAND_PUBLIC = ROOT / "public" / "images" / "brand"
BACKGROUNDS = (
    ("background-desktop", 1600),
    ("background-mobile", 1080),
    ("admin-home-hero", 1920),
)
JPEG_QUALITY = 78


def compress_background(stem: str, max_width: int) -> Path:
    png_path = BRAND_RESOURCES / f"{stem}.png"
    jpg_path = BRAND_RESOURCES / f"{stem}.jpg"
    source = png_path if png_path.exists() else jpg_path
    if not source.exists():
        raise FileNotFoundError(source)

    img = Image.open(source)
    if img.mode in ("RGBA", "P"):
        img = img.convert("RGB")
    elif img.mode != "RGB":
        img = img.convert("RGB")
    if img.width > max_width:
        ratio = max_width / img.width
        img = img.resize(
            (max_width, int(img.height * ratio)),
            Image.Resampling.LANCZOS,
        )
    out = BRAND_RESOURCES / f"{stem}.jpg"
    img.save(out, format="JPEG", quality=JPEG_QUALITY, optimize=True)
    if png_path.exists() and out != png_path:
        png_path.unlink(missing_ok=True)
    return out


def main() -> int:
    for stem, max_width in BACKGROUNDS:
        try:
            out = compress_background(stem, max_width)
        except FileNotFoundError:
            print(f"Omitido (no existe): {stem}", file=sys.stderr)
            continue
        BRAND_PUBLIC.mkdir(parents=True, exist_ok=True)
        dest = BRAND_PUBLIC / out.name
        dest.write_bytes(out.read_bytes())
        public_png = BRAND_PUBLIC / f"{stem}.png"
        public_png.unlink(missing_ok=True)
        print(f"OK fondo: {out.name} -> {out.stat().st_size // 1024} KiB")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
