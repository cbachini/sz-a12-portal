#!/usr/bin/env bash
# scripts/pull-from-aws.sh — Traz estado do AWS DEV para o ambiente local
#
# O que faz:
#   1. Valida pré-requisitos
#   2. Faz dump do banco no RDS via ECS exec → download via S3
#   3. Sobrepõe banco local
#   4. Executa search-replace URLs (AWS → local)
#   5. Sincroniza mídia S3 → local (somente arquivos novos/modificados)
#
# Uso:
#   ./scripts/pull-from-aws.sh [--skip-uploads] [--dry-run]

set -euo pipefail

# ── Configuração ────────────────────────────────────────────────────────────
AWS_PROFILE="${AWS_PROFILE:-a12-dev}"
AWS_REGION="sa-east-1"
AWS_ACCOUNT="688819141871"
ECS_CLUSTER="a12-dev-cluster"
ECS_SERVICE="a12-portal-dev-service"
CONTAINER_NAME="a12-portal"
S3_BUCKET="a12-dev-uploads"
RDS_HOST="a12-dev-db-cluster.cluster-c1w46skqsu44.sa-east-1.rds.amazonaws.com"
RDS_DB="a12dev"
RDS_USER="a12_user"
SSM_PASS_PATH="/a12/dev/db_password"
REMOTE_URL="https://a12.soyuz.com.br"
LOCAL_URL="http://wordpress.sz-a12-portal.orb.local"

LOCAL_CONTAINER_DB="a12-mysql"
LOCAL_CONTAINER_WP="a12-wordpress"
LOCAL_DB_USER="a12"
LOCAL_DB_PASS="a12local123"
LOCAL_DB_NAME="a12_local"
LOCAL_UPLOADS="$(cd "$(dirname "$0")/.." && pwd)/wp-content/uploads"
WP_PORT="${WP_PORT:-8080}"

SKIP_UPLOADS=false
DRY_RUN=false

# ── Parse de argumentos ────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
  case "$1" in
    --skip-uploads) SKIP_UPLOADS=true; shift ;;
    --dry-run)      DRY_RUN=true; shift ;;
    *) echo "Uso: $0 [--skip-uploads] [--dry-run]"; exit 1 ;;
  esac
done

# ── Helpers ────────────────────────────────────────────────────────────────
log()  { echo "[$(date +%H:%M:%S)] $*"; }
die()  { echo "ERRO: $*" >&2; exit 1; }
ok()   { echo "  ✓ $*"; }
skip() { echo "  ~ $* (dry-run)"; }

run_ecs() {
  local cmd="$1"
  local task_id
  task_id=$(AWS_PAGER="" aws ecs list-tasks \
    --cluster "$ECS_CLUSTER" --service-name "$ECS_SERVICE" \
    --region "$AWS_REGION" 2>&1 | \
    python3 -c "import sys,json; t=json.load(sys.stdin)['taskArns']; print(t[0].split('/')[-1]) if t else exit(1)")

  AWS_PAGER="" aws ecs execute-command \
    --cluster "$ECS_CLUSTER" \
    --task "$task_id" \
    --container "$CONTAINER_NAME" \
    --interactive \
    --region "$AWS_REGION" \
    --command "bash -c '$cmd'" 2>&1 | \
    grep -v "^The Session Manager\|^Starting session\|^Exiting session\|^\s*$" || true
}

# ── Aviso de sobrescrita ────────────────────────────────────────────────────
echo ""
echo "  ⚠️  ATENÇÃO: este script SOBRESCREVE o banco e uploads locais"
echo "     com o estado atual do AWS DEV."
echo ""
read -rp "  Confirme digitando 'sim': " CONFIRM
[[ "$CONFIRM" == "sim" ]] || { echo "Abortado."; exit 0; }
echo ""

# ── 1. Validações ──────────────────────────────────────────────────────────
log "Verificando pré-requisitos..."
export AWS_PROFILE

command -v docker   >/dev/null || die "Docker não encontrado"
command -v aws      >/dev/null || die "AWS CLI não encontrado"
command -v python3  >/dev/null || die "python3 não encontrado"
command -v session-manager-plugin >/dev/null || die "session-manager-plugin não encontrado"

docker ps --filter "name=$LOCAL_CONTAINER_DB" --format "{{.Names}}" | grep -q "$LOCAL_CONTAINER_DB" \
  || die "Container local '$LOCAL_CONTAINER_DB' não está rodando. Execute: docker compose up -d"

docker ps --filter "name=$LOCAL_CONTAINER_WP" --format "{{.Names}}" | grep -q "$LOCAL_CONTAINER_WP" \
  || die "Container local '$LOCAL_CONTAINER_WP' não está rodando. Execute: docker compose up -d"

AWS_PAGER="" aws sts get-caller-identity --region "$AWS_REGION" 2>&1 | grep -q "$AWS_ACCOUNT" \
  || die "Credenciais AWS inválidas ou conta errada. Perfil: $AWS_PROFILE"

ok "Pré-requisitos OK"

# ── 2. Dump do RDS via ECS exec → S3 ──────────────────────────────────────
log "Exportando banco do RDS via ECS exec..."
STAMP=$(date +%Y%m%d_%H%M)
S3_DUMP_KEY="migrations/pull_${STAMP}.sql.gz"
LOCAL_DUMP="/tmp/a12_pull_${STAMP}.sql.gz"

