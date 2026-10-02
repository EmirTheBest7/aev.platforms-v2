#!/usr/bin/env python3
"""Copies the retained static assets from the cleaned source tree (./aev-new) into the new layout.

    python3 scripts/import-retained.py [--source aev-new] [--dry-run]

- Pure copies: every file is verified byte-identical (SHA-256) after copying; nothing is edited here.
  (Stylesheets/scripts that the new architecture must change are edited by hand afterwards and are
  listed in docs/MIGRATION.md.)
- Never copies PHP, Markdown notes, `.DS_Store`, or `_inc/` (the legacy kernel holds compromised credentials).
- Idempotent: re-running only (re)copies files that differ.
"""
from __future__ import annotations

import argparse
import hashlib
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

# (source dir/file relative to the source tree, destination relative to the repo root, rule)
#   rule: "all" copy everything except the global exclusions; "static" additionally skips *.php/*.md
MAP: list[tuple[str, str, str]] = [
    # shared chrome
    ("_assets/css/bootstrap-grid.css", "public/assets/css/bootstrap-grid.css", "all"),
    ("_assets/css/header.css", "public/assets/css/header.css", "all"),
    ("_assets/js/core.js", "public/assets/js/legacy/core.js", "all"),
    ("_assets/js/aev-video.js", "public/assets/js/legacy/aev-video.js", "all"),
    ("_assets/js/core-gpu.js", "public/assets/js/legacy/core-gpu.js", "all"),
    ("_assets/js/origElectron.js", "public/assets/js/legacy/origElectron.js", "all"),
    ("_assets/icon/season1", "public/assets/icons/season1", "all"),
    ("_assets/icon/season2", "public/assets/icons/season2", "all"),
    ("_assets/icon/season3", "public/assets/icons/season3", "all"),
    ("_assets/icon/pwa", "public/assets/icons/pwa", "all"),
    ("_assets/images", "public/assets/images", "all"),
    # fonts: Ndot is used by the design (public); the other two are kept as project assets, not web-served
    ("_assets/fonts/Ndot-55.otf", "public/assets/fonts/Ndot-55.otf", "all"),
    ("_assets/fonts/DotlineBold.ttf", "resources/fonts/DotlineBold.ttf", "all"),
    ("_assets/fonts/SF-Pro.ttf", "resources/fonts/SF-Pro.ttf", "all"),
    # owner's architecture note (documentation, not served)
    ("_assets/CVX Arch", "docs/history/CVX-Arch.txt", "all"),
    # main page
    ("page/main/img", "public/assets/images/home", "all"),
    ("page/main/widgets/3droom/style.css", "resources/legacy/3droom.style.css", "all"),
    # careers
    ("page/careers/list/style.css", "public/assets/css/careers/list.css", "all"),
    ("page/careers/list/uikit.min.css", "public/assets/css/careers/uikit.min.css", "all"),
    ("page/careers/list/script.js", "public/assets/js/careers/list.js", "all"),
    ("page/careers/desc/style.css", "public/assets/css/careers/desc.css", "all"),
    ("page/careers/team/style.css", "public/assets/css/careers/team.css", "all"),
    ("page/careers/team/img", "public/assets/images/careers/team", "all"),
    # contact
    ("page/contact/style.css", "public/assets/css/contact.css", "all"),
    ("page/contact/script.js", "public/assets/js/contact.js", "all"),
    # downloads
    ("page/downloads/style.css", "public/assets/css/downloads.css", "all"),
    ("page/downloads/script.js", "public/assets/js/downloads.js", "all"),
    ("page/downloads/logo", "public/assets/brand", "all"),
    ("page/downloads/docs", "public/downloads/docs", "all"),
    ("page/downloads/wallpapers", "public/downloads/wallpapers", "all"),
    # auth
    ("home/auth/style.css", "public/assets/css/auth.css", "all"),
    ("home/auth/script.js", "public/assets/js/auth.legacy.js", "all"),
    # _api: retained as a static bundle served under its historical URL; PHP entry points and private
    # notes are re-implemented / kept out of public/ (see docs/MIGRATION.md)
    ("home/_api/UI", "public/home/_api/UI", "static"),
    ("home/_api/Docs", "public/home/_api/Docs", "static"),
]

EXCLUDE_NAMES = {".DS_Store"}


def sha(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1 << 20), b""):
            digest.update(chunk)
    return digest.hexdigest()


def files_under(src: Path, rule: str):
    if src.is_file():
        yield src, Path(src.name)
        return
    for path in sorted(src.rglob("*")):
        if not path.is_file() or path.name in EXCLUDE_NAMES:
            continue
        if rule == "static" and path.suffix.lower() in {".php", ".md"}:
            continue
        yield path, path.relative_to(src)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--source", default="aev-new")
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()
    source = (ROOT / args.source).resolve()
    if not source.is_dir():
        print(f"source tree not found: {source}", file=sys.stderr)
        return 1

    copied = unchanged = missing = 0
    total_bytes = 0
    for src_rel, dst_rel, rule in MAP:
        src = source / src_rel
        if not src.exists():
            print(f"MISSING  {src_rel}")
            missing += 1
            continue
        dst_base = ROOT / dst_rel
        for file, rel in files_under(src, rule):
            dst = dst_base if src.is_file() else dst_base / rel
            if dst.exists() and sha(dst) == sha(file):
                unchanged += 1
                continue
            if not args.dry_run:
                dst.parent.mkdir(parents=True, exist_ok=True)
                shutil.copyfile(file, dst)
                assert sha(dst) == sha(file), f"verification failed: {dst}"
            copied += 1
            total_bytes += file.stat().st_size

    print(f"copied {copied} files ({total_bytes // 1024} KB), {unchanged} already identical, {missing} sources missing")
    return 1 if missing else 0


if __name__ == "__main__":
    raise SystemExit(main())
