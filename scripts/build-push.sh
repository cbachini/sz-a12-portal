#!/usr/bin/env bash
# scripts/build-push.sh — Build da imagem Docker e push para ECR
#
# Uso:
#   ./scripts/build-push.sh               # build + push :latest
#   ./scripts/build-push.sh --deploy      # build + push + atualiza ECS
#   ./scripts/build-push.sh --no-push     # só build local (sem push)
#
# Requer: Docker Desktop rodando, credencial AWS válida (profile a12-dev)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
AWS_PROFILE="${AWS_PROFILE:-a12-dev}"
AWS_REGION="sa-east-1"
AWS_ACCOUNT="688819141871"
ECR_REPO="a12-portal-dev"
ECR_URI="${AWS_ACCOUNT}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}"
ECS_CLUSTER="a12-dev-cluster"
ECS_SERVICE="a12-portal-dev-service"
TASK_FAMILY="a12-portal-dev"

DEPLOY=false
NO_PUSH=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    --deploy)  DEPLOY=true;  shift ;;
    --no-push) NO_PUSH=true; shift ;;
    *) echo "Uso: $0 [--deploy] [--no-push]"; exit 1 ;;
  esac
done

log() { echo "[$(date +%H:%M:%S)] $*"; }
die() { echo "ERRO: $*" >&2; exit 1; }

# ── 1. Sincroniza lista de plugins em a12-sync-guard.php ──────────────────
cd "$SCRIPT_DIR"
log "Sincronizando A12_VERSIONED_PLUGINS com composer.json..."

python3 - <<'PY'
import json, re, pathlib

base = pathlib.Path(".")
composer = json.loads((base / "composer.json").read_text())
guard    = base / "wp-content/mu-plugins/a12-sync-guard.php"

# Plugins do composer.json
wpack = [k.replace("wpackagist-plugin/", "")
         for k in composer.get("require", {})
         if k.startswith("wpackagist-plugin/")]

# Plugins do Dockerfile (COPY fixo)
dockerfile_plugins = ["elementor-pro", "s3-uploads"]

# Bundled com WP core
wp_bundled = ["akismet", "hello"]

all_versioned = sorted(set(wpack + dockerfile_plugins + wp_bundled))

lines  = ["    // @generated por build-push.sh — não editar manualmente\n"]
lines += [f"    '{p}',\n" for p in all_versioned]
new_block = "define( 'A12_VERSIONED_PLUGINS', [\n" + "".join(lines) + "] );"

content = guard.read_text()
updated = re.sub(
    r"define\(\s*'A12_VERSIONED_PLUGINS'.*?\]\s*\);",
    new_block,
    content,
    flags=re.DOTALL,
)
guard.write_text(updated)
print(f"  Plugins versionados: {', '.join(all_versioned)}")
PY

log "a12-sync-guard.php atualizado."

# ── 2. Build ───────────────────────────────────────────────────────────────
log "Construindo imagem Docker..."
docker build \
  --platform linux/amd64 \
  --tag "${ECR_URI}:latest" \
  --tag "${ECR_REPO}:local" \
  .
log "Build concluído."

[[ "$NO_PUSH" == true ]] && { log "Pulando push (--no-push)."; exit 0; }

# ── 2. Login ECR ───────────────────────────────────────────────────────────
log "Autenticando no ECR..."
AWS_PROFILE="$AWS_PROFILE" aws ecr get-login-password \
  --region "$AWS_REGION" | \
  docker login \
    --username AWS \
    --password-stdin \
    "${AWS_ACCOUNT}.dkr.ecr.${AWS_REGION}.amazonaws.com"

# ── 3. Push ────────────────────────────────────────────────────────────────
log "Enviando imagem para ECR..."
docker push "${ECR_URI}:latest"
log "Push concluído: ${ECR_URI}:latest"

# ── 4. Deploy ECS (opcional) ───────────────────────────────────────────────
if [[ "$DEPLOY" == true ]]; then
  log "Forçando novo deployment no ECS (${ECS_CLUSTER}/${ECS_SERVICE})..."
  AWS_PROFILE="$AWS_PROFILE" AWS_PAGER="" aws ecs update-service \
    --region "$AWS_REGION" \
    --cluster "$ECS_CLUSTER" \
    --service "$ECS_SERVICE" \
    --force-new-deployment \
    --output json \
    --query 'service.{desired:desiredCount,running:runningCount,taskDef:taskDefinition}' \
    | cat
  log "Deploy iniciado. Acompanhe: aws ecs describe-services --cluster $ECS_CLUSTER --services $ECS_SERVICE"
fi
