<?php
/**
 * Plugin Name: B2网盘链接有效检测
 * Plugin URI: https://mewcg.com
 * Description: 检测文章中的网盘链接状态，支持单文章单独检测和多链接批量检测，在后台文章列表显示链接状态，前台B2主题下载区域显示状态并支持用户手动检测
 * Version: 2.3.5
 * Author: 喵CG
 * Author URI: https://mewcg.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-netdisk-link-checker
 * Last Updated: 2025-06-24
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WPNLC_VERSION', '2.3.5');
define('WPNLC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPNLC_PLUGIN_URL', plugin_dir_url(__FILE__));

// 自动加载器
require_once WPNLC_PLUGIN_DIR . 'includes/class-wpnlc-autoloader.php';

// 初始化插件
function wpnlc_init() {
    // 确保自动加载器已注册
    if (!class_exists('WPNLC_Core')) {
        require_once WPNLC_PLUGIN_DIR . 'includes/class-wpnlc-autoloader.php';
    }
    
    // 加载核心类
    WPNLC_Core::get_instance();
}

// 插件激活钩子
register_activation_hook(__FILE__, array('WPNLC_Core', 'activate'));

// 插件停用钩子
register_deactivation_hook(__FILE__, array('WPNLC_Core', 'deactivate'));

// 初始化插件
add_action('plugins_loaded', 'wpnlc_init');
