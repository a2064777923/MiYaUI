<?php
/**
 * 话题聚合页面模板
 * Template Name: Thread List Page
 * 
 * @package XHTheme_AI_Toolbox
 */

get_header();
?>
<div class="xhaitool-thread-container">
    <?php
    while (have_posts()):
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php
get_footer();
