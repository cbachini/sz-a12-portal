#!/usr/bin/env bash
# scripts/export-mirror.sh — Exporta um espelho local do portal
# Gera um dump do banco e um arquivo compactado dos uploads para compartilhar

set -euo pipefail

CONTAINER_DB="a12-mysql"
PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

cd "${PROJECT_ROOT}"

if [ ! -f .env ]; then
  echo "Erro: arquivo .env não encontrado."
  echo "Crie o arquivo .env a partir de .env.example"
  exit 1
fi

# shellcheck disable=SC1091
set -a; source .env; set +a

if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_DB}$"; then
  echo "Erro: container '${CONTAINER_DB}' não está rodando."
  echo "Rode: docker compose up -d"
  exit 1
fi

mkdir -p db/dumps

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
DB_DUMP="db/dumps/a12-mirror-${TIMESTAMP}.sql.gz"
UPLOADS_ARCHIVE="db/dumps/a12-mirror-${TIMESTAMP}-uploads.tar.gz"

echo "Exportando banco para ${DB_DUMP}..."
docker exec "${CONTAINER_DB}" mysqldump \
  -u"${DB_USER}" \
  -p"${DB_PASSWORD}" \
  --single-transaction \
  --quick \
  --default-character-set=utf8mb4 \
  "${DB_NAME}" | gzip > "${DB_DUMP}"

echo "Compactando uploads para ${UPLOADS_ARCHIVE}..."
tar -czf "${UPLOADS_ARCHIVE}" -C wp-content uploads

echo ""
echo "Espelho exportado com sucesso:"
echo "- Banco:   ${DB_DUMP}"
echo "- Uploads: ${UPLOADS_ARCHIVE}"
echo ""
echo "Compartilhe esses dois arquivos fora do Git."