<?php
//B2子主题目录url
define('B2_CHILD_URI', get_stylesheet_directory_uri() );
//勿删
require_once get_theme_file_path('/jitheme_functions.php');
// B2主题美化 侧边跟随鼠标显文字工具条
require_once get_stylesheet_directory() . '/Modules/Templates/Footer.php';

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

function isHomePage() {
    // 获取当前页面的 URL
    $current_url = ($_SERVER['REQUEST_URI'] == '/') ? true : false;
    return $current_url;
}



function get_latest_announcements($request) {
    // 设置查询参数
    $args = array(
        'post_type'      => 'announcement', // 文章类型（可以根据需要改为自定义文章类型）
        'posts_per_page' => 1,       // 获取最新的 5 条公告
    );

    // 查询文章
    $query = new WP_Query($args);

    // 如果没有文章，返回空数组
    if (!$query->have_posts()) {
        return array();
    }

    // 格式化返回的数据
    $announcements = array();
    while ($query->have_posts()) {
        $query->the_post();
        $announcements[] = array(
            'id'      => get_the_ID(),
            'title'   => get_the_title(),
            // 'content' => get_the_content(),
            'date'   => get_the_date('Y-m-d H:i:s'),
            'link'    => get_permalink(),
        );
    }

    // 重置查询
    wp_reset_postdata();

    // 返回 JSON 格式的数据
    return rest_ensure_response($announcements);
}

// 注册自定义 API 路由
function register_custom_api_routes() {
    register_rest_route('custom/v1', '/latest-announcements', array(
        'methods'  => 'GET',
        'callback' => 'get_latest_announcements',
    ));
}

// 初始化 API 路由
add_action('rest_api_init', 'register_custom_api_routes');

function jitheme_home_xinxia(){
    $html ='';
    $index_gg_off= b2_get_option('Jitheme_index_main','index_gg_off');
    if($index_gg_off){
        // 创建一个新的WP_Query实例
        $query = new WP_Query(array(
            'post_type' => 'announcement', // 只查询类型为'announcement'的文章
            'posts_per_page' => 1, // 设置只获取一篇文章
            'orderby' => 'date', // 按照发布日期排序
            'order' => 'DESC', // 降序排列（最新的文章排在前面）
        ));
        
        // 判断是否有文章
        if ($query->have_posts()) :
            // 获取第一篇文章
            $query->the_post();
            $html = '<div class="gRule">
                    <div class="ysTxt">
                        <div class="qxbg">
                            <p>
                                <span class="title">' . get_the_title() . '</span>
                                <span class="date">发布日期: ' . get_the_date('Y-m-d') . '</span>
                            </p>
                            <i class="ysclose Jifont Jifont-close" id="closebtn"></i>
                        </div>
                        <div class="ysMain">
                            '.get_the_content().'
                        </div>
                    </div>
                </div>';
            wp_reset_postdata();
            else :
                // 没有文章时的提示
                    $html =  '<div class="gRule">
                        <div class="ysTxt">
                            <div class="qxbg"><span class="ysicon"></span>
                                <p>
                                    <span class="title">暂无公告！</span>
                                    <span class="date">可在后台发布新的公告哦！</span>
                                </p>
                                <i class="ysclose" id="closebtn"></i>
                            </div>
                            <div class="ysMain">
                                暂无内容！
                            </div>
                        </div>
                    </div>';
        endif;
        
    }
    
    return $html;
}



/**
 * WordPress 自动为文章标签添加该标签的链接
 */
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







// 添加回到顶部按钮的样式
function add_scroll_top_button_style() {
   ?>
    <style>
        #return-top{
            bottom: 20px;
            right: 20px;
            display: none;  // 始终初始化为隐藏，无论页面是否有滚动条
            z-index: 99;
        }
        #return-top a{
            display: block;
            width: 50px;
            height: 50px;
            background: #000;
            color: #fff;
            text-align: center;
            line-height: 50px;
            text-decoration: none;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'add_scroll_top_button_style' );
// 右侧小工具回到顶部按钮滑动显示JavaScript代码
function add_scroll_top_button_script() {
   ?>
    <script>
        jQuery(document).ready(function($) {
            $(window).scroll(function() {
                // 当滚动条与页面顶部距离超过100px时显示按钮，初始就是隐藏状态，只有滚动时才判断是否显示
                if ($(window).scrollTop() > 100) {
                    $('#return-top').fadeIn();
                } else {
                    $('#return-top').fadeOut();
                }
            });
            $('#return-top a').click(function() {
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        });
    </script>
    <?php
}
add_action( 'wp_footer', 'add_scroll_top_button_script' );