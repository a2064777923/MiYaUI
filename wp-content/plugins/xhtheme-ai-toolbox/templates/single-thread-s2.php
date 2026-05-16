<?php
/**
 * Template Name: Thread Single Style 2
 * Template Post Type: thread
 * 
 * @package XHTheme_AI_Toolbox
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables with xhai_ prefix

get_header();

// Start the WordPress loop
while (have_posts()):
    the_post();

    $post_id = get_the_ID();
    $xhai_comment_count = get_comments_number();

    // 获取评论头像数组（已去重，最多3个）
    $xhai_comment_avatars = XHTheme\AIToolbox\XHThread::get_comment_avatars($post_id, 3);
    ?>

    <div class="xhaitool-thread-container xhaitool-thread-c1">
        <!-- 话题头部 -->
        <header class="thread-header">
            <div class="xhheader-conten">

                <h1 class="thread-title" style="text-align:center;"><?php the_title(); ?></h1>

                <div class="thread-meta-wrapper"
                    style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:24px;">
                    <?php if ($xhai_comment_count > 0): ?>
                        <div class="thread-stats" style="justify-content:center;">
                            <div class="xhaitool-thread-avatars">
                                <?php foreach ($xhai_comment_avatars as $xhai_avatar_url): ?>
                                    <div class="xhaitool-avatar-item"
                                        style="background-image: url(<?php echo esc_url($xhai_avatar_url); ?>);"></div>
                                <?php endforeach; ?>
                                <span
                                    class="xhaitool-avatar-count"><?php echo esc_html($xhai_comment_count > 99 ? '99+' : $xhai_comment_count); ?>
                                    <?php esc_html_e(' participants', 'xhtheme-ai-toolbox'); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="thread-stats">
                            <a href="#comments" class="xhbtn-discussion">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="margin-right:6px">
                                    <path
                                        d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                </svg>
                                <?php esc_html_e('Join Discussion', 'xhtheme-ai-toolbox'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- 来源文章模块 -->
        <?php
        // 获取父文章信息
        $xhai_parent_id = get_post_meta($post_id, 'xhai_postparent', true);
        $xhai_parent_post = $xhai_parent_id ? get_post($xhai_parent_id) : null;
        if ($xhai_parent_post): ?>
            <div class="source-section" style="margin-bottom:40px;max-width:800px;margin-left:auto;margin-right:auto;">
                <div class="source-card">
                    <div class="section-label">TOPIC SOURCE</div>
                    <a href="<?php echo esc_url(get_permalink($xhai_parent_id)); ?>" class="source-content"
                        style="display:flex;gap:20px;align-items:flex-start;">
                        <?php
                        // 尝试获取特色图片
                        $xhai_source_image = get_the_post_thumbnail_url($xhai_parent_id, 'thumbnail');

                        // 如果没有特色图片，尝试从内容中提取第一张图片
                        if (!$xhai_source_image) {
                            $xhai_parent_content = $xhai_parent_post->post_content;
                            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $xhai_parent_content, $matches);
                            if (!empty($matches[1])) {
                                $xhai_source_image = $matches[1];
                            }
                        }
                        ?>

                        <div class="source-icon"
                            style="width:64px;height:64px;flex-shrink:0;<?php echo $xhai_source_image ? 'background-image:url(' . esc_url($xhai_source_image) . ');background-size:cover;background-position:center;border-radius:12px;' : 'background-color:#F3F4F6;display:flex;align-items:center;justify-content:center;border-radius:12px;'; ?>">
                            <?php if (!$xhai_source_image): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                            <?php endif; ?>
                        </div>

                        <div class="source-body" style="flex-grow:1;">
                            <div class="source-tags">
                                <?php
                                $xhai_categories = get_the_category($xhai_parent_id);
                                $xhai_primary_tag = !empty($xhai_categories) ? $xhai_categories[0]->name : __('Uncategorized', 'xhtheme-ai-toolbox');
                                ?>
                                <span class="xhtag-primary"><?php echo esc_html($xhai_primary_tag); ?></span>
                                <span class="xhtag-meta"><?php echo esc_html(get_the_date('Y.m', $xhai_parent_post)); ?></span>
                            </div>
                            <h3 class="source-title"><?php echo esc_html($xhai_parent_post->post_title); ?></h3>
                        </div>

                        <div class="source-arrow" style="align-self:center;flex-shrink:0;">
                            <svg viewBox="0 0 1024 1024" width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="40" style="transform: rotate(-45deg);" xmlns="http://www.w3.org/2000/svg">
                                <path d="M918.613333 534.613333l-298.666666 298.666667a32 32 0 0 1-45.226667-45.269333l244.053333-244.053334H128a32 32 0 1 1 0-64h690.730667l-244.053334-244.053333a32 32 0 1 1 45.269334-45.269333l298.666666 298.666666a32.042667 32.042667 0 0 1 0 45.312z">
                                </path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- 文章内容 -->
        <div class="xh-content-section" style="max-width:800px;margin-left:auto;margin-right:auto;">
            <div class="xhprose">
                <?php the_content(); ?>
            </div>
            <?php
            $xhai_thread_desc = XHTheme\AIToolbox\xh_option('primaryThreadDesc');
            if ($xhai_thread_desc) {
                printf(
                    '<p style="opacity:.5;font-size:14px;margin-top:24px;display:flex;align-items:center;gap:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;width:14px;height:14px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>%s</span>
                    </p>',
                    wp_kses_post($xhai_thread_desc)
                );
            }
            ?>
        </div>

        <!-- 评论区 -->
        <?php
        comments_template('/templates/comments-thread.php');
        ?>

        <!-- 相关话题推荐 -->
        <?php
        $xhai_related_threads = XHTheme\AIToolbox\XHThread::get_related_threads($post_id, 6);
        if (!empty($xhai_related_threads)): ?>
            <div class="xhai-xhrelated-section">
                <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
                    <?php $xhai_thread_page_url = XHTheme\AIToolbox\XHThread::get_thread_page_url(); ?>
                    <div class="xhrelated-header"
                        style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
                        <h2 class="xhrelated-title"><?php esc_html_e('Further Reading', 'xhtheme-ai-toolbox'); ?></h2>
                        <?php if ($xhai_thread_page_url): ?>
                            <a href="<?php echo esc_url($xhai_thread_page_url); ?>" class="xhrelated-more">
                                <?php esc_html_e('View More Topics', 'xhtheme-ai-toolbox'); ?>
                                <svg viewBox="0 0 1024 1024" width="16" height="16" fill="currentColor" stroke="currentColor" stroke-width="40" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M918.613333 534.613333l-298.666666 298.666667a32 32 0 0 1-45.226667-45.269333l244.053333-244.053334H128a32 32 0 1 1 0-64h690.730667l-244.053334-244.053333a32 32 0 1 1 45.269334-45.269333l298.666666 298.666666a32.042667 32.042667 0 0 1 0 45.312z">
                                    </path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="xhrelated-grid">
                        <?php
                        foreach ($xhai_related_threads as $xhai_thread):
                            $xhai_thread_id = $xhai_thread->ID;
                            $xhai_thread_comments = get_comments_number($xhai_thread_id);
                            ?>
                            <div class="xhrelated-card">
                                <a href="<?php echo esc_url(get_permalink($xhai_thread_id)); ?>">
                                    <div class="xhcard-label">RELATED TOPIC</div>
                                    <h3 class="xhcard-title"><?php echo esc_html($xhai_thread->post_title); ?></h3>
                                    <p class="xhcard-excerpt">
                                        <?php echo esc_html(has_excerpt($xhai_thread) ? get_the_excerpt($xhai_thread) : wp_trim_words($xhai_thread->post_content, 20, '...')); ?>
                                    </p>
                                    <div class="xhcard-meta">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                        </svg>
                                        <?php
                                        /* translators: %s: Number of discussions */
                                        printf(esc_html__('%s discussions', 'xhtheme-ai-toolbox'), esc_html($xhai_thread_comments));
                                        ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
endwhile;
get_footer();
