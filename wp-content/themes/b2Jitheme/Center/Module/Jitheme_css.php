<?php
/**
*/
$Jitheme_css='';
// $tj_user_hang =100/b2_get_option('Jitheme_index_tab5','one_index_tj_user_hang');
$Jitheme_header_image = b2_get_option('Jitheme_main_sys','one_header_image');
$page_width = b2_get_option('template_main','wrapper_width');
$redius = b2_get_option('template_main','button_radius');
$web_color = b2_get_option('template_main','web_color');
$bg_color =b2_get_option('template_main','bg_color');
$index_jiaobiao_color1=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color1');
$index_jiaobiao_color2=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color2');
$index_jiaobiao_color3=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color3');
$index_jiaobiao_color4=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color4');
$index_jiaobiao_color5=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color5');
$index_jiaobiao_color6=b2_get_option('Jitheme_main_tab2','index_jiaobiao_color6');


$index_jiaobiao_color1_rgb=jitheme_rgb($index_jiaobiao_color1);
$index_jiaobiao_color2_rgb=jitheme_rgb($index_jiaobiao_color2);
$index_jiaobiao_color3_rgb=jitheme_rgb($index_jiaobiao_color3);
$index_jiaobiao_color4_rgb=jitheme_rgb($index_jiaobiao_color4);
$index_jiaobiao_color5_rgb=jitheme_rgb($index_jiaobiao_color5);
$index_jiaobiao_color6_rgb=jitheme_rgb($index_jiaobiao_color6);


$jianboff = b2_get_option('Jitheme_Archive_main','Onecad_listbj_off');
$onecad_Archive_list_color=b2_get_option('Jitheme_Archive_main','onecad_Archive_list_color');
$wrapper_width = b2_get_option('template_main','wrapper_width');
$index_vip = b2_get_option('normal_user','user_vip_group');
$list_margin =b2_get_option('Jitheme_main_sys','list_margin');
$list_margin_2 =intval($list_margin)*0.5.'px';
$list_margin_0 ='-'.$list_margin.'';
$box_padding=b2_get_option('Jitheme_main_sys','box_padding');
$box_padding_1='-'.$box_padding.'';
$box_padding_3=intval($box_padding)*1.8.'px';
$box_padding_2=intval($box_padding)*0.2.'px';
// $ji_0item ='-'.$list_margin.'';
// $ji_2item =intval($list_margin)*0.5.'px';
// $ji_2item_4x =intval($list_margin)*0.25.'px';
// $ji_3item ='-'.$ji_2item.'';
$jitheme_radius =b2_get_option('Jitheme_main_sys','jitheme_radius');
$head_top_cd_off =b2_get_option('Jitheme_top_main','head_top_cd_off');
$head_top_cd_ys =b2_get_option('Jitheme_top_main','head_top_cd_ys');
$one_template_top_vip_img=b2_get_option('Jitheme_top_main','one_template_top_vip_img');
$img_logo = b2_get_option('normal_main','img_logo');
$img_logo_white = b2_get_option('normal_main','img_logo_white');
$width = b2_get_option('template_main','sidebar_width');
$Jitheme_css ='
    /*常规颜色*/
    :root {
    --this-red-color: #ff5473;
    --this-red-bg: rgba(255, 84, 115, .1);
    --this-blue-color: #2997f7;
    --this-blue-bg: rgba(41, 151, 247, .1);
    --wrapper-width: '.$wrapper_width.'px;
    --ji-bg-color:var(--b2color);
    }
    /* 浅色模式 */
    :root {
    --key-hover:var(--b2color);
    --key-color: #333333;
    --main-color: #4e5358;
    --main-shadow: rgba(116, 116, 116, 0.08);
    --this-text: #626F86;
    --body-bg-color: '.$bg_color.';
    --main-bg-color: #FFF;
    --main-border-color:#e2e3e4;
    --float-btn-bg:var(--main-bg-color);
    --box-ty:0px 5px 40px 0px rgba(17,58,93,.1);
    --blur-bg: rgba(255, 255, 255, 1);
    --header-bg: var(--blur-bg);
    --header-color: var(--main-color);
    --footer-bg: var(--main-bg-color);
    --footer-color: var(--muted-2-color);
    --content:"\e925";
    --logo-url:url('.$img_logo.');
    --ji-btcolor:#f2f4f5;
    --ji-single-header:#F5F6F7;
    }
    /* 深色模式 */
    body.style-for-dark {
    --key-hover:var(--b2color);
    --b2color:#22c55e;
    --ji-border:#22c55e;
    --key-color: #cecece;
    --main-color: #a1a1a8;
    --main-shadow: rgba(24, 24, 24, 0.1);
    --this-text: #969696;
    --body-bg-color: #181818;
    --main-bg-color: #212121;
    --main-border-color:#22c55e;
    --box-ty:0px 5px 40px 0px rgba(17,58,93,.1);
    --blur-bg:#283039;
    --float-btn-bg: var(--main-bg-color);
    --header-bg: rgba(0, 0, 0, .88);
    --footer-bg: var(--main-bg-color);
    --footer-color: var(--muted-2-color);
    --content:"\ea83";
    --logo-url:url('.$img_logo_white.');
    --ji-btcolor:#181818;
    --ji-single-header:#212121;
    }
