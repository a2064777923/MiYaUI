<?php
/**
 * Template Name:排行榜
 * 极主题设计 QQ：860376600
 * 网址：https://www.jitheme.com
 */
 get_header();
 $ranks_title = b2_get_option('Jitheme_page_ranks','ranks_title');
 $ranks_desc = b2_get_option('Jitheme_page_ranks','ranks_desc');
 $ranks_img = b2_get_option('Jitheme_page_ranks','ranks_image');

 $ranks_rules = b2_get_option('Jitheme_page_ranks','ranks_rules');

 $ranks_select = b2_get_option('Jitheme_page_ranks','ranks_select');
 //增加设置项
 $ranks_fl_title = b2_get_option('Jitheme_page_ranks','ranks_fl_title');
 $ranks_fl_desc = b2_get_option('Jitheme_page_ranks','ranks_fl_desc');
 $ranks_rq_title = b2_get_option('Jitheme_page_ranks','ranks_rq_title');
 $ranks_rq_desc = b2_get_option('Jitheme_page_ranks','ranks_rq_desc');
 if(empty($ranks_title)){
     $ranks_title='极主题排行榜';
 }
 if(empty($ranks_desc)){
     $ranks_desc='榜单刷新时间：实时刷新';
 }
 if(empty($ranks_select)){
     $ranks_select='new';
 }
 if(empty($ranks_rules)){
    $ranks_rules='<a href="/wp-admin/admin.php?page=b2_Jitheme_page_ranks" class="red">请到《上榜规则设置项》设置</a>';
 }
 if(empty($ranks_fl_title) || empty($ranks_fl_desc) || empty($ranks_rq_title) || empty($ranks_rq_desc)){
        $ranks_fl_title = $ranks_fl_desc = $ranks_rq_title = $ranks_rq_desc = '请在后台设置';
 }
?>
<!--<div class="hbbg">-->
<!--    <div class="container">-->
<!--        <i class="hbg"></i>-->
<!--    </div>-->
<!--</div>-->
<div class="jitheme-ranks"> 
<div class="ji-rank-archive-header" style="background-image: url(<?php echo $ranks_img ?>);">
    <div class="phb_wrapper">
        <section class="wrapper ji-paih-title">
            <h5><?php echo $ranks_title ?></h5>
            <h6><?php echo $ranks_desc ?></h6>
        </section>
        <div class="wrapper header-menus b2-radius">
            <div class="menus">
                <div class="cont">
                    <div class="cats">
                        <h3 class="cont-title"><?php echo $ranks_fl_title ?> <small><?php echo $ranks_fl_desc ?></small> </h3>
                        <div class="cont-main">
                            <?php echo get_ranks_arc() ?>
                            <span class="item m-item"> 
                                <a href="/tags" target="_blank"> <i class="jitheme sangedian"></i> <em class="count">more</em> <strong class="txt">更多</strong></a> 
                            </span>
                        </div>
                    </div>
                    <div class="pops">
                        <h3 class="cont-title"><?php echo $ranks_rq_title ?> <small><?php echo $ranks_rq_desc ?></small> </h3>
                        <div class="cont-main">
                            <div class="items">
                                <?php echo get_ranks_user() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="join">
                <div class="cont">
                    <h3 class="cont-title">上榜规则</h3>
                    <p><?php echo $ranks_rules ?></p>
                    <a href="/write" target="_blank" class="btn btn-pink b2-radius">我要发布作品</a>
                </div>
            </div>
        </div>
        <div class="jitheme-rank-tab  b2-radius">
            <div class=" mi-tab-wrap">
            <div class="header-tab-item popularity active">
                <a class="sub-title new-btn ">新发布</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title popularity-btn ">活跃榜</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title activity-btn">人气榜</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title recommendation-tab">都喜欢</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title user-ranking-tab">用户榜</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title ask-ranking-tab">问答榜</a>
            </div>
            <div class="header-tab-item popularity">
                <a class="sub-title circle-ranking-tab">圈子榜</a>
            </div>
        </div>
        </div>
    </div>
