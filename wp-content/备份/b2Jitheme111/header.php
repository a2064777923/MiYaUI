<!doctype html>
<html <?php language_attributes(); ?> class="avgrund-ready  b2dark ">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta id="<?php echo jithemeid()?>" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
	<meta name="theme-color"  content="<?php echo b2_get_option('template_top','gg_bg_color'); ?>">
	<link rel="stylesheet" href="<?php echo B2_CHILD_URI ?>/Render/Fonts/iconfont.css">
	<link rel="stylesheet" href="<?php echo B2_CHILD_URI ?>/Render/fontello/css/fontello.css" type="text/css"/>
	<script type="text/javascript" src="<?php echo B2_CHILD_URI ?>/Render/Js/jquery.min.js"></script> 
	<script type="text/javascript" src="<?php echo B2_CHILD_URI ?>/Render/Js/swiper.min.js"></script>
    <script type="text/javascript" src="<?php echo B2_CHILD_URI ?>/Render/Js/script.js"></script>
    <link rel="stylesheet"href="//at.alicdn.com/t/c/font_3151013_elxymugdmxd.css"/>
	<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/swiper.min.css"/>
	<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/monbile_1200.css"/>
	<!--<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/monbile_480_767.css"/>-->
	<!--<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/monbile_768_960.css"/>-->
	<!--<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/monbile_960_1199.css"/>-->
	<link rel="stylesheet"href="<?php echo B2_CHILD_URI ?>/Render/Css/phb.css"/>
	
    <!--视频-->
	<?php wp_head();?>
</head>
    <body id="Jitheme.com" <?php body_class(b2_get_option('template_top','top_type')); ?>>
	<?php
		$bg = b2_get_option('template_main','bg_image'); 
		$bg_repeat = b2_get_option('template_main','bg_image_repeat');
		if($bg && $bg_repeat == 2){
			echo '<div class="b2-page-bg">
				<img src="'.b2_get_thumb(array('thumb'=>$bg,'width'=>80,'height'=>'100%')).'" />
			</div>';
		}
	?>
    <?php get_template_part('Center/Module/Jitheme_css' );?>
    <?php get_template_part('Render/Css/main' );?>
	<div id="page" class="site">

		<?php do_action('b2_header'); ?>
		
	<div id="content" class="site-content">
	
		<?php do_action('b2_content_before'); ?>
        <script type="text/javascript">
            if(window.localStorage){
                /* 暗黑判断 */
                var dark = localStorage.getItem('darkStyle');
                var toggle = document.querySelector('.dark-style-toggle');
                if(dark == 1 && !toggle.classList.contains('active')){
                    document.body.classList.add("style-for-dark");
                    toggle.classList.add('active')
                }else if(dark == 0 && toggle.classList.contains('active')){
                    document.body.classList.remove('style-for-dark');
                    toggle.classList.remove('active');
                }
            }
        </script>