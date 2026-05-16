<?php
use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinkAction;

$top_ad = b2_get_option('links_single','link_single_top');
$bottom_ad = b2_get_option('links_single','link_single_bottom');
$id = get_the_id();
$link_to = get_post_meta($id,'b2_link_to',true);
$link_icon = get_post_meta($id,'b2_link_icon',true);
$term = wp_get_post_terms( $id, 'link_cat', array( 'fields' => 'ids' ) );
if(!is_wp_error( $term ) && !empty($term)){
    $term = $term[0];
}else{
    $term = false;
}
$content = apply_filters( 'the_content', get_the_content() );
$excerpt = get_the_excerpt();

$content = $content ? $content : $excerpt;
$content = $content ? $content : __('这个站点没有任何描述','b2');

?>

<article class="single-article b2-radius box single-link ">
    <?php do_action('b2_single_article_before'); ?>
    <div class="link-breadcrumb b2-hover mg-b"><?php echo LinkAction::link_breadcrumb($id); ?></div>
        
        <div class="jitheme_links_post">
            <header class="entry-header">
            <div class="link-single-header">
                <div>
                    <h1><?php echo get_the_title(); ?></h1>
                    <div class="info">
                        <span><?php the_time('Y-m') ?>收录</span>
                        <!--<span>访问</span>-->
                    </div>
                </div>
        <div class="single-link-rating" ref="linkSingle" data-id="<?php echo $id; ?>">
            <button :disable="locked" @click="linkVote" :class="isUp ? 'hasup text' : 'text'">
                <span v-if="!isUp"><?php echo b2_get_icon('b2-thumb-up-line'); ?></span>
                <span v-else><?php echo __('已赞','b2'); ?></span>
                <span v-text="up"></span>
            </button>
        </div>
            </div>
        </header>
            <div class="entry-content site-single">
                <div class="site-row">
                    <div class="site-icon b2-radius">
                        <img class="bg loading" src="<?php echo $link_icon; ?>" data-was-processed="true">
        	            <i>
                            <img src="<?php echo $link_icon; ?>" class="loading" data-was-processed="true">
                        </i>
                    </div>

                    <div class="site-main">
                        <div class="site-list-cat">
                            <?php 
                                $terms = get_the_terms($id, 'link_cat' );
                                if( !empty( $terms ) ){
                                	foreach( $terms as $cat ){
                                        $name = $cat->name;
                                        $link = esc_url( get_term_link( $cat, 'res_category' ) );
                                        echo "<a href='$link' target='_blan'>".$name."</a>";
                                    }
                                }  
                            ?>
                           </div>
                        <div class="site-desc">
                            <p><?php echo $content; ?></p>
                        </div>
                        <div class="site-tags">
                            <i class="b2font b2-tags"></i>
                                                    </div>
                        <div class="site-go">
                		    <a class="b2-radius" href="<?php echo $link_to; ?>" target="_blank" rel="nofollow" uk-tooltip="访问网站">访问网站<i class="iconfont icon-go uk-margin-small-left"></i></a>
                		</div>
                    </div>
    	        </div>
            </div>
    	</div>
        <?php if($bottom_ad){ ?>
            <div class="link-single-bottom b2-radius mg-t">
                <?php echo $bottom_ad;?>
            </div>
        <?php } ?>
        
</article>
<?php if($term){ ?>
    <div class="link-related">
        <?php
            $html = new Links();

            $arg = LinkAction::get_default_settings($term,['link_count'=>3,'link_count_total'=>6,'link_order'=>'link_rating'],true);

            echo $html->init($arg,0);
        ?>
    </div>
<?php } ?>