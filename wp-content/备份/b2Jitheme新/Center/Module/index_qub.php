<?php
$lithemeMessageMethod = new PrivateLithemeMessage();
$mini_msg = $lithemeMessageMethod->get_message_list('all', 1, 15);
if($mini_msg['message'] && is_array($mini_msg['message'])){
    $New_html = ''; 
    foreach($mini_msg['message'] as $item){
        $post_data = \B2\Modules\Templates\Modules\Posts::get_post_metas($item['post_id']);
        $user_data = B2\Modules\Common\User::get_user_public_data($item['user_id']);
        $user_lv = B2\Modules\Common\User::get_user_lv($item['user_id']);
        $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
        $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
        if($item['type']  == 'register'){//注册用户
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">欢迎：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">成为我们的新用户</div></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';  
        }elseif($item['type']  == 'vote_up'){//点赞了文章
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">点赞文章</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';    
        }elseif($item['type']  == 'user_mission'){//签到
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">签到获取</div><span class="msg_post red"><i class="b2font b2-coin-line "></i>'.$item['meta']['credit_total'].'</span>点积分</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>'; 
        }elseif($item['type']  == 'user_comment'){//评论
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">评论文章</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';   
        }elseif($item['type']  == 'user_shop'){//购买商品
            $shop_price_credit=get_post_meta($item['post_id'],'shop_price_credit',true);
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">购买了商品</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';    
        }elseif($item['type']  == 'user_vip'){//开通VIP
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">铁粉：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">开通了VIP</div></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';   
        }elseif($item['type']  == 'user_po_ask'){//回答问答
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">智者：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">发布了</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';    
        }elseif($item['type']  == 'user_best_answer'){//确定最佳答案
            $gold_type='<i class="b2font b2-coin-line"></i>'.$item['meta']['credit_total'].'点积分';
            if($item['meta']['gold_type'] == 1){
                $gold_type=$item['meta']['credit_total']*.01;
                $gold_type=B2_MONEY_SYMBOL.$gold_type.B2_MONEY_NAME;
            }
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">智者：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">的回答被采纳奖励</div><span class="msg_post red">'.$gold_type.'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>'; 
        }elseif($item['type']  == 'user_po_answer'){//参与回答了
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">智者：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">参与回答</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>'; 
        }elseif($item['type']  == 'user_po_circle'){//发布了圈子
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">圈友：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">发布圈子</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';    
        }elseif($item['type']  == 'user_buy_download'){//成功下载了
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">成功下载</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';   
        }elseif($item['type']  == 'user_buy_hidden'){//购买了隐藏内容
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">用户：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">购买隐藏内容</div><span class="msg_post red">'.$post_data['title'].'</span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';     
        }elseif($item['type']  == 'user_ds'){//打赏
            $New_html .='<div class="'.$item['type'].'">
                    <li><a rel="bookmark" href="'.$post_data['link'].'" >
                            <div>'.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'</div>
                            <div class="mas_name"><div class="mas_tis">感谢：</div><div class="green">'.substr_cut($user_data['name']).'</div></div>
                            <div class="mas_title"><div class="mas_txt">打赏了<span class=" red">'.$post_data['title'].'<em>'.B2_MONEY_SYMBOL.$item['meta']['order_price']. B2_MONEY_NAME.'</em></span></div>
                            <div class="badge-secondary-lighten ml-1">'.b2_timeago($item['create_time']).'</div>
                        </a>
                    </li>
                </div>';    
        }             
    }
}






//fenlei

$qkb_list =b2_get_option('Jitheme_index_tab12','index_qukb_list');
if(is_array($qkb_list)){
    $fenlei_html = '';
    foreach ($qkb_list as $k => $v) {
        $html='';
        $fenlei_html .= '<li class="fl-li b2-radius"><div class="p1">
                            <a href="'.isset($array['link']).'" target="_blank">
                                <img src="'.$v['img'].'" alt="'.$v['title'].'">
                                <span>
                                    <i class="hot">'.$v['title'].'</i>
                                </span>
                            </a>
                            <i class="item-btn"><i class="ico b2font b2-arrow-right-s-line "></i></i>
                            </div>';
        $key = new B2\Modules\Templates\Modules\Search();                    
        $key =$key->str_to_array($v['qukb_key']);
        foreach ($key as $_v) {
            $_v = trim($_v, " \t\n\r\0\x0B\xC2\xA0");
            $array= explode("|", $_v);
            $url = $array[0];
            $text = $array[1];
            $html .= '<span><a target="_blank" href="'.$url.'" target="_blank">'.$text.'</a></span>';
        }
        $fenlei_html .= '<div class="p2">
                            <div class="espic">
                                '.$html.'
                            </div>
                    </div></li>';  
    }
}
?>
<div id='Mini_qukb' class="content-area">
    <div class="box Mini_qk b2-radius">
        <div class="syncont">
            <div class="synfl">
                <ul>
                    <?php echo $fenlei_html; ?>
                </ul>
                <div class="mini_quk_box b2-radius">
                    <div class="left">
                        <ul>
                            <li>
                                <p class="p1">素材总量：</p>
                                <p class="p2"><?php $count_posts = wp_count_posts(); echo $published_posts =$count_posts->publish;?><i>个</i></p>
                            </li>
                            <li>
                                <p class="p1">本周发布：</p>
                                <p class="p2"><?php echo get_posts_count_from_last_168h(); ?><i>个</i></p>
                            </li>
                            <li>
                                <p class="p1">今日发布：</p>
                                <p class="p2"><?php echo nd_get_24h_post_count(); ?><i>个</i></p>
                            </li>
                            <li>
                                <p class="p1">活跃用户：</p>
                                <p class="p2"><?php 	global $wpdb;
                	$users = $wpdb->get_var("select count(id) from $wpdb->users");
                	echo $users ?><i>人</i></p>
                            </li>
                        </ul>
                    </div>
                    <div class="synscroll">
                        <span>
                            <div class="tit">网站动态</div>
                            <div class="bd">
                                <div class="tempWrap"  style="overflow:hidden; position:relative; height:25px">
                                    <ul id="Mini_msg_list" class="Mini_msg_list">
                                        <?php echo $New_html ?>
                                    </ul>
                                </div>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style id='parent-style-inline-css' type='text/css'>

</style>
<script type="text/javascript">
 $(function () {
 setInterval("Mini_msg('.Mini_msg_list','-25px',500)", 3000);
 });
</script>