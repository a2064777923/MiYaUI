<?php
use B2\Modules\Common\Post;
$index_document_title =b2_get_option('Jitheme_index_tab6','one_index_document_title');
$index_document_desc =b2_get_option('Jitheme_index_tab6','one_index_document_desc');
$count = b2_get_option('Jitheme_index_tab6','one_index_document_shuliang');
$paged = get_query_var('paged');
?>
<div id="Onecad-module-list"  class="wrapper" >
    <div class="post-modules-top ">
        <div class="modules-title-box">
            <div class="Onecad_title"><div><?php echo $index_document_title ?></div> <div><?php echo $index_document_desc ?></div></div>
        </div>
        <div class="post-list-cats post-list-cats-has-title">
            <div class="post-carts-list-row">
                    <a href="../requests" class="cat-list post-load-button picked">
                        <span data-type="cat">
                            全部
                        </span>
                    </a>
            </div>
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