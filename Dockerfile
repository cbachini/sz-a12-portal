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
# Idioma: pt-BR
# WPLANG é definido em docker-compose.yml (WORDPRESS_CONFIG_EXTRA)
# Após primeiro boot: wp language core install pt_BR --activate --allow-root
# ---------------------------------------------------------------

# ---------------------------------------------------------------
# Permissões
# ---------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html/wp-content \
    && chown -R www-data:www-data /var/www/html/vendor

EXPOSE 80 443
