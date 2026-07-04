<?php
/**
 * Must-Use Plugin: A12 S3 Uploads Configuration
 *
 * Configura e carrega o plugin humanmade/s3-uploads (instalado via Composer)
 * usando variáveis de ambiente do container ECS.
 *
 * NÃO ativa em ambiente local (A12_ENV=local) — uploads ficam em volume local.
 *
 * Variáveis de ambiente necessárias no container:
 *   S3_UPLOADS_BUCKET      — nome do bucket (ex: a12-dev-uploads)
 *   S3_UPLOADS_REGION      — região AWS (padrão: sa-east-1)
 *   S3_UPLOADS_BUCKET_URL  — URL pública (opcional, ex: CloudFront URL)
 *
 * @package A12
 */

// Previne acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Em ambiente local, s3-uploads não é usado — uploads ficam em volume Docker
$a12_env = getenv( 'A12_ENV' ) ?: 'local';
if ( 'local' === $a12_env ) {
    return;
}

// Carrega o Composer autoloader (necessário para o AWS SDK usado pelo s3-uploads).
// Feito aqui para garantir disponibilidade independente do WORDPRESS_CONFIG_EXTRA.
$a12_autoload = ABSPATH . 'vendor/autoload.php';
if ( file_exists( $a12_autoload ) && ! class_exists( 'Aws\S3\S3Client', false ) ) {
    require_once $a12_autoload;
}
unset( $a12_autoload );

// Verificar se bucket foi configurado
$s3_bucket = getenv( 'S3_UPLOADS_BUCKET' );
if ( empty( $s3_bucket ) ) {
    error_log( '[A12] S3_UPLOADS_BUCKET não definido. Uploads serão locais (pode causar problemas em Fargate sem EFS).' );
    return;
}

// Definir constantes ANTES de carregar o plugin
define( 'S3_UPLOADS_BUCKET', $s3_bucket );
define( 'S3_UPLOADS_REGION', getenv( 'S3_UPLOADS_REGION' ) ?: 'sa-east-1' );

// Em ECS Fargate com IAM Task Role: não precisa de KEY/SECRET.
// As credenciais são obtidas automaticamente pelo AWS SDK via
// o metadata endpoint do container (http://169.254.170.2).
define( 'S3_UPLOADS_USE_INSTANCE_PROFILE', true );

// URL pública para servir os arquivos (S3 direto, CloudFront, etc.)
$s3_bucket_url = getenv( 'S3_UPLOADS_BUCKET_URL' );
if ( ! empty( $s3_bucket_url ) ) {
    define( 'S3_UPLOADS_BUCKET_URL', rtrim( $s3_bucket_url, '/' ) );
}

// Cache-Control para assets de mídia (30 dias)
define( 'S3_UPLOADS_HTTP_CACHE_CONTROL', 30 * 24 * 60 * 60 );

// Carregar o plugin s3-uploads APÓS todas as constantes estarem definidas.
// O plugin foi instalado via Composer em wp-content/plugins/s3-uploads/
$s3_plugin_file = WP_PLUGIN_DIR . '/s3-uploads/s3-uploads.php';

if ( file_exists( $s3_plugin_file ) ) {
    require_once $s3_plugin_file;

    // O bucket tem Object Ownership = BucketOwnerEnforced (ACLs desabilitados).
    // Remove o parâmetro ACL de todas as requisições PutObject para evitar
    // o erro "AccessControlListNotSupported".
    add_filter( 's3_uploads_putObject_params', function ( array $params ) : array {
        unset( $params['ACL'] );
        return $params;
    } );
} else {
    error_log( '[A12] Plugin s3-uploads não encontrado em: ' . $s3_plugin_file . '. Execute: composer install' );
}
