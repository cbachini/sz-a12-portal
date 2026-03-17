# Portal A12 — WordPress Image
# Base: WordPress oficial com PHP 8.2 + Apache
# Inclui: wp-cli, mu-plugins, configuração PHP customizada
#
# Conteúdo da imagem (conforme arquitetura):
#   - WordPress core
#   - plugins aprovados (instalados em build de produção)
#   - tema customizado (copiado em build de produção)
#   - mu-plugins
#   - configuração PHP
#   - wp-cli
#
# NÃO incluído: uploads, banco de dados, arquivos gerados em runtime

FROM wordpress:6.7-php8.2-apache

LABEL maintainer="Soyuz Digital Studio"
LABEL project="Portal A12"

# ---------------------------------------------------------------
# Dependências do sistema
# ---------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    less \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------
# WP-CLI
# ---------------------------------------------------------------
RUN curl -sS -o /usr/local/bin/wp \
    https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp \
    && wp --info --allow-root

# ---------------------------------------------------------------
# Configuração PHP customizada
# ---------------------------------------------------------------
COPY config/php.ini /usr/local/etc/php/conf.d/a12-custom.ini

# ---------------------------------------------------------------
# Must-use plugins (carregados automaticamente pelo WordPress)
# ---------------------------------------------------------------
COPY wp-content/mu-plugins/ /var/www/html/wp-content/mu-plugins/

# ---------------------------------------------------------------
# Tema e plugins (copiados em imagens de DEV / STAGE / PROD)
# Em LOCAL, são montados via volume no docker-compose.yml
# ---------------------------------------------------------------
# COPY wp-content/themes/  /var/www/html/wp-content/themes/
# COPY wp-content/plugins/ /var/www/html/wp-content/plugins/

# ---------------------------------------------------------------
# Permissões
# ---------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html/wp-content

EXPOSE 80
