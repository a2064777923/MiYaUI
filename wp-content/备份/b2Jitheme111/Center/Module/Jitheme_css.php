<?php
/**
*/
$Jitheme_css='';
// $tj_user_hang =100/b2_get_option('Jitheme_index_tab5','one_index_tj_user_hang');
$Jitheme_header_image = b2_get_option('Jitheme_main_tab1','one_header_image');
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
$jianboff = b2_get_option('Jitheme_Archive_main','Onecad_listbj_off');
$onecad_Archive_list_color=b2_get_option('Jitheme_Archive_main','onecad_Archive_list_color');
$wrapper_width = b2_get_option('template_main','wrapper_width');
$index_vip = b2_get_option('normal_user','user_vip_group');
$ji_1item =b2_get_option('Jitheme_main_tab1','jitheme_padding');
$list_padding=b2_get_option('Jitheme_main_tab1','mokuai_padding');
$ji_0item ='-'.$ji_1item.'';
$ji_2item =intval($ji_1item)*0.5.'px';
$ji_2item_4x =intval($ji_1item)*0.25.'px';
$ji_3item ='-'.$ji_2item.'';
$jitheme_radius =b2_get_option('Jitheme_main_tab1','jitheme_radius');
$head_top_cd_off =b2_get_option('Jitheme_top_main','head_top_cd_off');
$head_top_cd_ys =b2_get_option('Jitheme_top_main','head_top_cd_ys');
$one_template_top_vip_img=b2_get_option('Jitheme_top_main','one_template_top_vip_img');
$img_logo = b2_get_option('normal_main','img_logo');
$img_logo_white = b2_get_option('normal_main','img_logo_white');
$Jitheme_top_login=b2_get_option('Jitheme_top_login','Jitheme_top_login_img');
$width = b2_get_option('template_main','sidebar_width');
$Jitheme_css.='
    /*常规颜色*/
    :root {
    --this-red-color: #ff5473;
    --this-red-bg: rgba(255, 84, 115, .1);
    --this-blue-color: #2997f7;
    --this-blue-bg: rgba(41, 151, 247, .1);
    --wrapper-width: '.$wrapper_width.'px;
    }
    /* 浅色模式 */
    :root {
    --key-color: #091E42;
    --main-color: #4e5358;
    --main-shadow: rgba(116, 116, 116, 0.08);
    --this-text: #626F86;
    --body-bg-color: '.$bg_color.';
    --main-bg-color: #FFF;
    --muted-border-color: rgba(0, 0, 0, 0.03);
    --main-border-color: #F1F2F4;
    --float-btn-bg:var(--main-bg-color);
    --box-ty:0px 5px 40px 0px rgba(17,58,93,.1);
    --blur-bg: rgba(255, 255, 255, 1);
    --header-bg: var(--blur-bg);
    --header-color: var(--main-color);
    --footer-bg: var(--main-bg-color);
    --footer-color: var(--muted-2-color);
    --content:"\e690";
    --logo-url:url('.$img_logo.');
    --ji--neutral: rgba(55,65,81,.8);
    }
    /* 深色模式 */
    body.style-for-dark {
    --key-color: #DEE4EA;
    --main-color: #a1a1a8;
    --main-shadow: rgba(24, 24, 24, 0.1);
    --this-text: #738496;
    --body-bg-color: #101214;
    --main-bg-color: #161A1D;
    --main-border-color: rgba(114, 114, 114, 0.1);
    --muted-border-color: rgba(184, 184, 184, 0.02);
    --box-ty:0px 5px 40px 0px rgba(17,58,93,.1);
    --blur-bg:#283039;
    --float-btn-bg: var(--main-bg-color);
    --header-bg: rgba(0, 0, 0, .88);
    --footer-bg: var(--main-bg-color);
    --footer-color: var(--muted-2-color);
    --content:"\e68f";
    --logo-url:url('.$img_logo_white.');
    --ji--neutral: rgba(55,65,81,.8);
    }
