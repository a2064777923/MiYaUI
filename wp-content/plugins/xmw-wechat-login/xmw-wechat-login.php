<?php
/**
 * Plugin Name: b2微信订阅号登陆插件
 * Plugin URI: https://www.ahap.cn/xmw-product/8617.html
 * Description: 用于B2PRO主题上使用微信订阅号登陆功能
 * Version: 1.0
 * Author: 许天
 * Author URI:https://www.ahap.cn
 */
 
defined( 'ABSPATH' ) || exit;

define('XMW_WECHAT_LOGIN_PATH', plugin_dir_path(__FILE__) );

define('XMW_WECHAT_LOGIN_URL', plugin_dir_url(__FILE__ ) );

require 'inc/wechat-php-sdk-master/include.php';

require_once('includes/xmw-class-fun.php');

require_once('includes/xmw-class-out.php');

require_once('admin/settings/Settings.php');


