<?php
$index_onecad_search_title = b2_get_option('Jitheme_index_tab2','index_onecad_search_title');
$index_onecad_search_desc = b2_get_option('Jitheme_index_tab2','index_onecad_search_desc');
$index_onecad_search_kuangtext = b2_get_option('Jitheme_index_tab2','index_onecad_search_kuangtext');
$index_onecad_search_mp4 = b2_get_option('Jitheme_index_tab2','index_onecad_search_mp4');
$index_onecad_search_kuangtext = b2_get_option('Jitheme_index_tab2','index_onecad_search_kuangtext');
$index_onecad_search_slider = b2_get_option('Jitheme_index_tab2','index_onecad_search_slider');
$index_onecad_search_color = b2_get_option('Jitheme_index_tab2','index_onecad_search_color');
$index_onecad_search_wloff = b2_get_option('Jitheme_index_tab2','index_onecad_search_wloff');
$index_search_cat = b2_get_option('Jitheme_index_tab2','index_search_soft_cat');
$wrapper_width = b2_get_option('template_main','wrapper_width');
$video_hight = b2_get_option('Jitheme_index_tab2','index_mp4_hight');
//WordPress整站文章访问计数
function ui_get_all_view(){
	global $wpdb;
	$count=0;
	$views= $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key='views'");
	foreach($views as $key=>$value){
		$meta_value=$value->meta_value;
		if($meta_value!=' '){
			$count+=(int)$meta_value;
		}
	}return $count;
}
if(empty($video_hight)){
    $video_hight='700px';
}
// $tongji='    网站累计下载'.ui_get_all_view().'次';
$soustext=$index_onecad_search_kuangtext;
?>
<!--首页视频区块-->
<style>
.poa {
    color: <?php echo $index_onecad_search_color ?>;
} 
p.home-banner-linkss.line-one1 { width: 80%; font-size: 14px; height:
20px; line-height: 20px; color: <?php echo $index_onecad_search_color ?>; text-shadow: 0 2px 4px rgb(0 0 0/ 27%); margin-top: 40px; text-align: center; }
</style>
<?php  
$search_off=b2_get_option('Jitheme_index_tab2','index_onecad_searchoff');
$index_onecad_search_header_off=b2_get_option('Jitheme_index_tab2','index_onecad_search_header_off');
$index_diy_search_list =b2_get_option('Jitheme_index_tab2','index_diy_search_list');
$index_diy_searchB_list =b2_get_option('Jitheme_index_tab2','index_diy_searchB_list');
$index_search_cd ='';
if($search_off == 1){ 
    $index_search_cd = index_search_cd_xf();
    // wp_enqueue_script( 'Jitheme-adminjs', B2_CHILD_URI.'/Render/Js/web.js', array(), null , true );
}
$top_navs_html ='';
if($index_onecad_search_header_off){
    $top_navs_html .='<div class="top-navs">
    <div class="wrapper layout-center Onecad_clearfix">';
        $top_navs_html .='<div class="top-navs-l fl">
        <!-- 首页搜索左长条重点展示5组 --> ';
        if(is_array($index_diy_search_list)){
            foreach ($index_diy_search_list as $k => $v) {
                $top_navs_html .='<div class="top-navs-l-item fl">
                        <p class="top-navs-l-title">
                            <a href="'.$v['index_diy_search_list_title_link'].'" target="_blank">
                            <img src="'.$v['index_diy_search_list_img'].'" alt="'.$v['index_diy_search_list_title'].'">
                            <span class="wz">'.$v['index_diy_search_list_title'].'</span>
                            </a>
                        </p>
                        <p class="top-navs-l-links">
                        '.$v['index_diy_search_list_link'].'    
                        </p>
                    </div>';
            }   
        }    
        $top_navs_html.='</div>
        <div class="top-navs-r fl Onecad_clearfix">';
        if(is_array($index_diy_searchB_list)){
            foreach ($index_diy_searchB_list as $k => $v) {
                $top_navs_html .='<a class="fl" rel="nofollow" target="_blank" href="'.$v['index_diy_searchB_list_link'].'">
                        <img src="'.$v['index_diy_searchB_list_img'].'" alt="'.$v['index_diy_searchB_list_title'].'">
                    <p>
                        '.$v['index_diy_searchB_list_title'].'
                    </p>
                </a>';
            }
            
        }
        $top_navs_html .=' </div></div>
    </div>';
}
?> 
<div class="home-banner por">
    <?php 
    $html='';
    if($index_onecad_search_slider == 1){
       $html.= '<section class="section"  style="height: '.$video_hight.';max-height: '.$video_hight.';">
            '.$index_search_cd.'
            <div class="video-wrapper">
                <video  autoplay playsinline="" loop muted="" src="'.$index_onecad_search_mp4.'">
                </video>
            </div>
            <div class="video-overlay">
            </div>
            '.$top_navs_html.'
        </section>';
    }else {
        $html.= '<section class="section">'.$index_search_cd.do_shortcode('[b2_index_module key=onecad_slider]').'</section>';
    }
    echo $html;
    ?>
    <?php 

    if($search_off == 0){?>
        <div class="wrapper  poa">
            <div class="home-banner-content Onecad_clearfix">
                <div class="slogan-text por fl">
                    <p class="big_title"><?php echo $index_onecad_search_title ?>
                    <i class="iblock corner" style="background:url(<?php echo b2_get_option('Jitheme_index_tab2','index_onecad_search_xtimg')?>) no-repeat;"></i>
                    </p>
                    <p class="promote-sub-title line-one">
                        <?php echo $index_onecad_search_desc ?>
                    </p>
                </div>
            </div>
            <div class="home-banner-search por searchv2-top-m">
                <div class="primary-menus" style=" position: unset;transform: translate(1px, 1px);">
                    <?php 
                    $html='';
                    if($index_onecad_search_wloff){
                        $html .='
                            <div class="search-types-cycles poa">
                                <ul class="selects">
                                    <li data-target="search_1">
                                        百度
                                    </li>
                                    <li data-target="search_2">
                                        Bing
                                    </li>
                                    <li data-target="search_3">
                                        分类
                                    </li>
                                    <li data-target="search_4" class="current">
                                        站内搜索
                                    </li>
                                    <li data-target="search_5">
                                        头条
                                    </li>
                                    <li data-target="search_6">
                                        知乎
                                    </li>
                                    <li data-target="search_7">
                                        360
                                    </li>
                                </ul>
                            </div>';
                    }
                    echo $html;
                    ?>
                    <div class="cont">
                        <div class="left-cont" id="jitheme_search">
                            <form class="search hidden" id="search_1" action="https://www.baidu.com/s?wd="
                            method="get" target="_blank">
                                <input type="text" name="wd" class="search_baidu" placeholder="<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn search_baidu">
                                    百度搜索
                                </button>
                            </form>
                            <form class="search hidden" id="search_2" action="https://cn.bing.com/search?q="
                            method="get" target="_blank">
                                <input type="text" name="q" class="search_bing" placeholder="Bing搜索:<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn search_bing">
                                    Bing搜索
                                </button>
                            </form>
                            <form class="search hidden" id="search_3" action="<?php bloginfo('url'); ?>"
                            method="get" target="_blank">
                                <div class="jitheme_cat_search">
                                    <?php wp_dropdown_categories(array(
                                        'hierarchical'     => true,
                                        'class'     => 'form-select',
                                        
                                    )); ?>
                                    <input type="text" name="archiveSearch" class="" placeholder="<?php echo $soustext; ?>">
                                    <button type="submit" name="" class="btn ">
                                        按分类搜
                                    </button>
                                </div>
                            </form>
                            <form class="search" id="search_4" action="<?php echo esc_url( home_url( '' ) ); ?>/?s=" method="get"
                            target="_blank">
                                <input type="text" name="s" class="s" placeholder="<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn" >
                                    站内搜索
                                </button>
                            </form>
                            <form class="search hidden" id="search_5" action="https://so.toutiao.com/search?dvpf=pc&source=input&keyword="
                            method="get" target="_blank">
                                <input type="text" name="query" class="search_toutiao" placeholder="<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn search_toutiao">
                                    头条搜索
                                </button>
                            </form>
                            <form class="search hidden" id="search_6" action="https://www.zhihu.com/search?q="
                            method="get" target="_blank">
                                <input type="text" name="q" class="search_zhihu" placeholder="<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn search_zhihu">
                                    知乎搜索
                                </button>
                            </form>
                            <form class="search hidden" id="search_7" action="https://www.so.com/s?q="
                            method="get" target="_blank">
                                <input type="text" name="q" class="search_360" placeholder="<?php echo $soustext; ?>">
                                <button type="submit" name="" class="btn search_360">
                                    360搜索
                                </button>
                            </form>
                            <?php   
                                $data =b2_get_option('Jitheme_index_tab2','index_onecad_search_key');
                                $_key ='';
                                $key=array(
                                    'key' => explode(",", $data),
                                	); //调用用户ID
                                if($data){
                                        $_key .= '<div class="tag"><i class="ico icon-fire"></i>&nbsp;热搜词：';
                                        foreach ($key['key']as $k => $v)  {
                                            $_key .= '<a href="'.home_url('/?s='.$v).' " target="_blank" >'.$v.'</a>';
                                        }
                                        $_key .= '</div>';
                                }
                                echo $_key;
                            ?> 
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($index_search_cat){
                echo index_search_cat();
            }?>
        </div>
    <?php } 
    ?>
</div>