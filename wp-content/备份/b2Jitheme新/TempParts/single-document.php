<?php
use B2\Modules\Common\Post;
$post_id = get_the_id();
$help = get_post_meta($post_id,'b2_help_count',true);
$help = is_array($help) ? $help : array(
    'count'=>0,
    'useful'=>0
);
?>
<h1><?php echo get_the_title(); ?></h1>
<div class="mini-document-meta">
    <?php echo jithem_document_breadcrumb($post_id); ?>
</div>
<div class="entry-content">
    <?php the_content(); ?>
</div>
<div class="single-document-footer">
    <div class="document-tips"><?php echo __('这篇文章对你有帮助吗？','b2'); ?></div>
    <div class="document-help">
        <button @click="vote('up')"><?php echo b2_get_icon('b2-check-line').__('是','b2'); ?></button>
        <button @click="vote('down')"><?php echo b2_get_icon('b2-close-line').__('否','b2'); ?></button>
    </div>
    <div class="document-help-count">
        <?php echo sprintf(__('%s人中%s人觉得有帮助','b2'),'<span class="useful" v-text="postData.up + postData.down"></span>','<span class="unuse" v-text="postData.up"></span>'); ?>
    </div>
</div>
<footer class="mini-document-footer wedocs-entry-footer">
    <time itemprop="dateModified" datetime="<?php echo get_the_date('Y-m-d G:i:s'); ?>"><?php echo __('最后更新：','b2').get_the_date('Y-m-d'); ?></time>
</footer>
<?php
    $related = Post::get_related_posts($post_id);

    if(!empty($related)){
        echo '<div class="document-related"><h2>'.__('以下内容可能对你有帮助：','b2').'</h2><ul class="document-more-help b2-hover">';
            foreach ($related as $k => $v) {
                echo '<li><a href="'.$v['link'].'">'.$v['title'].'</a></li>';
            }
        echo '</ul></div>';
    }
?>

