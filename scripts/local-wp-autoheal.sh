#!/usr/bin/env bash
set -euo pipefail

log() {
  echo "[a12-autoheal] $*"
}

WP_PATH="/var/www/html"
PLUGIN_SLUG="smart-slider-3"
PLUGIN_MAIN="${PLUGIN_SLUG}/${PLUGIN_SLUG}.php"
PLUGIN_TRAIT="${WP_PATH}/wp-content/plugins/${PLUGIN_SLUG}/Nextend/Framework/Pattern/GetPathTrait.php"

# Rotina apenas para ambiente local/dev.
if [[ "${A12_ENV:-}" != "local" ]]; then
  log "A12_ENV=${A12_ENV:-unset}; auto-heal desativado."
  exec apache2-foreground
fi

# Aguarda WordPress/DB ficarem prontos para comandos WP-CLI.
READY=false
for _ in $(seq 1 30); do
  if wp --allow-root --path="$WP_PATH" --skip-plugins --skip-themes core is-installed >/dev/null 2>&1; then
    READY=true
    break
  fi
  sleep 2
done

if [[ "$READY" == "false" ]]; then
  log "WordPress ainda nao pronto; seguindo boot sem auto-heal."
  exec apache2-foreground
fi

# Se o plugin estiver ausente/corrompido, reinstala e reativa automaticamente.
if [[ ! -f "$PLUGIN_TRAIT" ]]; then
  log "${PLUGIN_SLUG} ausente ou corrompido (trait nao encontrada). Reinstalando..."

  wp --allow-root --path="$WP_PATH" --skip-plugins --skip-themes plugin deactivate "$PLUGIN_SLUG" >/dev/null 2>&1 || true
  wp --allow-root --path="$WP_PATH" --skip-plugins --skip-themes plugin install "$PLUGIN_SLUG" --force --activate >/dev/null

  if [[ -f "$PLUGIN_TRAIT" ]]; then
    log "${PLUGIN_SLUG} recuperado com sucesso."
  else
    log "Falha ao recuperar ${PLUGIN_SLUG}; continuar boot para diagnostico manual."
  fi
else
  log "${PLUGIN_SLUG} integro; nenhuma acao necessaria."
fi

exec apache2-foreground
