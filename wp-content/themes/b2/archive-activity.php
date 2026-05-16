<?php
$open = b2_get_option('activity_main','activity_open');
if(!$open){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}
/**
 * 网址导航
 */
get_header();
?>
互动
<?php
get_footer();