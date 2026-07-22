#!/usr/bin/env bash
set -euo pipefail

log() {
  echo "[a12-efs-seed] $*"
}

WP_PATH="/var/www/html"

# wp-content/upgrade-temp-backup: usado pelo WP >= 6.3 para guardar uma copia
# da versao antiga de um plugin/tema ANTES de atualizar (permite rollback se
# a atualizacao falhar). Fica no disco local efemero do container — NAO faz
# parte de nenhum access point EFS — entao normalmente nem existe ate a
# primeira atualizacao real. Se essa pasta for criada por um processo rodando
# como root (ex.: wp-cli --allow-root usado em diagnostico manual via
# `ecs execute-command`), ela fica root:root; qualquer atualizacao real
# seguinte (rodando como www-data via Apache/mod_php) falha ao tentar
# escrever ali, com o erro "Nao foi possivel mover a versao antiga para o
# diretorio upgrade-temp-backup" — e o plugin fica CORROMPIDO no meio do
# processo (incidente real: elementor-pro, 2026-07-22, ver
# /memories/repo/a12-bugs-resolved.md). Fix: garantir dono correto em TODO
# boot, ANTES do early-return abaixo — roda mesmo fora do fluxo de seed do
# EFS (DEV) porque e local/barato e protege qualquer ambiente que venha a
# permitir atualizacoes de plugin no futuro.
mkdir -p "${WP_PATH}/wp-content/upgrade-temp-backup"
chown -R www-data:www-data "${WP_PATH}/wp-content/upgrade-temp-backup"

# Rotina apenas para tasks ECS com EFS montado (DEV).
# Ativar via env var A12_EFS_SEED=1 na task definition.
if [[ "${A12_EFS_SEED:-0}" != "1" ]]; then
  log "A12_EFS_SEED != 1; seed desativado. Seguindo boot padrão."
  exec docker-entrypoint.sh apache2-foreground
fi

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