';
if (empty($box_padding) || !isset($box_padding)) {
    $Jitheme_css .= '
        :root {
            --ji--padding: 0px;
        }
        :root {
            --ji--padding: 0px;
        }
        .post-8 .b2_gap>li .item-in{
            padding:var(--ji--padding); 
        }
    	.post-excerpt,.item-in .post-info h2{
    		margin: 10px 16px;
    		border-radius: 5px;
    	}
    	@media screen and (min-width:1200px) {
    	.list-footera,.item-in .post-info h2, .post-list-meta-box,.ji-images-title {
    		margin: 5px 5px;
    		border-radius: 5px;
    	}}
    	@media screen and (max-width:768px) {
    	.list-footera,.item-in .post-info h2, .post-list-meta-box,.ji-images-title {
    		margin:8px;
    		border-radius: 5px;
    	}}
    	.post-3 .post-info {
            margin: 10px 16px;
        }
        .list-footer{
            padding:10px 16px
        }
        .mini_tips span {
            margin-left: 0px;
        }
        .post-1 .b2_gap>li .item-in::before,.post-2 .b2_gap>li .item-in::before ,.post-7 .b2_gap>li .item-in::before,.post-9 .b2_gap>li .item-in::before  {
            height: 0px;
        }
        ';
} else {
    $Jitheme_css .= '
        :root {
            --ji--padding: ' . $box_padding . ';
        }
        .post-8 .b2_gap>li .item-in{
            padding:var(--ji--padding); 
        }
    	.post-excerpt,.item-in .post-info h2{
    		margin: 10px 0px;
    		border-radius: 5px;
    	}
    	.list-footera,.item-in .post-info h2, .post-list-meta-box ,.ji-images-title{
    		margin: 10px 5px;
    		border-radius: 5px;
    	}
    	.post-3 .post-info {
            margin: 0px 0px  0px 16px;
        }
        .list-footer{
            padding:0px 5px 5px 5px
        }
        .mini_tips span {
            margin-left: 10px;
        }
        ';
}

