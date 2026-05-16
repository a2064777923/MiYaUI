<?php   
//获得数据 
global $lithemeMessageMethod;
    $msg_off=b2_get_option('Jitheme_index_tab10','msg_off');
    $array = b2_get_option('Jitheme_index_tab10','msg_nr');
    if (empty($array) || !is_array($array)) {
        $array = ['all'];
    }
if($msg_off == 0 || empty($msg_off)){
?>     
    <!-- 轮播消息样式1 -->
    <div  id="jitheme_new_ht"  class="home_row">
        <div class="wrapper">
            <div class="swiper-dynamic box b2-radius">
                <div class="float-left"><span class="badge-danger"><i class="jitheme jitheme-volume-up-line"></i> 网站动态</span>
                    <div id="jithemeid_box" class="scroll-dynamic">
                        <?php 
                        $jitheme_new_b = $lithemeMessageMethod->get_message_list($array, 1, 15);
                        if($jitheme_new_b['message'] && is_array($jitheme_new_b['message'])){
                            $New_a_html = '';
                            foreach($jitheme_new_b['message'] as $item){
                                $post_data = \B2\Modules\Templates\Modules\Posts::get_post_metas($item['post_id']);
                                $user_data = B2\Modules\Common\User::get_user_public_data($item['user_id']);
                                $user_lv = B2\Modules\Common\User::get_user_lv($item['user_id']);
                                $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                                $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                                if($item['type']  == 'register'){//注册用户
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$user_data['link'].'">欢迎<span class="new_span green">'.$user_data['name'].'</span><b>加入本站</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'vote_up'){//点赞了文章
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="new_span green">'.$user_data['name'].'</span><b>点赞文章<em class="red">'.$post_data['title'].'</em></b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_mission'){//签到
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$user_data['link'].'"><span class="new_span green">'.$user_data['name'].'</span><b>签到获取</b><span class="jitheme_text_sx jitheme_text"><i class="b2font b2-coin-line "></i>'.$item['meta']['credit_total'].'</span>点积分<span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_comment'){//评论
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.$user_data['name'].'</span>评论<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_shop'){//购买商品
                                    $shop_price_credit=get_post_meta($item['post_id'],'shop_price_credit',true);
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.substr_cut($user_data['name']).'</span>成功购买商品<b  class="new_span red">'.$post_data['title'].'</b> 奖励<span class="jitheme_text_sx jitheme_text"><i class="b2font b2-coin-line "></i>'.$shop_price_credit.'</span>点积分<span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_vip'){//开通VIP
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$user_data['link'].'">谢谢<span class="new_span green">'.substr_cut($user_data['name']).'</span>开通<b  class="new_span red">'.$item['meta']['vip_name'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_po_ask'){//回答问答
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.$user_data['name'].'</span>发布问答<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_best_answer'){//确定最佳答案
                                    $gold_type='<i class="b2font b2-coin-line"></i>'.$item['meta']['credit_total'].'点积分';
                                    if($item['meta']['gold_type'] == 1){
                                        $gold_type=$item['meta']['credit_total']*.01;
                                        $gold_type=B2_MONEY_SYMBOL.$gold_type.B2_MONEY_NAME;
                                    }
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'">智者<span class="green">'.$user_data['name'].'</span>在问答<b  class="new_span red">'.$post_data['title'].'</b> 的回答被采纳奖励<span class="jitheme_text_sx jitheme_text">'.$gold_type.'</span><span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_po_answer'){//参与回答了
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.$user_data['name'].'</span>回答<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_po_circle'){//发布了圈子
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.$user_data['name'].'</span>发布圈子<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_buy_download'){//成功下载了
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.substr_cut($user_data['name']).'</span>成功下载<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_buy_hidden'){//购买了隐藏内容
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.substr_cut($user_data['name']).'</span>购买隐藏内容<b  class="new_span red">'.$post_data['title'].'</b> <span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }elseif($item['type']  == 'user_ds'){//打赏
                                    $New_a_html .='<div class="'.$item['type'].'">
                                            <li><a rel="bookmark" href="'.$post_data['link'].'"><span class="green">'.substr_cut($user_data['name']).'</span>对文章<b  class="new_span red">'.$post_data['title'].'</b>打赏<span class="jitheme_text_sx jitheme_text">'.B2_MONEY_SYMBOL.$item['meta']['order_price']. B2_MONEY_NAME.'</span><span class="badge badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</span></a></li>
                                        </div>';    
                                }             
                            }
                        }    
                        ?>
                        <ul>
                            <?php echo $New_a_html ?>
                        </ul>
                    </div>
                </div>
            <span class="float-right d-none d-lg-block">
                    <small class="mr-2">今日发布：<span class="badge jitheme_text_bg jitheme_text"><?php echo nd_get_24h_post_count(); ?></span></small>
                    <small class="mr-2">本周：<span class="badge jitheme_text jitheme_text_bg"><?php echo get_posts_count_from_last_168h(); ?></span></small>
                    <small class="mr-2">总数：<span class="badge jitheme_text jitheme_text_bg"><?php $count_posts = wp_count_posts(); echo $published_posts =$count_posts->publish;?></span></small>
                    <small class="mr-2">会员数：<span class="badge jitheme_text jitheme_text_bg">
                    <?php 	global $wpdb;
                    	$users = $wpdb->get_var("select count(id) from $wpdb->users");
                    	echo $users ?>
                    </span>人</small>
        
                    </span>
            </div>        
        </div>
        <script type="text/javascript">
         $(function () {
         setInterval("noticeUp('.scroll-dynamic ul','-25px',500)", 3000);
         });
        </script>
    </div>
<?php  }elseif($msg_off == 1){ ?>
    <!-- 轮播消息样式2 -->
    <div class=" home_row">
        <div class="wrapper">
            <div id="jitheme_new" class="plate-item plate-news box  b2-radius">
            <div class="swiper-container swiper-container-vertical" id="newsOrange">
                <div class="swiper-wrapper">
                    <?php
                    //第一个滚动box
                    $jitheme_new_a = $lithemeMessageMethod->get_message_list(['register', 'user_mission'], 1, 15);
                    if($jitheme_new_a['message'] && is_array($jitheme_new_a['message'])){
                        $A_html='';
                        foreach($jitheme_new_a['message'] as $item){
                            $user_data = B2\Modules\Common\User::get_user_public_data($item['user_id']);
                            $user_lv = B2\Modules\Common\User::get_user_lv($item['user_id']);
                            $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                            $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                            if($item['type']  == 'register'){
                                $A_html .='<div class="swiper-slide news-item orange">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                    <div class="new_fl">
                                        <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                        <p class="txt-nowrap-ellipsis">欢迎新用户入驻本站<em class="red">热烈欢迎！！</em></p>
                                    </div>
                                </div>';    
                            }elseif($item['type']  == 'user_mission'){
                                $A_html .='<div class="swiper-slide news-item orange">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <p class="txt-nowrap-ellipsis">签到获取<em class="red">'.$item['meta']['credit_total'].'</em>点积分</p>
                                     </div>
                                 </div>';    
                            }
                        }
                     echo $A_html;
                    }    
                    ?>                  
                </div>
            </div>
            <div class="swiper-container swiper-container-vertical" id="newsBlue">
                <div class="swiper-wrapper">
                    <?php
                    //第二个滚动box
                    $jitheme_new_a = $lithemeMessageMethod->get_message_list(['user_comment', 'vote_up','user_po_answer','user_po_ask','user_po_circle'], 1, 20);
                    if($jitheme_new_a['message'] && is_array($jitheme_new_a['message'])){
                        $B_html='';
                        foreach($jitheme_new_a['message'] as $item){
                            $user_data = B2\Modules\Common\User::get_user_public_data($item['user_id']);
                            $user_lv = B2\Modules\Common\User::get_user_lv($item['user_id']);
                            $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                            $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                            $post_data = \B2\Modules\Templates\Modules\Posts::get_post_metas($item['post_id']);
                            
                            if($item['type']  == 'user_comment'){
                                $B_html .='<div class="swiper-slide news-item blue">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                    <div class="new_fl">
                                        <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                        <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">评论了<em class="red">'.$post_data['title'].'</em></a>
                                    </div>
                                </div>';    
                            }elseif($item['type']  == 'vote_up'){
                                $B_html .='<div class="swiper-slide news-item blue">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">点赞了文章<em class="red">'.$post_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_po_answer'){
                                $ask_data =\B2\Modules\Templates\Modules\Posts::get_post_metas($item['meta']['ask_id']);
                                $B_html .='<div class="swiper-slide news-item blue">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$ask_data['link'].'" class="txt-nowrap-ellipsis">回答了<em class="red">'.$ask_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_po_ask'){
                                $B_html .='<div class="swiper-slide news-item blue">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">发布问答<em class="red">'.$post_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_po_circle'){
                                $B_html .='<div class="swiper-slide news-item blue">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.$user_data['name'].'" class="uname">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">发布问答<em class="red">'.$post_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }
                        }
                     echo $B_html;
                    }    
                    ?> 
                </div>
            </div>
            <div class="swiper-container swiper-container-vertical" id="newsGreen">
                <div class="swiper-wrapper">
                    <?php
                    //第三个滚动box
                    $jitheme_new_a = $lithemeMessageMethod->get_message_list(['user_vip', 'user_shop', 'user_buy_download', 'user_buy_hidden','user_ds'], 1, 15);
                    if($jitheme_new_a['message'] && is_array($jitheme_new_a['message'])){
                        $C_html='';
                        foreach($jitheme_new_a['message'] as $item){
                            $user_data = B2\Modules\Common\User::get_user_public_data($item['user_id']);
                            $user_lv = B2\Modules\Common\User::get_user_lv($item['user_id']);
                            $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                            $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                            $post_data = \B2\Modules\Templates\Modules\Posts::get_post_metas($item['post_id']);
                            if($item['type']  == 'user_vip'){
                                $C_html .='<div class="swiper-slide news-item green">
                                     '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                       <h6><span class="author-name"><span title="'.substr_cut($user_data['name']).'" class="uname">'.substr_cut($user_data['name']).'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <p class="txt-nowrap-ellipsis">开通了<em class="red">'.$item['meta']['vip_name'].'</em></p>
                                        
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_shop'){
                                
                                // $gold_type_3='<i class="b2font b2-coin-line"></i>'+$item['meta']['credit_total']+'点积分';
                                // if($item['meta']['gold_type'] == 1){
                                //     $gold_type_3=$item['meta']['credit_total']*.01;
                                //     $gold_type_3=B2_MONEY_SYMBOL.$gold_type_3.B2_MONEY_NAME;
                                // }
                                $shop_price_credit=get_post_meta($item['post_id'],'shop_price_credit',true);
                                if($shop_price_credit == ''){
                                    $credit_html='';
                                }else {
                                    $credit_html='奖励<span class="jitheme_text_sx jitheme_text"><i class="b2font b2-coin-line"></i>'.$shop_price_credit.'</span>';
                                }
                                $C_html .='<div class="swiper-slide news-item green">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.substr_cut($user_data['name']).'" class="uname">'.substr_cut($user_data['name']).'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">成功购买商品<em class="red">'.$post_data['title'].'</em>'.$credit_html.'</a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_buy_download'){
                                $C_html .='<div class="swiper-slide news-item green">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.substr_cut($user_data['name']).'" class="uname">'.substr_cut($user_data['name']).'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">成功下载了<em class="red">'.$post_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_buy_hidden'){
                                $C_html .='<div class="swiper-slide news-item green">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.substr_cut($user_data['name']).'" class="uname">'.substr_cut($user_data['name']).'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">购买了隐藏内容<em class="red">'.$post_data['title'].'</em></a>
                                     </div>
                                 </div>';    
                            }elseif($item['type']  == 'user_ds'){
                                $C_html .='<div class="swiper-slide news-item green">
                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                     <div class="new_fl">
                                         <h6><span class="author-name"><span title="'.substr_cut($user_data['name']).'" class="uname">'.substr_cut($user_data['name']).'</span><span class="long-label">'.$user_vip.$user_lv.'</span><i class="fr">'.b2_timeago($item['create_time']).'</i></h6>
                                         <a target="_blank" href="'.$post_data['link'].'" class="txt-nowrap-ellipsis">对文章<em class="red">'.$post_data['title'].'</em>打赏了<span class="jitheme_text_sx jitheme_text">'.B2_MONEY_SYMBOL.$item['meta']['order_price']. B2_MONEY_NAME.'</span></a>
                                     </div>
                                 </div>';    
                            }
                        }
                     echo $C_html;
                    }    
                    ?>
                </div>
            </div>
        </div>
        </div>
    </div>
<script>
        /*消息列表-----start*/
        var swiperOrange = new Swiper('#newsOrange', {
            direction: 'vertical',  // 垂直轮播
            loop : true,    //切换效果 循环
            autoplay: {
                delay: 3000,//1秒切换一次
            },
            // mousewheel: {
            //     eventsTarged: '#newsOrange',
            //     // eventsTarged: 'body', 鼠标在页面中任意地方都可控制swiper
            // }
            autoplayPauseOnMouseEnter: true,
            autoplayDisableOnInteraction: false,
        });
        // 鼠标放置悬停效果
        $('#newsOrange').mouseenter(function () {
            swiperOrange.stopAutoplay();
        })
        $('#newsOrange').mouseleave(function () {
            swiperOrange.startAutoplay();
        })
    
        var swiperBlue = new Swiper('#newsBlue', {
            direction: 'vertical',
            loop : true,    //切换效果 循环
            autoplay: {
                delay: 4000,//1秒切换一次
            },
            autoplayPauseOnMouseEnter: true,
            autoplayDisableOnInteraction: false,
        });
        // 鼠标放置悬停效果
        $('#newsBlue').mouseenter(function () {
            swiperBlue.stopAutoplay();
        })
        $('#newsBlue').mouseleave(function () {
            swiperBlue.startAutoplay();
        })
    
        var swiperGreen = new Swiper('#newsGreen', {
            direction: 'vertical',
            loop : true,    //切换效果 循环
            autoplay: {
                delay: 3500,//1秒切换一次
            },
            // mousewheel: {
            //     eventsTarged: '#newsGreen',
            //     // eventsTarged: 'body', 鼠标在页面中任意地方都可控制swiper
            // }
            autoplayPauseOnMouseEnter: true,
            autoplayDisableOnInteraction: false,
        });
        // 鼠标放置悬停效果
        $('#newsGreen').mouseenter(function () {
            swiperGreen.stopAutoplay();
        })
        $('#newsGreen').mouseleave(function () {
            swiperGreen.startAutoplay();
        })
        /* 消息列表-----end */
</script>
<?php }

?>