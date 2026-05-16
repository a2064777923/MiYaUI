<?php

/**
 *
 * Plugin Name:       XHTheme AI Toolbox
 * Description:       Through AI, content generation/optimization, automatic comment generation and scheduled publishing, TAG extraction, summary generation, automatic aliasing, and automatic category allocation are achieved, significantly improving the efficiency and quality of content creation.
 * Version:           1.8.3
 * Plugin URI:        https://www.xhtheme.com/xhtheme-ai-toolbox.html
 * Requires at least: 6.6
 * Requires PHP:      7.0
 * Author:            XHTheme
 * Author URI:        https://www.xhtheme.com
 * Text Domain:       xhtheme-ai-toolbox
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

namespace XHTheme\AIToolbox;

defined('ABSPATH') || die();

// 定义插件版本号
define('XHTHEME_AI_TOOLBOX_VERSION', '1.8.3');
define('XHTHEME_AI_TOOLBOX_RESTNAME', 'xhthemeai/v1');
define('XHTHEME_AI_TOOLBOX_BASENAME', plugin_basename(__FILE__));
define('XHTHEME_AI_TOOLBOX_SLUG', 'xhtheme-ai-toolbox');
define('XHTHEME_AI_TOOLBOX_APITIMEOUT', 240);
define('XHTHEME_AI_TOOLBOX_APIVERSION', '4.0');
// 定义图片压缩接口
define('XHTHEME_AI_TOOLBOX_IMAGE_COMPRESS_URL', 'https://imager.cxory.com/compress?url=');
// 临时图床上传接口
define('XHTHEME_AI_TOOLBOX_IMAGE_TMPURL', 'https://imager.cxory.com/fileupload');


// 引入AI处理文件
require_once plugin_dir_path(__FILE__) . 'function/function.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-tool.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-block-template.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-comment.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-aiblock.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-postauto.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-cronqueue.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-thread.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-aiimage.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-xhtheme-admin.php';

// 初始化插件
XHThemeAi::getInstance();
XHCronQueue::getInstance();
XHComment::getInstance();
XHThread::getInstance();
XHPostAuto::getInstance();
XHAiimage::getInstance();
XHAdmin::getInstance();
Xhtheme_Block_Template::getInstance();

/**
 * 优先从本地加载语言包
 */
