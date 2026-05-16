<?php

namespace XHTheme\AIToolbox;

final class XHThread
{

    private static $instance;
    private $taklsType = 'thread';
    private $XHCron;
    private $XHAi;

    private $thread_template = false;

    private $blockTheme = false;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->XHAi = XHThemeAi::getInstance();
        $this->XHCron = XHCronQueue::getInstance();
        $this->blockTheme = function_exists('wp_is_block_theme') && wp_is_block_theme();
    }

    /**
     * 获取话题聚合页编辑链接
     */
    private function get_threadpage()
    {
        $cached_page_id = get_option('xhaitool_thread_pageid');
        if ($cached_page_id) {
            $page = get_post($cached_page_id);
            if ($page && in_array($page->post_status, ['publish', 'draft'])) {
                $content = get_post_field('post_content', $page->ID);
                if (has_shortcode($content, 'xhaitoolbox_threads') || has_block('xhaitoolbox/thread_list')) {
                    return $page;
                }
            }
            delete_option('xhaitool_thread_pageid');
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cached via get_option above
        $page_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = %s 
                AND post_status IN ('publish', 'draft')
                AND (post_content LIKE %s OR post_content LIKE %s)
                LIMIT 1",
                'page',
                '%[xhaitoolbox_threads%',
                '%xhaitoolbox/thread_list%'
            )
        );

        if ($page_id) {
            update_option('xhaitool_thread_pageid', $page_id, false);
            return get_post($page_id);
        }

        return false;
    }

    public function get_pageedit_url()
    {
        $page = $this->get_threadpage();
        if ($page && $page->post_status === 'publish') {
            return get_edit_post_link($page->ID, 'raw');
        }
        return 0;
    }

    /**
     * 获取话题聚合页的前端链接
     * 仅当 primaryThreadPage 选项启用且页面存在时返回链接
     *
     * @return string|false 页面链接或 false
     */
    public static function get_thread_page_url()
    {
        // 检查 primaryThreadPage 选项是否启用
        if (!xh_option('primaryThreadPage')) {
            return false;
        }

        // 获取话题页面
        $page = self::getInstance()->get_threadpage();
        if ($page && $page->post_status === 'publish') {
            return get_permalink($page->ID);
        }

        return false;
    }

    public function toggle_thread_page($status = false)
    {
        $pageItem = $this->get_threadpage();
        if ($status) {
            if ($pageItem) {
                if ($pageItem->post_status !== 'publish') {
                    $update_post = [
                        'ID' => $pageItem->ID,
                        'post_status' => 'publish'
                    ];
                    if ($this->blockTheme) {
                        $update_post['meta_input']['_wp_page_template'] = 'thread-list-s1';
                    }
                    wp_update_post($update_post);
                }
                $message = sprintf(
                    '%s <a href="%s" target="_blank" class="xht-text-red-500 xht-underline">%s</a> | <a href="%s" target="_blank" class="xht-text-blue-500 xht-underline">%s</a>',
                    __('Thread page has been published! ', 'xhtheme-ai-toolbox'),
                    get_permalink($pageItem->ID),
                    __('View page', 'xhtheme-ai-toolbox'),
                    get_edit_post_link($pageItem->ID, 'raw'),
                    __('Edit Page', 'xhtheme-ai-toolbox')
                );
            } else {
                // 创建页面
                $threadHeaderContent = $this->add_patterns([], true);

                // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed -- Heredoc is cleaner for multiline block content
                $post_content = <<<HTML
                {$threadHeaderContent}

                <!-- wp:shortcode -->
                [xhaitoolbox_threads] 
                <!-- /wp:shortcode -->
                HTML;
                $meta_input = [
                    'xhai_thread_page' => '1'
                ];
                if ($this->blockTheme) {
                    $meta_input['_wp_page_template'] = 'thread-list-s1';
                }
                $page_id = wp_insert_post([
                    'post_title' => __('Popular Topics', 'xhtheme-ai-toolbox'),
                    'post_name' => 'threads',
                    'post_content' => $post_content,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => get_current_user_id(),
                    'comment_status' => 'closed',
                    'meta_input' => $meta_input
                ]);

                if (is_wp_error($page_id)) {
                    return [
                        'success' => false,
                        'message' => __('Failed to create page: ', 'xhtheme-ai-toolbox') . $page_id->get_error_message()
                    ];
                }

                $message = sprintf(
                    '%s <a href="%s" target="_blank" class="xht-text-red-500 xht-underline">%s</a> | <a href="%s" target="_blank" class="xht-text-blue-500 xht-underline">%s</a>',
                    __('Thread page created successfully! ', 'xhtheme-ai-toolbox'),
                    get_permalink($page_id),
                    __('View page', 'xhtheme-ai-toolbox'),
                    get_edit_post_link($page_id, 'raw'),
                    __('Edit Page', 'xhtheme-ai-toolbox')
                );
            }
            xh_setoption('primaryThreadPage', 1);
            return ['success' => true, 'message' => $message];
        }

        /**
         * 关闭话题页面
         */
        if (!$pageItem || $pageItem->post_status !== 'publish') {
            $message = __('Closed!', 'xhtheme-ai-toolbox');
        } else {
            wp_update_post([
                'ID' => $pageItem->ID,
                'post_status' => 'draft'
            ]);
            $message = __('Thread page has been moved to drafts!', 'xhtheme-ai-toolbox');
        }
        xh_setoption('primaryThreadPage', 0);
        return ['success' => true, 'message' => $message];
    }

    public function threads_shortcode($atts, $content = null)
    {
        $atts = shortcode_atts([
            'number' => 20,
        ], $atts, 'xhaitoolbox_threads');

        $posts_per_page = intval($atts['number']);

        // 查询话题列表
        $args = [
            'post_type' => $this->taklsType,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => 1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ];

        $thread_query = new \WP_Query($args);

        if (!$thread_query->have_posts()) {
            return '<div class="xhaitool-threads-empty">' .
                __('No topics available', 'xhtheme-ai-toolbox') .
                '</div>';
        }

        ob_start();
?>
        <div class="xhaitool-thread-s1">
            <div class="xhaitool-threads-container">
                <div class="xhaitool-threads-grid">
                    <?php
                    while ($thread_query->have_posts()) {
                        $thread_query->the_post();
                        $this->render_thread_card(get_post());
                    }
                    wp_reset_postdata();
                    ?>
                </div>

                <?php if ($thread_query->max_num_pages > 1): ?>
                    <div class="xhaitool-loadmore-wrap">
                        <button class="xhaitool-loadmore-btn" data-current-page="1"
                            data-max-pages="<?php echo esc_attr($thread_query->max_num_pages); ?>"
                            data-posts-per-page="<?php echo esc_attr($posts_per_page); ?>">
                            <?php esc_html_e('Load More', 'xhtheme-ai-toolbox'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php

        return ob_get_clean();
    }

    /**
     * 渲染单个话题卡片
     */
    private function render_thread_card($post)
    {
        // 日期处理：当天显示几小时前，其他显示月-日
        $post_timestamp = get_post_time('U', false, $post->ID);
        $current_time = xh_local_timestamp();
        $time_diff = $current_time - $post_timestamp;

        if ($time_diff < 86400 && wp_date('Ymd', $post_timestamp) === wp_date('Ymd', $current_time)) {
            $post_date = human_time_diff($post_timestamp, $current_time) . __(' ago', 'xhtheme-ai-toolbox');
        } else {
            $post_date = get_the_date('m-d', $post->ID);
        }

        // 阅读时间计算 (假设 300 字/分钟)
        $content_text = wp_strip_all_tags($post->post_content);
        $content_length = function_exists('mb_strlen') ? mb_strlen($content_text) : strlen($content_text);
        $read_time = ceil($content_length / 300);
        /* translators: %d: Reading time in minutes */
        $read_time_str = sprintf(esc_html__('%d min read', 'xhtheme-ai-toolbox'), $read_time);

        $post_title = get_the_title($post->ID);
        $post_link = get_permalink($post->ID);
        $post_excerpt = has_excerpt($post->ID)
            ? get_the_excerpt($post->ID)
            : wp_trim_words(strip_shortcodes($post->post_content), 60, '...');

        // 获取评论信息
        $comment_count = get_comments_number($post->ID);
        $comment_html = '';
        $comment_link = get_permalink($post->ID) . '#comments'; // 评论区链接

        if ($comment_count > 0) {
            // 获取去重后的评论头像
            $avatars = self::get_comment_avatars($post->ID, 3);

            $avatars_html = '';
            foreach ($avatars as $avatar_url) {
                $avatars_html .= sprintf(
                    '<div class="xhaitool-avatar-item" style="background-image: url(\'%s\');"></div>',
                    esc_url($avatar_url)
                );
            }

            // 显示数量 (如 7+, 99+)
            $count_display = $comment_count > 99 ? '99+' : $comment_count . '+';
            // 数量文本显示在头像后方
            $count_html = sprintf('<span class="xhaitool-avatar-count">%s</span>', esc_html($count_display));

            $comment_html = sprintf(
                '<a href="%s" class="xhaitool-thread-avatars">%s</a>',
                esc_url($comment_link),
                $avatars_html . $count_html
            );
        } else {
            // 没有评论显示 "参与讨论" 按钮
            $comment_html = sprintf(
                '<a href="%s" class="xhaitool-thread-join-btn">%s</a>',
                esc_url($comment_link),
                __('Join Discussion', 'xhtheme-ai-toolbox')
            );
        }
        // 获取文章图片（特色图或内容中的第一张图片）
        $image_url = '';
        if (has_post_thumbnail($post->ID)) {
            $image_url = get_the_post_thumbnail_url($post->ID, 'medium');
        } else {
            // 从内容中提取第一张图片
            preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
            if (!empty($matches[1])) {
                $image_url = $matches[1];
            }
        }
    ?>
        <div class="xhaitool-thread-card<?php echo $image_url ? ' has-image' : ''; ?>">
            <?php if ($image_url): ?>
                <div class="xhaitool-thread-bg-image" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                </div>
            <?php endif; ?>

            <div class="xhaitool-thread-header">
                <?php if ($time_diff < 86400 && wp_date('Ymd', $post_timestamp) === wp_date('Ymd', $current_time)): ?>
                    <div class="xhaitool-thread-time-display">
                        <?php echo esc_html($post_date); ?>
                    </div>
                <?php else: ?>
                    <div class="xhaitool-thread-decorator">
                        <span class="xhaitool-decorator-line line-1"></span>
                        <span class="xhaitool-decorator-line line-2"></span>
                        <span class="xhaitool-decorator-line line-3"></span>
                    </div>
                <?php endif; ?>
                <div class="xhaitool-thread-readtime"><?php echo esc_html($read_time_str); ?></div>
            </div>

            <div class="xhaitool-thread-body">
                <h3 class="xhaitool-thread-title">
                    <a href="<?php echo esc_url($post_link); ?>">
                        <?php echo esc_html($post_title); ?>
                    </a>
                </h3>
                <div class="xhaitool-thread-excerpt">
                    <?php echo esc_html($post_excerpt); ?>
                </div>
            </div>

            <div class="xhaitool-thread-footer">
                <?php
                echo wp_kses_post($comment_html); // 输出评论部分 
                ?>
                <a href="<?php echo esc_url($post_link); ?>" class="xhaitool-thread-readmore"
                    aria-label="<?php esc_attr_e('Read More', 'xhtheme-ai-toolbox'); ?>">
                    <span class="xhaitool-icon-arrow"></span>
                </a>
            </div>
        </div>
    <?php
    }

    /**
     * 注册并加载话题页面静态资源
     */
    public function enqueue_style()
    {
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        $plugin_path = plugin_dir_path(dirname(__FILE__));

        $css_file = defined('XHAITOOLBOX_DEBUG') && XHAITOOLBOX_DEBUG ? 'assets/css/thread-page.css' : 'assets/css/thread-page.min.css';
        if (file_exists($plugin_path . $css_file)) {
            wp_enqueue_style(
                'xhaitool-thread-page',
                $plugin_url . $css_file,
                [],
                filemtime($plugin_path . $css_file)
            );
        }
    }
    public function enqueue_thread_page_assets()
    {

        if (!$this->thread_template && !is_single()) {
            return;
        }

        $thread_template = xh_option('primaryThreadTemplate');
        $thread_listpage = xh_option('primaryThreadPage') ? $this->get_threadpage() : null;
        if (!apply_filters('xhtheme_ai_toolbox_threadassets', true, $thread_template, $thread_listpage)) {
            return;
        }

        $this->enqueue_style();
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        $plugin_path = plugin_dir_path(dirname(__FILE__));
        $js_file = defined('XHAITOOLBOX_DEBUG') && XHAITOOLBOX_DEBUG ? 'assets/js/thread-page.js' : 'assets/js/thread-page.min.js';
        if (file_exists($plugin_path . $js_file)) {
            wp_enqueue_script(
                'xhaitool-thread-page',
                $plugin_url . $js_file,
                [],
                filemtime($plugin_path . $js_file),
                true
            );
            wp_localize_script('xhaitool-thread-page', 'xhThreadPageData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => get_rest_url(null, 'aitoolboxv1'),
                'nonce' => wp_create_nonce('wp_rest'),
                'postId' => get_the_ID()
            ]);
        }
    }

    /**
     * 在块编辑器中加载样式
     */
    public function enqueue_block_editor_assets()
    {
        $this->enqueue_style();
    }


    // 在文章内容后显示话题列表
    public function threads_after_content($content)
    {
        if (!is_single() || !is_main_query()) {
            return $content;
        }

        $threads = $this->get_threads(get_the_ID(), ['publish']);
        if (empty($threads)) {
            return $content;
        }
        $threadTitle = xh_option('primaryThreadTitle', '');
        $threadType = xh_option('primaryThreadtype', 'default') == 'default' ? 'default' : 'vertical';
        $filterContent = '';
        if (apply_filters('xhtheme_ai_toolbox_threadPost-custom', false, $threadType)) {
            $filterContent = apply_filters('xhtheme_ai_toolbox_threadPosthtml', '', $threadTitle, $threads, $threadType);
        } else {
            $filterContent = $this->threadPost_webstyle($threads, $threadTitle, $threadType);
        }
        return $content . $filterContent;
    }



    public function admin_thread_style()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== $this->taklsType)
            return;
    ?>
        <style>
            .thread-type,
            .thread-perspective {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 500;
                line-height: 1.5;
                transition: all 0.2s ease;
            }

            .thread-type-item-question {
                background: #f8f9ff;
                color: #5b6fe8;
            }

            .thread-type-item-definition {
                background: #fff5f8;
                color: #e83e8c;
            }

            .thread-type-item-interest {
                background: #f0f9ff;
                color: #0284c7;
            }

            .thread-type-item-argument {
                background: #f0fdf4;
                color: #16a34a;
            }

            .thread-perspective {
                border: 1px solid;
                border-radius: 20px;
                padding: 3px 10px;
            }

            .thread-perspective-item-blogger {
                background: #fff5f5;
                color: #dc2626;
                border-color: #fecaca;
            }

            .thread-perspective-item-expert {
                background: #f0fdfa;
                color: #0f766e;
                border-color: #99f6e4;
            }

            .thread-perspective-item-observer {
                background: #faf5ff;
                color: #7c3aed;
                border-color: #e9d5ff;
            }

            .thread-perspective-item-general {
                background: #fffbeb;
                color: #d97706;
                border-color: #fde68a;
            }

            .tablenav.top .alignleft.actions {
                display: flex;
                gap: 8px;
                align-items: center;
            }
        </style>
