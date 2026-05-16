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
 * links-rss-log.php
 */
// 引入头部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/header.php';

$timestamp = wp_next_scheduled( 'fct_links_rss_cron_hook' );
if ($timestamp) {
    echo '<p><b>' . __('WP-Cron下次执行检查RSS的时间： ', 'feng-custom') . wp_date('Y-m-d H:i:s', $timestamp) . '</b></p><hr />';
}else {
    echo '<p><a href="' . admin_url('themes.php?page=feng-custom&module=links') . '">' . '点击这里请检查是否勾选了“开启检查RSS定时计划”' . '</a><p><hr />';
}

require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-cache.php';
$CacheClass = Feng_Custom_Cache::instance();
$log = $CacheClass->get('rss_cron_log', 'links');
$log_bak = $CacheClass->get('rss_cron_log_bak', 'links');

echo $log . $log_bak;

// 引入底部模板
require_once FENG_CUSTOM_PATH . 'admin/partials/footer.php';