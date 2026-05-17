<?php
 

//定义文件夹
add_filter('csf_fa4', '__return_true');

//定义文件夹
function csf_custom_csf_override_zib2()
{
    return 'inc/csf-framework';
}
add_filter('csf_override', 'csf_custom_csf_override_zib2');

//获取主题设置链接
function zib2_mh_get_admin_csf_url($tab = '')
{
    $tab_array          = explode("/", $tab);
    $tab_array_sanitize = array();
    foreach ($tab_array as $tab_i) {
        $tab_array_sanitize[] = sanitize_title($tab_i);
    }
    $tab_attr = esc_attr(implode("/", $tab_array_sanitize));
    $url      = add_query_arg('page', 'zib2_bb_admin', admin_url('admin.php'));
    $url      = $tab ? $url . '#tab=' . $tab_attr : $url;
    return esc_url($url);
}

// 获取及设置主题配置参数
function zib2($name, $default = false, $subname = '')
{
    //声明静态变量，加速获取
    static $zib_get_option = null;
    if ($zib_get_option) {
        $options = $zib_get_option;
    } else {
        $zib_get_option = get_option('zib2_bb_admin');
    }
    if (isset($options[$name])) {
        if ($subname) {
            return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
        } else {
            return $options[$name];
        }
    }
    return $default;
}

function zib2_hq($name, $value)
{
    $get_option        = get_option('zib2_bb_admin');
    $get_option        = is_array($get_option) ? $get_option : array();
    $get_option[$name] = $value;
    return update_option('zib2_bb_admin', $get_option);
}

//获取及设置压缩后的posts_meta
if (!function_exists('of_get_posts_meta')) {
    function of_get_posts_meta($name, $key, $default = false, $post_id = '')
    {
        global $post;
        $post_id  = $post_id ? $post_id : $post->ID;
        $get_mate = get_post_meta($post_id, $name, true);
        if (isset($get_mate[$key])) {
            return $get_mate[$key];
        }
        return $default;
    }
}

if (!function_exists('of_set_posts_meta')) {
    function of_set_posts_meta($post_id = '', $name, $key, $value)
    {
        if (!$name) {
            return false;
        }
        global $post;
        $post_id        = $post_id ? $post_id : $post->ID;
        $get_mate       = get_post_meta($post_id, $name, true);
        $get_mate       = (array) $get_mate;
        $get_mate[$key] = $value;
        return update_post_meta($post_id, $name, $get_mate);
    }
}

