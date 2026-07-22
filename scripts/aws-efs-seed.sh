#!/usr/bin/env bash
set -euo pipefail

log() {
  echo "[a12-efs-seed] $*"
}

# Rotina apenas para tasks ECS com EFS montado (DEV).
# Ativar via env var A12_EFS_SEED=1 na task definition.
if [[ "${A12_EFS_SEED:-0}" != "1" ]]; then
  log "A12_EFS_SEED != 1; seed desativado. Seguindo boot padrão."
  exec docker-entrypoint.sh apache2-foreground
fi

WP_PATH="/var/www/html"
BAKED_PLUGINS="/opt/a12-baked/wp-content/plugins"

is_empty_dir() {
  local dir="$1"
  [[ -d "$dir" ]] && [[ -z "$(ls -A "$dir" 2>/dev/null)" ]]
}

# wp-content/plugins — seed a partir do snapshot baked no build.
# Sem isso, a primeira montagem de EFS vazio apaga os plugins do Composer.
# IMPORTANTE: chown -R SO roda no seed inicial (EFS vazio). Em boots
# seguintes o conteudo ja pertence a www-data (chown feito no seed anterior);
# repetir chown -R sobre milhares de arquivos de plugin via NFS a cada boot
# adicionava dezenas de segundos/minutos ao startup (medido 2026-07-22,
# ver /memories/repo/a12-bugs-resolved.md) — provavel causa raiz principal
# do estouro de health check, mais relevante que a latencia de languages.
if is_empty_dir "${WP_PATH}/wp-content/plugins"; then
  log "wp-content/plugins vazio (EFS novo). Semeando com conteudo baked..."
  cp -a "${BAKED_PLUGINS}/." "${WP_PATH}/wp-content/plugins/"
  chown -R www-data:www-data "${WP_PATH}/wp-content/plugins"
else
  log "wp-content/plugins ja possui conteudo; nenhum seed/chown necessario."
fi

# wp-content/languages NAO fica mais em EFS (ver Dockerfile) — e baked na
# imagem em /usr/src/wordpress/wp-content/languages e copiado localmente
# pelo docker-entrypoint.sh padrao a cada novo container (ephemeral, rapido,
# sem NFS). Nenhum seed necessario aqui.

# wp-content/upgrade — staging temporario do updater do WP, so precisa existir.
# wp-content/themes-test — diretorio extra de temas em teste (nao sobrepoe
# wp-content/themes, onde o a12-theme fica baked). Registrado via
# register_theme_directory() no mu-plugin a12-themes-test-dir.php.
# Ambos ficam praticamente vazios/pequenos — chown aqui e barato mesmo via NFS.
mkdir -p "${WP_PATH}/wp-content/upgrade" "${WP_PATH}/wp-content/themes-test"
chown -R www-data:www-data \
  "${WP_PATH}/wp-content/upgrade" \
  "${WP_PATH}/wp-content/themes-test"

log "Seed do EFS concluido. Iniciando WordPress..."
exec docker-entrypoint.sh apache2-foreground
