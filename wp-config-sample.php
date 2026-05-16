<?php
/**
 * MiYaUI WordPress 配置模板
 *
 * 使用方法：复制此文件为 wp-config.php，然后填入实际值
 * cp wp-config-sample.php wp-config.php
 */

// 处理反向代理 HTTPS（如果使用宝塔反向代理架构，必须保留此代码）
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// ** 数据库设置 ** //
define( 'DB_NAME', 'www_miyaui_com' );
define( 'DB_USER', '你的数据库用户名' );
define( 'DB_PASSWORD', '你的数据库密码' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// ** 安全密钥 ** //
// 访问 https://api.wordpress.org/secret-key/1.1/salt/ 生成新的密钥并替换以下内容
define( 'AUTH_KEY',         '在此填入生成的密钥' );
define( 'SECURE_AUTH_KEY',  '在此填入生成的密钥' );
define( 'LOGGED_IN_KEY',    '在此填入生成的密钥' );
define( 'NONCE_KEY',        '在此填入生成的密钥' );
define( 'AUTH_SALT',        '在此填入生成的密钥' );
define( 'SECURE_AUTH_SALT', '在此填入生成的密钥' );
define( 'LOGGED_IN_SALT',   '在此填入生成的密钥' );
define( 'NONCE_SALT',       '在此填入生成的密钥' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

// ** 站点地址（根据实际域名修改）** //
define( 'WP_HOME', 'https://www.miyaui.com' );
define( 'WP_SITEURL', 'https://www.miyaui.com' );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
