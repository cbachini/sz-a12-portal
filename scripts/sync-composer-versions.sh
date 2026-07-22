#!/usr/bin/env bash
set -euo pipefail

# ---------------------------------------------------------------------------
# sync-composer-versions.sh
#
# Ajuda a "promover" para o composer.json as versoes de plugins/tema que
# foram atualizadas manualmente (ou por auto-update) pelo wp-admin em DEV.
#
# Contexto: em DEV, DISALLOW_FILE_MODS e AUTOMATIC_UPDATER_DISABLED ficam
# liberados (ver wp-content/mu-plugins/a12-env-config.php), porque
# wp-content/plugins fica em EFS persistente naquele ambiente. Isso permite
# testar a nova versao de um plugin direto no wp-admin antes de comprometer
# a mudanca no composer.json (fonte da verdade para staging/producao).
#
# Este script NAO edita nada sozinho — ele so lista o que esta rodando em
# DEV agora, lado a lado com o que o composer.json declara, para revisao
# humana. Depois de revisar:
#   1) Ajuste a constraint da versao em composer.json.
#   2) composer update <slug>
#   3) docker build + push da nova imagem.
#   4) Registre nova task-definition e faca deploy (staging/producao).
#
# Requer: AWS CLI configurado (AWS_PROFILE=a12-dev), Session Manager plugin
# instalado, e uma task RUNNING do a12-portal-dev-service com
# --enable-execute-command (o servico ja registra as tasks assim, ver
# `enableExecuteCommand` na task-definition).
# ---------------------------------------------------------------------------

CLUSTER="a12-dev-cluster"
SERVICE="a12-portal-dev-service"

echo "==> Localizando task em execução no serviço ${SERVICE}..."
TASK_ARN=$(aws ecs list-tasks --cluster "$CLUSTER" --service-name "$SERVICE" \
  --desired-status RUNNING --query 'taskArns[0]' --output text)

if [[ -z "$TASK_ARN" || "$TASK_ARN" == "None" ]]; then
  echo "Nenhuma task RUNNING encontrada em ${SERVICE}." >&2
  exit 1
fi

TASK_ID="${TASK_ARN##*/}"
echo "==> Task: ${TASK_ID}"

EXEC_ENABLED=$(aws ecs describe-tasks --cluster "$CLUSTER" --tasks "$TASK_ID" \
  --query 'tasks[0].enableExecuteCommand' --output text)
if [[ "$EXEC_ENABLED" != "True" ]]; then
  echo "Essa task não foi iniciada com --enable-execute-command." >&2
  exit 1
fi

echo ""
echo "=== Plugins/tema instalados AGORA em DEV (wp-cli) ==="
aws ecs execute-command --cluster "$CLUSTER" --task "$TASK_ID" \
  --container a12-portal --interactive \
  --command "wp plugin list --allow-root" 2>/dev/null || true

echo ""
echo "=== Constraints declaradas em composer.json ==="
python3 - <<'PYEOF'
import json

with open('composer.json') as f:
    composer = json.load(f)

for pkg, constraint in composer.get('require', {}).items():
    if pkg.startswith('wpackagist-plugin/') or pkg.startswith('wpackagist-theme/'):
        slug = pkg.split('/', 1)[1]
        print(f"  {slug:35s} {constraint}")
PYEOF

cat <<'EOF'

------------------------------------------------------------------------------
Compare as versões acima. Para promover uma versão validada em DEV para
staging/produção:
  1) Ajuste a constraint em composer.json (ex.: "^22.9" ou versão exata).
  2) composer update <slug>
  3) docker build --platform linux/amd64 -t <repo>:<nova-tag> .
  4) docker push <repo>:<nova-tag>
  5) Registre nova task-definition (image = <nova-tag>) e faça deploy.

Elementor Pro (premium, não vem via wpackagist) e outros plugins fora do
composer.json precisam de atualização manual do zip fonte no repositório,
fora do escopo deste script.
------------------------------------------------------------------------------
EOF
