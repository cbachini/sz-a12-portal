#!/usr/bin/env bash
# scripts/delta.sh — Relatório de diferenças entre Local e AWS DEV
#
# Compara:
#   - Código     : git status, branch, commits não enviados
#   - Banco      : contagens por post_type/status, opções críticas, plugins ativos
#   - Mídia      : arquivos somente no S3, somente local, tamanho total
#
# NÃO modifica nada. Apenas lê e exibe.
#
# Uso:
#   ./scripts/delta.sh [--no-media]

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

NO_MEDIA=false
ERRORS=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --no-media) NO_MEDIA=true; shift ;;
    *) echo "Uso: $0 [--no-media]"; exit 1 ;;
  esac
done

# ── Helpers ────────────────────────────────────────────────────────────────
section() { echo ""; echo "══════════════════════════════════════════════════"; echo "  $*"; echo "══════════════════════════════════════════════════"; }
row()     { printf "  %-35s %s\n" "$1" "$2"; }
diff_row(){ printf "  %-35s %-20s %s\n" "$1" "$2" "$3"; }
warn()    { echo "  ⚠  $*"; ERRORS=$((ERRORS+1)); }
ok()      { echo "  ✓  $*"; }

export AWS_PROFILE

run_local_wp() {
  docker exec "$LOCAL_CONTAINER_WP" wp --allow-root "$@" 2>/dev/null || echo "ERRO"
}

run_local_mysql() {
  docker exec "$LOCAL_CONTAINER_DB" mysql \
    -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" \
    -se "$1" 2>/dev/null || echo "ERRO"
}

run_ecs() {
  local cmd="$1"
  local task_id
  task_id=$(AWS_PAGER="" aws ecs list-tasks \
    --cluster "$ECS_CLUSTER" --service-name "$ECS_SERVICE" \
    --region "$AWS_REGION" 2>&1 | \
    python3 -c "import sys,json; t=json.load(sys.stdin)['taskArns']; print(t[0].split('/')[-1]) if t else exit(1)" 2>/dev/null) || { echo "ERRO_TASK"; return; }

  AWS_PAGER="" aws ecs execute-command \
    --cluster "$ECS_CLUSTER" \
    --task "$task_id" \
    --container "$CONTAINER_NAME" \
    --interactive \
    --region "$AWS_REGION" \
    --command "bash -c '$cmd'" 2>&1 | \
    grep -v "^The Session Manager\|^Starting session\|^Exiting session\|^\s*$" || true
}

echo ""
echo "  ╔══════════════════════════════════════════╗"
echo "  ║        DELTA: LOCAL ↔ AWS DEV           ║"
echo "  ║  $(date '+%Y-%m-%d %H:%M')                      ║"
echo "  ╚══════════════════════════════════════════╝"

# ══════════════════════════════════════════════════
section "1. CÓDIGO (git)"
# ══════════════════════════════════════════════════

cd "$(dirname "$0")/.."

BRANCH=$(git branch --show-current 2>/dev/null || echo "?")
HEAD=$(git rev-parse --short HEAD 2>/dev/null || echo "?")
REMOTE_HEAD=$(git rev-parse --short origin/"$BRANCH" 2>/dev/null || echo "?")
AHEAD=$(git rev-list --count origin/"$BRANCH"..HEAD 2>/dev/null || echo "?")
BEHIND=$(git rev-list --count HEAD..origin/"$BRANCH" 2>/dev/null || echo "?")
DIRTY=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')

row "Branch local" "$BRANCH"
row "HEAD local" "$HEAD"
row "HEAD remoto (origin)" "$REMOTE_HEAD"
row "Commits à frente (local)" "$AHEAD"
row "Commits atrás (origin)" "$BEHIND"
row "Arquivos não commitados" "$DIRTY"

[[ "$AHEAD" != "0" ]] && warn "$AHEAD commit(s) local não enviado(s) ao GitHub"
[[ "$BEHIND" != "0" ]] && warn "origin/$BRANCH tem $BEHIND commit(s) que você não tem localmente"
[[ "$DIRTY" != "0" ]] && warn "$DIRTY arquivo(s) modificado(s) sem commit"

# ECR vs local
ECR_DIGEST=$(AWS_PAGER="" aws ecr describe-images \
  --repository-name a12-portal-dev \
  --image-ids imageTag=latest \
  --region "$AWS_REGION" 2>&1 | \
  python3 -c "import sys,json; imgs=json.load(sys.stdin).get('imageDetails',[]); print(imgs[0]['imagePushedAt'][:10] + ' ' + imgs[0]['imageDigest'][:20]) if imgs else print('não encontrado')" 2>/dev/null || echo "ERRO")
row "Imagem ECR :latest" "$ECR_DIGEST"

