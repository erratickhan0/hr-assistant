#!/usr/bin/env bash
#
# HR Assistant — server deploy (Hostinger / SSH)
# Usage: ./deploy.sh
#        BRANCH=main ./deploy.sh
#

set -euo pipefail

BRANCH="${BRANCH:-main}"
REMOTE="${REMOTE:-origin}"

cd "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "==> Deploy: $(pwd)"
echo "==> Branch: ${BRANCH}"

log()  { echo "[deploy] $*"; }
warn() { echo "[deploy] WARNING: $*" >&2; }
die()  { echo "[deploy] ERROR: $*" >&2; exit 1; }

command -v git >/dev/null 2>&1 || die "git not found"
command -v php >/dev/null 2>&1 || die "php not found"
command -v composer >/dev/null 2>&1 || die "composer not found (install Composer on server)"

[[ -f artisan ]] || die "artisan not found — run this from Laravel project root"
[[ -f .env ]] || die ".env missing — copy .env.example to .env and configure first"

log "Maintenance mode ON"
php artisan down --retry=60 || true

cleanup() {
  log "Maintenance mode OFF"
  php artisan up || true
}
trap cleanup EXIT

log "git fetch ${REMOTE}"
git fetch "${REMOTE}"

log "git pull ${REMOTE} ${BRANCH}"
git pull "${REMOTE}" "${BRANCH}"

log "composer install (production)"
composer install \
  --no-dev \
  --prefer-dist \
  --optimize-autoloader \
  --no-interaction \
  --no-progress

if command -v npm >/dev/null 2>&1; then
  log "npm ci + build"
  npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
  npm run build
else
  if [[ ! -f public/build/manifest.json ]]; then
    warn "npm not on server and public/build/manifest.json missing."
    warn "Locally run: npm run build — then upload public/build/ or redeploy after build."
  else
    log "npm not found — using existing public/build/"
  fi
fi

if [[ ! -L public/storage ]]; then
  log "php artisan storage:link"
  php artisan storage:link || true
fi

log "migrate"
php artisan migrate --force

log "optimize"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

log "permissions"
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

log "Deploy finished OK."
