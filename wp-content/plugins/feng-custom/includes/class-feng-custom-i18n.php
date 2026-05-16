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
 * Define the internationalization functionality
 *
 * @link       https://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_i18n {


	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'feng-custom',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