# ══════════════════════════════════════════════════
section "2. BANCO DE DADOS"
# ══════════════════════════════════════════════════

printf "  %-35s %-20s %-20s\n" "Métrica" "LOCAL" "AWS DEV"
printf "  %-35s %-20s %-20s\n" "-------" "-----" "-------"

# Contagens locais
L_POSTS=$(run_local_mysql "SELECT COUNT(*) FROM wp_posts WHERE post_type='post' AND post_status='publish'")
L_PAGES=$(run_local_mysql "SELECT COUNT(*) FROM wp_posts WHERE post_type='page' AND post_status='publish'")
L_MEDIA=$(run_local_mysql "SELECT COUNT(*) FROM wp_posts WHERE post_type='attachment'")
L_USERS=$(run_local_mysql "SELECT COUNT(*) FROM wp_users")
L_SITEURL=$(run_local_mysql "SELECT option_value FROM wp_options WHERE option_name='siteurl'")
L_WP_VER=$(run_local_mysql "SELECT option_value FROM wp_options WHERE option_name='db_version'")
L_LAST_POST=$(run_local_mysql "SELECT MAX(post_modified) FROM wp_posts WHERE post_type='post' AND post_status='publish'" | head -n1)
L_PLUGINS=$(run_local_wp plugin list --status=active --field=name | tr '\n' ',' | sed 's/,$//')

# Contagens AWS
A_POSTS=$(run_ecs "wp --allow-root post list --post_type=post --post_status=publish --format=count 2>/dev/null" | grep -E "^[0-9]+" | head -n1 || echo "ERRO")
A_PAGES=$(run_ecs "wp --allow-root post list --post_type=page --post_status=publish --format=count 2>/dev/null" | grep -E "^[0-9]+" | head -n1 || echo "ERRO")
A_MEDIA=$(run_ecs "wp --allow-root post list --post_type=attachment --format=count 2>/dev/null" | grep -E "^[0-9]+" | head -n1 || echo "ERRO")
A_USERS=$(run_ecs "wp --allow-root user list --format=count 2>/dev/null" | grep -E "^[0-9]+" | head -n1 || echo "ERRO")
A_SITEURL=$(run_ecs "wp --allow-root option get siteurl 2>/dev/null" | grep "http" | head -n1 || echo "ERRO")
A_LAST_POST=$(run_ecs "mysql -h $RDS_HOST -u $RDS_USER -p\$(aws ssm get-parameter --name $SSM_PASS_PATH --with-decryption --region $AWS_REGION 2>&1 | python3 -c \\\"import sys,json; print(json.load(sys.stdin)['Parameter']['Value'])\\\") $RDS_DB -se \\\"SELECT MAX(post_modified) FROM wp_posts WHERE post_type='post' AND post_status='publish'\\\" 2>/dev/null" | grep -E "^[0-9]{4}" | head -n1 || echo "ERRO")
A_PLUGINS=$(run_ecs "wp --allow-root plugin list --status=active --field=name 2>/dev/null" | tr '\n' ',' | sed 's/,$//' || echo "ERRO")

diff_row "Posts publicados" "$L_POSTS" "$A_POSTS"
diff_row "Páginas publicadas" "$L_PAGES" "$A_PAGES"
diff_row "Attachments (mídia)" "$L_MEDIA" "$A_MEDIA"
diff_row "Usuários" "$L_USERS" "$A_USERS"
diff_row "Último post modificado" "${L_LAST_POST:0:19}" "${A_LAST_POST:0:19}"
diff_row "DB version (WP)" "$L_WP_VER" "(via ECS)"

echo ""
row "URL local" "$L_SITEURL"
row "URL AWS" "$A_SITEURL"

# Verifica divergências críticas
[[ "$L_POSTS" != "$A_POSTS" ]] && warn "Posts divergem: local=$L_POSTS | AWS=$A_POSTS"
[[ "$L_USERS" != "$A_USERS" ]] && warn "Usuários divergem: local=$L_USERS | AWS=$A_USERS"

# Diff de plugins
echo ""
echo "  Plugins ativos:"
row "  LOCAL" "$L_PLUGINS"
row "  AWS  " "$A_PLUGINS"

# Opções críticas
section "3. OPÇÕES WORDPRESS (críticas)"
printf "  %-35s %-25s %-25s\n" "Opção" "LOCAL" "AWS DEV"
printf "  %-35s %-25s %-25s\n" "-----" "-----" "-------"

check_option() {
  local opt="$1"
  local l_val a_val
  l_val=$(run_local_mysql "SELECT option_value FROM wp_options WHERE option_name='$opt'" | head -n1)
  a_val=$(run_ecs "wp --allow-root option get $opt 2>/dev/null" | grep -v "^$" | head -n1 || echo "ERRO")
  local flag=""
  [[ "$l_val" != "$a_val" ]] && { flag=" ◀ DIFERENTE"; ERRORS=$((ERRORS+1)); }
  printf "  %-35s %-25s %-25s%s\n" "$opt" "${l_val:0:24}" "${a_val:0:24}" "$flag"
}

