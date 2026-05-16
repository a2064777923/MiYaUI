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
 * Fired when the plugin is uninstalled.
 *
 * @link       https://feng.pub
 *
 * @package    Feng_Custom
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * WP-Cron 卸载任务
 */
$timestamp = wp_next_scheduled( 'fct_links_rss_cron_hook' );
if ($timestamp) {
    wp_unschedule_event( $timestamp, 'fct_links_rss_cron_hook' );
}

/**
 * 删除配置数据
 */
// 记录的版本号
delete_option('feng_custom_version');
// 开关配置
delete_option('feng_custom_switch');
// 效果配置
delete_option('feng_custom_option');
// 节日氛围
delete_option('feng_custom_festivals');
// 雪花飘落
delete_option('feng_customfct_snowflake');
// 链接&RSS
delete_option('feng_custom_links');

/**
 * before v1.2.0
 */
delete_option('fct_xianshi_tianshu');
delete_option('fct_kaitong_shijian');
delete_option('fct_kaitong_bangding');
delete_option('fct_kaitong_neirong');
delete_option('fct_shurukuang');
delete_option('fct_denglong');
delete_option('fct_denglong_zi_1');
delete_option('fct_denglong_zi_2');
delete_option('fct_denglong_kaishi');
delete_option('fct_denglong_jieshu');
delete_option('fct_huise');
delete_option('fct_huise_kaishi');
delete_option('fct_huise_jieshu');
delete_option('fct_dengxiang');
delete_option('fct_dengxiang_bangding');
delete_option('fct_lianjie');
delete_option('fct_lianjie_weiba');
delete_option('fct_xuehua');
delete_option('fct_xuehua_option');
delete_option('fct_festivals_option');
delete_option('fct_links_option');
delete_option('fct_custom_version');

/**
 * 递归删除缓存目录
 * @param string $folder_path
 */
function feng_custom_clear_cahce($folder_path) {
    if (is_dir($folder_path)) {
        $files = array_diff(scandir($folder_path), array('.', '..'));
        foreach ($files as $file) {
            $file_path = $folder_path . '/' . $file;
            is_dir($file_path) ? feng_custom_clear_cahce($file_path) : unlink($file_path);
        }
        rmdir($folder_path);
    }
}
feng_custom_clear_cahce(WP_CONTENT_DIR . '/cache/feng-custom');

