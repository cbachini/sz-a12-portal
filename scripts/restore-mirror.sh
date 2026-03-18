#!/usr/bin/env bash
# scripts/restore-mirror.sh — Restaura um espelho local do portal
# Uso: ./scripts/restore-mirror.sh caminho/para/dump.sql.gz [caminho/para/uploads.tar.gz]

set -euo pipefail

CONTAINER_DB="a12-mysql"
CONTAINER_WP="a12-wordpress"
PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

cd "${PROJECT_ROOT}"

if [ $# -lt 1 ]; then
  echo "Uso: ./scripts/restore-mirror.sh caminho/para/dump.sql.gz [caminho/para/uploads.tar.gz]"
  exit 1
fi

DB_DUMP="$1"
UPLOADS_ARCHIVE="${2:-}"

if [ ! -f .env ]; then
  echo "Erro: arquivo .env não encontrado."
  echo "Crie o arquivo .env a partir de .env.example"
  exit 1
fi

if [ ! -f "${DB_DUMP}" ]; then
  echo "Erro: dump não encontrado em ${DB_DUMP}"
  exit 1
fi

if [ -n "${UPLOADS_ARCHIVE}" ] && [ ! -f "${UPLOADS_ARCHIVE}" ]; then
  echo "Erro: arquivo de uploads não encontrado em ${UPLOADS_ARCHIVE}"
  exit 1
fi

# shellcheck disable=SC1091
set -a; source .env; set +a

if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_DB}$"; then
  echo "Erro: container '${CONTAINER_DB}' não está rodando."
  echo "Rode: docker compose up -d"
  exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_WP}$"; then
  echo "Erro: container '${CONTAINER_WP}' não está rodando."
  echo "Rode: docker compose up -d"
  exit 1
fi

echo "Recriando banco ${DB_NAME}..."
docker exec "${CONTAINER_DB}" mysql \
  -uroot \
  -p"${DB_ROOT_PASSWORD}" \
  -e "DROP DATABASE IF EXISTS \\`${DB_NAME}\\`; CREATE DATABASE \\`${DB_NAME}\\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \\`${DB_NAME}\\`.* TO '${DB_USER}'@'%'; FLUSH PRIVILEGES;"

echo "Importando dump ${DB_DUMP}..."
if [[ "${DB_DUMP}" == *.gz ]]; then
  gunzip -c "${DB_DUMP}" | docker exec -i "${CONTAINER_DB}" mysql -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}"
else
  cat "${DB_DUMP}" | docker exec -i "${CONTAINER_DB}" mysql -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}"
fi

if [ -n "${UPLOADS_ARCHIVE}" ]; then
  echo "Restaurando uploads de ${UPLOADS_ARCHIVE}..."
  rm -rf wp-content/uploads
  mkdir -p wp-content
  tar -xzf "${UPLOADS_ARCHIVE}" -C wp-content
  docker exec "${CONTAINER_WP}" chown -R www-data:www-data /var/www/html/wp-content/uploads
fi

echo "Ajustando URL base para localhost..."
docker exec "${CONTAINER_WP}" wp search-replace --allow-root 'https://www.a12.com' 'http://localhost:${WP_PORT:-8080}' --skip-columns=guid 2>/dev/null || true
docker exec "${CONTAINER_WP}" wp search-replace --allow-root 'https://a12.com' 'http://localhost:${WP_PORT:-8080}' --skip-columns=guid 2>/dev/null || true
docker exec "${CONTAINER_WP}" wp option update --allow-root home "http://localhost:${WP_PORT:-8080}" >/dev/null
docker exec "${CONTAINER_WP}" wp option update --allow-root siteurl "http://localhost:${WP_PORT:-8080}" >/dev/null
docker exec "${CONTAINER_WP}" wp rewrite flush --allow-root >/dev/null
docker exec "${CONTAINER_WP}" wp elementor flush-css --allow-root >/dev/null 2>&1 || true

echo ""
echo "Espelho restaurado com sucesso."
echo "Acesse: http://localhost:${WP_PORT:-8080}"