if $DRY_RUN; then
  skip "ECS exec: mysqldump RDS → s3://$S3_BUCKET/$S3_DUMP_KEY"
else
  RDS_PASS=$(AWS_PAGER="" aws ssm get-parameter \
    --name "$SSM_PASS_PATH" --with-decryption \
    --region "$AWS_REGION" 2>&1 | \
    python3 -c "import sys,json; print(json.load(sys.stdin)['Parameter']['Value'])")

  DUMP_OUT=$(run_ecs "mysqldump -h $RDS_HOST -u $RDS_USER -p${RDS_PASS} --single-transaction --quick --no-tablespaces $RDS_DB 2>/dev/null | gzip | aws s3 cp - s3://$S3_BUCKET/$S3_DUMP_KEY --region $AWS_REGION 2>&1 && echo DUMP_OK")
  echo "$DUMP_OUT" | grep -q "DUMP_OK" || die "Falha no dump do RDS: $DUMP_OUT"
  ok "Dump RDS → s3://$S3_BUCKET/$S3_DUMP_KEY"

  AWS_PAGER="" aws s3 cp "s3://$S3_BUCKET/$S3_DUMP_KEY" "$LOCAL_DUMP" \
    --region "$AWS_REGION" >/dev/null
  DUMP_SIZE=$(du -sh "$LOCAL_DUMP" | cut -f1)
  ok "Download local: $LOCAL_DUMP ($DUMP_SIZE)"
fi

# ── 3. Sobrepõe banco local ────────────────────────────────────────────────
log "Importando banco no MySQL local..."

if $DRY_RUN; then
  skip "DROP/CREATE $LOCAL_DB_NAME + import $LOCAL_DUMP"
else
  docker exec "$LOCAL_CONTAINER_DB" mysql \
    -u root -p"${DB_ROOT_PASSWORD:-root}" \
    -e "DROP DATABASE IF EXISTS \`$LOCAL_DB_NAME\`; CREATE DATABASE \`$LOCAL_DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \`$LOCAL_DB_NAME\`.* TO '$LOCAL_DB_USER'@'%'; FLUSH PRIVILEGES;" 2>/dev/null

  if [[ "$LOCAL_DUMP" == *.gz ]]; then
    gunzip -c "$LOCAL_DUMP" | docker exec -i "$LOCAL_CONTAINER_DB" \
      mysql -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" 2>/dev/null
  else
    docker exec -i "$LOCAL_CONTAINER_DB" \
      mysql -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" 2>/dev/null < "$LOCAL_DUMP"
  fi
  ok "Banco local atualizado"
fi

# ── 4. Search-replace (AWS URL → local URL) ───────────────────────────────
log "Ajustando URLs para ambiente local..."

if $DRY_RUN; then
  skip "wp search-replace '$REMOTE_URL' → '$LOCAL_URL'"
else
  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root \
    search-replace "$REMOTE_URL" "$LOCAL_URL" \
    --all-tables --skip-columns=guid --quiet 2>/dev/null || true

  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root \
    option update home "http://localhost:${WP_PORT}" >/dev/null 2>&1 || true
  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root \
    option update siteurl "http://localhost:${WP_PORT}" >/dev/null 2>&1 || true
  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root cache flush >/dev/null 2>&1 || true
  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root rewrite flush >/dev/null 2>&1 || true
  ok "URLs ajustadas para localhost:$WP_PORT"
fi

# ── 5. Sync mídia S3 → local ───────────────────────────────────────────────
if $SKIP_UPLOADS; then
  log "Sync de uploads ignorado (--skip-uploads)"
else
  log "Sincronizando mídia S3 → local (somente arquivos novos)..."
  mkdir -p "$LOCAL_UPLOADS"
  if $DRY_RUN; then
    COUNT=$(aws s3 sync "s3://$S3_BUCKET/uploads/" "$LOCAL_UPLOADS" \
      --region "$AWS_REGION" --dryrun --exclude "*.DS_Store" 2>&1 | wc -l | tr -d ' ')
    skip "aws s3 sync (~$COUNT arquivos a baixar)"
  else
    aws s3 sync "s3://$S3_BUCKET/uploads/" "$LOCAL_UPLOADS" \
      --region "$AWS_REGION" --exclude "*.DS_Store" --no-progress
    ok "Uploads sincronizados para $LOCAL_UPLOADS"
  fi
fi

# ── Validação ──────────────────────────────────────────────────────────────
log "Validando ambiente local..."
if ! $DRY_RUN; then
  POSTS=$(docker exec "$LOCAL_CONTAINER_WP" wp --allow-root \
    post list --post_status=publish --post_type=post --format=count 2>/dev/null || echo "?")
  SITEURL=$(docker exec "$LOCAL_CONTAINER_WP" wp --allow-root \
    option get siteurl 2>/dev/null || echo "?")

  echo ""
  echo "  ┌─────────────────────────────────────────"
  echo "  │  Posts publicados : $POSTS"
  echo "  │  Site URL         : $SITEURL"
  echo "  │  Admin            : http://localhost:${WP_PORT}/wp-admin"
  echo "  └─────────────────────────────────────────"
fi

log "Pull concluído."
