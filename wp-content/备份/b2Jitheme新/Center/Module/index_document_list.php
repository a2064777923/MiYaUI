<?php
use B2\Modules\Common\Post;

$post_id = get_the_id();
$cat = Post::get_categorys($post_id,'ask_cat');
foreach ($cat as $k => $v) {
    $cat_html =$v['name'];
    //$cat_html .='<span><a href="'.$v['link'].'">'.$v['name'].'</a></span>';
}
?>
<li class="px_item fl"><a class="font_hidden listMask_pic_name" href="<?php echo get_permalink(); ?>" title="<?php echo get_the_title(); ?>" rel="bookmark"><?php echo jitheme_get_icon('Jifont-channel-4'); echo get_the_title(); ?>
    <div class="question">
    <?php echo $cat_html; ?>
    </div></a>
</li>