';
$Jitheme_css_1441='';
//条件判断
if(isset($Jitheme_header_image)){
        $Jitheme_header_image = b2_get_option('Jitheme_main_tab1','one_header_image');
    }else{
         $Jitheme_header_image ='';
}
if(empty($list_padding)){
    $list_padding='0px';
    $Jitheme_css.='.jitheme-post-info,.shop-normal-item-count{
        padding: 0px var(--ji--1item) var(--ji--1item) var(--ji--1item);   
    }
    .shop-list-item h2 {
    margin: var(--ji--1item);
    }
    .jitheme-shop-normal-item-price {
        margin:  0px var(--ji--1item) var(--ji--1item) var(--ji--1item);
    }
    @media screen and (max-width:768px){
        .jitheme-post-info{
        padding: '.$list_padding.';
        }
    }
    @media screen and (min-width:768px){
        .jitheme_cat_jb{margin-left:-var(--ji--1item)}
        .post-8 .jitheme_cat_jb{margin-left:0px}
    }
    
    .post-7 .jitheme-post-info {
    padding: 30px var(--ji--1item) var(--ji--1item) var(--ji--1item);
    }';
}else{
    $Jitheme_css .='.post-7 .jitheme-post-info {
    padding: 30px 0px 0px 0px;
    }
    
    ';
}
if(empty($ji_1item)){
$Jitheme_css .='
:root {
    --ji--radius: 0px;
    --ji--0item: -var(--ji--1item);
    --ji--1item: var(--ji--1item);
    --ji--2item: 8px;
    --ji--3item: -8px;
    --ji--2item-4x: 4px;
}';
}else{
$Jitheme_css .='
:root {
    --ji--radius: '.$jitheme_radius.';
    --ji--neutral: rgba(229, 231, 235,.8);
    --ji--0item: '.$ji_0item.';
    --ji--1item: '.$ji_1item.';
    --ji--2item: '.$ji_2item.';
    --ji--3item: '.$ji_3item.';
    --ji--2item-4x: '.$ji_2item_4x.';
}';
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
        max-width:'.$wrapper_width.'px;
    }
    #jitheme_arc_b .button, #jitheme_arc_b button{
        border-radius: '.$redius.';
    }
    .header .login-button button.empty{
        color:#fff;
    }
    .coll-3-box-in {
        margin: '.$ji_2item.'!important;
    }
    .collection-box {
        margin: '.$ji_3item.'!important;
    }
    .jitheme-radius,.post-thumb{
        border-radius: var(--ji--radius);
    }
@media screen and (min-width: 1250px){
    .post-1 .b2_gap>li .item-in,.post-3 .b2_gap>li .item-in,.post-4 .b2_gap>li .item-in,.post-7 .b2_gap>li .item-in,.post-8 .b2_gap>li .item-in, .shop-list-item, .shop-normal-item-in, .user-search-list li > div,.archive-row .post-3.post-3-li-dubble .b2_gap>li .item-in,.archive-row .post-3 .post-3-li .item-in{
        margin-bottom: var(--ji--1item)!important;
        margin-right: var(--ji--1item)!important;
        padding:'.$list_padding.';
    }
    .post-1 .b2_gap,.post-2 .b2_gap,.post-3 .b2_gap,.post-4 .b2_gap,.post-7 .b2_gap,.shop-normal-list, .shop-category, .user-search-list, .home-collection .collection-out,.one-grid{
        margin-right:  var(--ji--0item)!important;
        margin-bottom:  var(--ji--0item)!important;  
    }
    .item-in-video,,.Jitheme_about_main .serve_box,.Jitheme_about_main .about-selection .d-item{
        margin-bottom: var(--ji--1item)!important;
        margin-right: var(--ji--1item)!important;
    }
    .b2_gap,.post-2 .b2_gap,.post-4 .b2_gap,   .shop-normal-list, .shop-category, .user-search-list, .home-collection .collection-out,.one-grid ,.post-3 .b2_gap,{
        margin-right:  var(--ji--0item)!important;
        margin-bottom:  var(--ji--0item)!important;
    }
}';

