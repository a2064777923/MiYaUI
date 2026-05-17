<?php
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
    $_SERVER['HTTPS'] = 'on';
}

define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'www_miyaui_com_dev' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'www_miyaui_com_dev' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: 'miyaui_local_wp' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'mysql:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'local-auth-key-change-if-needed' );
define( 'SECURE_AUTH_KEY',  'local-secure-auth-key-change-if-needed' );
define( 'LOGGED_IN_KEY',    'local-logged-in-key-change-if-needed' );
define( 'NONCE_KEY',        'local-nonce-key-change-if-needed' );
define( 'AUTH_SALT',        'local-auth-salt-change-if-needed' );
define( 'SECURE_AUTH_SALT', 'local-secure-auth-salt-change-if-needed' );
define( 'LOGGED_IN_SALT',   'local-logged-in-salt-change-if-needed' );
define( 'NONCE_SALT',       'local-nonce-salt-change-if-needed' );

$table_prefix = getenv( 'WP_TABLE_PREFIX' ) ?: 'wp_';

$wp_debug = filter_var( getenv( 'WP_LOCAL_DEBUG' ) ?: 'false', FILTER_VALIDATE_BOOLEAN );
define( 'WP_DEBUG', $wp_debug );
define( 'WP_DEBUG_LOG', $wp_debug );
define( 'WP_DEBUG_DISPLAY', false );

define( 'DISALLOW_FILE_EDIT', true );
define( 'FORCE_SSL_ADMIN', false );
define( 'WP_CACHE', true );
define( 'MIYAUI_LOCAL_ENV', true );
define( 'MIYAUI_BRIDGE_SECRET', getenv( 'WORDPRESS_AUTH_BRIDGE_SECRET' ) ?: 'miyaui-local-bridge-secret' );

define( 'WP_HOME', getenv( 'WP_HOME' ) ?: 'http://localhost:8082' );
define( 'WP_SITEURL', getenv( 'WP_SITEURL' ) ?: 'http://localhost:8082' );

if ( getenv( 'WP_REDIS_HOST' ) ) {
    define( 'WP_REDIS_HOST', getenv( 'WP_REDIS_HOST' ) );
    define( 'WP_REDIS_PORT', (int) ( getenv( 'WP_REDIS_PORT' ) ?: 6379 ) );
    define( 'WP_REDIS_DATABASE', (int) ( getenv( 'WP_REDIS_DATABASE' ) ?: 1 ) );
}

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
