#!/usr/bin/env bash
# scripts/sync-dockerfile.sh — Detecta plugins/temas instalados no container
# que não estão no Dockerfile/composer.json e mostra como adicioná-los.
#
# Uso:
#   ./scripts/sync-dockerfile.sh           # auditoria (somente leitura)
#
# O que faz:
#   1. Lê plugins e temas do container ECS em produção via WP-CLI
#   2. Compara com composer.json
#   3. Reporta o que está faltando com a instrução exata para adicionar

set -euo pipefail

AWS_PROFILE="${AWS_PROFILE:-a12-dev}"
AWS_REGION="sa-east-1"
ECS_CLUSTER="a12-dev-cluster"
ECS_SERVICE="a12-portal-dev-service"
CONTAINER="a12-portal"
COMPOSER_JSON="$(cd "$(dirname "$0")/.." && pwd)/composer.json"

log() { echo "[$(date +%H:%M:%S)] $*"; }

# ── Obtém task em execução ──────────────────────────────────────────────────
TASK_ARN=$(AWS_PAGER="" aws ecs list-tasks \
  --cluster "$ECS_CLUSTER" --service-name "$ECS_SERVICE" \
  --region "$AWS_REGION" --desired-status RUNNING \
  --query 'taskArns[0]' --output text)
TASK_ID="${TASK_ARN##*/}"
log "Task: $TASK_ID"

# ── Coleta plugins do container (exclui must-use — gerenciados via COPY mu-plugins/) ──
log "Lendo plugins do container..."
PLUGINS_RAW=$(AWS_PAGER="" aws ecs execute-command \
  --cluster "$ECS_CLUSTER" --task "$TASK_ID" \
  --container "$CONTAINER" --region "$AWS_REGION" --interactive \
  --command "bash -c 'wp --allow-root plugin list --fields=name,status --format=csv --skip-plugins --skip-themes 2>/dev/null | grep -v \",must-use\"'" \
  2>&1 | grep -v "^The Session Manager\|^Starting session\|^Exiting session\|^$\|\-\-\-" | tail -n +2)

# ── Plugins no composer.json ────────────────────────────────────────────────
COMPOSER_PLUGINS=$(python3 -c "
import json
with open('$COMPOSER_JSON') as f:
    d = json.load(f)
pkgs = [k.replace('wpackagist-plugin/','') for k in d.get('require',{}) if k.startswith('wpackagist-plugin/')]
print('\n'.join(pkgs))
")

# Plugins gerenciados fora do composer (Dockerfile COPY)
DOCKERFILE_PLUGINS="elementor-pro s3-uploads"

# Plugins que vêm com o WP core (não precisam de versionamento)
WP_BUNDLED="akismet hello"

echo
echo "============================================================"
echo "  AUDITORIA DE PLUGINS"
echo "============================================================"
MISSING=0
while IFS=, read -r name status; do
  [[ -z "$name" || "$name" == "name" ]] && continue
  [[ "$status" == "must-use" ]] && continue   # mu-plugins são versionados via COPY
  if echo "$COMPOSER_PLUGINS" | grep -qx "$name" 2>/dev/null; then
    echo "  ✓ $name  (composer.json)"
  elif echo "$DOCKERFILE_PLUGINS" | grep -qw "$name" 2>/dev/null; then
    echo "  ✓ $name  (Dockerfile COPY)"
  elif echo "$WP_BUNDLED" | grep -qw "$name" 2>/dev/null; then
    echo "  ✓ $name  (bundled com WP core)"
  else
    echo "  ✗ $name  ← NÃO VERSIONADO"
    echo "    → Adicionar em composer.json:"
    echo "      \"wpackagist-plugin/${name}\": \"*\""
    MISSING=$((MISSING + 1))
  fi
done <<< "$PLUGINS_RAW"

echo
if [[ $MISSING -eq 0 ]]; then
  echo "  Todos os plugins estão versionados. Container é portável."
else
  echo "  $MISSING plugin(s) não versionado(s)."
  echo "  Adicione-os ao composer.json e execute:"
  echo "    ./scripts/build-push.sh --deploy"
fi
echo "============================================================"