if(b2_get_option('Jitheme_index_main','ji_top_tm')){
    $Jitheme_css.='
        .poa {
            top: calc(50% - 200px);
            right: calc(50% - ('.$wrapper_width.'px / 2));
        }
    @media screen and (min-width:768px){
        .home .jitheme_logo{
        background-image:url('.$img_logo_white.'); ;
        }
        .home .ji_haeder .jitheme_logo{
            background-image: url('.$img_logo.');;
        }
        .style-for-dark .ji_haeder .jitheme_logo{
            background-image:url('.$img_logo_white.'); ;
        }.home .header {
        background:unset!important;
        }
        .home .change-theme i {
        color: #fff;
        }    
    }';
}else{
    $Jitheme_css.='
        .poa {
            top: calc(50% - 180px);
            right: calc(50% - ('.$wrapper_width.'px / 2));
        }';
}
if($jianboff == 1 || $jianboff == 2 ){
    $padding = intval($list_padding);
    $user_top=$padding*3;
    $Jitheme_css.='.item-author .item-bg .thumb-a {
    background-image: linear-gradient(120deg, #eee 0%, #eee 100%);
    }
    @media screen and (min-width:1200px){
    .jitheme-ranks .home-authors .item-tobe-author .tobe-author,.jitheme-ranks .item-author .item-top{
        margin:'.$user_top.'px  '.$list_padding.' '.$list_padding.' '.$list_padding.';
        padding: 25px 15px  10px  15px!important;
    }
    
    .home .home-authors .item-tobe-author .tobe-author,.home .item-author .item-top{
        margin:'.$user_top.'px '.$list_padding.' '.$list_padding.' '.$list_padding.';
        padding:15px;
    }}';
}else{
    $Jitheme_css.='
    .jitheme-ranks .home-authors .item-tobe-author .tobe-author,.jitheme-ranks .item-author .item-top{
        margin:'.$list_padding.';
        padding: 25px 15px  0px  15px!important;
    }
    .home-authors .item-tobe-author .tobe-author,.home .item-author .item-top{
        padding:var(--ji--1item)!important;
    }';
}
if(b2_get_option('Jitheme_main_tab1','jitheme_line')  == 3){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.login-form-item,.homebk12 .homebk8-ctn li,.ask-top{
    border: 1px solid var(--ji--neutral)!important;
    box-shadow: 0 10px 10px 0 rgb(0 0 0 / 2%);
    }';
}elseif (b2_get_option('Jitheme_main_tab1','jitheme_line')  == 2){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.login-form-item,.homebk12 .homebk8-ctn li,.ask-top{
    box-shadow: 0 10px 10px 0 rgb(0 0 0 / 2%);
    }';
}elseif (b2_get_option('Jitheme_main_tab1','jitheme_line')  == 1){
   $Jitheme_css.='.jitheme_slide_jb,.clearfix,.post-3 .post-3-li .item-in,.jitheme-fenlei,.author-items .item-wrapa,.ji_zt,.jitheme-zt .item-btns .btn,.tags-page ul li a,.one-dongtai,.home-homevip-boxmk a,.box, .side-fixed,.login-form-item,.homebk12 .homebk8-ctn li,.ask-top{
    border: 1px solid var(--ji--neutral)!important;
    }';
}
if(b2_get_option('Jitheme_main_tab1','one_logo_saog')){
    $Jitheme_css.='.logo{position:relative;font-size:2em;font-weight:700;line-height:39px;overflow:hidden;margin:0;}.logo::before{content:"";position:absolute;width:150px;height:10px;background-color:rgba(255,255,255,.5);-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-animation:searchLights 1s ease-in 1s infinite;animation:searchLights 1s ease-in 1s infinite;}@-webkit-keyframes searchLights{0%{left:-90px;top:0;}to{left:90px;top:0;}}';
}
if (b2_get_option('Jitheme_main_tab1','index_huise')){
    $Jitheme_css.='html{filter: grayscale(100%);-webkit-filter: grayscale(100%);-moz-filter: grayscale(100%);-ms-filter: grayscale(100%);-o-filter: grayscale(100%);filter:progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);}';
}
if (b2_get_option('Jitheme_index_main','index_descmk_off') == 0){
    $Jitheme_css.='.Onecad_title >div:nth-of-type(2){display: none;}';
}
if(is_array($index_vip)){
    foreach ($index_vip as $k => $v) {
        $Jitheme_css.='
            #onecad-id-'.$v['time'].' :hover span,#onecad-id-'.$v['time'].':hover strong{color: '.isset($v['color']).';}
            
            #onecad-id-'.$v['time'].' :hover .vips_tj {background-color: '.isset($v['color']).'!important;}'; 
    }
}
if($one_template_top_vip_img){
    $Jitheme_css.='.Onecad_header_vip {
    background: url('.$one_template_top_vip_img.') no-repeat 50%/120px;}';
}
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
if(!empty($list_padding)){
$Jitheme_css_1441.='.item-in,.item-author, .home-authors 
    {
    padding:0px;
    }
    .list-footer {
       padding: 10px 0px;
    }
    .item-post-style-3 .jitheme_cat_jb {
        margin-left:0px!important
    }
    .post-1 .post-excerpt, .post-2 .post-excerpt,.post-list-meta-box{
        margin: 10px 0px;
    }
    .list-footer {
    margin-bottom: 0px!important;
    }
    ';
}else{
    $jitheme_css_a=$list_padding*2;
    $Jitheme_css_1441.='
    .item-author .item-wrap{padding-bottom:'.$list_padding.'}
    .list-footer {
        padding: 12px 0px;
    }
    .post-info h2 {
        margin: 5px 0px;
    }

    .home-authors .item-tobe-author .tobe-author {
    height: calc(100% - '.$jitheme_css_a.');
    }.list-footer {
    margin-bottom: 0px;}';
    

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
    width: 25%;
}
.home_row {
    margin-bottom:  var(--ji--1item)!important;
}
}
.jitheme_slide_tjmk {
    border-radius: 0px 0px $jitheme_radius $jitheme_radius;
}
.site-header-in::after {
    content: '';
    position: absolute;
    top: -20px;
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
.show-modal {
    background-image: url($Jitheme_top_login);
}
.mg-t- {
    margin-bottom: var(--ji--1item);
}
.button, button {
    border-radius: var(--b2radius);
}
#jitheme_arc_b .bg{background-image:-webkit-linear-gradient(145deg,#20c3f2 0%,#7e39fb 100%)}"



//上方
//后面增加CSS
;
echo $Jitheme_css;
echo '</style>'
?>