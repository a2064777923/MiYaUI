<?php
//B2子主题目录url
define('B2_CHILD_URI', get_stylesheet_directory_uri() );
define('B2_VERIFY_ICON','<i class="vrnzhengss b2font b2-color"></i>');   
wp_enqueue_script( 'b2-jquery','/wp-content/themes/b2Jitheme/jquery.min.js', array(), null , false );
//勿删
require_once get_theme_file_path('/jitheme_functions.php');
//图片上传自动命名-用户可以自行删除
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );
function custom_upload_filter( $file ){
    $info = pathinfo($file['name']);
    $ext = $info['extension'];
    $filedate = date('YmdHis').rand(10,99);//为了避免时间重复，再加一段2位的随机数
    $file['name'] = $filedate.'.'.$ext;
    return $file;
}
/* 删除文章时删除图片附件
/* ———————— */
function delete_post_and_attachments($post_ID) {
global $wpdb;
//删除特色图片
$thumbnails = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
foreach ($thumbnails as $thumbnail) {
wp_delete_attachment($thumbnail->meta_value, true);
}
//删除图片附件
$attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent = $post_ID AND post_type = 'attachment'");
foreach ($attachments as $attachment) {
wp_delete_attachment($attachment->ID, true);
}
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
}
add_action('before_delete_post', 'delete_post_and_attachments');

// // WP后台文章列表增加文章列标题
// function custom_columns_head($defaults) {
//     $new_columns = array(
//         'admin_ji_jb' => '角标',
//     );
//     // 将自定义列标题插入到第二个位置
//     $defaults_spliced = array_slice($defaults, 0, 2, true) + $new_columns + array_slice($defaults, 2, NULL, true);
//     return $defaults_spliced;
// }
// add_filter('manage_posts_columns', 'custom_columns_head');
// function custom_columns_content($column_name, $post_id) {
//     if ($column_name == 'admin_ji_jb') {
//         $custom_field_value = get_post_meta($post_id, 'b2_post_onecad_lb', true);
//         // 显示自定义字段的值
//         echo admin_listjiaobiao($custom_field_value);
//     }
// }
// add_action('manage_posts_custom_column', 'custom_columns_content', 10, 2);

function jitheme_post_time_ls($post_id){
    //发布时间
    $postTime =Mini_post_time($post_id);
    $currentTime = time();
    $timeDiff = floor(($currentTime - $postTime) / (60 * 60 * 24));
    if ($timeDiff <= 3) {
        $title_new= '<i class="ico icon-talk-hot-1"></i>';
    } else {
        $title_new= '';
    }
    return $title_new;
}