</div>
<div class="jitheme-ranking">
    <div class="jitheme-rank-page wrapper">
        <div class="rank-page-content">
            <div class="rank-page-list jitheme-content-block">
                <div class="jitheme-list rank-list tab-content new-tab active">
                    <?php echo get_post_list_results('post-1', 'new', 20, 4); ?>
                </div>
                <div class="jitheme-list rank-list tab-content popularity-tab  ">
                    <?php echo get_post_list_results('post-3', 'comments', 20, 2); ?>
                </div>
                <div class="jitheme-list rank-list tab-content activity-tab">
                    <?php echo get_post_list_results('post-3', 'views', 20, 2); ?>
                </div>
                <div class="jitheme-list rank-list tab-content recommendation-tab">
                    <?php echo get_post_list_results('post-3', 'like', 20, 2); ?>
                </div>
                <div class="jitheme-list home rank-list tab-content user-ranking-tab">
                    <div id="Onecad-tuijian" class="home_row module-posts ">
    					<div id="user-list"  class="home-authors authors_jitheme" ref="searchUser">
                            <div class="wrapper" >
                                <div  class="part-content">
                                    <div class="items author-items">
                                        <?php 
                                            $args = array(
                                                'number' => 12,
                                                'orderby' => 'post_count comment_count',
                                                'order' => 'DESC'
                                            );
                                            $i=0;
                                            $user_html='';
                                            $users = get_users($args);
                                            foreach ($users as $user) {
                                                $user_data = B2\Modules\Common\User::get_user_public_data($user->ID);
                                                $user_lv = B2\Modules\Common\User::get_user_lv($user->ID);
                                                $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                                                $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                                                $tips = __('这个人很懒，什么都没有留下！','b2');
                                                $followers = get_user_meta($user->ID,'zrz_followed',true);
                                                $followers = is_array($followers) ? count($followers) : 0;
                                                $following = get_user_meta($user->ID,'zrz_follow',true);
                                                $following = is_array($following) ? count($following) : 0;
                                                $title = get_user_meta($user->ID,'b2_title',true);
                                                $desc = get_the_author_meta( 'description', $user->ID );
                                                $post_user = count_user_posts($user->ID,'post',true);
                                                $post_comment = B2\Modules\Common\Comment::get_user_comment_count($user->ID);
                                                $ids[] = $user->ID;
                                                $i++;
                                                
                                                if (!empty($post_user)) {
                                                    $influence = ($post_user + $post_comment + $followers) * 11;
                                                    $output = '<span>'.$influence.'</span>';
                                                } else {
                                                    $output = '<span>0</span>';
                                                }
                                                
                                                $user_html.='<div id="item-author" class="item item-author" >
                                                    <div class="item-wrap box b2-radius">
                                                        '.jithem_jianbian().'
                                                        <div class="item-top b2-radius">
                                                            <a class="author-intro" href="'.$user_data['link'].'" target="_blank">
                                                                <div class="author-avatar">
                                                                '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                                                '.($user_data['user_title'] ? $user_data['verify_icon'] : '').'
                                                                </div>
                                                                <div class="author-main">
                                                                    <h2 class="author-name">
                                                                        <span class="uname" title="'.$user_data['name'].'">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span>
                                                                    </h2>
                                                                    <h3 class="author-meta">
                                                                        <span><i class="b2font b2-hearts-line"></i>
                                                                            粉丝 '.$followers.'
                                                                        </span>
                                                                        <span class="red"><i class="jitheme jitheme-huo"></i>
                                                                            人气 '.$output.'
                                                                        </span>
                                                                    </h3>
                                                                </div>
                                                            </a>
                                                            <div class="author-info">
                                                                <p><i class="b2font b2-file-list-2-line"></i>'.(!empty($user_data['user_title']) ? $user_data['user_title'] : (!empty($user_data['desc']) ? $user_data['desc'] : $tips)).'</p>
                                                            
                                                                <div class="author-btn">
                                                                    <div class="btn btn-orange" data-component="follow" data-count="471" data-original-count="471"data-login="need" data-uid="21727">
                                                                        <div class="user-s-follow jitheme-button">
                                                                        <button class="author-has-follow red-color" v-if="follow['.$user->ID.'] === true" v-cloak @click="followAc('.$user->ID.')"><i class="b2font b2-heart-fill"></i>'.__('已关注','b2').'</button>
                                                                        <button class="empty  red-color" v-else v-cloak @click="followAc('.$user->ID.')"><i class="b2font b2-add-line "></i>'.__('关注','b2').'</button>
                                                                        <button class="blue-color" @click="dmsg('.$user->ID.')"><i class="b2font b2-mail-send-line "></i>'.__('私信','b2').'</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="item-bottom">
                                                            <h3 class="item-bottom-title">TA的动态</h3>
                                                            <div class="item-bottom-cont">
                                                                <div class="items">';
                                                                    $query = new WP_Query(
                                                                        array(
                                                                            'author' => $user->ID,
                                                                            'posts_per_page' =>2,
                                                                        )
                                                                    );
                                                                    $posts = $query->posts;
                                                                    if(!empty($posts)){
                                                                        foreach($posts as $k => $p):
                                                                            $thumb_url = \B2\Modules\Common\Post::get_post_thumb($p->ID);
                                                                            $thumb = b2_get_img(array('src'=>$thumb_url));
                                                                                foreach((get_the_category($p->ID)) as $category){
                                                                                    $wenzfl=$category->cat_name; 
                                                                                    }
                                                                            $user_html.='<div class="ap-item">
                                                                            <a class="ap-item-wrap has-thumb" href="'.get_permalink($p->ID).'"target="_blank">
                                                                                <div class="ap-item-thumb  b2-radius">
                                                                                    '.$thumb.'
                                                                                </div>
                                                                                <div class="ap-item-main">
                                                                                    <h3 class="ap-item-title">'.$p->post_title.'</h3>
                                                                                    <h4 class="ap-item-meta">
                                                                                        <div class="jitheme_cat"><div class="post-list-cat">'.$wenzfl.'</div></div>
                                                                                    </h4>
                                                                                </div>
                                                                            </a>
                                                                            </div>';
                                                                        endforeach; 
                                                                    }else{
                                                                	    $user_html.='<div class="user-item">
                                                                	                    <div class="tobe-author-wrap">
                                                                	                        <div class="post-info  b2-radius">
                                                                	                            <h2 class="title">暂无文章</h2> 
                                                                	                            <div class="post-excerpt">
                                                                	                                <p>目前他暂无发布任何文章，了解更多，请前往个人中心查看。</p>
                                                                	                           </div> 
                                                                	                           <div class="item-btns"><a class="jitheme-jb-btn" href="'.$user_data['link'].'" target="_blank">个人中心</a></div>
                                                                	                       </div>
                                                                	                   </div>
                                                                	               </div>';
                                                                	}
                                                                    $user_html.='
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>';
                                            }  
                                            unset($users_found);
                                            unset($users);
                                            wp_localize_script( 'b2-js-main', 'b2_search_data', array(
                                                'users'=>$ids
                                            ));   
                                            echo $user_html;
                                        ?>
                                    </div>    
                                </div>
                            </div>
    				    </div>
                    </div>
                </div>
                <div id="jitheme-ranks-ask" class="jitheme-list rank-list tab-content ask-tab">
                    <?php echo get_ranks_ask(12) ?>
                </div>
                <div class="jitheme-list rank-list tab-content circle-tab">
                    <?php echo get_ranks_circle(12) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('.new-btn').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            $('.tab-content').removeClass('active');
            $('.new-tab').addClass('active');
        });
            

        $('.popularity-btn').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            $('.tab-content').removeClass('active');
            $('.popularity-tab').addClass('active');
        });
    
        $('.activity-btn').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            $('.tab-content').removeClass('active');
            $('.activity-tab').addClass('active');
        });
        
        $('.recommendation-tab').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            $('.tab-content').removeClass('active');
            $('.recommendation-tab').addClass('active');
        });
        
        $('.user-ranking-tab').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            
            $('.tab-content').removeClass('active');
            $('.user-ranking-tab').addClass('active');
        });
        $('.ask-ranking-tab').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            
            $('.tab-content').removeClass('active');
            $('.ask-tab').addClass('active');
        });
        $('.circle-ranking-tab').click(function() {
            $('.mi-tab-wrap div').removeClass('active');
            $(this).parent().addClass('active');
            
            $('.tab-content').removeClass('active');
            $('.circle-tab').addClass('active');
        });
    });
</script>

<?php

get_footer();