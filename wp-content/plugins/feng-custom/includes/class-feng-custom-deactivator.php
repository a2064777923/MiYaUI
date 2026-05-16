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
 * Fired during plugin deactivation
 *
 * @link       http://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_Deactivator {
	/**
	 * 禁用插件时执行
	 */
	public static function deactivate() {
	    require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
	    $OptionsClass = Feng_Custom_Options::instance();
	    // 关闭配置开关
	    $OptionsClass->update_switch('option.run_open', 0);
	    $OptionsClass->update_switch('option.keyboard_open', 0);
	    $OptionsClass->update_switch('option.lantern_open', 0);
	    $OptionsClass->update_switch('option.gray_page_open', 0);
	    $OptionsClass->update_switch('option.light_box_open', 0);
	    $OptionsClass->update_switch('option.url_open', 0);
	    
	    $OptionsClass->update_switch('festivals', 0);
	    $OptionsClass->update_switch('snowflake', 0);
	    $OptionsClass->update_switch('links', 0);
	    
	    // 清空缓存
	    require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-cache.php';
	    Feng_Custom_Cache::instance()->clear();
	    
	}

}
