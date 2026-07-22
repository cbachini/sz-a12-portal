# Portal A12 — WordPress Image
# Base: WordPress oficial com PHP 8.4 + Apache
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

FROM wordpress:php8.4-apache

LABEL maintainer="Soyuz Digital Studio"
LABEL project="Portal A12"

# ---------------------------------------------------------------
# Dependências do sistema
# ---------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    less \
    default-mysql-client \
    openssl \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------
# Extensões PHP: Redis + APCu
# ---------------------------------------------------------------
RUN pecl install redis apcu \
    && docker-php-ext-enable redis apcu \
    && rm -rf /tmp/pear

# ---------------------------------------------------------------
# OPcache (já incluído no PHP 8.4 — só precisa de ini)
# ---------------------------------------------------------------
COPY config/php-opcache.ini /usr/local/etc/php/conf.d/a12-opcache.ini

# ---------------------------------------------------------------
# Módulos Apache: SSL, compressão, headers, expires
# ---------------------------------------------------------------
RUN a2enmod ssl headers expires deflate

# ---------------------------------------------------------------
# SSL local — certificado auto-assinado (válido 10 anos)
# ---------------------------------------------------------------
RUN mkdir -p /etc/apache2/ssl \
    && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
       -keyout /etc/apache2/ssl/a12-local.key \
       -out    /etc/apache2/ssl/a12-local.crt \
       -subj  "/C=BR/ST=SP/O=A12 Local Dev/CN=wordpress.sz-a12-portal.orb.local" \
       -addext "subjectAltName=DNS:wordpress.sz-a12-portal.orb.local,DNS:localhost,IP:127.0.0.1"

# Redis object-cache drop-in: desabilitado até Redis (ElastiCache) ser provisionado no ECS.
# Para reativar: descomentar o bloco abaixo e definir WP_REDIS_HOST na task definition.
# RUN curl -sS -o /usr/src/wordpress/wp-content/object-cache.php \
#     "https://raw.githubusercontent.com/rhubarbgroup/redis-cache/develop/includes/object-cache.php"

# ---------------------------------------------------------------
# Apache: VirtualHost SSL + MPM afinado
# ---------------------------------------------------------------
COPY config/apache-ssl.conf /etc/apache2/sites-enabled/a12-ssl.conf

# ---------------------------------------------------------------
# WP-CLI
# ---------------------------------------------------------------
RUN curl -sS -o /usr/local/bin/wp \
    https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp \
    && wp --info --allow-root

# Script de auto-heal para ambiente local/dev
COPY scripts/local-wp-autoheal.sh /usr/local/bin/local-wp-autoheal.sh
RUN chmod +x /usr/local/bin/local-wp-autoheal.sh

# ---------------------------------------------------------------
# Configuração PHP customizada
# ---------------------------------------------------------------
COPY config/php.ini /usr/local/etc/php/conf.d/a12-custom.ini

# ---------------------------------------------------------------
# Configuração Apache customizada
# ---------------------------------------------------------------
COPY config/apache.conf /etc/apache2/conf-enabled/a12-custom.conf

# ---------------------------------------------------------------
# .htaccess (permalink structure + proxy de uploads local)
# ---------------------------------------------------------------
COPY .htaccess /var/www/html/.htaccess
COPY health.php /var/www/html/health.php

# ---------------------------------------------------------------
# Composer (binário copiado da imagem oficial composer:2)
# ---------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# ---------------------------------------------------------------
# Dependências PHP via Composer
# Instala humanmade/s3-uploads e suas dependências (AWS SDK v3)
# O vendor/ fica em /var/www/html/vendor/
# O s3-uploads é instalado em wp-content/plugins/s3-uploads/
# ---------------------------------------------------------------
COPY composer.json composer.lock* /var/www/html/
RUN cd /var/www/html && \
    composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress

# ---------------------------------------------------------------
# Must-use plugins (carregados automaticamente pelo WordPress)
# ---------------------------------------------------------------
COPY wp-content/mu-plugins/ /var/www/html/wp-content/mu-plugins/

# ---------------------------------------------------------------
# Premium plugins — não disponíveis no WPackagist (requerem licença)
# Elementor Pro: baixar em elementor.com/my-account e extrair aqui
# Os plugins gratuitos são instalados pelo Composer acima:
#   elementor, advanced-custom-fields, polylang, wordpress-seo
# ---------------------------------------------------------------
COPY wp-content/plugins/elementor-pro/ /var/www/html/wp-content/plugins/elementor-pro/

