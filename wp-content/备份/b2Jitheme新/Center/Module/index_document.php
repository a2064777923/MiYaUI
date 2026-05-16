<?php
use B2\Modules\Common\Post;
$index_document_title =b2_get_option('Jitheme_index_tab6','one_index_document_title');
$index_document_desc =b2_get_option('Jitheme_index_tab6','one_index_document_desc');
$index_document_img =b2_get_option('Jitheme_index_tab6','one_index_document_img');
$default_image =B2_CHILD_URI.'/Center/Assets/images/wd.svg';
$count = b2_get_option('Jitheme_index_tab6','one_index_document_shuliang');
$paged = get_query_var('paged');
?>
<div id="Onecad-module-list">
        <div class="shop-box-title jitheme-post-title">
            <div class="modules-title-box jitheme-post-title ">
                <?php echo jithem_home_title($index_document_title,$index_document_desc,$index_document_img,$default_image,'/ask') ?>
            </div>
        </div>
   <div class="encyclopedias"> 
        <ul class="clearfix box b2-radius"> 
            <?php 
            $_pages = 0;
                $args = array(
                'post_type' => 'ask',
                'orderby'  => 'modified',
                'order'=>'DESC',
                'post_status'=>'publish',
                'posts_per_page'=>$count,
                'paged'=>$paged
            );
            $document_the_query = new \WP_Query( $args );
            if ( $document_the_query->have_posts()) {
                $_pages = $document_the_query->max_num_pages;
                while ( $document_the_query->have_posts() ) {
                    $document_the_query->the_post();
                    get_template_part( '/Center/Module/index_document_list','normal');
                }
                wp_reset_postdata();
            }else{
                echo B2_EMPTY;
            }
            unset($document_the_query);
        ?>
        </ul> 
    </div> 
</div>