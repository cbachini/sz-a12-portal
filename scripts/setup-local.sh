#!/usr/bin/env bash
# scripts/setup-local.sh — Primeiro setup do ambiente local
# Executa após: docker compose up -d   (aguarde os containers subirem)
#
# O que faz:
#   1. Aguarda o MySQL estar pronto
#   2. Instala o WordPress via WP-CLI
#   3. Ativa plugins básicos
#   4. Cria usuário admin

set -euo pipefail

CONTAINER_WP="a12-wordpress"
CONTAINER_DB="a12-mysql"

# Carrega variáveis do .env
if [ ! -f .env ]; then
  echo "Erro: arquivo .env não encontrado."
  echo "Crie o arquivo .env a partir de .env.example"
  exit 1
fi

# shellcheck disable=SC1091
set -a; source .env; set +a

WP_PORT="${WP_PORT:-8080}"
SITE_URL="http://localhost:${WP_PORT}"
SITE_TITLE="${SITE_TITLE:-Portal A12 (Local)}"
ADMIN_USER="${WP_ADMIN_USER:-admin}"
ADMIN_PASS="${WP_ADMIN_PASS:-admin123}"
ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@a12.local}"

echo "=== Portal A12 — Setup Local ==="
echo "URL: ${SITE_URL}"
echo ""

# ---------------------------------------------------------------
# 1. Aguarda MySQL
# ---------------------------------------------------------------
echo "Aguardando MySQL..."
for i in $(seq 1 30); do
  if docker exec "${CONTAINER_DB}" mysqladmin ping -h localhost -u root -p"${DB_ROOT_PASSWORD}" --silent 2>/dev/null; then
    echo "MySQL pronto."
    break
  fi
  sleep 2
done

# ---------------------------------------------------------------
# 2. Instala WordPress (caso não instalado)
# ---------------------------------------------------------------
if docker exec "${CONTAINER_WP}" wp core is-installed --allow-root 2>/dev/null; then
  echo "WordPress já instalado. Pulando..."
else
  echo "Instalando WordPress..."
  docker exec "${CONTAINER_WP}" wp core install --allow-root \
    --url="${SITE_URL}" \
    --title="${SITE_TITLE}" \
    --admin_user="${ADMIN_USER}" \
    --admin_password="${ADMIN_PASS}" \
    --admin_email="${ADMIN_EMAIL}" \
    --skip-email
  echo "WordPress instalado!"
fi

# ---------------------------------------------------------------
# 3. Configurações básicas
# ---------------------------------------------------------------
echo "Configurando WordPress..."
docker exec "${CONTAINER_WP}" wp option update --allow-root blogdescription "O portal católico do Brasil"
docker exec "${CONTAINER_WP}" wp option update --allow-root timezone_string "America/Sao_Paulo"
docker exec "${CONTAINER_WP}" wp option update --allow-root date_format "d/m/Y"
docker exec "${CONTAINER_WP}" wp option update --allow-root permalink_structure "/%postname%/"
docker exec "${CONTAINER_WP}" wp rewrite flush --allow-root

# ---------------------------------------------------------------
# 4. Remove conteúdo padrão
# ---------------------------------------------------------------
echo "Removendo conteúdo padrão..."
docker exec "${CONTAINER_WP}" wp post delete 1 2 --allow-root --force 2>/dev/null || true
docker exec "${CONTAINER_WP}" wp comment delete 1 --allow-root --force 2>/dev/null || true

# ---------------------------------------------------------------
# 5. Restaurar uploads do zip Duplicator (se existir e uploads vazio)
# ---------------------------------------------------------------
UPLOAD_COUNT=$(docker exec "${CONTAINER_WP}" find /var/www/html/wp-content/uploads -type f 2>/dev/null | wc -l | tr -d ' ')
ARCHIVE=$(ls *.zip 2>/dev/null | head -1)

if [ "${UPLOAD_COUNT}" -lt "10" ] && [ -n "${ARCHIVE}" ]; then
  echo "Restaurando uploads do arquivo ${ARCHIVE}..."
  unzip -q "${ARCHIVE}" "wp-content/uploads/*" -d /tmp/a12-uploads-restore 2>/dev/null || true
  if [ -d "/tmp/a12-uploads-restore/wp-content/uploads" ]; then
    docker cp /tmp/a12-uploads-restore/wp-content/uploads/. "${CONTAINER_WP}:/var/www/html/wp-content/uploads/"
    docker exec "${CONTAINER_WP}" chown -R www-data:www-data /var/www/html/wp-content/uploads
    rm -rf /tmp/a12-uploads-restore
    echo "Uploads restaurados."
  fi
elif [ "${UPLOAD_COUNT}" -gt "10" ]; then
  echo "Uploads já presentes (${UPLOAD_COUNT} arquivos). Pulando..."
fi

# ---------------------------------------------------------------
# 6. Regenerar CSS do Elementor
# ---------------------------------------------------------------
docker exec "${CONTAINER_WP}" wp elementor flush-css --allow-root 2>/dev/null || true

echo ""
echo "=== Setup concluído! ==="
echo ""
echo "WordPress: ${SITE_URL}"
echo "Admin:     ${SITE_URL}/wp-admin"
echo "Usuário:   ${ADMIN_USER}"
echo "Senha:     ${ADMIN_PASS}"
echo ""
echo "Para usar WP-CLI: ./scripts/wp <comando>"
