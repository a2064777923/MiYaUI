<?php
namespace XHTheme\AIToolbox;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('WP_Posts_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/screen.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}

class XHPosts_List_Table extends \WP_Posts_List_Table
{

    private $XHCron;
    private $XHAi;


    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->XHAi = XHThemeAi::getInstance();
        $this->XHCron = XHCronQueue::getInstance();
        \add_action('admin_head', [$this, 'print_responsive_list_table_styles']);
    }

    private function get_tagcolumn($post, $tremType)
    {
        $terms = get_the_terms($post->ID, $tremType);

        if (!empty($terms) && !is_wp_error($terms)) {
            $links = array_map(function ($term) {
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(add_query_arg(array('tag' => $term->slug), 'edit.php')),
                    esc_html($term->name)
                );
            }, $terms);
            echo wp_kses_post(implode('、', $links));
        } else {
            $postTypedata = xh_postTypedata($post->post_type);
            $postTypeTasks = $postTypedata && isset($postTypedata['tasks']) && is_array($postTypedata['tasks']) ? $postTypedata['tasks'] : [];
            if (!xh_option('tagEnabled', false) || !in_array('tags', $postTypeTasks)) {
                echo '——';
                return;
            }
            echo $this->XHCron->addcrontask('tags', (int) $post->ID);
        }
    }

    /**
     * 重写产品标签列
     */
    public function column_product_tag($post)
    {
        $this->get_tagcolumn($post, 'product_tag');
    }

    /**
     * 重写网址标签列
     */
    public function column_default($post, $column_name)
    {
        if ($column_name == 'taxonomy-sitetag') {
            $this->get_tagcolumn($post, 'sitetag');
        } else {
            parent::column_default($post, $column_name);
        }
    }

    /**
     * 重写产品评论列
     */
    public function column_product_comments($post)
    {
        printf('<style>@media (min-width: 1367px){.column-product_comments {width:150px}}</style>');
        echo '<div class="column-comments">';
        if (!$this->XHAi->isMember() || !xh_option('commentEnabled', false)) {
            parent::column_comments($post);
        } else {
            // 获取AI评论数量
            $ai_comments = get_comments([
                'post_id' => $post->ID,
                'type' => 'ai_comment',
                'count' => true
            ]);
            $comment_count = get_comments_number($post->ID);
            if ($ai_comments > 0 || $comment_count > 0) {
                parent::column_comments($post);
            }

            echo $this->XHCron->addcrontask('comment', (int) $post->ID, [
                'subif' => $ai_comments < 5 && $comment_count < 5
            ]);
        }
        echo '</div>';
    }

    /**
     * 重写标签列显示
     */
    public function column_tags($post)
    {
        $this->get_tagcolumn($post, 'post_tag');
    }

    /**
     * 重写评论列显示
     */
    public function column_comments($post)
    {
        $postTypedata = xh_postTypedata($post->post_type);
        $postTypeTasks = $postTypedata && isset($postTypedata['tasks']) && is_array($postTypedata['tasks']) ? $postTypedata['tasks'] : [];
        if (!xh_option('commentEnabled', false) || !in_array('comments', $postTypeTasks)) {
            parent::column_comments($post);
            return;
        }

        // 获取AI评论数量
        $ai_comments = get_comments([
            'post_id' => $post->ID,
            'type' => 'ai_comment',
            'count' => true
        ]);
        $comment_count = get_comments_number($post->ID);
        if ($ai_comments > 0 || $comment_count > 0) {
            parent::column_comments($post);
        }

        echo $this->XHCron->addcrontask('comment', (int) $post->ID, [
            'subif' => $ai_comments < 5 && $comment_count < 5
        ]);
    }

    /**
     * 为小屏优化列表显示样式
     */
    public function print_responsive_list_table_styles()
    {
        $screen = \function_exists('get_current_screen') ? \get_current_screen() : null;
        if (empty($screen) || (strpos($screen->id, 'edit') === false && strpos($screen->id, 'edit-') === false)) {
            return;
        }
        ?>
        <style id="xhtheme-ai-list-responsive">
            /* 仅在较小屏幕下放宽布局，避免内容拥挤 */
            @media (max-width: 1366px) {
                .wp-list-table.fixed {
                    table-layout: auto;
                }

                .wp-list-table td,
                .wp-list-table th {
                    white-space: normal;
                    word-break: break-word;
                }

                /* 让常见列宽度自适应，避免硬性挤压 */
                .wp-list-table .column-title {
                    min-width: 220px;
                }

                .wp-list-table .column-date,
                .wp-list-table .column-author,
                .wp-list-table .column-categories,
                .wp-list-table .column-tags,
                .wp-list-table .column-product_comments,
                .wp-list-table .column-comments {
                    width: auto !important;
                }

                /* 行内操作按钮在小屏下堆叠展示，避免遮挡 */
                .wp-list-table .row-actions {
                    position: static;
                    display: block;
                    margin-top: 6px;
                }
            }
        </style>
        <?php
    }
}

// 替换默认的列表表格类