# ---------------------------------------------------------------
# Tema customizado A12 (código proprietário)
# hello-elementor é instalado pelo Composer acima
# ---------------------------------------------------------------
COPY wp-content/themes/a12-theme/ /var/www/html/wp-content/themes/a12-theme/

# ---------------------------------------------------------------
# Idioma pt-BR — arquivos baked na imagem
# Lê a versão exata do WP instalado e baixa a tradução correspondente.
# Sem isso, o WP reverte para inglês a cada novo container.
# ---------------------------------------------------------------
RUN WP_VER=$(grep -oP "(?<=\\\$wp_version = ')[\d.]+" /usr/src/wordpress/wp-includes/version.php) \
    && echo "WP version: ${WP_VER}" \
    && mkdir -p /usr/src/wordpress/wp-content/languages \
    && curl -fsSL \
       "https://downloads.wordpress.org/translation/core/${WP_VER}/pt_BR.zip" \
       -o /tmp/pt_BR_core.zip \
    && unzip -q /tmp/pt_BR_core.zip -d /usr/src/wordpress/wp-content/languages/ \
    && rm /tmp/pt_BR_core.zip \
    && echo "pt_BR language files baked for WP ${WP_VER}"

# ---------------------------------------------------------------
# Traducoes pt-BR dos plugins — baked na imagem (NAO fica em EFS)
# Motivo: wp-content/languages e lido via load_textdomain() em TODA
# requisicao (core + cada plugin ativo + tema) e nao passa pelo OPcache
# (e dado, nao bytecode PHP) — diferente de wp-content/plugins/*.php,
# que e compilado uma vez e cacheado em memoria. Montar languages em
# EFS/NFS gera latencia recorrente por request (~800-950ms medidos em
# 2026-07-22). Ver /memories/repo/a12-bugs-resolved.md.
# Resolve versao real de cada plugin instalado (header do arquivo
# principal) e baixa a traducao da API oficial do WordPress.org,
# tolerando plugins sem traducao publica (ex.: s3-uploads, elementor-pro).
# ---------------------------------------------------------------
RUN mkdir -p /usr/src/wordpress/wp-content/languages/plugins \
    && for plugin_dir in /var/www/html/wp-content/plugins/*/; do \
         slug=$(basename "$plugin_dir"); \
         main_file=$(grep -rl "^[[:space:]]*\*[[:space:]]*Version:" "$plugin_dir" --include="*.php" -m1 2>/dev/null | head -n1); \
         if [ -z "$main_file" ]; then \
           echo "SKIP ${slug} (sem header de versao encontrado)"; \
           continue; \
         fi; \
         ver=$(grep -oP "(?<=Version:)[[:space:]]*[0-9][0-9.]*" "$main_file" | head -n1 | tr -d '[:space:]'); \
         if [ -z "$ver" ]; then \
           echo "SKIP ${slug} (versao nao identificada)"; \
           continue; \
         fi; \
         echo "Tentando traducao pt_BR: ${slug} ${ver}"; \
         curl -fsSL "https://downloads.wordpress.org/translation/plugin/${slug}/${ver}/pt_BR.zip" -o "/tmp/${slug}-ptbr.zip" \
           && unzip -q -o "/tmp/${slug}-ptbr.zip" -d /usr/src/wordpress/wp-content/languages/plugins/ \
           && rm -f "/tmp/${slug}-ptbr.zip" \
           && echo "OK: ${slug} ${ver}" \
           || echo "SEM TRADUCAO disponivel (ignorado): ${slug} ${ver}"; \
       done

# ---------------------------------------------------------------
# Snapshot dos plugins baked (usado para seed do EFS em AWS/ECS)
# Necessário porque montar EFS em wp-content/plugins sobrepõe (overlay)
# o que o Composer instalou no build. O script aws-efs-seed.sh usa esta
# cópia para popular o EFS somente quando ele estiver vazio.
# ---------------------------------------------------------------
RUN mkdir -p /opt/a12-baked/wp-content/plugins \
    && cp -a /var/www/html/wp-content/plugins/. /opt/a12-baked/wp-content/plugins/

# ---------------------------------------------------------------
# Script de seed do EFS para ambiente AWS/ECS (DEV)
# Ativado apenas quando A12_EFS_SEED=1 na task definition.
# ---------------------------------------------------------------
COPY scripts/aws-efs-seed.sh /usr/local/bin/aws-efs-seed.sh
RUN chmod +x /usr/local/bin/aws-efs-seed.sh

# ---------------------------------------------------------------
# Permissões
# ---------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html/wp-content \
    && chown -R www-data:www-data /var/www/html/vendor

EXPOSE 80 443
