<?
/*
Plugin Name: B2主题-仿子比统计
Plugin URI: https://acg.vxras.com/
Description: B2主题-仿子比统计插件
Author: 李初一
Author URI: https://www.vxras.com/
Version: 1.0
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
define( 'b2_tj_path', plugin_dir_path( __FILE__ ) );
define( 'b2_tj_url', plugin_dir_url(__FILE__) );
// 商城统计
function acg_shop_widgets()
{
    if (is_super_admin()) {
        wp_add_dashboard_widget(
            'acg_shop_widget',
            '商城统计',
            'acg_shop_widgets_function'
        );
    }
}

function acg_shop_widgets_function()
{
    wp_enqueue_style('acgpay_page', b2_tj_url . '/css/pay-page.css', array());
    wp_enqueue_script('highcharts', b2_tj_url . '/js/highcharts.js', array('jquery'));
    wp_enqueue_script('westeros', b2_tj_url . '/js/westeros.min.js', array('jquery', 'highcharts'));
    wp_enqueue_script('acgpay_page', b2_tj_url . '/js/pay-page.js', array('jquery'));

    echo '<div class="pay-dashboard-widget">';
    require_once b2_tj_path . '/pay.php'; 
    echo '</div>';
}
add_action('wp_dashboard_setup', 'acg_shop_widgets');
// 站内统计
function acg_blog_widgets()
{
    if (is_super_admin()) {
        add_meta_box(
            'acg_blog_widgets', //ID
            '站内统计', // Title
            'acg_blog_widgets_function', // Callback function
            'dashboard', // Page 
            'side', // Context
            'high' // Priority
        );
    }
}
add_action('wp_dashboard_setup', 'acg_blog_widgets');
function acg_blog_widgets_function()
{
    echo '<div class="pay-dashboard-widget">';
    require_once b2_tj_path . '/blog.php'; 
    echo '</div>';
}