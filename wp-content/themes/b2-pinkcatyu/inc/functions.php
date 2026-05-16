<?php
if (!defined('ABSPATH')) {
	die('-1');
}

//自定义css、js
function csf_add_custom_wp_enqueue_zib2()
{
    // Style
    wp_enqueue_style('csf_custom_css', B2_CHILD_URI.'/inc/csf-framework/assets/css/style.min.css',array(), '1.0');
	wp_enqueue_style('csf_custom_css', B2_CHILD_URI. '/inc/csf-framework/assets/css/codemirror.min.css',array(), '1.0');
    // Script
    wp_enqueue_script('csf_custom_js',B2_CHILD_URI.'/inc/csf-framework/assets/js/main.min.js', array('jquery'), '1.0');
    
}
add_action('csf_enqueue', 'csf_add_custom_wp_enqueue_zib2');


//引入核心变量
if ( ! function_exists( 'senyu_pz' ) ) {
  function senyu_pz( $option = '', $default = null ) {
    $options = get_option( 'senyu_admin' ); 
    return ( isset( $options[$option] ) ) ? $options[$option] : $default;
  }
}



/**
 * 主题启动时执行函数
 *
 * @return
 */
function senyu_init_theme()
{
    global $pagenow;
    if ('themes.php' == $pagenow && isset($_GET['activated'])) {
         wp_redirect(admin_url('/admin.php?page=senyu_admin'));
        //exit;
    }
}
add_action('after_setup_theme', 'senyu_init_theme');
add_action('after_switch_theme', 'senyu_init_theme');