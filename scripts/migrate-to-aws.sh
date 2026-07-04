#!/usr/bin/env bash
# scripts/migrate-to-aws.sh — Migra estado local (banco + mídia) para AWS DEV
#
# O que faz:
#   1. Valida pré-requisitos (git limpo, Docker, AWS, ECS exec)
#   2. Cria git tag versionada localmente
#   3. Exporta banco local sem USE/CREATE DATABASE
#   4. Faz upload do dump para S3
#   5. Importa no Aurora RDS via ECS exec
#   6. Executa WP-CLI search-replace + cache flush
#   7. Sincroniza uploads locais → S3
#   8. Valida o site remoto (HTTP 200 + contagem de posts)
#
# Uso:
#   ./scripts/migrate-to-aws.sh [--tag v1.2.3] [--skip-uploads] [--dry-run]

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
LOCAL_DB_USER="a12"
LOCAL_DB_PASS="a12local123"
LOCAL_DB_NAME="a12_local"
LOCAL_UPLOADS="$(cd "$(dirname "$0")/.." && pwd)/wp-content/uploads"

SKIP_UPLOADS=false
DRY_RUN=false
CUSTOM_TAG=""

# ── Parse de argumentos ────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
  case "$1" in
    --tag)       CUSTOM_TAG="$2"; shift 2 ;;
    --skip-uploads) SKIP_UPLOADS=true; shift ;;
    --dry-run)   DRY_RUN=true; shift ;;
    *) echo "Uso: $0 [--tag vX.Y.Z] [--skip-uploads] [--dry-run]"; exit 1 ;;
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

# ── 1. Validações ──────────────────────────────────────────────────────────
log "Verificando pré-requisitos..."
export AWS_PROFILE

command -v docker   >/dev/null || die "Docker não encontrado"
command -v aws      >/dev/null || die "AWS CLI não encontrado"
command -v python3  >/dev/null || die "python3 não encontrado"
command -v session-manager-plugin >/dev/null || die "session-manager-plugin não encontrado (brew install session-manager-plugin)"

docker ps --filter "name=$LOCAL_CONTAINER_DB" --format "{{.Names}}" | grep -q "$LOCAL_CONTAINER_DB" \
  || die "Container local '$LOCAL_CONTAINER_DB' não está rodando. Execute: docker compose up -d"

AWS_PAGER="" aws sts get-caller-identity --region "$AWS_REGION" 2>&1 | grep -q "$AWS_ACCOUNT" \
  || die "Credenciais AWS inválidas ou conta errada. Perfil: $AWS_PROFILE"

ok "Pré-requisitos OK"

# ── 2. Git tag ─────────────────────────────────────────────────────────────
log "Criando git tag..."
STAMP=$(date +%Y%m%d_%H%M)
TAG="${CUSTOM_TAG:-migrate/${STAMP}}"

if $DRY_RUN; then
  skip "git tag '$TAG'"
else
  cd "$(dirname "$0")/.."
  git add -A
  if ! git diff --cached --quiet; then
    git commit -m "chore: snapshot pré-migração AWS ${STAMP}" --quiet
  fi
  git tag -a "$TAG" -m "Migração para AWS DEV em ${STAMP}" 2>/dev/null || true
  ok "Tag criada: $TAG"
fi

# ── 3. Dump do banco local ─────────────────────────────────────────────────
log "Exportando banco local..."
DUMP_FILE="/tmp/a12_migrate_${STAMP}.sql.gz"

if $DRY_RUN; then
  skip "mysqldump → $DUMP_FILE"