add_filter('load_textdomain_mofile', __NAMESPACE__ . '\load_textdomain_mofile', 10, 2);
function load_textdomain_mofile($mofile, $domain)
{
	if ('xhtheme-ai-toolbox' === $domain) {
		$locale = apply_filters('xhtheme_ai_toolbox_locale', determine_locale(), $domain);
		$local_mofile = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/languages/' . $domain . '-' . $locale . '.mo';
		if (file_exists($local_mofile)) {
			return $local_mofile;
		}
	}
	return $mofile;
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\addplugin_activate');
function addplugin_activate()
{
	set_transient('xhtheme_ai_toolbox_activated', true, 30);
}

add_action('admin_init', __NAMESPACE__ . '\plugin_redirect');
function plugin_redirect()
{
	if (get_transient('xhtheme_ai_toolbox_activated')) {
		delete_transient('xhtheme_ai_toolbox_activated');
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Activation redirect check, no data submission
		if (!isset($_GET['activate-multi'])) {
			$settings = get_option('xhtheme_ai_toolbox_settings', []);
			if (empty($settings)) {
				wp_safe_redirect(admin_url('admin.php?page=xhtheme-ai-wizard'));
			} else {
				wp_safe_redirect(admin_url('admin.php?page=xhtheme-ai-toolbox'));
			}
			exit;
		}
	}
}

add_action('wp_loaded', __NAMESPACE__ . '\thread_rewrite_rules');
function thread_rewrite_rules()
{
	if (get_option('xhtheme_ai_toolbox_thread_rewrite') == 'load') {
		flush_rewrite_rules();
		update_option('xhtheme_ai_toolbox_thread_rewrite', 'pass');
	}
}

/**
 * 统一调试信息
 */
add_action('admin_notices', __NAMESPACE__ . '\xhtheme_ai_debug');
function xhtheme_ai_debug()
{
	$error_log = get_transient('xhtheme_ai_toolbox_error_log');
	if (!$error_log) {
		return;
	}
	// 格式化日志内容
	$formatted_log = '<pre>' . esc_html(wp_json_encode($error_log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
?>
	<div class="notice notice-success is-dismissible">
		<p><strong>XHAI DEBUG：</strong></p>
		<?php echo wp_kses_post($formatted_log); ?>
	</div>
<?php
}

// 注册错误日志 hook - 写入 PHP error_log
add_action('xhaitoolbox_log_error', __NAMESPACE__ . '\log_error');
function log_error($error)
{
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for plugin diagnostics
	error_log('[XHTheme AI Toolbox] ' . $error);
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\add_plugins_style');
function add_plugins_style()
{
	$csscode = '.xhtheme-ai-toolbox-status {--xht-ai-color:#999}';
	$aiExcerpt = xh_option('summaryStyle', 'simple');
	if ($aiExcerpt !== 'none' && (!apply_filters('xhtheme_ai_toolbox_postexcerpt_show', false, $aiExcerpt) || $aiExcerpt == 'custom')) {
		switch ($aiExcerpt) {
			case 'custom':
				$csscode .= '.eb-card {position:relative;font-size:16px}.eb-icon{opacity:.3;margin-left:auto}.eb-titicon{transform:translateY(-1px)}.eb-card-bgbox{position:absolute;inset:0}.eb-card-body{position:relative}.eb-desc{margin-top:10px}';
				$csscode .= xh_option('summaryCssCode', '');
				break;
			case 'gradient':
				$csscode .= '
				.eb-card {--eb-text-color: #4e607a;--eb-title-color: #4a90e2;font-size:16px}
				' . xh_darkClass('.eb-card') . '{--eb-text-color: #d9d6d6;--eb-title-color: #fff}
				.eb-card {position:relative;border-radius:8px;overflow:hidden;margin-bottom:1.2rem;display:flex;flex-direction:column;}
				.eb-card-bgbox {position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);z-index:1;opacity: .3;}
				.eb-card-body {position:relative;padding:20px;z-index:2;}
				.eb-titlebox {display:flex;align-items:center;margin-bottom:12px;justify-content:space-between;}
				.eb-titlebox .eb-icon {opacity:.3;margin-left:auto;}
				.eb-titicon svg {width:16px;height:16px;color:var(--eb-title-color);transform:translateY(-2px);}
				.eb-title {font-size:.9em;font-weight:400;color:var(--eb-title-color);margin-left:4px;letter-spacing:2px}
				.eb-icon svg {width:24px;height:24px;color:var(--eb-title-color);margin-left:auto;}
				.eb-excerpt {font-size:.9em;color:var(--eb-text-color);line-height:1.8;margin-bottom:12px;}
				.eb-desc {font-size:.75em;color:var(--eb-text-color);text-align:right;opacity:.8;margin-top:10px;}
				.eb-desc span {font-style:italic;}
				';
				break;
			default:
				$csscode .= '
				.eb-card {--eb-text-color: #4e607a;--eb-title-color: #4a90e2;font-size:16px;--eb-bg-color: #f5f6ff}
				' . xh_darkClass('.eb-card') . '{--eb-text-color: #b3b1b1;--eb-title-color: #fff;--eb-bg-color: rgba(0,0,0,.2)}
				.eb-card {position:relative;border-radius:8px;overflow:hidden;margin-bottom:1.2rem;display:flex;flex-direction:column;}
				.eb-card-bgbox {position:absolute;top:0;left:0;width:100%;height:100%;background-color:var(--eb-bg-color);z-index:1}
				.eb-card-body {position:relative;padding:20px;z-index:2;}
				.eb-titlebox {display:flex;align-items:center;margin-bottom:12px;justify-content:space-between}
				.eb-titlebox .eb-icon {opacity:.3;margin-left:auto;}
				.eb-titicon svg {width:16px;height:16px;color:var(--eb-title-color);transform:translateY(-2px)}
				.eb-title {font-size:.9em;font-weight:500;color:var(--eb-title-color);margin-left:4px;letter-spacing:2px}
				.eb-icon svg {width:24px;height:24px;color:var(--eb-title-color);margin-left:auto;}
				.eb-excerpt {font-size:.9em;color:var(--eb-text-color);line-height:1.8;margin-bottom:12px;}
				.eb-desc {font-size:.75em;color:var(--eb-text-color);text-align:right;opacity:.7;margin-top:10px;}
				.eb-desc span {font-style:italic;}
				';
				break;
		}
		$csscode = preg_replace('/[\r\n\t]+/', ' ', $csscode);
		$csscode = preg_replace('/\s{2,}/', ' ', $csscode);
		$csscode = str_replace(['; ', ';}', ': ', '{ ', '} ', ' {'], [';', '}', ':', '{', '}', '{'], $csscode);
		$csscode = trim($csscode);
	}
	wp_register_style('xhtheme-ai-toolbox', false, array(), XHTHEME_AI_TOOLBOX_VERSION);
	wp_enqueue_style('xhtheme-ai-toolbox');
	wp_add_inline_style('xhtheme-ai-toolbox', wp_strip_all_tags($csscode));
}

register_deactivation_hook(__FILE__, __NAMESPACE__ . '\plugin_deactivation');
function plugin_deactivation()
{
	delete_option('xhtheme_ai_toolbox_thread_rewrite');

	// 清理定时任务挂载点
	wp_clear_scheduled_hook('xhaitoolbox_minute_cron');
	wp_clear_scheduled_hook('xhaitoolbox_twicedaily_cron');
}
