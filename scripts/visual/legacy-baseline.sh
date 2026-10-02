#!/usr/bin/env bash
# Serves a SANITISED copy of the legacy site (./aev-new by default) for visual comparison.
#   - copies only _assets/ and page/ (never home/, never credentials)
#   - replaces _inc/functions.php with a stub: no credentials, notify() is a no-op
#   - rewrites absolute production URLs to http://localhost:$PORT
#   - serves with PHP's built-in server using several workers (the legacy homepage requests itself)
# NEVER submit the legacy forms or run its cron.php: the real notify() would hit a live Telegram chat.
#
# Usage: scripts/visual/legacy-baseline.sh up|down   (PORT=8099 by default)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
LEGACY="${LEGACY_SRC:-$ROOT/aev-new}"   # the cleaned source of truth; override with LEGACY_SRC=…
PORT="${PORT:-8099}"
WORK="${TMPDIR:-/tmp}/aliev-legacy-baseline"
NAME="aliev-legacy-baseline"

case "${1:-up}" in
  up)
    [ -d "$LEGACY/page" ] || { echo "legacy tree not found at $LEGACY" >&2; exit 1; }
    rm -rf "$WORK" && mkdir -p "$WORK/_inc"
    rsync -a --exclude='.DS_Store' --exclude='page/downloads/wallpapers' --exclude='page/design_store' --exclude='page/DC25' \
      "$LEGACY/_assets" "$LEGACY/page" "$WORK/"
    cat > "$WORK/_inc/functions.php" <<'PHP'
<?php
// BASELINE STUB — no credentials; notify() is disabled. Never replace with the real file.
define("BASE_URL", 'http://' . $_SERVER['HTTP_HOST'] . "/");
define("LOGO", BASE_URL . "page/downloads/logo/ALIEV.svg");
define("LOGO_IO", BASE_URL . "page/downloads/logo/ALIEV3.svg");
function notify($t) {}
function connect() { return null; }
function random_str(int $l = 16): string { return bin2hex(random_bytes($l)); }
PHP
    python3 - "$WORK" "$PORT" <<'PY'
import os, re, sys
work, port = sys.argv[1], sys.argv[2]
for dp, _, fs in os.walk(work):
    for f in fs:
        if f.endswith(('.php', '.html', '.js', '.css', '.json')):
            p = os.path.join(dp, f)
            t = open(p, errors='ignore').read()
            u = re.sub(r'https://(www\.)?aliev\.io', f'http://localhost:{port}', t)
            u = re.sub(r'<script[^>]*js\.web4ukraine\.org[^>]*></script>', '', u)  # remote script that can redirect visitors
            if u != t:
                open(p, 'w').write(u)
PY
    docker rm -f "$NAME" >/dev/null 2>&1 || true
    docker run -d --name "$NAME" -p "$PORT:$PORT" -e PHP_CLI_SERVER_WORKERS=6 -v "$WORK:/app" -w /app php:8.3-cli \
      php -S "0.0.0.0:$PORT" -t /app >/dev/null
    echo "legacy baseline at http://localhost:$PORT/page/main/"
    ;;
  down)
    docker rm -f "$NAME" >/dev/null 2>&1 || true
    rm -rf "$WORK"
    ;;
  *) echo "usage: $0 up|down" >&2; exit 2 ;;
esac
