<?php


?>
<link rel="stylesheet" href="<?php echo B2_CHILD_URI ?>/Render/Css/imglist.css">
<div id="jitheme_index_imglist" class="  home_row home_row_6  module-posts  "
style="background-color:;">
    <div class="wrapper">
        <div class="b2-content shape">
            <div id="primary-home" class="content-area">
                <div class=" home_row home_row_0  module-posts">
                    <div class="nav-container wbrr">
                        <div class="nav-title pointer">
                            测试标题
                            <span class="yc1200">
                                天天换壁纸，时时好心情！用清新唯美的壁纸给眼镜做按摩。🌞
                            </span>
                        </div>
                        <div class="search-content">
                            <div class="tag selected yc800">
                                最新壁纸
                            </div>
                            <a href="/wallpaper/">
                                <div class="tag">
                                    横屏壁纸
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="wbrr">
                        <div class="home-row-left content-area  ">
                            <div class="post-1 post-list post-item-1 putu">
                                <div class="hidden-line">
                                    <ul class="b2_gap">
<?php 
    $link_cats = b2_get_option('Jitheme_index_tab10','index_imglist_cat');
	$ids = array();
	if($link_cats){
		foreach($link_cats as $v){
			$links = get_term_by('id', $v, 'category');
			if($links){
				$ids[] = $links->term_id;
			}
		}
	}
	$ids = implode(",", $ids);
    $cts= explode(",", $ids); 
        $args=array(
		        'cat' =>$cts,   // 分类ID
		        'posts_per_page' =>5, // 显示篇数
		    );
	       query_posts($args);
                if(have_posts()) : while (have_posts()) : the_post();
                $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');?>
                <li class="post-list-item">
                        <div class="item-in">
                            <div class="post-module-thumb yj" style="padding-top:178%"><a href="<?php the_permalink() ?>" class="thumb-link" target="_blank"><img class="post-thumb lazy loaded" data-src="<? echo $thumb[0]; ?>" src="<? echo $thumb[0]; ?>" data-was-processed="true"></a>
                            <div class="post-list-meta-box">
                                <ul class="post-list-meta shu">
                              <li class="post-list-meta-views"><span><i class="sai feng-wdscreenshot"></i>910×2048</span></li>
                              <li class="post-list-meta-like"><span><i class="sai feng-xiangmuzongshu"></i>9</span></li>
                                    </ul>
                            </div>
                            </div>
                            <div class="post-info zhzi">
                                <h2><a href="<?php the_permalink() ?>" target="_blank"><?php the_title(); ?></a></h2>
                            </div>
                        </div>
                    </li>
                    
           <?php endwhile; endif; wp_reset_query();?>
                                    </ul>
                                </div>
                            </div>
                            <div class="post-1 post-list post-item-1 pptu">
                                <div class="hidden-line">
                                    <ul class="b2_gap">
<?php 
    $link_cats = b2_get_option('Jitheme_index_tab10','index_imglist_cat02');
	$ids = array();
	if($link_cats){
		foreach($link_cats as $v){
			$links = get_term_by('id', $v, 'category');
			if($links){
				$ids[] = $links->term_id;
			}
		}
	}
	$ids = implode(",", $ids);
    $cts= explode(",", $ids); 
        $args=array(
		        'cat' =>$cts,   // 分类ID
		        'posts_per_page' =>8, // 显示篇数
		    );
	       query_posts($args);
                if(have_posts()) : while (have_posts()) : the_post();
                $thumba = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');?>
                                        <li class="post-list-item">
                                            <div class="item-in">
                                                <div class="post-module-thumb yj" style="padding-top:56.25%">
                                                    <a href="<?php the_permalink() ?>" target="_blank" class="thumb-link">
                                                        <img class="post-thumb lazy loaded" data-src="<? echo $thumba[0]; ?>" src="<? echo $thumba[0]; ?>" data-was-processed="true">
                                                    </a>
                                                    <div class="post-list-meta-box">
                                                        <ul class="post-list-meta shu">
                                                            <li class="post-list-meta-views">
                                                                <span>
                                                                    <i class="sai feng-wdscreenshot">
                                                                    </i>
                                                                    5120×2880
                                                                </span>
                                                            </li>
                                                            <li class="post-list-meta-like">
                                                                <span>
                                                                    <i class="sai feng-xiangmuzongshu">
                                                                    </i>
                                                                    19
                                                                </span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="post-info">
                                                    <h2>
                                                        <a href="https://zhutix.com/wallpaper/bloom-w/" target="_blank">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h2>
                                                </div>
                                            </div>
                                        </li>
                <?php endwhile; endif; wp_reset_query();?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>