if (empty($list_margin)) {
    $Jitheme_css .= '
        :root {
            --ji--radius: 0px;
            --ji--margin: 16px;
            --ji--margin-2: 8px;
            --ji--margin-2-: -8px;
            --ji--margin-0: -16px;
        }
        .post-3 .post-info{
            padding: var(--ji--margin) var(--ji--margin) var(--ji--margin) 0px;
        }
        .item-author .item-bottom {
        	padding: 0 16px 16px 16px;
        }
        ';
} else {
    $Jitheme_css .= '
        :root {
            --ji--radius: '.$jitheme_radius.';
            --ji--margin: ' . $list_margin . ';
            --ji--margin-2: ' . $list_margin_2 . ';
            --ji--margin-2-:-' . $list_margin_2 . ';
            --ji--margin-0:' . $list_margin_0 . ';
        }
        @media screen and (min-width:768px) {
            .jitheme-zt .item-main,#jitheme_new.plate-news ,.sort {
                padding: var(--ji--margin);
            }
        }
        ';
}
$Jitheme_css_1441='';
//条件判断
if(isset($Jitheme_header_image)){
        $Jitheme_header_image = b2_get_option('Jitheme_main_sys','one_header_image');
    }else{
         $Jitheme_header_image ='';
}
//边框判断
if (!empty($box_padding) || isset($box_padding)) {
    $Jitheme_css .= '
        ';
} 
if (b2_get_option('Jitheme_top_main','Top_menu_icon')){
    $Jitheme_css.='#jitheme-header-diy .b2-menu-1 .sub-menu-0 li a img,#jitheme-header-left .b2-menu-1 .sub-menu-0 li a img{
        display: none;
    }';
}else{
    $Jitheme_css.='#jitheme-header-diy .b2-menu-1 .sub-menu-0 li a .ico,#jitheme-header-left .b2-menu-1 .sub-menu-0 li a .ico{
        display: none;
    }';
}
// if(empty($list_margin)){
// $Jitheme_css .='
// :root {
//     --ji--radius: 0px;
//     --ji--0item: -var(--ji--margin);
//     --ji--margin: var(--ji--margin);
//     --ji--2item: 8px;
//     --ji--3item: -8px;
//     --ji--2item-4x: 4px;
// }';
// }else{
// $Jitheme_css .='
// :root {
//     --ji--radius: '.$jitheme_radius.';
//     --ji--neutral: rgba(229, 231, 235,.8);
//     --ji--0item: '.$ji_0item.';
//     --ji--margin: '.$list_margin.';
//     --ji--2item: '.$ji_2item.';
//     --ji--3item: '.$ji_3item.';
//     --ji--2item-4x: '.$ji_2item_4x.';
// }';
// }
if(!empty(b2_get_option('Jitheme_main_sys','jitheme_dian')) && b2_get_option('Jitheme_main_sys','jitheme_dian') == 1){
    $jitheme_dian = $box_padding_3;
    $Jitheme_css .='.post-1 .b2_gap>li .item-in::before,.post-2 .b2_gap>li .item-in::before ,.post-7 .b2_gap>li .item-in::before,.post-9 .b2_gap>li .item-in::before  {
            content: "";
            display: block;
            background: #fc625d;
            top: -19px;
            border-radius: 50%;
            width: 11px;
            height: 11px;
            box-shadow: 16px 0 #fdbc40, 32px 0 #35cd4b;
            margin: 0px 0px -11px;
            z-index: 2;
            position: relative;
        }';
    
}else{
    $jitheme_dian = 'var(--ji--padding)';
}
$Jitheme_css .='
.jitheme_jb_1 {
    background-image: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
    }
    .jitheme_jb_2 {
    background-image: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
    }
    .jitheme_jb_3 {
    background-image: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
    }
    .jitheme_jb_4 {
    background-image: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
    }
    .jitheme_jb_5 {
    background-image: linear-gradient(180deg, #2af598 0%, #009efd 100%);
    }
    .jitheme_jb_6 {
    background-image: linear-gradient(to top, #ff0844 0%, #ffb199 100%);
    }
    .jitheme_jb_7 {
    background-image: linear-gradient(to right, #f83600 0%, #f9d423 100%);
    }
    .jitheme_jb_8 {
    background-image: linear-gradient(-60deg, #ff5858 0%, #f09819 100%);
    }
    .jitheme_jb_9 {
    background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
    }
    #top-menu-ul li ul{
        max-width: var(--ji-wrapper-width), var(--wrapper-width);

    }
    #jitheme_arc_b .button, #jitheme_arc_b button{
        border-radius: '.$redius.';
    }
    .header .login-button button.empty{
        color:#fff;
    }
    .coll-3-box-in {
        margin: '.$list_margin_2.'!important;
    }
    .collection-box {
        margin: var(--ji--margin-2-)!important;
    }
    .jitheme-radius,.post-thumb{
        border-radius: var(--ji--radius);
    }
@media screen and (min-width: 1200px){
    .post-1 .b2_gap>li .item-in,.post-3 .b2_gap>li .item-in,.post-2 .b2_gap>li .item-in,.post-4 .b2_gap>li .item-in,.post-7 .b2_gap>li .item-in, .shop-list-item, .shop-normal-item-in, .user-search-list li > div,.archive-row .post-3.post-3-li-dubble .b2_gap>li .item-in,.archive-row .post-3 .post-3-li .item-in,.post-8 .b2_gap>li .item-in,.post-9 .b2_gap>li .item-in,.post-10 .b2_gap>li .item-in, .post-11 .b2_gap>li .item-in{
        margin-bottom: var(--ji--margin);
        margin-right: var(--ji--margin);
    }
    .post-3 .b2_gap>li .item-in,.post-4 .b2_gap>li .item-in.shop-list-item, .shop-normal-item-in, .user-search-list li > div,.archive-row .post-3.post-3-li-dubble .b2_gap>li .item-in,.archive-row .post-3 .post-3-li .item-in{
        padding:var(--ji--padding) var(--ji--padding) '.$box_padding_2.';
    }
    .post-1 .b2_gap>li .item-in,.post-2 .b2_gap>li .item-in,.post-7 .b2_gap>li .item-in,.post-9 .b2_gap>li .item-in,.post-10 .b2_gap>li .item-in, .post-11 .b2_gap>li .item-in{
        padding:'.$jitheme_dian.' var(--ji--padding) '.$box_padding_2.' var(--ji--padding) ;
    }
    .post-1 .b2_gap,.post-2 .b2_gap,.post-3 .b2_gap,.post-4 .b2_gap,.post-7 .b2_gap,.post-8 .b2_gap,.post-9 .b2_gap,.shop-box .b2_gap,.shop-normal-list, .shop-category, .user-search-list, .home-collection .collection-out,.post-8 .b2_gap,.post-10 .b2_gap, .post-11 .b2_gap{
        margin-right:  var(--ji--margin-0);
        margin-bottom:  var(--ji--margin-0);  
    }
    .item-in-video,,.Jitheme_about_main .serve_box,.Jitheme_about_main .about-selection .d-item{
        margin-bottom: var(--ji--margin-2)!important;
        margin-right: var(--ji--margin-2)!important;
    }
} 
    
    ';

if(b2_get_option('Jitheme_index_main','ji_top_tm')){
    $Jitheme_css.='
        .poa {
            top: 0;
            right: calc(50% - (var(--ji-wrapper-width, '.$wrapper_width.'px) / 2));
        }
    @media screen and (min-width:768px){
        .jiheme_home .home .header .jitheme_logo{
            background-image:url('.$img_logo_white.'); ;
        }
        .jiheme_home .home .ji_haeder .jitheme_logo{
            background-image: url('.$img_logo.');;
        }
        .style-for-dark .ji_haeder .jitheme_logo{
            background-image:url('.$img_logo_white.'); ;
        }.jiheme_home .home .header {
            background:unset!important;
        }
        .jiheme_home .home .change-theme i {
            color: #fff;
        }    
    }';
}else{
    $Jitheme_css.='
        .poa {
            top:0;
            right: calc(50% - (var(--ji-wrapper-width, '.$wrapper_width.'px) / 2));
        }';
}
if(archive_list()){
      $Jitheme_css.='@media (min-width: 768px) {#jitheme_arc_b{display:none}}';  
    }
if($jianboff == 1 || $jianboff == 2 ){
    $padding = intval($list_margin);
    $user_top=$padding*2;
    $Jitheme_css.='.item-author .item-bg .thumb-a {
    background-image: linear-gradient(120deg, #eee 0%, #eee 100%);
    }
    @media screen and (min-width:1200px){
    .jitheme-ranks .home-authors .item-tobe-author .tobe-author,.jitheme-ranks .item-author .item-top{
        margin:'.$user_top.'px  '.$list_margin.' '.$list_margin.' '.$list_margin.';
        padding: 25px 15px  10px  15px!important;
    }
    
    .jiheme_home .home .home-authors .item-tobe-author .tobe-author,.jiheme_home .home .item-author .item-top{
        margin:'.$user_top.'px '.$list_margin.' 0px '.$list_margin.';
        padding:15px 15px '.$list_margin.' 15px;
    }}';
}else{
    $Jitheme_css.='
    .jitheme-ranks .home-authors .item-tobe-author .tobe-author,.jitheme-ranks .item-author .item-top{
        margin:'.$list_margin.';
        padding: 25px 15px  0px  15px!important;
    }
    .home-authors .item-tobe-author .tobe-author,.jiheme_home .home .item-author .item-top{
        padding:var(--ji--margin)!important;
    }';
}
if(b2_get_option('Jitheme_main_sys','jitheme_line')  == 3){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.login-form-item,.homebk12 .homebk8-ctn li,.guding{
    border: 1px solid rgba(76, 87, 102, .1);
    box-shadow: 0px 4px 16px 0px rgba(22,23,47,.08);
    }';
}elseif (b2_get_option('Jitheme_main_sys','jitheme_line')  == 2){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.homebk12 .homebk8-ctn li,.guding{
    box-shadow: 0px 4px 16px 0px rgba(22,23,47,.08);
    }';
}elseif (b2_get_option('Jitheme_main_sys','jitheme_line')  == 1){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.login-form-item,.homebk12 .homebk8-ctn li,.guding{
    border: 1px solid rgba(76, 87, 102, .1)!important;
    }';
}
if(b2_get_option('Jitheme_main_sys','one_logo_saog')){
    $Jitheme_css.='.logo{position:relative;font-size:2em;font-weight:700;line-height:39px;overflow:hidden;margin:0;}.logo::before{content:"";position:absolute;width:150px;height:10px;background-color:rgba(255,255,255,.5);-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-animation:searchLights 1s ease-in 1s infinite;animation:searchLights 1s ease-in 1s infinite;}@-webkit-keyframes searchLights{0%{left:-90px;top:0;}to{left:90px;top:0;}}';
}
if (b2_get_option('Jitheme_main_sys','index_huise')){
    $Jitheme_css.='html{filter: grayscale(100%);-webkit-filter: grayscale(100%);-moz-filter: grayscale(100%);-ms-filter: grayscale(100%);-o-filter: grayscale(100%);filter:progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);}';
}
if (b2_get_option('Jitheme_index_main','index_descmk_off') == 0){
    $Jitheme_css.='.Onecad_title >div:nth-of-type(2){display: none;}';
}
// if(is_array($index_vip)){
//     foreach ($index_vip as $k => $v) {
//         $Jitheme_css.='
//             #onecad-id-'.$v['time'].' :hover span,#onecad-id-'.$v['time'].':hover strong{color: '.isset($v['color']).';}
            
//             #onecad-id-'.$v['time'].' :hover .vips_tj {background-color: '.isset($v['color']).'!important;}'; 
//     }
// }
if(b2_get_option('Jitheme_index_tab4','one_index_fenlei_kd')){
$Jitheme_css.='#onecad_archive.swiper {
      width: 100%;
      height: 80px;
      margin-top: 0px;
      margin-bottom: 20px;
    },#onecad_5zuimg.swiper {
      width: 100%;
      margin-top: 0px;
      margin-bottom: 20px;
    }';  
}else {
    $Jitheme_css.='@media screen and (min-width: 768px){#onecad_archive.swiper {
    width: '.$wrapper_width.'px;
    height: 80px;
    margin-top: 0px;
    margin-bottom: 20px;
    }#onecad_5zuimg.swiper {
    width: '.$wrapper_width.'px;
    margin-top: 0px;
    margin-bottom: 20px;
    }}';
}

