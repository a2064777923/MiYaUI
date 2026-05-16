<?php

/**
 * XHTheme PosItem
 *
 * @package XHTheme
 * @since 1.0.0
 */

namespace XHTheme\AIToolbox;

final class XHPostAuto
{

    private static $instance;
    private $XHCron;
    private $XHAi;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->XHAi = XHThemeAi::getInstance();
        $this->XHCron = XHCronQueue::getInstance();
        require_once 'class-xhtheme-list-table.php';

        add_filter('wp_list_table_class_name', [$this, 'filter_post_columns'], 10, 2);
        add_filter('manage_post_posts_columns', [$this, 'add_summary_column']);
        add_action('manage_post_posts_custom_column', [$this, 'render_summary_column'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_ajax_handle_xhaitool_aitasks', [$this, 'handle_xhaitool_aitasks']);

        // 产品
        add_filter('manage_product_posts_columns', [$this, 'filter_product_columns']);
        add_filter('woocommerce_product_reviews_table_columns', [$this, 'filter_product_reviews_columns']);
        add_action('woocommerce_product_reviews_table_column_comments_status', [$this, 'fill_product_reviews_column']);


        // 定时任务处理
        add_filter('xhaitoolbox_cronitem_summary', [$this, 'process_aitasks'], 10, 2);
        add_filter('xhaitoolbox_cronitem_tags', [$this, 'process_aitasks'], 10, 2);
        add_filter('xhaitoolbox_cronitem_threadlist', [$this, 'process_aitasks'], 10, 2);
        add_filter('xhaitoolbox_cronitem_primary', [$this, 'process_aitasks'], 10, 2);

        // 自动化
        add_action('wp_ajax_xhaitoolbox_automate_rules', [$this, 'ajaxAutomateRules']);
        add_action('wp_ajax_xhaitoolbox_automate_rules_save', [$this, 'ajaxAutomateRulesSave']);
        add_action('wp_after_insert_post', [$this, 'handle_updated_meta'], 10, 3);
        add_action('add_meta_boxes', [$this, 'add_aitasks_metabox']);
        add_action('save_post', [$this, 'save_aitasks_metabox']);

        // 处理默认分类
        add_action('admin_notices', [$this, 'default_term_notices']);
        add_action('admin_init', [$this, 'default_term_setting']);
    }

    public function default_term_setting()
    {
        if (post_type_exists('sites')) {
            add_settings_field(
                'default_favorites',
                esc_html__('Default site category', 'xhtheme-ai-toolbox'),
                [$this, 'render_default_term_dropdown'],
                'writing'
            );
            register_setting('writing', 'default_favorites', array(
                'sanitize_callback' => 'intval'
            ));
        }
    }

    public function render_default_term_dropdown()
    {
        $default_trem = get_option('default_favorites');
        $taxonomy = xh_postType_config('sites', 'categories');
        wp_dropdown_categories(array(
            'taxonomy' => $taxonomy,
            'name' => 'default_favorites',
            'selected' => $default_trem,
            'option_none_value' => '',
            'hierarchical' => true,
            'show_count' => false,
            'hide_empty' => false
        ));
        echo '<p class="description">';
        /* translators: instruction for setting default category */
        esc_html_e('Please set the default category for the site category column to avoid AI mistakes in processing intelligent classification!', 'xhtheme-ai-toolbox');
        echo '</p>';
    }

    public function default_term_notices()
    {
        $notices = [];
        if (post_type_exists('sites')) {
            $typeData = xh_postType_config('sites', 'tasks');
            if ($typeData && is_array($typeData) && in_array('category', $typeData)) {
                $default_trem = get_option('default_favorites');
                if (!$default_trem) {
                    // 翻译
                    $notices[] = sprintf(
                        /* translators: %s: site category column name */
                        esc_html__('Please set the default category for %s to avoid AI mistakes in processing intelligent classification!', 'xhtheme-ai-toolbox'),
                        sprintf('<strong>%s</strong>', esc_html__('「Site category column」', 'xhtheme-ai-toolbox'))
                    );
                }
            }
        }

        if ($notices) {
?>
            <div class="notice notice-error">
                <p><?php echo wp_kses_post(implode('</p><p>', $notices)); ?></p>
                <p><a
                        href="<?php echo esc_url(admin_url('options-writing.php')); ?>"><strong><?php esc_html_e('Set now', 'xhtheme-ai-toolbox'); ?></strong></a><span
                        style="margin-left:20px"> ——
                        <?php esc_html_e('Message from [XHTheme AI Toolbox] (the message will no longer be displayed after disabling automatic classification or setting the default category)!', 'xhtheme-ai-toolbox'); ?></span>
                </p>
            </div>
        <?php
        }
    }



    public function fill_product_reviews_column($comment)
    {
        echo '<p style="padding-top:5px;padding-bottom:5px">';
        if ($comment->comment_approved == 0 && $comment->comment_type === 'ai_comment') {
            echo '<span style="color: #ff9800;">';
            /* translators: %s: scheduled date and time */
            echo esc_html(sprintf(__('Schedule [%s]', 'xhtheme-ai-toolbox'), mysql2date('Y-m-d H:i', $comment->comment_date)));
            echo '</span>';
        } else {
            $status = wp_get_comment_status($comment);
            $statuses = get_comment_statuses();
            switch ($status) {
                case 'approved':
                    $status = 'approve';
                    break;
                case 'unapproved':
                    $status = 'hold';
                    break;
            }
            if (isset($statuses[$status])) {
                echo esc_html($statuses[$status]);
            } else {
                echo esc_html($status);
            }
        }
        echo '</p>';
    }

    public function filter_product_reviews_columns($columns)
    {
        if ($this->XHAi->isMember() && xh_option('commentEnabled', false)) {
            $columns['comments_status'] = __('Status', 'xhtheme-ai-toolbox');
        }
        return $columns;
    }

    public function add_aitasks_metabox()
    {
        add_meta_box(
            'xhaitoolbox_aitasks_metabox',
            __('Join AI Queue', 'xhtheme-ai-toolbox'),
            [$this, 'render_aitasks_metabox'],
            xh_postTypes(),
            'side',
            'default',
            [
                '__back_compat_meta_box' => true
            ]
        );
    }

    public function render_aitasks_metabox($post)
    {
        $aitasks_status = get_post_meta($post->ID, '_xhaitool_aitasks_status', true);
        wp_nonce_field('xhaitoolbox_aitasks_metabox', 'xhaitoolbox_aitasks_metabox_nonce');
        // 获取当前值
        $checked = false;
        if (xh_postType_config($post->post_type, 'queue')) {
            $checked = true;
        }
        ?>
        <style>
            #xhaitoolbox_aitasks_metabox .misc-pub-section {
                padding: 15px 10px 10px;
            }

            <?php
            if ($aitasks_status) {
            ?>#xhaitoolbox_aitasks_metabox {
                display: none;
            }

            <?php
            }
            /**
             * 自动化任务配置检测
             */
            $aitaskoption = false;
            if (xh_postType_config($post->post_type, 'tasks')) {
                $aitaskoption = true;
            }
            ?>
        </style>
        <div class="misc-pub-section">
            <label for="_xhaitool_aitasks">
                <input type="checkbox" id="_xhaitool_aitasks" name="_xhaitool_aitasks" value="1" <?php checked($checked); ?>
                    <?php disabled(!$aitaskoption); ?> />
                <?php esc_html_e('Join AI Queue', 'xhtheme-ai-toolbox'); ?>
            </label>
            <?php
            if (!$aitaskoption) {
                printf(
                    '
                    <p style="color:#f66">%s</p>
                    <p><a href="%s">%s</a></p>
                    ',
                    esc_html__('Queue automation tasks not configured.', 'xhtheme-ai-toolbox'),
                    esc_url(admin_url('admin.php?page=xhtheme-ai-automate')),
                    esc_html__('Configure now', 'xhtheme-ai-toolbox')
                );
            } else {
                printf(
                    '<p style="color:#f66">%s</p>',
                    esc_html__('AI queue skips draft articles.', 'xhtheme-ai-toolbox')
                );
            }
            ?>
        </div>
<?php
    }

    public function save_aitasks_metabox($post_id)
    {
        // 检查nonce
        if (
            !isset($_POST['xhaitoolbox_aitasks_metabox_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xhaitoolbox_aitasks_metabox_nonce'])), 'xhaitoolbox_aitasks_metabox')
        ) {
            return;
        }

        // 检查权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // 保存数据
        if (isset($_POST['_xhaitool_aitasks'])) {
            update_post_meta($post_id, '_xhaitool_aitasks', empty($_POST['_xhaitool_aitasks']) ? 0 : 1);
        } else {
            add_post_meta($post_id, '_xhaitool_aitasks', 0, true);
        }
    }

    function handle_updated_meta($post_id, $post, $update)
    {
        if ($post->post_status == 'draft' || $post->post_status == 'auto-draft') {
            return;
        }

        /**
         * 获取类型配置
         */
        $postType_tasks = xh_postType_config($post->post_type, 'tasks');
        if (!$postType_tasks || !is_array($postType_tasks)) {
            return;
        }

        if (get_post_meta($post_id, '_xhaitool_aitasks_status', true)) {
            return;
        }

        $cronqueue = get_post_meta($post->ID, '_xhaitool_aitasks', true);

        if (empty($cronqueue) && $cronqueue !== '') {
            return;
        }

        if ($cronqueue == '' && !xh_postType_config($post->post_type, 'queue')) {
            return;
        }

        delete_post_meta($post->ID, '_xhaitool_aitasks');

        /**
         * 获取会员状态
         */
        $usertype = $this->XHAi->isMember();

        // 'summary', 'slug', 'tags', 'comments', 'category', 'thread', 'aiimage'
        $option_apitypes = [];
        $contentNone = empty(trim($post->post_content)) || strlen(trim($post->post_content)) < 10;
        $taxonomy = xh_postType_config($post->post_type, 'categories');
        $default_category = $taxonomy ? get_option('default_' . $taxonomy) : 0;
        $autoCategory = $taxonomy ? isset($postType_tasks['category']) : false;
        foreach ($postType_tasks as $tasks => $config) {
            if (!$usertype && ($tasks == 'comments' || $tasks == 'thread' || $tasks == 'aiimage'))
                continue;
            switch ($tasks) {
                case 'tags':
                    if (has_term('', xh_postType_config($post->post_type, 'tags'), $post)) {
                        continue 2;
                    }
                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $option_apitypes['tags'] = [
                            'number' => [
                                'min' => max(1, xh_option('tagMinCount', 0)),
                                'max' => max(1, xh_option('tagMaxCount', 6))
                            ],
                            'seotitle' => (int) xh_option('tagSeoTitle', false)
                        ];
                        if ($filter_category == 2) {
                            $option_apitypes['tags']['config']['categories'] = $config;
                        }
                    }
                    break;
                case 'aiimage':
                    $has_content_img = strpos($post->post_content, '<img') !== false;
                    if ($has_content_img || has_post_thumbnail($post_id)) {
                        continue 2;
                    }
                    $_aiimage_status = get_post_meta($post_id, '_aiimage_status', true);
                    if ($_aiimage_status == -1) {
                        continue 2;
                    }
                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $option_apitypes['imageword'] = [
                            'style' => xh_option('imageStyle', 'auto'),
                            'size' => xh_option('imageSize', '1280x768')
                        ];
                        if ($filter_category == 2) {
                            $option_apitypes['imageword']['config']['categories'] = $config;
                        }
                    }


                    break;
                case 'comments':
                    if (get_comments_number($post_id) > 5) {
                        continue 2;
                    }
                    $commentNumber = 1;
                    $commentMinCount = max(1, xh_option('commentMinCount', 0));
                    $commentMaxCount = max(1, xh_option('commentMaxCount', 0));
                    if ($commentMaxCount > $commentMinCount) {
                        $commentNumber = wp_rand($commentMinCount, $commentMaxCount);
                    }
                    $commentDays = xh_option('commentMaxday', 1) ?: 1;
                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $option_apitypes['comment'] = [
                            'allnumber' => $commentNumber,
                            'maxday' => $commentDays,
                            'times' => xh_randTimeList('', $commentDays, $commentNumber)
                        ];
                        if ($filter_category == 2) {
                            $option_apitypes['comment']['config']['categories'] = $config;
                        }
                    }


                    break;
                case 'summary':
                    $_excerptai = get_post_meta($post_id, '_excerptai', true);
                    if ($_excerptai) {
                        continue 2;
                    }
                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $option_apitypes['summary'] = [
                            'length' => (int) xh_option('summaryMaxLength', 150),
                            'model' => xh_option('summaryModel', 'summary')
                        ];
                        if ($filter_category == 2) {
                            $option_apitypes['summary']['config']['categories'] = $config;
                        }
                    }

                    break;
                case 'slug':
                    $post_slug = $post->post_name;
                    $post_oldslug = sanitize_title($post->post_title);
                    if ($post_slug !== $post_oldslug) {
                        continue 2;
                    }

                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $option_apitypes['primary']['postslug'] = true;
                        if ($filter_category == 2) {
                            $option_apitypes['primary']['postslug'] = [
                                'config' => [
                                    'categories' => $config
                                ]
                            ];
                        }
                    }

                    break;
                case 'category':
                    if (!$taxonomy) {
                        continue 2;
                    }
                    $post_categories = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'ids']);
                    if (empty($post_categories) || (count($post_categories) == 1 && in_array($default_category, $post_categories))) {
                        $categories = get_categories([
                            'taxonomy' => $taxonomy,
                            'hide_empty' => false,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ]);

                        $terms = [];
                        $default_term = get_term($default_category, $taxonomy);
                        $default_termid = $default_term ? $default_term->term_id : 0;

                        // 构建分类数组
                        foreach ($categories as $category) {
                            $terms[] = [
                                'id' => $category->term_id,
                                'name' => $category->name,
                            ];
                        }
                        $option_apitypes['primary']['postterms'] = [
                            'terms' => $terms,
                            'multiple' => false, //分类多选单选
                            'default' => $default_termid
                        ];
                    } else {
                        continue 2;
                    }

                    break;
                case 'thread':
                    // 判断是否有话题
                    $XHThread = XHThread::getInstance();
                    if ($XHThread->get_threads($post_id, ['publish', 'pending'])) {
                        continue 2;
                    }
                    $filter_category = xh_filter_category($post, $config, $taxonomy, $default_category, $autoCategory);
                    if ($filter_category) {
                        $threadNumber = xh_option('primaryThreadNumber', 3);
                        $threadTypes = xh_option('primaryThreadTypes', ['question', 'definition']);
                        $threadPerspectives = xh_option('primaryThreadPerspectives', ['blogger', 'expert']);
                        if ($threadNumber) {
                            $option_apitypes['primary']['postthread'] = [
                                'number' => $threadNumber,
                                'types' => $threadTypes,
                                'perspectives' => $threadPerspectives
                            ];
                            if ($filter_category == 2) {
                                $option_apitypes['primary']['postthread']['config']['categories'] = $config;
                            }
                        }
                    }
                    break;
            }
        }
        if (empty($option_apitypes)) {
            return;
        }

        $addtasks = false;
        $taskStatus = 'hold';
        $filterTasks = '';

        /**
         * 内容为空处理
         */
        if ($contentNone) {
            if (isset($option_apitypes['imageword'])) {
                $CronItem = $this->XHCron->getrow('imageword' . '_' . $post_id);
                if (!$CronItem) {
                    $option_apitypes['imageword']['config']['taskStatus'] = 1;
                    if ($this->XHCron->insert($post_id, 'imageword', $option_apitypes['imageword'])) {
                        $addtasks = true;
                        $taskStatus = 'freeze';
                        $filterTasks = 'imageword';
                    } else {
                        do_action('xhaitoolbox_log_error', '自动任务添加失败-imageword');
                    }
                }
            }
        }

        if ($autoCategory && $taskStatus == 'hold') {
            if (isset($option_apitypes['primary']['postterms'])) {
                $CronItem = $this->XHCron->getrow('primary' . '_' . $post_id);
                if (!$CronItem) {
                    $option_apitypes['primary']['config']['taskStatus'] = 1;
                    if ($this->XHCron->insert($post_id, 'primary', $option_apitypes['primary'])) {
                        $addtasks = true;
                        $taskStatus = 'freeze';
                        $filterTasks = 'primary';
                    } else {
                        do_action('xhaitoolbox_log_error', '自动任务添加失败-primary');
                    }
                }
            }
        }

        foreach ($option_apitypes as $key => $items) {
            if ($filterTasks && $key == $filterTasks)
                continue;
            $CronItem = $this->XHCron->getrow($key . '_' . $post_id);
            if ($CronItem) {
                continue;
            }
            if ($this->XHCron->insert($post_id, $key, $items, 0, $taskStatus)) {
                $addtasks = true;
            } else {
                do_action('xhaitoolbox_log_error', '自动任务添加失败-' . $key);
            }
        }

        if ($addtasks) {
            update_post_meta($post_id, '_xhaitool_aitasks_status', 1);
        }
    }


    /**
     * AJAX handler for getting automation rules
     */
    public function ajaxAutomateRules()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        // 获取规则数据
        $rules_json = get_option('xhtheme_ai_toolbox_automate_rules', '[]');
        $rules_data = json_decode($rules_json, true);

        if (!is_array($rules_data)) {
            $rules_data = [];
        }

        if (empty($rules_data)) {
            $rules_data = xh_migrate_automate_config();
        }

        if (!empty($rules_data) && is_array($rules_data)) {
            $post_types = get_post_types(array(
                'public' => true,
                '_builtin' => false
            ), 'names');
            if (!$post_types) {
                $post_types = [];
            }
            $post_types = array_keys($post_types);
            $post_types = array_merge(['post'], $post_types);

            $datas = xh_postTypedata();
            foreach ($rules_data as $key => $rule) {
                $postType = $rule['postType'];
                if (!isset($datas[$postType]) || !in_array($postType, $post_types)) {
                    unset($rules_data[$key]);
                }
            }
        }

        wp_send_json_success(['rules' => array_values($rules_data)]);
    }

    /**
     * AJAX handler for saving automation rules
     */
    public function ajaxAutomateRulesSave()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        // 从 POST 获取 rules 数据
        $rules_raw = isset($_POST['rules']) ? wp_unslash($_POST['rules']) : '[]';
        $rules = json_decode($rules_raw, true);

        if (!is_array($rules)) {
            wp_send_json_error(['message' => __('Invalid rules data', 'xhtheme-ai-toolbox')]);
        }

        $validated_rules = [];

        foreach ($rules as $rule) {
            if (!isset($rule['id']) || !isset($rule['name']) || !isset($rule['postType'])) {
                continue;
            }
            $validated_tasks = [];
            if (isset($rule['tasks']) && is_array($rule['tasks'])) {
                foreach ($rule['tasks'] as $task_key => $task_config) {
                    if (is_array($task_config)) {
                        $validated_tasks[$task_key] = [
                            'enabled' => isset($task_config['enabled']) ? (bool) $task_config['enabled'] : false,
                            'categories' => [
                                'type' => isset($task_config['categories']['type']) && $task_config['categories']['type'] !== 'all' && !empty($task_config['categories']['list']) ? sanitize_text_field($task_config['categories']['type']) : 'all',
                                'list' => isset($task_config['categories']['list']) && is_array($task_config['categories']['list'])
                                    ? array_map('intval', $task_config['categories']['list']) : []
                            ]
                        ];
                    } else {
                        $validated_tasks[$task_key] = [
                            'enabled' => (bool) $task_config,
                            'categories' => ['type' => 'all', 'list' => []]
                        ];
                    }
                }
            }

            $validated_rule = [
                'id' => sanitize_text_field($rule['id']),
                'name' => sanitize_text_field($rule['name']),
                'postType' => sanitize_text_field($rule['postType']),
                'enabled' => isset($rule['enabled']) ? (bool) $rule['enabled'] : true,
                'defaultTrigger' => isset($rule['defaultTrigger']) ? sanitize_text_field($rule['defaultTrigger']) : 'auto',
                'publishOnStatuses' => isset($rule['publishOnStatuses']) && is_array($rule['publishOnStatuses']) ? array_map('sanitize_text_field', $rule['publishOnStatuses']) : [],
                'tasks' => $validated_tasks,
                'createdAt' => isset($rule['createdAt']) ? sanitize_text_field($rule['createdAt']) : '',
                'updatedAt' => isset($rule['updatedAt']) ? sanitize_text_field($rule['updatedAt']) : ''
            ];

            $validated_rules[] = $validated_rule;
        }

        // 保存规则到数据库
        update_option('xhtheme_ai_toolbox_automate_rules', wp_json_encode($validated_rules));

        // 旧配置清理
        if (get_option('xhtheme_ai_toolbox_automate', false)) {
            $automate_migrated = get_transient('xhtheme_ai_toolbox_automate_migrated');
            if (!$automate_migrated) {
                delete_option('xhtheme_ai_toolbox_automate');
            } elseif ($automate_migrated == 'load') {
                set_transient('xhtheme_ai_toolbox_automate_migrated', 'yes', 86400 * 7);
            }
        }

        wp_send_json_success([
            'message' => __('Automation rules saved successfully', 'xhtheme-ai-toolbox'),
            'data' => $validated_rules
        ]);
    }

    public function process_aitasks($cronArr, $itemType)
    {
        if (!isset($cronArr['post_id'])) {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('Invalid parameters [code:003]', 'xhtheme-ai-toolbox');
            return $cronArr;
        }

        $appid = xh_option('appId', '');
        if (!$appid) {
            $this->XHAi->modelError('appId', '');
            return $cronArr;
        }
        $postId = $cronArr['post_id'];
        $post = get_post($postId);

        if (!$post || $post->post_status == 'trash') {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('Post does not exist or has been deleted!', 'xhtheme-ai-toolbox');
            return $cronArr;
        }

        $postTypedata = xh_postTypedata($post->post_type);

        $apitype = '';
        $apiArgs = isset($cronArr['data']) && is_array($cronArr['data']) ? $cronArr['data'] : [];
        switch ($itemType) {
            case 'summary':
                $apitype = 'post-excerpt';
                break;
            case 'tags':
                $apitype = 'post-tags';
                break;
            case 'threadlist':
            case 'primary':
                $apitype = 'post-primary';
                break;
        }

        if (!$apitype) {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('Invalid task definition [code:002]', 'xhtheme-ai-toolbox');
            return $cronArr;
        }

        $config = [];
        if (isset($apiArgs['config'])) {
            $config = is_array($apiArgs['config']) ? $apiArgs['config'] : [];
            unset($apiArgs['config']);
        }

        /**
         * 预处理分类过滤
         */
        if ($apitype == 'post-primary') {
            if (is_array($apiArgs)) {
                if (isset($apiArgs['postterms'])) {
                    foreach ($apiArgs as $key => $task) {
                        if (is_array($task) && isset($task['config'])) {
                            $config[$key] = $task['config'];
                            $apiArgs[$key] = 1;
                        }
                    }
                    $config['checkCategory'] = true;
                } else {
                    foreach ($apiArgs as $key => $task) {
                        if (is_array($task) && isset($task['config'])) {
                            if (isset($task['config']['categories']) && !xh_loop_filter_category($postId, $task['config']['categories'])) {
                                unset($apiArgs[$key]);
                            } else {
                                $apiArgs[$key] = 1;
                            }
                        }
                    }
                    if (empty($apiArgs)) {
                        $cronArr['status'] = 'skip';
                        $cronArr['message'] = esc_html__('Category not match', 'xhtheme-ai-toolbox');
                        // 跳过判断文章是否需要发布
                        xh_loop_auto_publish($post, $cronArr['cron_id']);
                        return $cronArr;
                    }
                }
            }
        } elseif (isset($config['categories'])) {
            if (!xh_loop_filter_category($postId, $config['categories'])) {
                $cronArr['status'] = 'skip';
                $cronArr['message'] = esc_html__('Category not match', 'xhtheme-ai-toolbox');
                // 跳过判断文章是否需要发布
                xh_loop_auto_publish($post, $cronArr['cron_id']);
                return $cronArr;
            }
        }

        /**
         * 组装请求参数
         */
        $postContent = xh_filterContent($post->post_content, $post);
        $respapi = wp_remote_post($this->XHAi->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $appid,
                'Referer' => home_url()
            ],
            'body' => json_encode([
                'type' => $apitype,
                'apivar' => XHTHEME_AI_TOOLBOX_APIVERSION,
                'content' => $postContent,
                'imagevl' => xh_get_imagevl($postContent),
                'posttype' => isset($postTypedata['slug']) ? $postTypedata['slug'] : $post->post_type,
                'language' => xh_post_language($post->ID),
                'posttitle' => $post->post_title,
                'stream' => false,
                'backstage' => true,
                'imagerec' => xh_option('imageRecognition', true),
                'model' => xh_option('modelType', 'auto'),
                'version' => $this->XHAi->getVersion(),
                'timestamp' => time(),
                'args' => $apiArgs
            ]),
            'timeout' => XHTHEME_AI_TOOLBOX_APITIMEOUT,
            'sslverify' => false
        ]);
        if (!is_wp_error($respapi)) {
            $body = wp_remote_retrieve_body($respapi);
            $response_data = json_decode($body, true);
            if (isset($response_data['code']) && $response_data['code'] == 0) {
                $aidata = $response_data['data']['aidata'];
                if (!empty($aidata)) {
                    $updatePostArr = [];
                    $updatePassok = false;
                    switch ($itemType) {
                        case 'summary':
                            $content = $aidata['content'];
                            if ($content) {
                                $updatePostArr = [
                                    'meta_input' => [
                                        '_xhai_excerpt' => trim($content),
                                        '_excerptai' => 2
                                    ]
                                ];
                            }
                            break;
                        case 'tags':
                            if (is_array($aidata)) {
                                $newtags = [];
                                $tremName = isset($postTypedata['tags']) ? $postTypedata['tags'] : 'post_tag';
                                foreach ($aidata as $key => $tagdata) {
                                    if (empty($tagdata) || !is_array($tagdata) || empty($tagdata['name']) || is_numeric($tagdata['name']))
                                        continue;
                                    $tagstrName = sanitize_text_field($tagdata['name']);
                                    $tagExists = term_exists($tagstrName, $tremName);
                                    if (!$tagExists) {
                                        $newtag = wp_insert_term($tagstrName, $tremName, [
                                            'description' => $tagdata['description'],
                                            'slug' => $tagdata['slug']
                                        ]);
                                        if (!is_wp_error($newtag)) {
                                            $newtags[] = $tagstrName;
                                            if (isset($tagdata['title']) && !empty($tagdata['title'])) {
                                                add_term_meta($newtag['term_id'], '_seotitle', sanitize_text_field($tagdata['title']));
                                            }
                                        }
                                    } else {
                                        $newtags[] = $tagstrName;
                                    }
                                }

                                if (!empty($newtags)) {
                                    $updatePostArr['tax_input'][$tremName] = $newtags;
                                }
                            }
                            break;
                        case 'threadlist':
                        case 'primary':
                            // 为文章设置分类
                            $checkCategory = isset($config['checkCategory']);
                            $newCategory = [];
                            if (isset($aidata['category']) && !empty($aidata['category'])) {
                                $tremName = $postTypedata ? $postTypedata['categories'] : 'category';
                                $updatePostArr['tax_input'][$tremName] = array_map('absint', (array) $aidata['category']);
                                if ($checkCategory) {
                                    $newCategory = $updatePostArr['tax_input'][$tremName];
                                }
                            }
                            // 为文章添加话题
                            $threadlist = isset($aidata['thread']) && is_array($aidata['thread']) ? $aidata['thread'] : [];
                            if (!empty($threadlist) && (!$checkCategory || !isset($config['thread']['categories']) || xh_loop_filter_category($postId, $config['thread']['categories'], $newCategory))) {
                                $threadList = XHThread::getInstance();
                                $threadList->add_thread($threadlist, $post->ID);
                                $updatePassok = true;
                            }

                            // 为文章设置别名
                            if (isset($aidata['slug']) && !empty($aidata['slug'])) {
                                if (!$checkCategory || !isset($config['postslug']['categories']) || xh_loop_filter_category($postId, $config['postslug']['categories'], $newCategory)) {
                                    $updatePostArr['post_name'] = sanitize_title($aidata['slug']);
                                }
                            }
                            break;
                    }

                    $postAutopublish = true;
                    if (!empty($updatePostArr)) {
                        $updatePostArr['ID'] = $post->ID;
                        if ($post->post_status !== 'publish') {
                            $postAutopublish = false;
                            $new_status = xh_check_publish_status($post, $cronArr['cron_id']);
                            if ($new_status) {
                                $updatePostArr['post_status'] = 'publish';
                            }
                        }
                        $updatePassok = wp_update_post($updatePostArr);
                    }

                    if ($updatePassok) {
                        $cronArr['status'] = 'success';

                        /**
                         * 自动发布文章检查
                         */
                        if ($postAutopublish) {
                            xh_loop_auto_publish($post, $cronArr['cron_id']);
                        }

                        /**
                         * 在这里处理任务完成后的操作
                         */
                        if (isset($config['taskStatus'])) {
                            xh_check_hold_task($itemType, $postId);
                        }
                    }
                }
            } elseif (isset($response_data['message'])) {
                $cronArr['message'] = $response_data['message'];
                $errorNum = (int) $cronArr['errornum'];
                $errorNum = $errorNum + 1;
                $cronArr['errornum'] = $errorNum;
                if ($response_data['code'] == 2 || $errorNum >= 5) {
                    $cronArr['status'] = 'error';
                }
            }
        } else {
            $error_message = $respapi->get_error_message();
            $cronArr['message'] = esc_html__('Request error or timeout, please try again later!', 'xhtheme-ai-toolbox');
            do_action('xhaitoolbox_log_error', 'API request failed: ' . $error_message);
        }
        return $cronArr;
    }

    public function handle_xhaitool_aitasks()
    {
        // 验证 nonce
        check_ajax_referer('xhaitoolbox_nonce', '_ajax_nonce');

        // 获取请求参数
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $task_type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';

        // 验证参数
        if (!$post_id || !$task_type) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }
        if (!in_array($task_type, ['summary', 'threadlist', 'comment', 'tags'])) {
            wp_send_json_error(['message' => esc_html__('Invalid task type', 'xhtheme-ai-toolbox')]);
        }

        $taskArgs = [];

        switch ($task_type) {
            case 'comment':
                $commentNumber = 1;
                $commentMinCount = max(1, xh_option('commentMinCount', 0));
                $commentMaxCount = max(1, xh_option('commentMaxCount', 0));
                if ($commentMaxCount > $commentMinCount) {
                    $commentNumber = wp_rand($commentMinCount, $commentMaxCount);
                }
                $commentDays = xh_option('commentMaxday', 1) ?: 1;
                $taskArgs['allnumber'] = $commentNumber;
                $taskArgs['maxday'] = $commentDays;
                $taskArgs['times'] = xh_randTimeList('', $commentDays, $commentNumber);
                break;
            case 'tags':
                $taskArgs['number'] = [
                    'min' => max(1, xh_option('tagMinCount', 0)),
                    'max' => max(1, xh_option('tagMaxCount', 6))
                ];
                $taskArgs['seotitle'] = (int) xh_option('tagSeoTitle', false);
                break;
            case 'summary':
                $taskArgs['length'] = (int) xh_option('summaryMaxLength', 150);
                $taskArgs['model'] = xh_option('summaryModel', 'summary');
                break;
            case 'threadlist':
                $taskArgs['postthread'] = [
                    'number' => (int) xh_option('primaryThreadNumber', 3),
                    'types' => xh_option('primaryThreadTypes', ['question', 'definition']),
                    'perspectives' => xh_option('primaryThreadPerspectives', ['blogger', 'expert'])
                ];
                break;
        }

        $CronItem = $this->XHCron->getrow($task_type . '_' . $post_id, 'skip');
        if ($CronItem) {
            $this->XHCron->delete($task_type . '_' . $post_id);
        }

        $cronAdd = $this->XHCron->insert($post_id, $task_type, $taskArgs);
        if ($cronAdd) {
            $CronItem = $this->XHCron->getrow($task_type . '_' . $post_id);
            if ($CronItem) {
                wp_send_json_success([
                    'message' => sprintf('<span style="background: %s;-webkit-background-clip: text;background-clip: text;color: transparent;font-weight: 600;">%s</span>', 'linear-gradient(90deg, #fe4ffb 0%, #009ffe 100%);', esc_attr__('AI queue processing', 'xhtheme-ai-toolbox'))
                ]);
            }
        }
        wp_send_json_error([
            'message' => esc_html__('Failed to add task to queue', 'xhtheme-ai-toolbox')
        ]);
    }

    public function admin_scripts($hook)
    {
        wp_register_style('xhtheme-ai-toolbox-editlist', false, array(), XHTHEME_AI_TOOLBOX_VERSION);
        wp_enqueue_style('xhtheme-ai-toolbox-editlist');
        // 动态插入CSS
        $custom_css = "
            .fixed .column-comments {width: 13em;}
            .queue-processing {background: linear-gradient(90deg, #fe4ffb 0%, #009ffe 100%);-webkit-background-clip: text;background-clip: text;color: transparent;font-size: 12px;font-weight: 600;}
            .queue-btntack {padding: 0 8px;line-height: 26px;height: 26px;font-size: 12px;max-width: 100%;overflow: hidden;text-overflow: ellipsis;min-height: 26px;}
            .queue-btnerrortack {display:inline-flex;align-items:center;padding:2px 4px 2px 12px;border-radius:20px;background-color:#f44336;color:#fff;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.2s ease;}
            @media screen and (min-width: 782px){.fixed .column-xh_summary {width: 9em;max-width: 9em;}}
        ";
        wp_add_inline_style('xhtheme-ai-toolbox-editlist', $custom_css);

        wp_enqueue_script(
            'xhtheme-ai-toolbox-tablebuttons',
            plugins_url('assets/js/admin-buttons.js', dirname(__FILE__)),
            array('jquery'),
            filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/admin-buttons.js'),
            true
        );

        wp_localize_script('xhtheme-ai-toolbox-tablebuttons', 'xhaitoolbox_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'errorMsg' => esc_html__('Error, please try again!', 'xhtheme-ai-toolbox'),
            'nonce' => wp_create_nonce('xhaitoolbox_nonce'),
            'pagehook' => $hook,
            'userMember' => $this->XHAi->isMember(),
            'aiComment' => xh_option('commentEnabled', false),
        ]);
    }

    /**
     * 添加摘要列
     */
    public function add_summary_column($columns)
    {
        if (xh_option('summaryEnabled', false)) {
            $new_columns = [];
            $addtype = false;
            foreach ($columns as $key => $value) {
                $new_columns[$key] = $value;
                if ($key === 'tags') {
                    $addtype = true;
                    $new_columns['xh_summary'] = esc_html__('AI Summary', 'xhtheme-ai-toolbox');
                }
            }
            if (!$addtype) {
                $new_columns['xh_summary'] = esc_html__('AI Summary', 'xhtheme-ai-toolbox');
            }
            return $new_columns;
        }
        return $columns;
    }

    /**
     * 产品列表挂载评论数列
     */
    public function filter_product_columns($columns)
    {
        if ($this->XHAi->isMember() && xh_option('commentEnabled', false)) {
            $columns['product_comments'] = esc_html__('Comments', 'xhtheme-ai-toolbox');
        }
        return $columns;
    }

    /**
     * 渲染评论数列内容
     */
    public function render_product_column($column, $post_id)
    {
        if ($column === 'product_comments') {
            printf('<style>.column-xh_comments {width:100px}</style>');
            echo '--';
        }
    }

    /**
     * 渲染评论数列内容
     */
    public function render_summary_column($column, $post_id)
    {
        if ($column === 'xh_summary') {
            $_excerptai = (int) get_post_meta($post_id, '_excerptai', true);
            if ($_excerptai) {
                printf('
                    <style>.fixed .column-xh_summary {max-width: 9em;}</style>
                    <p>
                        <svg t="1745160184251" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="11828" width="16" height="16">
                            <path d="M89.216226 575.029277c-6.501587-7.223986-10.47478-15.892769-12.641975-26.367549-1.805996-10.47478-0.722399-20.22716 3.973192-29.257143l4.695591-10.47478c5.05679-8.307584 11.558377-13.725573 19.865961-15.892769 7.946384-2.167196 15.892769-0.361199 23.477954 5.417989L323.995767 639.322751c8.307584 5.779189 17.698765 8.668783 27.812346 8.307584 10.11358-0.361199 18.782363-3.611993 26.006349-10.11358L898.302646 208.411993c7.585185-5.779189 16.253968-8.307584 26.006349-7.585185 9.752381 0.722399 18.059965 4.334392 24.922751 10.47478l-12.641975-12.641975c6.501587 7.223986 9.752381 15.17037 9.752381 24.561552 0 9.391182-3.250794 17.337566-9.752381 24.561552L376.008466 816.310406c-7.223986 7.223986-15.17037 10.47478-24.200353 10.47478-9.029982 0-16.976367-3.250794-24.200353-9.752381L89.216226 575.029277z" p-id="11829" fill="#1afa29"></path>
                        </svg>
                    </p>
                ');
            } else {
                $taskHtml = $this->XHCron->addcrontask('summary', (int) $post_id);
                if ($taskHtml) {
                    echo $taskHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $taskHtml is internally sanitized HTML with SVG from addcrontask()
                }
            }
        }
    }

    public function filter_post_columns($class_name, $args)
    {
        $postTypes = xh_postTypes();
        if (in_array($args['screen']->post_type, $postTypes) && $args['screen']->base === 'edit') {
            return '\XHTheme\AIToolbox\XHPosts_List_Table';
        }
        return $class_name;
    }
}
