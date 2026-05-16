<?php
    use B2\Modules\Common\Document;
    get_header();
    $post_id = get_the_id();
    $terms = get_the_terms($post_id,'document_cat');
    
    $term_id = isset($terms[0]->term_id) ? $terms[0]->term_id : 0;
    global $wp;
    $current_url = B2_HOME_URI.'/'.$wp->request;
?>
    
    <?php do_action('b2_document_wrapper_before'); ?>
    <div class="b2-document-content wrapper">
        <?php do_action('b2_document_before'); ?>

        <div class="document-left widget-area b2-pd-r">
            <?php 
                $li = '';
                $args = array(
                    'post_type' => 'document',
                    'post_status'=>'publish',
                    'tax_query' => array(
                        array(
                        'taxonomy' => 'document_cat',
                        'field' => 'term_id',
                        'terms' => array($term_id)
                        )
                    ),
                    'order' => 'ASC',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'b2_document_order',
                            'type' => 'NUMERIC',
                        ),
                        array(
                            'key' => 'b2_document_order',
                            'compare' => 'NOT EXISTS'
                        )
                    ),
                    'orderby'   => 'rand',//meta_value_num为默认值
                    'posts_per_page'=>50,
                    'no_found_rows'=>true
                );

                $document_the_query = new \WP_Query( $args );

                if ( $document_the_query->have_posts()) {

                    while ( $document_the_query->have_posts() ) {
                        
                        $document_the_query->the_post();
                        $link = get_permalink();
                        if($current_url === $link){
                            $li .= '<li>'.get_the_title().'</li>';
                        }else{
                            $li .= '<li><a href="'.$link.'" style="color: #333;">'.get_the_title().'</a></li>';
                        }
                        
                    }
                    
                }else{
                    $li = '';
                }
                wp_reset_postdata();
             
            ?>
            <?php if($li) { ?>
                <div class="document-left-item box b2-hover b2-radius mg-b mini-document-left-item">
                    <h2><i class="Jifont Jifont-nav-post-1 Jifont_pad"></i><?php echo __('最新文章','b2'); ?></h2>
                    <ul>
                        <?php echo $li; ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="document-left-item box b2-radius request-supper">
                <h2><?php echo __('需要支持？','b2'); ?></h2>
                <p><?php echo __('如果通过文档没办法解决您的问题，请提交工单获取我们的支持！','b2'); ?></p>
                <div><a href="javascript:void(0)" class="button" @click="canRequest('<?php echo b2_get_custom_page_url('requests'); ?>')"><?php echo __('发布文章','b2'); ?></a></div>
            </div>
        </div>

        <div id="primary-home" class="content-area">

            <?php  while ( have_posts() ) : the_post();

                echo '<div class="mini-document-box box b2-radius b2-pd mg-b">';

                do_action('b2_document_content_before');

                get_template_part( 'TempParts/single-document');

                do_action('b2_document_content_after');

                echo '</div>';

                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
        
                endwhile; ?>
            
        </div>
        
        <?php do_action('b2_document_after'); ?>
        <aside id="secondary " class="widget-area">
                    <div class="sidebar">
                        <div class="sidebar-innter widget-ffixed">
                             <?php dynamic_sidebar('sidebar-102'); ?>
                        </div>
                    </div>
                </aside>
            </div>

    <?php do_action('b2_document_wrapper_after'); ?>
<?php
get_footer();