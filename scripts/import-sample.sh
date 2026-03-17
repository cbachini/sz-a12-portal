#!/usr/bin/env bash
# scripts/import-sample.sh — Importa amostra de conteúdo para o ambiente local
#
# Estratégia de migração (conforme arquitetura):
#   Local → 500–2000 posts (amostra representativa)
#   DEV   → dataset intermediário
#   STAGE → dataset quase completo
#   PROD  → acervo completo (~90.000 posts)
#
# Uso:
#   ./scripts/import-sample.sh caminho/para/export.xml [--posts=500]

set -euo pipefail

CONTAINER_WP="a12-wordpress"
IMPORT_FILE="${1:-}"
POST_LIMIT="${2:---posts=500}"

if [ -z "${IMPORT_FILE}" ]; then
  echo "Uso: $0 <arquivo-export.xml> [--posts=500]"
  echo ""
  echo "Gere o export no WP Admin → Ferramentas → Exportar"
  exit 1
fi

if [ ! -f "${IMPORT_FILE}" ]; then
  echo "Erro: arquivo '${IMPORT_FILE}' não encontrado."
  exit 1
fi

echo "=== Importando amostra de conteúdo ==="
echo "Arquivo: ${IMPORT_FILE}"
echo "Limite:  ${POST_LIMIT}"
echo ""

# Copia o arquivo para dentro do container
docker cp "${IMPORT_FILE}" "${CONTAINER_WP}:/tmp/import.xml"

# Instala o plugin WordPress Importer via WP-CLI (se necessário)
docker exec "${CONTAINER_WP}" wp plugin install wordpress-importer --activate --allow-root 2>/dev/null || true

# Executa a importação
docker exec "${CONTAINER_WP}" wp import /tmp/import.xml \
  --authors=create \
  --allow-root

echo ""
echo "Importação concluída."
echo "Verifique em: $(grep WP_PORT .env 2>/dev/null | cut -d= -f2 || echo 8080) → http://localhost:8080"