check_option "blogname"
check_option "blogdescription"
check_option "template"
check_option "stylesheet"

# ══════════════════════════════════════════════════
if $NO_MEDIA; then
  section "4. MÍDIA — ignorada (--no-media)"
else
  section "4. MÍDIA (uploads)"
  # ══════════════════════════════════════════════════

  L_COUNT=$(find "$LOCAL_UPLOADS" -type f ! -name '.DS_Store' 2>/dev/null | wc -l | tr -d ' ')
  L_SIZE=$(du -sh "$LOCAL_UPLOADS" 2>/dev/null | cut -f1 || echo "?")

  A_COUNT=$(AWS_PAGER="" aws s3 ls "s3://$S3_BUCKET/uploads/" \
    --recursive --region "$AWS_REGION" 2>/dev/null | wc -l | tr -d ' ' || echo "ERRO")
  A_SIZE=$(AWS_PAGER="" aws s3 ls "s3://$S3_BUCKET/uploads/" \
    --recursive --summarize --region "$AWS_REGION" 2>/dev/null | \
    grep "Total Size" | awk '{print $3}' | \
    python3 -c "import sys; b=int(sys.stdin.read().strip() or 0); print(f'{b/1024/1024/1024:.1f}G')" 2>/dev/null || echo "ERRO")

  diff_row "Arquivos" "$L_COUNT" "$A_COUNT"
  diff_row "Tamanho total" "$L_SIZE" "$A_SIZE"

  ONLY_LOCAL=$(aws s3 sync "$LOCAL_UPLOADS" "s3://$S3_BUCKET/uploads/" \
    --region "$AWS_REGION" --dryrun --exclude "*.DS_Store" 2>/dev/null | \
    grep "^upload:" | wc -l | tr -d ' ' || echo "?")
  ONLY_S3=$(aws s3 sync "s3://$S3_BUCKET/uploads/" "$LOCAL_UPLOADS" \
    --region "$AWS_REGION" --dryrun --exclude "*.DS_Store" 2>/dev/null | \
    grep "^download:" | wc -l | tr -d ' ' || echo "?")

  echo ""
  row "  Apenas no local (faltam no S3)" "$ONLY_LOCAL arquivos"
  row "  Apenas no S3 (faltam no local)" "$ONLY_S3 arquivos"

  [[ "$ONLY_LOCAL" != "0" && "$ONLY_LOCAL" != "?" ]] && \
    warn "$ONLY_LOCAL arquivo(s) local não sincronizado(s) com S3"
  [[ "$ONLY_S3" != "0" && "$ONLY_S3" != "?" ]] && \
    warn "$ONLY_S3 arquivo(s) no S3 não presente(s) localmente"
fi

# ══════════════════════════════════════════════════
section "5. RESUMO E RECOMENDAÇÃO"
# ══════════════════════════════════════════════════

if [[ "$ERRORS" -eq 0 ]]; then
  ok "Ambientes sincronizados — nenhuma diferença crítica detectada."
else
  echo "  Detectadas $ERRORS diferença(s) marcadas com ⚠ acima."
  echo ""
  echo "  Ações sugeridas:"
  echo ""

  [[ "$AHEAD" != "0" ]] && \
    echo "  → $AHEAD commit(s) pendente(s): git push origin $BRANCH"

  [[ "$L_POSTS" != "$A_POSTS" && "$L_POSTS" -gt "${A_POSTS:-0}" ]] 2>/dev/null && \
    echo "  → Local tem mais posts: ./scripts/migrate-to-aws.sh --skip-uploads" || true

  [[ "$L_POSTS" != "$A_POSTS" && "${A_POSTS:-0}" -gt "$L_POSTS" ]] 2>/dev/null && \
    echo "  → AWS tem mais posts: ./scripts/pull-from-aws.sh --skip-uploads" || true

  [[ "$ONLY_LOCAL" != "0" && "$ONLY_LOCAL" != "?" ]] && \
    echo "  → Mídia faltando no S3: aws s3 sync ./wp-content/uploads/ s3://$S3_BUCKET/uploads/ --region $AWS_REGION --profile $AWS_PROFILE"

  [[ "$ONLY_S3" != "0" && "$ONLY_S3" != "?" ]] && \
    echo "  → Mídia faltando localmente: aws s3 sync s3://$S3_BUCKET/uploads/ ./wp-content/uploads/ --region $AWS_REGION --profile $AWS_PROFILE"
fi

echo ""