<?php
    }

    public function threadPost_webstyle($threads, $threadTitle, $threadType)
    {
        // 使用新的对话气泡SVG图标
        $svgimage = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 1024 1024" aria-labelledby="chatBubbleTitle" style="vertical-align: middle; margin-right: 5px;width:18px;height:18px;">
            <path d="M504.149333 89.429333A422.570667 422.570667 0 0 0 81.578667 512c0 66.56 2.048 192.170667 4.096 289.109333a136.533333 136.533333 0 0 0 136.533333 133.461334h267.946667a431.445333 431.445333 0 0 0 436.565333-414.72A422.570667 422.570667 0 0 0 504.149333 89.429333z m30.037334 580.266667H286.72a34.133333 34.133333 0 1 1 0-68.266667h247.466667a34.133333 34.133333 0 0 1 0 68.266667zM662.869333 443.733333H286.72a34.133333 34.133333 0 1 1 0-68.266666h375.466667a34.133333 34.133333 0 0 1 0 68.266666z" fill="currentColor"/>
            </svg>';

        // 箭头SVG图标
        $arrowSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 1024 1024" aria-labelledby="arrowRightTitle" style="vertical-align: middle; margin-left: 3px;width:14px;height:14px;">
            <path d="M670.37 558.41c-12.31 0-24.56-4.69-33.94-14.06L319.68 227.53c-18.75-18.75-18.75-49.12 0-67.88s49.12-18.75 67.88 0l316.75 316.81c18.75 18.75 18.75 49.12 0 67.88-9.38 9.38-21.63 14.07-33.94 14.07z" fill="currentColor"/>
            <path d="M353.62 878.41c-12.31 0-24.56-4.69-33.94-14.06-18.75-18.75-18.75-49.12 0-67.88l316.75-316.81c18.75-18.75 49.12-18.75 67.88 0s18.75 49.12 0 67.88l-316.75 316.8c-9.38 9.38-21.63 14.07-33.94 14.07z" fill="currentColor"/>
            </svg>';

        // 替换为新的SVG图标
        $tagSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 1024 1024" aria-labelledby="tagTitle" style="vertical-align: middle; margin-right: 4px;width:14px;height:14px;">
            <path d="M718.46 116.077c23.387 4.124 39.003 26.426 34.88 49.814l-24.882 141.108L867 307c23.748 0 43 19.252 43 43s-19.252 43-43 43H713.293L670.27 637H821c23.748 0 43 19.252 43 43s-19.252 43-43 43H655.105l-25.75 146.043c-4.125 23.388-26.427 39.004-49.814 34.88-23.388-4.124-39.004-26.426-34.88-49.814L567.778 723H391.105l-25.75 146.044c-4.125 23.388-26.427 39.004-49.814 34.88-23.388-4.124-39.004-26.426-34.88-49.814L303.778 723 158 723c-23.748 0-43-19.252-43-43s19.252-43 43-43h160.942l43.024-244H204c-23.748 0-43-19.252-43-43s19.252-43 43-43h173.13l27.516-156.043c4.124-23.388 26.426-39.004 49.813-34.88 23.388 4.124 39.004 26.426 34.88 49.814l-24.881 141.108H641.13l27.516-156.042c4.124-23.388 26.426-39.004 49.813-34.88z m-92.494 276.922H449.293L406.27 637h176.672l43.024-244z" fill="currentColor"/>
            </svg>';

        $items = [];
        foreach ($threads as $thread) {
            $items[] = sprintf(
                '<div class="xhtheme-threads-item"><a href="%s"><span>%s%s</span><span class="arrow-container">%s</span></a></div>',
                get_permalink($thread->ID),
                $tagSvg,
                $thread->post_title,
                $arrowSvg
            );
        }

        return sprintf(
            '
            <div class="xhtheme-threads-box">
                <h3 class="xhtheme-threads-title">%s</h3>
                <div class="xhtheme-threads-list %s">
                    %s
                </div>
            </div>
            ',
            $svgimage . ' ' . $threadTitle,
            $threadType == 'default' ? 'xhtheme-threads-horizontal' : 'xhtheme-threads-column',
            implode('', $items)
        );
    }

    public function register_post_type()
    {
        $labels = [
            'name' => __('Threads', 'xhtheme-ai-toolbox'),
            'all_items' => __('All Threads', 'xhtheme-ai-toolbox'),
            'add_new' => __('Add New Thread', 'xhtheme-ai-toolbox'),
            'add_new_item' => __('New Thread', 'xhtheme-ai-toolbox')
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'comments', 'thumbnail'],
            'menu_icon' => 'dashicons-editor-quote',
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => false,
                'edit_post' => 'edit_post'
            ],
            'map_meta_cap' => true,
            'has_archive' => false,
            'rewrite' => [
                'slug' => 'thread',
                'with_front' => false
            ]
        ];
        register_post_type($this->taklsType, $args);
    }

    public function thread_template($template)
    {
        /**
         * 为话题类型指定模版
         */
        if (is_singular($this->taklsType)) {
            $thread_template = xh_option('primaryThreadTemplate');

            // 块主题处理以及主题模版处理
            if ($this->blockTheme || $thread_template == 'theme') {
                $this->thread_template = true;
                return $template;
            }

            // s2模版处理
            if ($thread_template == 's2') {
                $templateName = 'single-thread-s2.php';
                $plugin_template = plugin_dir_path(dirname(__FILE__)) . 'templates/' . $templateName;
                if (file_exists($plugin_template)) {
                    $this->thread_template = true;
                    return $plugin_template;
                }
            }

            // 默认模版
            $theme_template = locate_template(['single-thread.php']);
            if (!empty($theme_template)) {
                return $theme_template;
            }

            $templateName = 'single-thread.php';
            $plugin_template = plugin_dir_path(dirname(__FILE__)) . 'templates/' . $templateName;
            if (file_exists($plugin_template)) {
                $this->thread_template = true;
                return $plugin_template;
            }
        }


        /**
         * 重写话题聚合页面模版
         */
        if (is_page()) {
            $threadpage = $this->get_threadpage();
            if ($threadpage && is_page($threadpage->ID)) {
                // 块主题处理
                if ($this->blockTheme) {
                    $this->thread_template = true;
                    return $template;
                }
                $theme_template = locate_template(['page-thread.php']);
                if (!empty($theme_template)) {
                    return $theme_template;
                }
                $plugin_template = plugin_dir_path(dirname(__FILE__)) . 'templates/page-thread.php';
                if (file_exists($plugin_template)) {
                    $this->thread_template = true;
                    return $plugin_template;
                }
            }
        }

        return $template;
    }


    // 添加自定义列
    public function add_custom_columns($columns)
    {
        $new_columns = [
            'parent_post' => __('Related Post', 'xhtheme-ai-toolbox'),
            'thread_type' => __('Thread Type', 'xhtheme-ai-toolbox'),
            'writing_perspective' => __('Writing Perspective', 'xhtheme-ai-toolbox'),
            'status' => __('Status', 'xhtheme-ai-toolbox'),
            'date' => $columns['date']
        ];
        unset($columns['date']);
        return array_merge($columns, $new_columns);
    }

    // 显示自定义列内容
    public function show_custom_columns($column, $post_id)
    {
        switch ($column) {
            case 'parent_post':
                $parent_id = get_post_meta($post_id, 'xhai_postparent', true);
                if ($parent_id) {
                    $parent = get_post($parent_id);
                    if ($parent) {
                        echo '<div>' . esc_html($parent->post_title) . '</div>';
                        echo '<div class="row-actions">';
                        echo '<a href="' . esc_url(get_permalink($parent_id)) . '" target="_blank">' . esc_attr__('View Post', 'xhtheme-ai-toolbox') . '</a> | ';
                        echo '<a href="' . esc_url(get_edit_post_link($parent_id)) . '">' . esc_attr__('Edit Post', 'xhtheme-ai-toolbox') . '</a>';
                        echo '</div>';
                    }
                }
                break;
            case 'thread_type':
                $threadType = get_post_meta($post_id, 'xhai_threadtype', true);
                $threadType = $threadType ?: 'none';
                $typeHtml = $this->get_thread_type($threadType, true);
                if ($typeHtml) {
                    echo wp_kses_post($typeHtml);
                } else {
                    echo '-';
                }
                break;
            case 'writing_perspective':
                $perspective = get_post_meta($post_id, 'xhai_perspective', true);
                $perspective = $perspective ?: 'none';
                $perspectiveHtml = $this->get_thread_perspective($perspective, true);
                if ($perspectiveHtml) {
                    echo wp_kses_post($perspectiveHtml);
                } else {
                    echo '-';
                }
                break;
            case 'status':
                $status = get_post_status($post_id);
                $post = get_post($post_id);
                if ($status === 'pending' && empty($post->post_content)) {
                    printf(
                        '
                        <span style="background: %s; 
                          -webkit-background-clip: text; 
                          background-clip: text; 
                          color: transparent;
                          font-weight: 600;">
                          %s
                        </span>
                        ',
                        'linear-gradient(90deg, #fe4ffb 0%, #009ffe 100%);',
                        esc_attr__('AI queue processing', 'xhtheme-ai-toolbox')
                    );
                } else {
                    if ($status === 'publish') {
                        esc_html_e('Published', 'xhtheme-ai-toolbox');
                    } else {
                        echo esc_attr(ucfirst($status));
                    }
                }
                break;
        }
    }

    // 添加筛选下拉框
    public function add_thread_filters()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== $this->taklsType)
            return;

        // Thread Type 筛选
        $thread_types = $this->get_thread_type();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list table filter, no data modification
        $current_type = isset($_GET['thread_type']) ? sanitize_text_field(wp_unslash($_GET['thread_type'])) : '';

        echo '<select name="thread_type" id="thread_type">';
        echo '<option value="">' . esc_html__('All Thread Types', 'xhtheme-ai-toolbox') . '</option>';
        foreach ($thread_types as $key => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($key),
                selected($current_type, $key, false),
                esc_html($label)
            );
        }
        echo '</select>';

        // Writing Perspective 筛选
        $perspectives = $this->get_thread_perspective();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list table filter, no data modification
        $current_perspective = isset($_GET['thread_perspective']) ? sanitize_text_field(wp_unslash($_GET['thread_perspective'])) : '';

        echo '<select name="thread_perspective" id="thread_perspective">';
        echo '<option value="">' . esc_html__('All Perspectives', 'xhtheme-ai-toolbox') . '</option>';
        foreach ($perspectives as $key => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($key),
                selected($current_perspective, $key, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    // 应用筛选条件
    public function filter_threads_by_meta($query)
    {
        global $pagenow;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list table filter
        $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
        if (!is_admin() || $pagenow !== 'edit.php' || $post_type !== $this->taklsType) {
            return;
        }

        $meta_query = [];

        // Thread Type 筛选
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list table filter, no data modification
        if (isset($_GET['thread_type']) && !empty($_GET['thread_type'])) {
            $meta_query[] = [
                'key' => 'xhai_threadtype',
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                'value' => sanitize_text_field(wp_unslash($_GET['thread_type'])),
                'compare' => '='
            ];
        }

        // Writing Perspective 筛选
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list table filter, no data modification
        if (isset($_GET['thread_perspective']) && !empty($_GET['thread_perspective'])) {
            $meta_query[] = [
                'key' => 'xhai_perspective',
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                'value' => sanitize_text_field(wp_unslash($_GET['thread_perspective'])),
                'compare' => '='
            ];
        }

        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $query->set('meta_query', $meta_query);
        }
    }

    // 为文章添加话题数量列
    public function add_post_columns($columns)
    {

        if (!$this->XHAi->isMember() || !xh_option('primaryThread', false)) {
            return $columns;
        }
        $new_columns = [
            'thread_count' => __('Threads', 'xhtheme-ai-toolbox')
        ];
        return array_merge($columns, $new_columns);
    }

    // 显示文章话题数量
    public function show_post_columns($column, $post_id)
    {
        if ($column === 'thread_count') {
            $threads = $this->get_threads($post_id, ['publish']);
            if (!empty($threads)) {
                $links = array_map(function ($thread) {
                    return sprintf(
                        '<a href="%s">#%s</a>',
                        esc_url(get_edit_post_link($thread->ID)),
                        esc_html($thread->post_title)
                    );
                }, $threads);
                echo wp_kses_post(implode('、', $links));
            } else {
                $taskHtml = $this->XHCron->addcrontask('threadlist', $post_id, [
                    'threadPending' => $this->get_threads($post_id, ['pending'])
                ]);
                if ($taskHtml) {
                    echo $taskHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $taskHtml is internally sanitized HTML with SVG from addcrontask()
                }
            }
        }
    }

    // 获取关联话题列表
    public function get_threads($post_id, $post_status = [])
    {
        // 缓存键只基于 post_id，不区分状态
        $cache_key = 'post_' . $post_id;
        $cache_group = 'xhtheme_threads';

        // 尝试从缓存获取
        $threads = wp_cache_get($cache_key, $cache_group);

        if ($threads === false) {
            // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required for parent-child relationship query
            $args = [
                'post_type' => $this->taklsType,
                'meta_key' => 'xhai_postparent',
                'meta_value' => $post_id,
                'posts_per_page' => 100,
                'post_status' => ['publish', 'pending']
            ];
            // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            $threads = get_posts($args);
            wp_cache_set($cache_key, $threads, $cache_group, DAY_IN_SECONDS);
        }

        // 获取缓存后再按状态过滤
        if (!empty($post_status) && is_array($post_status)) {
            $threads = array_filter($threads, function ($thread) use ($post_status) {
                return in_array($thread->post_status, $post_status, true);
            });
        }

        return $threads;
    }

    /**
     * 清除指定文章的话题缓存
     * @param int $post_id 父文章ID
     */
    public function clear_threads_cache($post_id)
    {
        wp_cache_delete('post_' . $post_id, 'xhtheme_threads');
    }

    public function add_threadaction($response, $params, $postId)
    {
        if (isset($params['postthread']) && !empty($params['postthread']) && is_array($params['postthread'])) {
            $this->add_thread($params['postthread'], $postId);
        }
        return $response;
    }

    public function add_thread($threads, $postId)
    {
        $postthread = (array) $threads;
        foreach ($postthread as $item) {
            $item = array_filter($item);
            if (empty($item) || !isset($item['title']) || !isset($item['slug']))
                continue;
            $itemTitle = sanitize_text_field($item['title']);
            $itemSlug = sanitize_title($item['slug']);
            $itemType = isset($item['type']) && in_array($item['type'], ['question', 'definition', 'interest', 'argument']) ? sanitize_text_field($item['type']) : 'question';
            $itemPerspectives = isset($item['perspectives']) && in_array($item['perspectives'], ['blogger', 'expert', 'observer', 'general']) ? sanitize_text_field($item['perspectives']) : 'blogger';
            if (empty($itemTitle) || empty($itemSlug))
                continue;
            $existing_post = get_posts([
                'title' => $itemTitle,
                'post_type' => $this->taklsType,
                'post_status' => 'all',
                'numberposts' => 1,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby' => 'post_date ID',
                'order' => 'ASC'
            ]);
            if (!empty($existing_post)) {
                continue;
            }
            $threadargs = [
                'post_title' => $itemTitle,
                'post_name' => $itemSlug,
                'post_status' => 'pending',
                'post_type' => $this->taklsType,
                'comment_status' => 'open',
                'ping_status' => 'closed',
                'post_content' => '',
                'meta_input' => [
                    'xhai_postparent' => $postId,
                    'xhai_threadtype' => $itemType,
                    'xhai_perspective' => $itemPerspectives,
                ]
            ];
            $threadId = wp_insert_post($threadargs);
            if ($threadId) {
                // 清除话题列表缓存
                $this->clear_threads_cache($postId);
                /**
                 *预留定时任务
                 */
                $this->XHCron->insert($threadId, 'thread');
            }
        }
    }

    public function process_cronitem_thread($cronArr)
    {
        if (!isset($cronArr['post_id'])) {
            $cronArr['status'] = 'error';
            return $cronArr;
        }
        $appid = xh_option('appId', '');
        if (!$appid) {
            $this->XHAi->modelError('appId', '');
            return $cronArr;
        }
        $postId = $cronArr['post_id'];
        $post = get_post($postId);
        if (!$post) {
            $cronArr['status'] = 'error';
            return $cronArr;
        }
        if ($post->post_status !== 'pending' || !empty($post->post_content)) {
            $cronArr['status'] = 'success';
            return $cronArr;
        }

        $deletepost = false;
        $parent_post = null;
        $postparent = (int) get_post_meta($postId, 'xhai_postparent', true);
        if (!$postparent) {
            $deletepost = true;
        } else {
            $parent_post = get_post($postparent);
            if (!$parent_post || $parent_post->ID !== $postparent || $parent_post->post_status == 'trash') {
                $deletepost = true;
            } elseif ($parent_post->post_status !== 'publish') {
                $cronArr['status'] = 'hold';
                $cronArr['message'] = esc_html__('Associated post is not published, delaying topic content generation!', 'xhtheme-ai-toolbox');
                return $cronArr;
            }
        }
        if ($deletepost || !$parent_post) {
            wp_delete_post($postId, true);
            $cronArr['status'] = 'success';
            return $cronArr;
        }

        /**
         * 组装请求参数
         */
        $parentContent = xh_filterContent($parent_post->post_content, $parent_post);
        $respapi = wp_remote_post($this->XHAi->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $appid,
                'Referer' => home_url()
            ],
            'body' => json_encode([
                'type' => 'post-thread',
                'apivar' => XHTHEME_AI_TOOLBOX_APIVERSION,
                'content' => $parentContent,
                'imagevl' => xh_get_imagevl($parentContent),
                'posttitle' => $parent_post->post_title,
                'posttype' => $parent_post->post_type,
                'language' => xh_post_language($post->ID),
                'stream' => false,
                'backstage' => true,
                'imagerec' => xh_option('imageRecognition', true),
                'model' => xh_option('modelType', 'auto'),
                'version' => $this->XHAi->getVersion(),
                'timestamp' => time(),
                'args' => [
                    'thread' => $post->post_title,
                    'threadtype' => get_post_meta($postId, 'xhai_threadtype', true) ?: 'none',
                    'perspective' => get_post_meta($postId, 'xhai_perspective', true) ?: xh_option('primaryThreadPerspectives', ['blogger', 'expert'])
                ]
            ]),
            'timeout' => XHTHEME_AI_TOOLBOX_APITIMEOUT,
            'sslverify' => false
        ]);

        //error_log(print_r($respapi,true));
        if (!is_wp_error($respapi)) {
            $body = wp_remote_retrieve_body($respapi);
            $response_data = json_decode($body, true);
            if (isset($response_data['code']) && $response_data['code'] == 0) {
                $content = $response_data['data']['aidata']['content'];
                if (!empty($content)) {
                    $post_update = wp_update_post([
                        'ID' => $postId,
                        'post_content' => $content,
                        'post_status' => 'publish'
                    ]);
                    if ($post_update) {
                        $postparent = (int) get_post_meta($postId, 'xhai_postparent', true);

                        // 清除话题列表缓存
                        $this->clear_threads_cache($postparent);
                        //生成配图
                        if (xh_option('primaryThreadimage', false) && xh_option('imageThumb', false)) {
                            // 比例判断
                            $imageRatio = (int) xh_option('primaryThreadimageRatio', 50);
                            $randomValue = wp_rand(1, 100);
                            if ($randomValue <= $imageRatio) {
                                $CronItem = $this->XHCron->getrow('imageword' . '_' . $postId);
                                if (!$CronItem) {
                                    if (
                                        $this->XHCron->insert($postId, 'imageword', [
                                            'style' => xh_option('imageStyle', 'auto'),
                                            'size' => xh_option('imageSize', '1280x768')
                                        ], 3)
                                    ) {
                                        update_post_meta($postId, '_aiimage_status', -1);
                                    }
                                }
                            }
                        }

                        wp_update_post([
                            'ID' => $postparent,
                            'meta_input' => [
                                'xhai_thread' => 1
                            ]
                        ]);
                        $cronArr['status'] = 'success';
                        if (xh_option('primaryThreadComment', false) && xh_option('commentEnabled', false)) {
                            // 添加新任务
                            $maxnumber = wp_rand(3, 15);
                            $this->XHCron->insert($postId, 'comment', [
                                'maxday' => 7,
                                'allnumber' => $maxnumber,
                                'times' => xh_randTimeList('', 7, $maxnumber)
                            ]);
                        }
                    }
                }
            } elseif (isset($response_data['message'])) {
                $cronArr['message'] = $response_data['message'];
                $errorNum = (int) $cronArr['errornum'];
                $errorNum = $errorNum + 1;
                $cronArr['errornum'] = $errorNum;
                if ($errorNum >= 5) {
                    $cronArr['status'] = 'error';
                }
            }
        }
        return $cronArr;
    }

    public function rest_api_init()
    {
        register_rest_route('aitoolboxv1', '/thread/get', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_api_thread'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('aitoolboxv1', '/thread/comment', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_api_comment'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('aitoolboxv1', '/thread/load-more', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_api_load_more'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('aitoolboxv1', '/comment/like', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_api_comment_like'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function rest_api_thread($request)
    {
        $postId = (int) $request->get_param('postId');
        $post = get_post($postId);

        if (!$post || $post->post_type !== $this->taklsType) {
            return rest_ensure_response([
                'success' => false,
                'data' => [
                    'message' => esc_html__('This thread does not exist, please try to access another thread!', 'xhtheme-ai-toolbox')
                ]
            ]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'content' => get_the_content(null, false, $post),
                'comment_count' => get_comments_number($postId)
            ]
        ]);
    }

    public function get_thread_type($type = '', $htmlTag = false)
    {
        $threadTypeOptions = [
            'question' => __('Related Q&A', 'xhtheme-ai-toolbox'),
            'definition' => __('Expert Analysis', 'xhtheme-ai-toolbox'),
            'interest' => __('Long-tail Prediction', 'xhtheme-ai-toolbox'),
            'argument' => __('Argument Supplement', 'xhtheme-ai-toolbox')
        ];
        if (empty($type)) {
            return $threadTypeOptions;
        }
        if (isset($threadTypeOptions[$type])) {
            $typeitem = $threadTypeOptions[$type];
            return $htmlTag ? '<span class="thread-type thread-type-item-' . $type . '">' . $typeitem . '</span>' : $typeitem;
        } elseif (!empty($type)) {
            return '—';
        }
        return null;
    }

    public function get_thread_perspective($perspective = '', $htmlTag = false)
    {
        $perspectiveOptions = [
            'blogger' => __('Personal Blogger', 'xhtheme-ai-toolbox'),
            'expert' => __('Domain Expert', 'xhtheme-ai-toolbox'),
            'observer' => __('Observer Analysis', 'xhtheme-ai-toolbox'),
            'general' => __('General Discussion', 'xhtheme-ai-toolbox')
        ];
        if (empty($perspective)) {
            return $perspectiveOptions;
        }
        if (isset($perspectiveOptions[$perspective])) {
            $perspectiveitem = $perspectiveOptions[$perspective];
            return $htmlTag ? '<span class="thread-perspective thread-perspective-item-' . $perspective . '">' . $perspectiveitem . '</span>' : $perspectiveitem;
        } elseif (!empty($perspective)) {
            return '—';
        }
        return null;
    }

    public function add_templates($templates)
    {
        $templates['thread-list-s1'] = [
            'title' => __('Thread List Page', 'xhtheme-ai-toolbox'),
            'description' => __('Clean page template for displaying thread list!', 'xhtheme-ai-toolbox'),
            'post_types' => ['page'],
            'custom' => true,
            'content' => $this->get_threads_template_content()
        ];

        // 块主题话题模版处理
        $thread_template = xh_option('primaryThreadTemplate');
        if ($thread_template != 's2' && $thread_template != 'default') {
            return $templates;
        }

        $thread_single_template = $this->get_thread_template_content();
        if (!$thread_single_template) {
            return $templates;
        }
        $templates['single-thread'] = [
            'title' => __('Thread Content Page', 'xhtheme-ai-toolbox'),
            'description' => __('For displaying thread content!', 'xhtheme-ai-toolbox'),
            'post_types' => ['thread'],
            'custom' => false,
            'content' => $thread_single_template
        ];
        return $templates;
    }


    /**
     * 话题列表模板内容
     */
    public function get_threads_template_content()
    {
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed -- Heredoc is cleaner for multiline block content
        return <<<HTML
        <!-- wp:template-part {"slug":"header"} /-->

        <!-- wp:group {"align":"full","tagName":"main","className":"xhaitool-thread-wrapper","style":{"spacing":{"padding":{"top":"0","bottom":"40px"}}},"layout":{"type":"default"}} -->
        <main class="wp-block-group alignfull xhaitool-thread-wrapper" style="padding-top:0;padding-bottom:40px">
            <!-- wp:post-content /-->
        </main>
        <!-- /wp:group -->

        <!-- wp:template-part {"slug":"footer"} /-->
        HTML;
    }

    /**
     * 话题内容模板内容
     * 
     * 为 thread 自定义文章类型提供完整的单篇内容模板
     * 优先从 templates/block-single-thread.php 文件加载
     * 如果文件不存在，使用内置默认模板作为fallback
     */
    public function get_thread_template_content()
    {
        // 尝试从文件加载模版
        $template_file = plugin_dir_path(dirname(__FILE__)) . 'templates/block-single-thread.php';

        if (file_exists($template_file)) {
            // 使用输出缓冲来执行 PHP 模板并获取渲染后的内容
            ob_start();
            include $template_file;
            $template_content = ob_get_clean();
            if ($template_content !== false && $template_content !== '') {
                return $template_content;
            }
        }
        return null;
    }

    /**
     * 注册话题页面标题区块样板
     */
    public function add_patterns($patterns, $templateOut = false)
    {
        $subtitle = __('Edit page to modify display content!', 'xhtheme-ai-toolbox');
        $patterns['thread-header'] = [
            'title' => __('Thread Page Title', 'xhtheme-ai-toolbox'),
            'description' => __('Full-width thread page title block with subtitle', 'xhtheme-ai-toolbox'),
            // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed -- Heredoc is cleaner for multiline block content
            'content' => <<<HTML
            <!-- wp:group {"align":"full","className":"xhaitool-thread-title-section","style":{"spacing":{"margin":{"top":"0","bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"backgroundColor":"base","textColor":"base","layout":{"type":"constrained","contentSize":"1200px"}} -->
            <div class="wp-block-group alignfull xhaitool-thread-title-section has-base-color has-base-background-color has-text-color has-background has-link-color" style="margin-top:0;margin-bottom:0;">
                <!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"},"className":"thread-title-wrapper"} -->
                <div class="wp-block-group thread-title-wrapper">
                    <!-- wp:post-title {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"800","lineHeight":"1.1"},"spacing":{"margin":{"bottom":"16px","top":"20px"}}}} /-->
                    
                    <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px"},"color":{"text":"#ffffff"}}} -->
                    <p class="has-text-align-center has-text-color" style="color:#ffffff;font-size:18px">{$subtitle}</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
            HTML
        ];
        if ($templateOut) {
            return $patterns['thread-header']['content'];
        }
        return $patterns;
    }

    /**
     * 定时更新历史话题文章的修改时间，让历史话题保持活力
     * @return void
     */
    public function process_update_threads()
    {
        $args = [
            'post_type' => $this->taklsType,
            'posts_per_page' => wp_rand(3, 8),
            'orderby' => 'rand',
            'order' => 'DESC',
            'date_query' => [
                [
                    'before' => '10 days ago'
                ]
            ]
        ];
        $query = new \WP_Query($args);
        if ($query->have_posts()):
            while ($query->have_posts()):
                $query->the_post();
                $post_id = get_the_ID();
                $random_seconds = wp_rand(0, 10800);
                $new_timestamp = xh_local_timestamp() - $random_seconds;
                $new_date = wp_date('Y-m-d H:i:s', $new_timestamp);

                wp_update_post([
                    'ID' => $post_id,
                    'post_modified' => $new_date,
                    'post_modified_gmt' => get_gmt_from_date($new_date),
                ]);
            endwhile;
            wp_reset_postdata();
        endif;
    }

    /**
     * 处理评论模版无法指定插件的情况
     */
    public function comments_template($template)
    {
        $suffix = 'templates/comments-thread.php';
        if (substr($template, -strlen($suffix)) === $suffix) {
            return plugin_dir_path(dirname(__FILE__)) . $suffix;
        }
        return $template;
    }

    /**
     * REST API 提交评论
     */
    public function rest_api_comment($request)
    {
        $post_id = (int) $request->get_param('post_id');
        $content = trim($request->get_param('content'));
        $author = trim($request->get_param('author'));
        $email = trim($request->get_param('email'));
        $parent_id = (int) $request->get_param('parent_id'); // Add parent support

        if (!$post_id || empty($content)) {
            return new \WP_Error('invalid_param', __('Invalid parameters', 'xhtheme-ai-toolbox'), ['status' => 400]);
        }

        if (!is_user_logged_in()) {
            return new \WP_Error('auth_required', __('Please login first!', 'xhtheme-ai-toolbox'), ['status' => 401]);
        }

        $user = wp_get_current_user();
        $comment_author = $user->exists() ? $user->display_name : $author;
        $comment_author_email = $user->exists() ? $user->user_email : $email;
        $user_id = $user->exists() ? $user->ID : 0;

        if (empty($comment_author) || empty($comment_author_email)) {
            if (!$user->exists()) {
                return new \WP_Error('auth_required', __('Please login or fill in your nickname and email', 'xhtheme-ai-toolbox'), ['status' => 401]);
            }
        }

        $commentdata = [
            'comment_post_ID' => $post_id,
            'comment_content' => $content,
            'comment_author' => $comment_author,
            'comment_author_email' => $comment_author_email,
            'user_id' => $user_id,
            'comment_type' => 'comment',
            'comment_approved' => 1,
            'comment_parent' => $parent_id // Add parent to comment data
        ];

        $comment_id = wp_insert_comment($commentdata);

        if ($comment_id) {
            $comment = get_comment($comment_id);

            // 使用 Walker 类的静态方法生成 HTML，确保与页面加载时完全一致
            require_once plugin_dir_path(__FILE__) . 'class-xhtheme-walker-thread-comment.php';
            $html = \XHTheme_Walker_Thread_Comment::render_single_comment($comment, $post_id);

            return rest_ensure_response(['success' => true, 'html' => $html, 'message' => __('Comment published successfully', 'xhtheme-ai-toolbox')]);
        } else {
            return new \WP_Error('comment_failed', __('Comment publication failed', 'xhtheme-ai-toolbox'), ['status' => 500]);
        }
    }

    /**
     * REST API 加载更多话题
     */
    public function rest_api_load_more($request)
    {
        $page = (int) $request->get_param('page');
        $posts_per_page = (int) $request->get_param('posts_per_page');

        // 参数验证
        if ($page < 1) {
            $page = 1;
        }
        if ($posts_per_page < 1 || $posts_per_page > 100) {
            $posts_per_page = 6;
        }

        $args = [
            'post_type' => $this->taklsType,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => 'modified',
            'order' => 'DESC'
        ];

        $thread_query = new \WP_Query($args);

        if (!$thread_query->have_posts()) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('No more content', 'xhtheme-ai-toolbox')
            ]);
        }

        ob_start();
        while ($thread_query->have_posts()) {
            $thread_query->the_post();
            $this->render_thread_card(get_post());
        }
        wp_reset_postdata();

        $html = ob_get_clean();

        return rest_ensure_response([
            'success' => true,
            'html' => $html,
            'current_page' => $page,
            'max_pages' => $thread_query->max_num_pages
        ]);
    }

    /**
     * REST API 评论点赞/取消点赞
     */
    public function rest_api_comment_like($request)
    {
        $comment_id = (int) $request->get_param('comment_id');

        if (!$comment_id) {
            return new \WP_Error('invalid_param', __('Invalid parameters', 'xhtheme-ai-toolbox'), ['status' => 400]);
        }

        $comment = get_comment($comment_id);
        if (!$comment) {
            return new \WP_Error('comment_not_found', __('Comment does not exist', 'xhtheme-ai-toolbox'), ['status' => 404]);
        }

        // 获取当前用户ID或IP地址作为唯一标识
        $user_id = get_current_user_id();
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Fallback provided
        $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $user_identifier = $user_id ? 'user_' . $user_id : 'ip_' . md5($user_ip);

        // 获取当前点赞列表
        $likes = get_comment_meta($comment_id, '_comment_likes', true);
        if (!is_array($likes)) {
            $likes = [];
        }

        $is_liked = in_array($user_identifier, $likes);

        if ($is_liked) {
            // 取消点赞
            $likes = array_diff($likes, [$user_identifier]);
        } else {
            // 添加点赞
            $likes[] = $user_identifier;
        }

        // 更新点赞列表
        update_comment_meta($comment_id, '_comment_likes', array_values($likes));

        $like_count = count($likes);

        return rest_ensure_response([
            'success' => true,
            'liked' => !$is_liked,
            'like_count' => $like_count,
            'message' => $is_liked ? __('Like cancelled', 'xhtheme-ai-toolbox') : __('Liked successfully', 'xhtheme-ai-toolbox')
        ]);
    }

    private function init()
    {
        $primaryThread = xh_option('primaryThread', false);
        if (!$primaryThread)
            return;
        /**
         * 注册文章类型
         */
        add_action('init', [$this, 'register_post_type']);
        add_filter('xhtheme_ai_toolbox_postupdate', [$this, 'add_threadaction'], 10, 3);
        add_filter('xhaitoolbox_cronitem_thread', [$this, 'process_cronitem_thread'], 10, 1);

        // 添加自定义列
        add_filter('manage_' . $this->taklsType . '_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_' . $this->taklsType . '_posts_custom_column', [$this, 'show_custom_columns'], 10, 2);

        // 添加筛选器
        add_action('restrict_manage_posts', [$this, 'add_thread_filters']);
        add_filter('parse_query', [$this, 'filter_threads_by_meta']);

        // 为默认文章类型添加话题数量列
        add_filter('manage_post_posts_columns', [$this, 'add_post_columns']);
        add_action('manage_post_posts_custom_column', [$this, 'show_post_columns'], 10, 2);

        // 在前台文章内容下方显示话题列表
        add_filter('the_content', [$this, 'threads_after_content']);
        add_action('admin_head', [$this, 'admin_thread_style']);
        add_filter('template_include', [$this, 'thread_template']);
        add_filter('xhaitoolbox_add_block_templates', [$this, 'add_templates'], 10, 1);
        add_filter('xhaitoolbox_add_block_patterns', [$this, 'add_patterns'], 10, 1);

        // 挂载restApi接口
        add_action('rest_api_init', [$this, 'rest_api_init']);

        // 注册短代码
        add_shortcode('xhaitoolbox_threads', [$this, 'threads_shortcode']);

        // 加载话题页面静态资源
        add_action('wp_enqueue_scripts', [$this, 'enqueue_thread_page_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_filter('comments_template', [$this, 'comments_template'], 10, 1);

        // 处理话题排序问题
        add_action('xhaitoolbox_twicedaily_cron', [$this, 'process_update_threads']);
    }



    /**
     * 获取文章评论头像数组
     */
    public static function get_comment_avatars($post_id = null, $limit = 3, $size = 48)
    {
        if ($post_id === null) {
            $post_id = get_the_ID();
        }

        if (!$post_id) {
            return [];
        }
        $cache_group = 'thread_avatars';
        $cache_key = $post_id . '_' . $limit . '_' . $size;
        $cached_avatars = wp_cache_get($cache_key, $cache_group);

        if ($cached_avatars !== false) {
            return $cached_avatars;
        }
        $fetch_count = $limit * 5;

        $comments = get_comments([
            'post_id' => $post_id,
            'status' => 'approve',
            'number' => $fetch_count,
            'orderby' => 'comment_date',
            'order' => 'DESC'
        ]);

        if (empty($comments)) {
            return [];
        }
        $all_avatars = [];
        foreach ($comments as $comment) {
            $avatar_url = get_avatar_url($comment, ['size' => $size]);
            if ($avatar_url) {
                $all_avatars[] = $avatar_url;
            }
        }
        $unique_avatars = array_unique($all_avatars);

        // 将 Gravatar 头像放到数组末尾，优先使用本地头像
        $local_avatars = [];
        $gravatar_avatars = [];
        foreach ($unique_avatars as $avatar) {
            if (strpos($avatar, 'gravatar.com') !== false) {
                $gravatar_avatars[] = $avatar;
            } else {
                $local_avatars[] = $avatar;
            }
        }
        $sorted_avatars = array_merge($local_avatars, $gravatar_avatars);

        $result = array_slice($sorted_avatars, 0, $limit);
        wp_cache_set($cache_key, $result, $cache_group, HOUR_IN_SECONDS);

        return $result;
    }

    /**
     * 获取推荐话题
     */
    public static function get_related_threads($current_id, $posts_per_page = 6, $cache_count = 30)
    {
        $orderby_options = ['modified', 'date', 'title', 'comment_count', 'rand'];
        $order_options = ['DESC', 'ASC'];
        $random_orderby = $orderby_options[array_rand($orderby_options)];
        $random_order = $order_options[array_rand($order_options)];

        // 组合缓存键
        $random_order_key = $random_orderby . '_' . strtolower($random_order);
        $cache_group = 'xhthread';
        $cache_key = 'related_' . $random_order_key;
        $cached_threads = wp_cache_get($cache_key, $cache_group);

        if ($cached_threads === false) {
            $query_args = [
                'post_type' => 'thread',
                'post_status' => 'publish',
                'posts_per_page' => $cache_count,
                'orderby' => $random_orderby,
                'order' => $random_order,
                'no_found_rows' => true,
                'update_post_term_cache' => false
            ];

            $query = new \WP_Query($query_args);
            $cached_threads = $query->posts;
            if (!empty($cached_threads)) {
                wp_cache_set($cache_key, $cached_threads, $cache_group, DAY_IN_SECONDS);
            }
            wp_reset_postdata();
        }

        if (empty($cached_threads)) {
            return [];
        }
        // 过滤当前文章
        $available_threads = array_filter($cached_threads, function ($thread) use ($current_id) {
            return $thread->ID !== $current_id;
        });
        $available_threads = array_values($available_threads);

        $available_count = count($available_threads);
        if ($available_count <= $posts_per_page) {
            shuffle($available_threads);
            return $available_threads;
        }

        // 随机取出指定条数
        $random_keys = array_rand($available_threads, $posts_per_page);
        if (!is_array($random_keys)) {
            $random_keys = [$random_keys];
        }

        $result = [];
        foreach ($random_keys as $key) {
            $result[] = $available_threads[$key];
        }
        return $result;
    }
}
