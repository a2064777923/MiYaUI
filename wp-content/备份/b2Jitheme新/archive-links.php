<?php

use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinksFn;

$open = b2_get_option('links_main','link_open');
if(!$open){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}
/**
 * 网址导航
 */
get_header();

$opts = get_option('b2_links_main');

$data = [];

if(!empty($opts['link_cats'])){

    foreach ($opts['link_cats'] as $k => $v) {
        $_opts = $opts;
        $_opts['link_cat'] = $v;
        $_opts['title'] = '';
        unset($_opts['link_cats']);
        $data[] = $_opts;
    }

    // var_dump($data);

    $title = b2_get_option('links_main','link_title');
    $total = LinksFn::link_total();
}
?>
<div class="b2-single-content wrapper">
    <div id="links" class="content-area links links-home">
		<main id="main" class="site-main mini-site-main">
            <div id="primary-home" class="content-area">
    
                <?php if((isset($total['link_count']) && $total['link_count'] == 0) || count($data) == 0){ ?>
                    <div class="box" style="width:100%"><?php echo B2_EMPTY; ?></div>
                <?php }else{ 
                    echo '<div class="home-links-content"><div class="b2-tab-links">
                    '.($title ? '<div class="mini-submenu b2-radius ">'.jitheme_get_icon('Jifont-1-home').$title.'</div>' : '').'
                    <div class="b2-tab-link-in"></div><div class="mini-submenu b2-radius "><a href="'.b2_get_custom_page_url('link-register').'" class="" target="_blank">'.jitheme_get_icon('Jifont-follow').'申请入驻</a></div>
                    </div><div class="home-links-right">
                        
                    <div class="links_list">';
                    if(b2_get_option('Jitheme_link_main','links_search_off') != 0){
                        require_once get_stylesheet_directory(). '/TempParts/links/site-search.php';
                    }
                    foreach ($data as $key => $value) {
                        $html = new Links();
                        echo $html->init($value,$key);
                    }
                    } ?>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();