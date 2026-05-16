<?php
//B2子主题目录url
define( 'B2_CHILD_URI', get_stylesheet_directory_uri() );
//认证图标
define('B2_VERIFY_ICON','<i class="vrnzhengss b2font b2-color"></i>');    

//加载父级样式(一般不用修改)
add_action( 'wp_enqueue_scripts', 'parent_theme_enqueue_styles',9 );
function parent_theme_enqueue_styles() {

    //加载父主题样式文件
    wp_enqueue_style( 'parent-style-main', get_template_directory_uri() . '/style.css',array() , B2_VERSION, 'all' );
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/Assets/fontend/style.css',array() , B2_VERSION, 'all');
}


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

// 加载子主题的自定义CSS文件
function child_theme_enqueue_custom_css() {
    // 获取子主题目录的URI
    $child_theme_uri = get_stylesheet_directory_uri();
    
    // 加载自定义CSS文件，依赖于父主题的style.css（保证加载顺序）
    wp_enqueue_style(
        'child-custom-style', // 唯一标识（自定义）
        $child_theme_uri . '/assets/css/custom.css', // 新CSS文件的路径
        array('parent-style'), // 依赖项：先加载父主题样式，再加载这个
        wp_get_theme()->get('Version'), // 版本号（可选，用于清除缓存）
        'all' // 媒体类型（all表示适配所有设备）
    );
}
// 挂载到wp_enqueue_scripts钩子，确保前端加载
add_action('wp_enqueue_scripts', 'child_theme_enqueue_custom_css');

//加载子主题样式
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles',99 );
function child_theme_enqueue_styles() {

    //禁用当前子主题默认加载项
    wp_dequeue_style( 'b2-style-main' );
    wp_dequeue_style( 'b2-style' );

    //加载子主题样式文件，使它在所有样式之后
    wp_enqueue_style( 'child-style', B2_CHILD_URI.'/style.css' , array() , B2_VERSION, 'all');
 
    //加载子主题JS文件
    wp_enqueue_script( 'b2-child', B2_CHILD_URI.'/child.js', array(), B2_VERSION , true );
    

}

/*分类页面顶部筛选样式修改*/
require_once( get_stylesheet_directory(). '/Modules/Templates/Archive.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/VueTemplates.php' );
require_once( get_stylesheet_directory(). '/Modules/Settings/Taxonomies.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/Footer.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/Header.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/Widgets/Mission.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/Widgets/Download.php' );
require_once( get_stylesheet_directory(). '/Modules/Templates/Single.php' );
require get_stylesheet_directory()."/Modules/emoji.php";


/*等级图标美化*/
require_once( get_stylesheet_directory(). '/Modules/Common/User.php' );



require_once get_theme_file_path('/inc/functions.php');
require_once get_theme_file_path('/inc/codestar-framework/codestar-framework.php');
 
require_once get_theme_file_path('/inc/options/admin-options.php');
wp_enqueue_script( 'b2-jquery','/wp-content/themes/b2-pinkcatyu/jquery.min.js', array(), null , false );

function pinkcatyu_get_cat_postcount($id) {
 // 获取当前分类信息
 $cat = get_category($id);
 echo pinkcatyu_cat_postcount(80);
 $cat_id = get_queried_object()->term_id;
echo pinkcatyu_get_cat_postcount($cat_id);
 // 当前分类文章数
 $count = (int) $cat->count;
 // 获取当前分类所有子孙分类
 $tax_terms = get_terms('category', array('child_of' => $id));
 foreach ($tax_terms as $tax_term) {
  // 子孙分类文章数累加
  $count +=$tax_term->count;
 }
 return $count;
}

// /**
//  * WordPress 自动为文章标签添加该标签的链接
//  */
function wpkj_auto_add_tag_link($content){
    $limit = 1; // 设置同一个标签添加几次链接
    $posttags = get_the_tags();
    if ($posttags) {
        foreach($posttags as $tag) {
            $link = get_tag_link($tag->term_id);
            $keyword = $tag->name;
            $cleankeyword = stripslashes($keyword);
            $url = '<a target="_blank" href="'.$link.'" title="'.str_replace('%s', addcslashes($cleankeyword, '$'), __('View all posts in %s')).'">'.addcslashes($cleankeyword, '$').'</a>';
            $regEx = '\'(?!((<.*?)|(<a.*?)))('. $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s';
            $content = preg_replace($regEx,$url,$content,$limit);
        }
    }
    return $content;
}
add_filter( 'the_content', 'wpkj_auto_add_tag_link', 1 );


// 在文章页面头部添加当前位置 
function custom_breadcrumb() {
    if (!is_front_page()) {
        echo '<div class="breadcrumb">';
        echo '当前位置： ';
        echo '<a href="' . home_url() . '">首页</a> > ';
        if (is_category() || is_single()) {
            the_category(' > ');
            if (is_single()) {
                echo ' > ';
                the_title();
            }
        } elseif (is_page()) {
            echo the_title();
        } elseif (is_search()) {
            echo '搜索结果： ';
            echo get_search_query();
        }
        echo '</div>';
    }
}
?>




