<div class="jitheme_slide_jb jitheme-background-default b2-radius">
    <?php if(b2_get_option('Jitheme_index_tab9','index_kjdh_list')){ ?> 
        <div class="jitheme_slide_n">
            <div class="jitheme_slide_s">
                <ul>
                <?php echo index_kjdh_fl1() ?>   
                </ul>
            </div>
            <div class="jitheme_slide_y">
                <?php echo index_kjdh_fl2() ?> 
            </div>
        </div>
        <div class="jitheme_slide_ss">
            <?php echo index_kjdh_links() ?> 
        </div>
        <div class="jitheme_slide_link">
            <ul class="jitheme-container">
            <?php echo index_kjdh_wzlinks() ?>   
            </ul>
        </div>
        <?php if(b2_get_option('Jitheme_index_tab9','jitheme_index_tongji_off')){ ?>   
        <div class="jitheme_slide_tjmk">
            <div class="jitheme_slide_tj">
                <!--访问总数-->
                <li>
                    <i class="jitheme ji-k-line">
                    </i>
                    访问总数：<?php echo nd_get_all_view(); ?>
                </li>
                <!--会员总数-->
                <!--<li>-->
                <!--    <i class="iconfont icon-anquan">-->
                <!--    </i>-->
                <!--    会员总数：-->
                <!--</li>-->
                <!--文章总数-->
                <li>
                    <i class="jitheme ji-k-line">
                    </i>
                    文章总数：<?php $count_posts = wp_count_posts(); echo $published_posts =$count_posts->publish;?>
                </li>
                <!--今日发布-->
                <!-- <li><i class="iconfont -->
                <!--"></i>-->
                <!--：-->
                <!--</li>-->
                <li>
                    <i class="jitheme ji-k-line">
                    </i>
                    今日发布：<?php echo nd_get_24h_post_count(); ?>
                </li>
                <!--本周发布-->
                <li>
                    <i class="jitheme ji-k-line">
                    </i>
                    本周发布：<?php echo get_posts_count_from_last_168h(); ?>
                </li>
                <!--运行天数-->
                <li>
                    <i class="jitheme ji-artboard--line">
                    </i>
                    运行天数：<?php
                    $riqi=b2_get_option('Jitheme_index_tab9','kjdh_data');
                    echo floor((time()-strtotime($riqi))/86400); ?>
                </li>
            </div>
        </div>
        <?php } ?> 
    <?php }else{ ?> 
    <div class="alert alert-warning alert-dismissible jitheme show mb-4" role="alert"  style="width: 100%;"><p></p>
        <h4 class="alert-heading">警告首次使用，请去后台<span class="red">极主题设置-首页设置-快捷导航</span>设置好各项数据，保存即可！</h4>
        <p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></p>
    </div>
    <?php } ?> 
</div>


