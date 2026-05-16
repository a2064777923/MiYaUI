<!-- 区块代码 开始 -->
<?php 
$aside_qkcode =b2_get_option('Jitheme_index_tab3','index_qukuai_list');
$index_qukuai_gg_off=b2_get_option('Jitheme_index_tab3','index_qukuai_gg_off');
$index_qukuai_taigao=b2_get_option('Jitheme_index_tab3','index_qukuai_taigao');
$qukuai_hd_title=b2_get_option('Jitheme_index_tab3','qukuai_hd_title');
$qukuai_hd_off=b2_get_option('Jitheme_index_tab3','qukuai_hd_off');
$qukuai_vip_title=b2_get_option('Jitheme_index_tab3','qukuai_vip_title');
$index_qukuai_top_sl=b2_get_option('Jitheme_index_tab3','index_qukuai_top_sl');
if(!empty($index_qukuai_top_sl)){
    $index_qukuai_sl=b2_get_option('Jitheme_index_tab3','index_qukuai_top_sl'); 
}else{
    $index_qukuai_sl=5;
}
?>


<div id="home-row-qukuai" class="home_row"> 
   <div class="wrapper">
        <div class="home-row-left content-area box b2-radius"> 
            <div class="sort b2-radius">    
                <ul class="sort-config"> 
                    <?php   
                        if(is_array($aside_qkcode)){
                                foreach ($aside_qkcode as $k => $v) {
                                    if($v['index_qukuai_img']){
                                        if(!empty($v['index_qukuai_title_hz'])){
                                            $xiaotubiao='<span class="go" style="background-color: '.$v['index_qukuai_title_hz_color'].';">'.$v['index_qukuai_title_hz'].'<i class="b2font b2-arrow-right-s-line"></i></span>';
                                        }else{
                                            $xiaotubiao='';
                                        }
                                        if($v['qukuai_img']){
                                            $qukuai_img_html='<div class="quk_img  b2-radius">'.b2_get_img(array('class'=>array('sort-config-icon'),'src'=>$v['index_qukuai_img'],)).'</div>';
                                        }else{
                                            $qukuai_img_html='<div class="item-ico  b2-radius"><i class="'.$v['qukuai_img_xmmc'].' '.$v['qukuai_img_ico'].'   b2-radius"></i></div>';
                                        }
                                        $html = '<li>
                                            <div class="sort-config-item">
                                                '.$qukuai_img_html.'
                                            <a href="'.$v['index_qukuai_links'].'" target="_blank"><p class="sort-config-title">'.$v['index_qukuai_title'].'
                                            '.$xiaotubiao.'
                                            </p>
                                            <span class="sort-config-desc">'.$v['index_qukuai_desc'].'</span> </a>
                                            </div></li> ';
                                    }
                                echo $html;
                                }
                            
                            }
                    ?>
                </ul> 
                <?php 
                    $viphtml =' 
                    <div id="userDisplayName" class="srot-mine-vips">
                    <!-- 永久会员 --> 
                    <a href="./vips" target="_blank" class="sort-vips-item"> <img class="sort-vips-icon" src="//s.ibaotu.com/next/img/new/person.b254.png" alt=""> <p class="sort-vips-tit">永久会员</p> <p class="sort-vips-tit2"><span class="sort-vips-tit2-kt" v-if="userData && userData.lv.vip.lv == \'vip3\'">已开通</span><span class="sort-vips-tit2-wk" v-else>未开通</span></p> </a> 
                    <!-- 一年会员 --> 
                    <a href="./vips" target="_blank" class="sort-vips-item"> <img class="sort-vips-icon" src="//s.ibaotu.com/next/img/new/ep.4814.png" alt=""> <p class="sort-vips-tit">年卡会员</p> <p class="sort-vips-tit2"><span class="sort-vips-tit2-kt"  v-if="userData && userData.lv.vip.lv == \'vip2\'">已开通</span><span class="sort-vips-tit2-wk" v-else>未开通</span></p> </a> 
                    <!-- 月费会员 --> 
                    <a href="./vips" target="_blank" class="sort-vips-item"> <img class="sort-vips-icon" src="//s.ibaotu.com/next/img/new/create.503f.png" alt=""> <p class="sort-vips-tit">月卡会员</p> <p class="sort-vips-tit2"><span class="sort-vips-tit2-kt"  v-if="userData && userData.lv.vip.lv == \'vip1\'">已开通</span><span class="sort-vips-tit2-wk" v-else>未开通</span></p> </a> 
                    <!-- 体验会员 --> 
                    <a href="./vips" target="_blank" class="sort-vips-item index-bjq-a"><img class="sort-vips-icon" src="//s.ibaotu.com/next/img/new/design.16dc.png" alt=""><p class="sort-vips-tit index-bjq-txt">体验会员</p><p class="sort-vips-tit2"><span class="sort-vips-tit2-kt"  v-if="userData && userData.lv.vip.lv == \'vip0\'">已开通</span><span class="sort-vips-tit2-wk" v-else>未开通</span></p></a>
                    </div>';
                    $daojishia='<div class="sort-mine-wrap">
                       <div class="srot-mine-tit">
                          <img src="'.B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg" alt="" class="srot-mine-ava" /> 
                          <span>'.$qukuai_hd_title.'</span>
                       </div>
                          <div class="timer">
                             <ul class="timer__content">
                                <li class="timer__item">
                                   <p id="_d_mc" class="timer__name">天</p>
                                   <span id="_d" class="timer__number">00</span>
                                </li>
                                <li class="timer__item">
                                   <p id="_h_mc" class="timer__name">时</p>
                                   <span id="_h" class="timer__number">00</span>
                                </li>
                                <li id="_m_mc" class="timer__item">
                                   <p class="timer__name">分</p>
                                   <span id="_m" class="timer__number">00</span>
                                </li>
                                <li class="timer__item">
                                   <p id="_s_mc" class="timer__name">秒</p>
                                   <span id="_s" class="timer__number">00</span>
                                </li>
                             </ul>
                             <a href="'.b2_get_option('Jitheme_index_tab3','qukuai_btn_link').'" class="timer_btn">
                                '.b2_get_option('Jitheme_index_tab3','qukuai_btn_title').'
                                <div class="timer_btn_jb">
                                '.b2_get_option('Jitheme_index_tab3','qukuai_btn_jb').'
                                </div>
                             </a>
                          </div>
                    </div>';
                    $daojishib='
                    <div class="jitheme_jishi_gg">
                            <div class="jitheme_jishi">'.$qukuai_hd_title.' <em id="_d">00</em>天 <em id="_h">00</em>时<em id="_m">00</em> 分<em id="_s">00</em> 秒</div>
                            
                            <a href="#" target="_blank"><img class="jitheme_jishi_img b2-radius" src="'.b2_get_option('Jitheme_index_tab3','qukuai_hd2_img').'"> </a>
                    </div>
                    ';
                ?>
            <?php if($index_qukuai_gg_off){   
                $top=b2_get_option('Jitheme_index_tab3','index_qukuai_top');
                if(!empty($top)){
                    $top = 1;
                }
            ?>
               <div class="sort-blocks"> 
                    <div class="bt-body"> 
                    <?php  if($top == 1){ ?>
                        <div id="jiheme_heml" class="wrapper">
                            <div id="jitheme_top" class="swiper-container">
                                <div class="swiper-wrapper">
                                            <?php   
                                                $aside_wuzu=b2_get_option('Jitheme_index_tab3','index_wuzu_list');
                                                if(is_array($aside_wuzu)){
                                                    foreach ($aside_wuzu as $k => $v){
                                                        $lv =$v['index_wuzu_jiaob'];
                                                        if($lv == 0 ){
                                                            $jiaobiao=  '<div class="qukuai_jb jiaobiao_color4">推荐</div>';
                                                        }elseif ($lv == 1) {
                                                            $jiaobiao=  '<div class="qukuai_jb jiaobiao_color3">热门</div>';
                                                        }elseif ($lv == 2) {
                                                            $jiaobiao=  '<div class="qukuai_jb jiaobiao_color5">活动</div>';
                                                        }elseif ($lv == 3) {
                                                            $jiaobiao=  '<div class="qukuai_jb jiaobiao_color1">精品</div>';
                                                        }else {
                                                            $jiaobiao= '<div class="qukuai_jb jiaobiao_color6">广告</div>';
                                                        }
                                                            if($v['index_wuzu_img']){
                                                                $wuzuhtml = '<div class="swiper-slide"><a class="card_gundong b2-radius" href="'.$v['index_wuzu_desc'].'" target="_blank">
                                                                                '.b2_get_img(array(
                                                                                    'class'=>array('swiper-slideimg loading b2-radius'),
                                                    								'src'=>$v['index_wuzu_img'],
                                                								)).'
                                                                                <div class="title">'.$v['index_wuzu_title'].'</div> 
                                                                                '.$jiaobiao.'
                                                                            </a></div>';
                                                            }
                                                        echo $wuzuhtml; 
                                                    }
                                                }
                                            ?>
                                </div> 
                                <div class="swiper-button-prev">
                                    <span class="jitheme_swiper_jt">
                                        <span class="b2font b2-arrow-left-s-line"></span>
                                    </span>
                                </div>
                                <div class="swiper-button-next">
                                    <span class="jitheme_swiper_jt">
                                        <span class="b2font b2-arrow-right-s-line"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php }else { ?>
                    <div id="jiheme_heml" class="wrapper">
                        <div id="jitheme_top" class="swiper-container">
                            <div class="swiper-wrapper">
                                <?php
                                $sticky = get_option('sticky_posts');
                                rsort( $sticky );
                                $sticky = array_slice( $sticky, 0, 10);
                                query_posts( array( 'post__in' => $sticky, 'caller_get_posts' => 1 ) );
                                if (have_posts()) :while (have_posts()) : the_post();
                                $thumb_url = \B2\Modules\Common\Post::get_post_thumb();
                                $thumb = b2_get_img(array('src'=>$thumb_url,'class'=>array('b2-radius')));
                                ?>
                                <div class="swiper-slide">
                                <a class="card_gundong containerrr b2-radius" href="<?php the_permalink(); ?>" target="_blank">
                                    <?php echo $thumb ?>
                                    <div class="title"><?php the_title() ?></div> 
                                    <div class="qukuai_jb jiaobiao_color4">置顶</div>
                                </a>    
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        <div class="swiper-button-prev">
                            <span class="jitheme_swiper_jt">
                                <span class="b2font b2-arrow-left-s-line"></span>
                            </span>
                        </div>
                        <div class="swiper-button-next">
                            <span class="jitheme_swiper_jt">
                                <span class="b2font b2-arrow-right-s-line"></span>
                            </span>
                        </div>
                        </div>
                    </div>
                    <?php } ?>
                    </div> 
                    <div class="srot-mine b2-radius" style="<?php echo b2_get_option('Jitheme_index_tab3','qukuai_color'); ?>"> 
                        <i class="srot-mine-bg <?php echo  $qukuai_hd_off ?>"></i> 
                        <?php
                            if($qukuai_hd_off == 1){
                            echo '<div class="sort-mine-wrap">
                                <div class="srot-mine-tit">
                                    <img src="'.B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg" alt="" class="srot-mine-ava" /> 
                                    <span>'.$qukuai_vip_title.'</span>
                                </div>
                                '.$viphtml.'
                            </div>';
                            }elseif($qukuai_hd_off == 0){
                                echo $daojishia ; 
                            }else{
                                echo $daojishib ;    
                            }    
                             ?>
                        
                    </div> 
                </div> 
                <?php }   ?>
            </div>
        </div>
    </div>
</div>
<script language="javascript"> 
var mySwiper = new Swiper('#jitheme_top',{
        centeredSlides : false,
        autoplay: {
            delay: 2000,//1秒切换一次
        },
        loop: true,
	slidesPerView : <?php echo $index_qukuai_sl ?>,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
})
</script>
<!-- 区块代码结束 -->