<?php
use B2\Modules\Common\Post;
get_header();
$tags = Post::get_post_tags(102);
?>
<div  class="b2-single-content wrapper">
    <div id="tags" class="tags-page wrapper">
        <?php echo jitheme_tags($tags) ?>
    </div>
</div>
<?php
get_footer();