if (b2_get_option('Jitheme_shop_main','shop_img_off')) {
    $ratio = b2_get_option('Jitheme_shop_main','shop_list_img');
    $ratio = explode('/',$ratio);
    $w_ratio = $ratio[0];
    $h_ratio = $ratio[1];
    $padding =round($h_ratio/$w_ratio*100,2);
    $Jitheme_css.='
    .shop-normal-item-img,.b2-widget-products .b2-widget-post-thumb .b2-widget-post-thumb-product-img {
    padding-top: '.$padding.'%!important;}';
}
if($head_top_cd_off){
   $Jitheme_css.='
   .header-banner {
    display: block;
    }
    @media screen and (min-width: 768px){
    .site-header {
        height: 100px!important;
    }
    }
    @media screen and (max-width: 768px){
    .site-header {
        height: 95px!important;
    }
    }' ;
}else {
  $Jitheme_css.='
    #jitheme_header_top02 .header-banner {
        display: none!important;
    }
    @media screen and (min-width: 768px){
    #jitheme_header_top02 .site-header {
        height: 70px;
    }
    }';  
}
echo '<style type="text/css">';
echo "
@media screen and (min-width: 1441px){
$Jitheme_css_1441
.home-authors .item,.home-authors .group-item .item-images .img-item.items, .item-author {
   width:calc((var(--b2-gap-li, 25%) - var(--box-margin, var(--ji--margin))) + 0%)
}
.home_row {
    margin-bottom:  var(--ji--margin)!important;
}
}
.jitheme_slide_tjmk {
    border-radius: 0px 0px $jitheme_radius $jitheme_radius;
}
.site-header-in::after {
    content: '';
    position: absolute;
    top: 0px;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 9;
    pointer-events: none;
    background: url($Jitheme_header_image) no-repeat top center / 1400px;
}
.jitheme_jb_0 {
$onecad_Archive_list_color
}
.home-screen,#onecad_footer_ht {
    background:$web_color;
    width: 100%;
}
.cat-info span a::after,.our-team .pic:before ,.our-team .pic:after,.our-team .social,.public-foot .tel-icon .tel-icon-a:hover,.Mrxu-block .Mrxu-link a:hover,#vips .vip-list .vip-item .vip-list-in:hover .vip-buy button,#vips .vip-in .empty,.puxin_gd .btn  {
    background: $web_color;
    color:#fff;
}
.btn-outline-dark:not(:disabled):not(.disabled).active, .btn-outline-dark:not(:disabled):not(.disabled):active, .show>.btn-outline-dark.dropdown-toggle, .go-send-email-code, .go-rest-password, .archive-filter-2 .current a, .archive-filter-2 a:hover, .pagination .current, .vipinfo-page, .btn-light:hover, .filters .dropdown-item.active, .filters .dropdown-item:active, .filters .dropdown-item:hover, .puxin-widget-catGrid .topCat .item, .puxin-widget-catGrid .bottomcat .small-item:hover, .btn-light, .puxin-widget-catGrid .bottomcat .big-item:hover {
    background-image: linear-gradient(to top, $web_color 0%, $web_color 100%);
    color: #fff;
}
.jiaobiao_color1{
    color:#fff;
    background-color:$index_jiaobiao_color1;
}
.jiaobiao_color2{
    color:#fff;
    background-color:$index_jiaobiao_color2;
}
.jiaobiao_color3{
    color:#fff;
    background-color:$index_jiaobiao_color3;
}
.jiaobiao_color4{
    color:#fff;
    background-color:$index_jiaobiao_color4;
}
.jiaobiao_color5{
    color:#fff;
    background-color:$index_jiaobiao_color5;
}
.jiaobiao_color6{
    color:#fff;
    background-color:$index_jiaobiao_color6;
}
.mini_tips .jiaobiao_color1{
    color:$index_jiaobiao_color1;
    background-color:$index_jiaobiao_color1_rgb;
}
.mini_tips .jiaobiao_color2{
    color:$index_jiaobiao_color2;
    background-color:$index_jiaobiao_color2_rgb;
}
.mini_tips .jiaobiao_color3{
    color:$index_jiaobiao_color3;
    background-color:$index_jiaobiao_color3_rgb;
}
.mini_tips .jiaobiao_color4{
    color:$index_jiaobiao_color4;
    background-color:$index_jiaobiao_color4_rgb;
}
.mini_tips .jiaobiao_color5{
    color:$index_jiaobiao_color5;
    background-color:$index_jiaobiao_color5_rgb;
}
.mini_tips .jiaobiao_color6{
    color:$index_jiaobiao_color6;
    background-color:$index_jiaobiao_color6_rgb;
}

.mg-t- {
    margin-bottom: var(--ji--margin);
}
.button, button {
    border-radius: var(--b2radius);
}
#jitheme_arc_b .bg{background-image:-webkit-linear-gradient(145deg,#20c3f2 0%,#7e39fb 100%)
  
}";

$Jitheme_css .='
#Jitheme_index_rili .rili-list-next{
    right: calc((100% - '.$wrapper_width.')/2 - 20px);
}
#Jitheme_index_rili .swiper-button-prev{
    right: auto;
    left: calc((100% - '.$wrapper_width.')/2 - 20px);
    background-image: unset;
}';
if(!empty(b2_get_option('Jitheme_index_tab10','home_new_off'))){
   $Jitheme_css .='@media screen and (min-width:1200px) {
    #jitheme_new.plate-news {
        padding: var(--ji--margin) 0;
    } }'; 
}
if(!empty(b2_get_option('Jitheme_index_tab3','home_qk_off'))){
   $Jitheme_css .='@media screen and (min-width:1200px) {
    .sort {
    padding: 26px 0;
}}'; 
}
//上方
//后面增加CSS
;
echo $Jitheme_css;
echo '</style>'
?>