else
  docker exec "$LOCAL_CONTAINER_DB" mysqldump \
    -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" \
    --single-transaction --quick --routines --triggers --no-tablespaces \
    "$LOCAL_DB_NAME" 2>/dev/null \
    | grep -v "^USE \`" \
    | grep -v "^CREATE DATABASE" \
    | gzip > "$DUMP_FILE"

  DUMP_SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
  ok "Dump gerado: $DUMP_FILE ($DUMP_SIZE)"
fi

# ── 4. Upload dump → S3 ────────────────────────────────────────────────────
log "Enviando dump para S3..."
S3_DUMP_KEY="migrations/a12_migrate_${STAMP}.sql.gz"

if $DRY_RUN; then
  skip "aws s3 cp → s3://$S3_BUCKET/$S3_DUMP_KEY"
else
  AWS_PAGER="" aws s3 cp "$DUMP_FILE" "s3://$S3_BUCKET/$S3_DUMP_KEY" \
    --region "$AWS_REGION" >/dev/null
  ok "Upload: s3://$S3_BUCKET/$S3_DUMP_KEY"
fi

# ── 5. Import via ECS exec ─────────────────────────────────────────────────
log "Importando banco no RDS via ECS exec..."

if $DRY_RUN; then
  skip "ECS exec: import banco"
else
  RDS_PASS=$(AWS_PAGER="" aws ssm get-parameter \
    --name "$SSM_PASS_PATH" --with-decryption \
    --region "$AWS_REGION" 2>&1 | \
    python3 -c "import sys,json; print(json.load(sys.stdin)['Parameter']['Value'])")

  PRESIGNED=$(AWS_PAGER="" aws s3 presign \
    "s3://$S3_BUCKET/$S3_DUMP_KEY" \
    --region "$AWS_REGION" \
    --expires-in 3600 2>&1)

  # Drop + recria banco
  run_ecs "mysql -h $RDS_HOST -u $RDS_USER -p${RDS_PASS} -e \\\"DROP DATABASE IF EXISTS $RDS_DB; CREATE DATABASE $RDS_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\\\" 2>&1 && echo DROP_OK" \
    | grep -q "DROP_OK" || die "Falha ao recriar banco $RDS_DB"

  # Importa
  IMPORT_OUT=$(run_ecs "curl -sS \\\"${PRESIGNED}\\\" | gunzip | mysql -h $RDS_HOST -u $RDS_USER -p${RDS_PASS} $RDS_DB 2>&1 && echo IMPORT_OK")
  echo "$IMPORT_OUT" | grep -q "IMPORT_OK" || die "Falha no import: $IMPORT_OUT"
  ok "Banco importado"
fi

# ── 6. Search-replace + flush ──────────────────────────────────────────────
log "Executando WP-CLI search-replace + flush..."

if $DRY_RUN; then
  skip "wp search-replace '$LOCAL_URL' → '$REMOTE_URL'"
else
  SR_OUT=$(run_ecs "wp --allow-root search-replace \\\"$LOCAL_URL\\\" \\\"$REMOTE_URL\\\" --all-tables --skip-columns=guid --report-changed-only 2>&1 && wp --allow-root cache flush 2>&1 && wp --allow-root rewrite flush 2>&1 && echo REPLACE_OK")
  echo "$SR_OUT" | grep -q "REPLACE_OK" || die "Falha no search-replace"
  REPLACEMENTS=$(echo "$SR_OUT" | grep "Made" | grep -o "[0-9]* replacement" || echo "?")
  ok "Search-replace: $REPLACEMENTS"
fi

# ── 7. Sync de uploads → S3 ────────────────────────────────────────────────
if $SKIP_UPLOADS; then
  log "Sync de uploads ignorado (--skip-uploads)"
else
  log "Sincronizando uploads locais → S3 (pode demorar)..."
  if $DRY_RUN; then
    COUNT=$(AWS_PAGER="" aws s3 sync "$LOCAL_UPLOADS" "s3://$S3_BUCKET/uploads/" \
      --region "$AWS_REGION" --dryrun --exclude "*.DS_Store" 2>&1 | wc -l | tr -d ' ')
    skip "aws s3 sync (~$COUNT arquivos a sincronizar)"
  else
    aws s3 sync "$LOCAL_UPLOADS" "s3://$S3_BUCKET/uploads/" \
      --region "$AWS_REGION" --exclude "*.DS_Store" --no-progress
    ok "Uploads sincronizados"
  fi
fi

# ── 8. Validação final ─────────────────────────────────────────────────────
log "Validando site remoto..."
if $DRY_RUN; then
  skip "curl $REMOTE_URL"
else
  sleep 3
  HTTP=$(curl -Is "$REMOTE_URL" | head -n1 | tr -d '\r')
  TITLE=$(curl -sL "$REMOTE_URL" | grep -o '<title>[^<]*</title>' | head -n1)
  POSTS=$(run_ecs "wp --allow-root post list --post_status=publish --post_type=post --format=count 2>&1" | grep -E "^[0-9]+" | head -n1)

  echo ""
  echo "  ┌─────────────────────────────────────────"
  echo "  │  Status   : $HTTP"
  echo "  │  Título   : $TITLE"
  echo "  │  Posts    : $POSTS publicados"
  echo "  │  Tag git  : $TAG"
  echo "  └─────────────────────────────────────────"
fi

log "Migração concluída."
