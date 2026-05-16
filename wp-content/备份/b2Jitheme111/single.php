<?php
use B2\Modules\Templates\Single;
    use B2\Modules\Templates\Main;
    use B2\Modules\Common\Post;

    get_header();
    $post_type = get_post_type();

    $b2_custom_post_type = b2_get_search_type();
    $post_id = get_the_ID();

    $post_meta = Post::post_meta($post_id);
    //获取缩略图
    $thumb = Post::get_post_thumb($post_id);
    //计算缩略图宽高
    $w = b2_get_option('template_main','wrapper_width');
    $w = preg_replace('/\D/s','',$w);
    $thumb = b2_get_thumb(array(
        'thumb'=>$thumb,
        'width'=>$w,
        'height'=>'100%'
    ));
    ?>
    <?php do_action('b2_single_wrapper_before'); ?>
    <?php single_header($post_id,$thumb,$post_meta,$post_type);?>
    <div class="b2-single-content wrapper">

        <?php do_action('b2_single_before'); ?>

        <div id="primary-home" class="content-area">

            <?php  while ( have_posts() ) : the_post();

                do_action('b2_single_content_before');

                get_template_part( 'TempParts/single',isset($b2_custom_post_type[$post_type]) ? $post_type : 'normal');

                do_action('b2_single_content_after');

                if ( (comments_open() || get_comments_number()) && (int)b2_get_option('template_comment','comment_close') === 1 && $post_type !== 'circle') :
                    comments_template();
                endif;

                endwhile;
                 ?>

                <?php do_action('b2_comments_after'); ?>
        </div>

        <?php do_action('b2_single_after'); ?>

    <?php 
        if($post_type !== 'announcement')
        get_sidebar(); 
    ?>
    
    </div>
    <?php do_action('b2_single_wrapper_after'); ?>
<?php
get_footer();