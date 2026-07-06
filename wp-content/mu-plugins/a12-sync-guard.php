<?php
/**
 * Plugin Name: A12 Sync Guard
 * Description: Detecta plugins ativos que não estão no container image e avisa o admin.
 *              Lista gerada automaticamente por build-push.sh — não editar manualmente.
 * Version: 1.0.0
 */

// ── Lista gerada por build-push.sh ─────────────────────────────────────────
// @generated — não editar manualmente. Regenerada em cada build da imagem.
define( 'A12_VERSIONED_PLUGINS', [
    // @generated por build-push.sh — não editar manualmente
    'advanced-custom-fields',
    'akismet',
    'elementor',
    'elementor-pro',
    'hello',
    'miniorange-wp-ldap-login',
    'pojo-accessibility',
    'polylang',
    'redirection',
    's3-uploads',
    'safe-svg',
    'wordpress-seo',
] );

// ── Aviso geral no admin ───────────────────────────────────────────────────
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $unversioned = a12_get_unversioned_plugins();
    if ( empty( $unversioned ) ) {
        return;
    }

    $items = implode( '</code>, <code>', array_map( 'esc_html', $unversioned ) );
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong>⚠ A12 Container — plugin(s) não versionado(s):</strong>
            <code><?php echo $items; ?></code>
        </p>
        <p>
            Esses plugins serão <strong>perdidos no próximo restart do container</strong>.<br>
            Para persistir, adicione ao <code>composer.json</code> (ou ao Dockerfile para plugins premium) e execute:
        </p>
        <pre style="background:#1e1e1e;color:#d4d4d4;padding:8px;margin:4px 0;border-radius:4px;font-size:12px">./scripts/build-push.sh --deploy</pre>
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=a12-sync-guard' ) ); ?>">Ver detalhes</a>
        </p>
    </div>
    <?php
} );

// ── Aviso imediato ao ativar plugin não versionado ─────────────────────────
add_action( 'activated_plugin', function ( $plugin_path ) {
    $slug = explode( '/', $plugin_path )[0];
    if ( ! in_array( $slug, A12_VERSIONED_PLUGINS, true ) ) {
        set_transient( 'a12_just_installed_' . sanitize_key( $slug ), $slug, 120 );
    }
} );

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $plugins = get_option( 'active_plugins', [] );
    foreach ( $plugins as $path ) {
        $slug = explode( '/', $path )[0];
        $val  = get_transient( 'a12_just_installed_' . sanitize_key( $slug ) );
        if ( $val ) {
            delete_transient( 'a12_just_installed_' . sanitize_key( $slug ) );
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>A12 Container:</strong> Plugin <code><?php echo esc_html( $slug ); ?></code>
                    instalado, mas <strong>não está no container image</strong>.
                    Adicione ao <code>composer.json</code> e execute
                    <code>./scripts/build-push.sh --deploy</code> para persistir.
                </p>
            </div>
            <?php
        }
    }
} );

// ── Página de status (opcional) ────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_management_page(
        'A12 Container Sync',
        'Container Sync',
        'manage_options',
        'a12-sync-guard',
        'a12_sync_guard_page'
    );
} );

function a12_sync_guard_page() {
    $unversioned = a12_get_unversioned_plugins();
    $versioned   = A12_VERSIONED_PLUGINS;
    $active      = array_map( fn( $p ) => explode( '/', $p )[0], get_option( 'active_plugins', [] ) );
    ?>
    <div class="wrap">
        <h1>A12 Container — Plugin Sync Status</h1>

        <?php if ( empty( $unversioned ) ) : ?>
            <div class="notice notice-success inline"><p>✓ Todos os plugins estão versionados. Container é portável.</p></div>
        <?php else : ?>
            <div class="notice notice-error inline">
                <p>⚠ <?php echo count( $unversioned ); ?> plugin(s) não versionado(s).</p>
            </div>
            <h2>O que fazer</h2>
            <p>Adicione ao <code>sz-a12-portal/composer.json</code>:</p>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:12px;border-radius:4px"><?php
                foreach ( $unversioned as $slug ) {
                    echo esc_html( '"wpackagist-plugin/' . $slug . '": "*"' ) . "\n";
                }
            ?></pre>
            <p>Depois execute:</p>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:12px;border-radius:4px">./scripts/build-push.sh --deploy</pre>
        <?php endif; ?>

        <h2>Status atual</h2>
        <table class="widefat striped" style="max-width:600px">
            <thead><tr><th>Plugin</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ( $active as $slug ) : ?>
                <?php $ok = in_array( $slug, $versioned, true ); ?>
                <tr>
                    <td><code><?php echo esc_html( $slug ); ?></code></td>
                    <td style="color:<?php echo $ok ? 'green' : 'red'; ?>">
                        <?php echo $ok ? '✓ no container image' : '✗ não versionado'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ── Helper ─────────────────────────────────────────────────────────────────
function a12_get_unversioned_plugins(): array {
    $active = array_map( fn( $p ) => explode( '/', $p )[0], get_option( 'active_plugins', [] ) );
    return array_values( array_filter(
        $active,
        fn( $s ) => ! in_array( $s, A12_VERSIONED_PLUGINS, true )
    ) );
}
