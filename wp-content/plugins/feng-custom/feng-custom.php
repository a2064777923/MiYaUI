<?php
// +----------------------------------------------------------------------
// | 晨风自定义 [ 用最简单的代码，实现最简单的事情。 ]
// +----------------------------------------------------------------------
// | Home Page: https://feng.pub/feng-custom
// +----------------------------------------------------------------------
// | Gitee: https://gitee.com/ouros/feng-custom
// +----------------------------------------------------------------------
// | WordPress: https://cn.wordpress.org/plugins/feng-custom
// +----------------------------------------------------------------------
// | Author: 阿锋 <mypen@163.com>
// +----------------------------------------------------------------------
/**
 * Plugin Name:       Feng Custom
 * Plugin URI:        https://gitee.com/ouros/feng-custom
 * Description:       晨风自定义，友情链接及链接RSS聚合功能，网页特效包含节日氛围、雪花飘落、底部运行天数、网页灰色、输入框七彩光子、图片灯箱特效等等。配置路径“仪表盘->外观->晨风自定义”
 * Version:           1.2.4
 * Author:            阿锋
 * Author URI:        https://feng.pub
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       feng-custom
 * Domain Path:       /languages
 * 
 * @package           feng-custom
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'FENG_CUSTOM_VERSION',  '1.2.4');

/**
 * 插件目录
 */
define('FENG_CUSTOM_PATH', plugin_dir_path(__FILE__));

/**
 * 插件目录的URL
 */
define('FENG_CUSTOM_URL', plugin_dir_url( __FILE__ ));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-feng-custom-activator.php
 */
function activate_feng_custom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feng-custom-activator.php';
	Feng_Custom_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-feng-custom-deactivator.php
 */
function deactivate_feng_custom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feng-custom-deactivator.php';
	Feng_Custom_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_feng_custom' );
register_deactivation_hook( __FILE__, 'deactivate_feng_custom' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-feng-custom.php';

/**
 * Begins execution of the plugin.
 */
function run_feng_custom() {
	$plugin = new Feng_Custom();
	$plugin->run();
}


run_feng_custom();
