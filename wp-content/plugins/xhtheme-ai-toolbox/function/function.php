<?php

namespace XHTheme\AIToolbox;

function xh_option($name, $default = '', $cache = true)
{
    $xhoptions_data = get_option('xhtheme_ai_toolbox_settings', []);
    if (isset($xhoptions_data[$name])) {
        return $xhoptions_data[$name];
    }
    return $default;
}

function xh_setoption($name, $value)
{
    $options = get_option('xhtheme_ai_toolbox_settings', []);
    $options = is_array($options) ? $options : [];
    $options[$name] = $value;
    update_option('xhtheme_ai_toolbox_settings', $options);
}

/**
 * 获取本地时区时间戳 
 * @return int 本地时区的 Unix 时间戳
 */
function xh_local_timestamp()
{
    return strtotime(current_time('mysql'));
}

function xh_darkClass($subClass = '')
{
    $baseClasses = [
        '.dark',
        '[data-bs-theme=dark]',
        '.io-black-mode',
        '.style-for-dark',
        '.dark-theme',
        '#ceotheme.night',
        '.wp-theme-begin.night',
        '.puock-dark',
        '.nice-dark-mode'
    ];

    $darkClasses = apply_filters('xhaitoolbox_darkClass', $baseClasses);
    if (!is_array($darkClasses) || empty($darkClasses)) {
        return trim($subClass);
    }

    if (empty($subClass)) {
        return implode(', ', array_filter($darkClasses));
    }

    $result = [];
    foreach ($darkClasses as $class) {
        $class = trim($class);
        if (!empty($class)) {
            $result[] = $class . ' ' . trim($subClass);
        }
    }
    return implode(', ', $result);
}

function xh_postTypedata($type = '')
{
    $datas = [
        'post' => [
            'name' => __('WordPress Post', 'xhtheme-ai-toolbox'),
            'type' => 'wp',
            'typename' => __('WordPress', 'xhtheme-ai-toolbox'),
            'subname' => __('Post', 'xhtheme-ai-toolbox'),
            'slug' => 'post',
            'description' => __('Auto-generate summaries, English slugs, images, reviews, tags, and categories with ease.', 'xhtheme-ai-toolbox'),
            'tags' => 'post_tag',
            'categories' => 'category',
            'color' => 'sky',
            'autopublish' => [],
            'queue' => true,
            'tasks' => [
                'summary',
                'slug',
                'aiimage',
                'thread',
                'comments',
                'tags',
                'category'
            ]
        ],
        'shop' => [
            'name' => __('B2 Theme Shopping District', 'xhtheme-ai-toolbox'),
            'type' => 'theme',
            'typename' => __('B2 Theme', 'xhtheme-ai-toolbox'),
            'subname' => __('Product', 'xhtheme-ai-toolbox'),
            'slug' => 'product',
            'description' => __('B2 Theme-compatible business district models with auto-generated reviews, tags, English slugs, and categories.', 'xhtheme-ai-toolbox'),
            'tags' => 'post_tag',
            'categories' => 'shoptype',
            'color' => 'green',
            'autopublish' => [],
            'queue' => true,
            'tasks' => [
                'slug',
                'comments',
                'tags',
                'category'
            ]
        ],
        'product' => [
            'name' => __('Woocommerce Product', 'xhtheme-ai-toolbox'),
            'type' => 'plugin',
            'typename' => __('Woocommerce Plugin', 'xhtheme-ai-toolbox'),
            'subname' => __('Product', 'xhtheme-ai-toolbox'),
            'slug' => 'product',
            'description' => __('WooCommerce product models with auto-generated reviews, tags, and categories.', 'xhtheme-ai-toolbox'),
            'tags' => 'product_tag',
            'categories' => 'product_cat',
            'color' => 'purple',
            'autopublish' => [],
            'queue' => true,
            'tasks' => [
                'slug',
                'comments',
                'tags',
                'category'
            ]
        ],
        'sites' => [
            'name' => __('OneNav Theme Website', 'xhtheme-ai-toolbox'),
            'type' => 'theme',
            'typename' => __('OneNav Theme', 'xhtheme-ai-toolbox'),
            'subname' => __('Website', 'xhtheme-ai-toolbox'),
            'slug' => 'sites',
            'description' => __('OneNav-compatible site models with auto-generated reviews, tags, and categories.', 'xhtheme-ai-toolbox'),
            'tags' => 'sitetag',
            'categories' => 'favorites',
            'color' => 'orange',
            'autopublish' => [],
            'queue' => true,
            'tasks' => [
                'slug',
                'comments',
                'tags',
                'category'
            ]
        ]
    ];
    if (!empty($type)) {
        return isset($datas[$type]) ? $datas[$type] : [];
    }
    return $datas;
}


function xh_postTypes()
{
    $datas = xh_postTypedata();
    return array_keys($datas);
}


function xh_active_post_types()
{
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
    $newargs = [];
    foreach ($post_types as $typename) {
        if (!isset($datas[$typename]) || !$datas[$typename]['queue']) {
            continue;
        }
        $newargs[$typename] = $datas[$typename];
        if (in_array('category', $datas[$typename]['tasks']) && !empty($datas[$typename]['categories'])) {
            // 获取分类列表通过get_terms
            $categories = get_terms([
                'taxonomy' => $datas[$typename]['categories'],
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            if (is_wp_error($categories)) {
                $categories = [];
            }
            $categories_list = [];
            foreach ($categories as $category) {
                $categories_list[] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                ];
            }
            $newargs[$typename]['categoriesList'] = $categories_list;
        }
    }
    return $newargs;
}

function xh_postType_config($type, $subtype = '')
{
    $datas = xh_postTypedata($type);
    if (!is_array($datas)) {
        return [];
    }

    $rules_json = get_option('xhtheme_ai_toolbox_automate_rules', '[]');
    $rules_data = json_decode($rules_json, true);

    if (empty($rules_data)) {
        $rules_data = xh_migrate_automate_config();
    }

    $datas['queue'] = false;
    $newtasks = [];
    if (is_array($rules_data) && !empty($rules_data)) {
        foreach ($rules_data as $rule) {
            if ($rule['enabled'] && $rule['postType'] === $type) {
                if ($rule['defaultTrigger'] == 'auto') {
                    $datas['queue'] = true;
                }

                // publishOnStatuses
                $datas['autopublish'] = [];
                if (isset($rule['publishOnStatuses']) && is_array($rule['publishOnStatuses'])) {
                    $datas['autopublish'] = $rule['publishOnStatuses'];
                }

                if (isset($rule['tasks']) && is_array($rule['tasks'])) {
                    foreach ($rule['tasks'] as $task_key => $task_config) {
                        if (is_array($task_config)) {
                            if ($task_config['enabled']) {
                                $newtasks[$task_key] = $task_config['categories'];
                            }
                        }
                    }
                }
            }
        }
    }

    $datas['tasks'] = $newtasks;
    if ($subtype) {
        return isset($datas[$subtype]) ? $datas[$subtype] : false;
    }
    return $datas;
}

function xh_autooption($post_type, $name, $subtype = '')
{
    static $xhautooptions_cache = [];

    if (!isset($xhautooptions_cache[$post_type])) {
        $config = xh_postType_config($post_type);
        $xhautooptions_cache[$post_type] = $config ? $config : [];
    }

    $xhautooptions_data = $xhautooptions_cache[$post_type];

    if (isset($xhautooptions_data[$name])) {
        if ($subtype) {
            return isset($xhautooptions_data[$name][$subtype]) ? $xhautooptions_data[$name][$subtype] : false;
        }
        return $xhautooptions_data[$name];
    }
    return false;
}

/**
 * 记录错误日志
 */
function insert_debuglog($name, $error)
{
    $error_log = get_transient('xhtheme_ai_toolbox_error_log');
    if (!$error_log || !is_array($error_log)) {
        $error_log = [];
    }
    $error_log[] = [
        'name' => $name,
        'time' => wp_date('Y-m-d H:i:s'),
        'log' => $error
    ];
    set_transient('xhtheme_ai_toolbox_error_log', $error_log, 600);
}

/**
 * 生成随机时间
 */
function xh_randTimeList($startTimeStr, $endDay, $numNodes)
{
    $startTimestamp = $startTimeStr ? strtotime($startTimeStr) : xh_local_timestamp();
    $endTimestamp = $startTimestamp + $endDay * 86400;
    $duration = $endTimestamp - $startTimestamp;

    if ($duration <= 0 || $numNodes <= 0) {
        return [];
    }

    $nodes = [];
    $generated = 0;
    $attempts = 0;

    $getRandomFloat = function () {
        if (class_exists('\Random\Randomizer')) {
            static $randomizer = null;
            if (!$randomizer) {
                $randomizer = new \Random\Randomizer();
            }
            if (method_exists($randomizer, 'getFloat')) {
                return $randomizer->getFloat(0, 1); // [0, 1)
            }
        }
        return lcg_value();
    };

    while ($generated < $numNodes && $attempts < $numNodes * 20) {
        $attempts++;

        $rand = pow($getRandomFloat(), 2);
        $timeOffset = (int) ($rand * $duration);
        $nodeTimestamp = $startTimestamp + $timeOffset;

        // 跳过凌晨1点到7点 - 需要使用本地时区判断
        // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Local timezone needed for user behavior simulation
        $hour = (int) wp_date('G', $nodeTimestamp);
        if ($hour >= 1 && $hour < 7) {
            continue;
        }

        // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Local timezone needed for consistency with WordPress
        $nodes[] = wp_date('Y-m-d H:i:s', $nodeTimestamp);
        $generated++;
    }

    sort($nodes);
    return $nodes;
}

/**
 * 获取文章语言
 */
function xh_post_language($post_id = null)
{
    $localeOption = xh_option('languageType', 'auto');
    if ($localeOption == 'webauto') {
        return 'auto';
    }
    if ($localeOption == 'auto') {
        if (!$post_id) {
            global $post;
            if (!$post || !isset($post->ID)) {
                return 'auto';
            }
            $post_id = $post->ID;
        }

        if (function_exists('pll_get_post_language')) {
            $language_code = pll_get_post_language($post_id, 'locale');
            if (!empty($language_code)) {
                return $language_code;
            }
        }

        if (defined('ICL_SITEPRESS_VERSION') || function_exists('wpml_get_language_information')) {
            $post_type = get_post_type($post_id);
            $args = array('element_id' => $post_id, 'element_type' => 'post_' . $post_type);
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WPML plugin hook
            $language_details = apply_filters('wpml_element_language_details', null, $args);

            if (is_object($language_details) && !empty($language_details->language_code)) {
                if ($language_details->language_code == 'zh-hant' || $language_details->language_code == 'zh-tw') {
                    return 'zh_TW';
                }
                if ($language_details->language_code == 'zh-hans' || $language_details->language_code == 'zh-cn') {
                    return 'zh_CN';
                }
                if ($language_details->language_code == 'en') {
                    return 'en_US';
                }
                return $language_details->language_code;
            }
        }
        return get_locale();
    }
    return $localeOption;
}

/**
 * 判断分类是否符合要求
 */
function xh_filter_category($post, $config, $taxonomy, $default_category = 0, $autoCategory = false)
{
    if (!$config || !is_array($config) || !isset($config['type']) || !$taxonomy) {
        return 1;
    }

    $filterType = $config['type'];
    if ($filterType == 'all') {
        return 1;
    }

    // 获取文章的分类ID列表
    $post_categories = is_array($taxonomy) && !empty($taxonomy) ? $taxonomy : wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);

    if (empty($post_categories) || is_wp_error($post_categories)) {
        return $autoCategory ? 2 : 1;
    }

    if ($autoCategory && $default_category && count($post_categories) == 1 && in_array($default_category, $post_categories)) {
        return 2;
    }

    $list = isset($config['list']) ? $config['list'] : [];
    switch ($filterType) {
        case 'whitelist':
            if (empty($list)) {
                return 0;
            }
            $has_matching_category = !empty(array_intersect($post_categories, $list));
            return $has_matching_category ? 1 : 0;

        case 'blacklist':
            if (empty($list)) {
                return 1;
            }
            $has_excluded_category = !empty(array_intersect($post_categories, $list));
            return $has_excluded_category ? 0 : 1;
    }
    return 0;
}

function xh_loop_filter_category($postId, $config, $newCategory = [])
{
    $post = get_post($postId);
    if (!$post) {
        return false;
    }
    $taxonomy = xh_postType_config($post->post_type, 'categories');
    $default_category = $taxonomy ? get_option('default_' . $taxonomy) : 0;
    return xh_filter_category($post, $config, !empty($newCategory) ? $newCategory : $taxonomy, $default_category, false);
}

function xh_check_hold_task($task, $postId)
{
    // 根据文章ID获取文章的所有任务
    $XHCron = XHCronQueue::getInstance();
    $CronItems = $XHCron->postTasks($postId);
    if (!$CronItems || !is_array($CronItems))
        return;
    $loadList = [];
    $priorityList = [];
    foreach ($CronItems as $CronItem) {
        if ($CronItem['type'] == $task || $CronItem['status'] !== 'freeze') {
            continue;
        }
        $setdata = [
            'status' => 'hold'
        ];
        $cron_id = $CronItem['cron_id'];
        $itemData = isset($CronItem['data']) && !empty($CronItem['data']) ? json_decode($CronItem['data'], true) : [];
        if ($CronItem['type'] == 'primary') {
            if (isset($itemData['postterms'])) {
                $itemData['config']['taskStatus'] = 1;
                $setdata['data'] = $itemData;
                $priorityList[$cron_id] = $setdata;
            } else {
                $loadList[$cron_id] = $setdata;
            }
        } else {
            if (isset($itemData['config']['categories'])) {
                $loadList[$cron_id] = $setdata;
            } else {
                $priorityList[$cron_id] = $setdata;
            }
        }
    }
    if ($priorityList && isset($priorityList['primary_' . $postId])) {
        foreach ($priorityList as $taskId => $item) {
            $XHCron->update($taskId, $item);
        }
        return;
    }

    $newList = $loadList + $priorityList;
    if ($newList && is_array($newList)) {
        foreach ($newList as $taskId => $item) {
            $XHCron->update($taskId, $item);
        }
    }
    return;
}

/**
 * 检查文章是否符合自动发布状态
 */
function xh_check_publish_status($post, $cron_id)
{
    if (!is_a($post, 'WP_Post')) {
        $post = get_post($post);
        if (!$post)
            return;
    }
    $checkStatus = xh_postType_config($post->post_type, 'autopublish');
    if (!$checkStatus || !is_array($checkStatus) || !in_array($post->post_status, $checkStatus))
        return false;
    $XHCron = XHCronQueue::getInstance();
    $CronItems = $XHCron->postTasks($post->ID);
    if (!$CronItems) {
        return true;
    }
    foreach ($CronItems as $Cron) {
        if ($Cron['cron_id'] == $cron_id || $Cron['status'] == 'skip') {
            continue;
        }
        return false;
    }
    return true;
}

function xh_loop_auto_publish($post, $cron_id)
{
    if (!is_a($post, 'WP_Post')) {
        $post = get_post($post);
        if (!$post)
            return;
    }
    if ($post->post_status !== 'publish' && xh_check_publish_status($post, $cron_id)) {
        $updatePost = [
            'ID' => $post->ID,
            'post_status' => 'publish'
        ];
        wp_update_post($updatePost);
    }
}

/**
 * 自动化配置迁移适配函数
 */
function xh_migrate_automate_config()
{
    if (get_transient('xhtheme_ai_toolbox_automate_migrated')) {
        return [];
    }

    $oldConfig = get_option('xhtheme_ai_toolbox_automate', '');
    if (empty($oldConfig)) {
        return [];
    }

    // 检查是否已有新配置
    $new_rules = get_option('xhtheme_ai_toolbox_automate_rules', '[]');
    $new_rules_data = json_decode($new_rules, true);
    if (is_array($new_rules_data) && !empty($new_rules_data)) {
        return $new_rules_data;
    }


    // 开始适配
    $old_configArr = json_decode($oldConfig, true);
    if (!is_array($old_configArr) || empty($old_configArr)) {
        return [];
    }

    // 获取所有公开的文章类型
    $post_types = get_post_types(array(
        'public' => true,
        '_builtin' => false
    ), 'names');
    if (!$post_types) {
        $post_types = [];
    }
    $post_types = array_keys($post_types);
    $post_types = array_merge(['post'], $post_types);

    // 获取支持的文章类型数据
    $datas = xh_postTypedata();

    // 开始迁移
    $migrated_rules = [];
    $timestamp = wp_date('Y-m-d H:i:s');

    // 遍历每个文章类型创建规则
    foreach ($post_types as $post_type) {
        if (!isset($datas[$post_type])) {
            continue;
        }

        $type_data = $datas[$post_type];
        $rule_tasks = [];
        $has_enabled_task = false;
        if (!isset($type_data['tasks']) || empty($type_data['tasks'])) {
            continue;
        }

        foreach ($type_data['tasks'] as $taskName) {
            $oldTaskobj = isset($old_configArr[$taskName]) ? $old_configArr[$taskName] : [];
            if (!isset($oldTaskobj['enabled'])) {
                continue;
            }
            $task_enabled = (bool) $oldTaskobj['enabled'];
            if (isset($oldTaskobj['notypes']) && is_array($oldTaskobj['notypes']) && in_array($post_type, $oldTaskobj['notypes'])) {
                $task_enabled = false;
            }

            $rule_tasks[$taskName] = [
                'enabled' => $task_enabled,
                'categories' => [
                    'type' => 'all',
                    'list' => []
                ]
            ];

            if ($task_enabled) {
                $has_enabled_task = true;
            }
        }

        if ($has_enabled_task) {
            $defaultTrigger = isset($old_configArr['aistatus']['enabled']) && $old_configArr['aistatus']['enabled'] && !in_array($post_type, $old_configArr['aistatus']['notypes']) ? 'auto' : 'manual';
            $migrated_rules[] = [
                'id' => $post_type . '_' . time(),
                'name' => $type_data['name'],
                'postType' => $post_type,
                'enabled' => true,
                'defaultTrigger' => $defaultTrigger,
                'publishOnStatuses' => ['pending'],
                'tasks' => $rule_tasks,
                'createdAt' => $timestamp,
                'updatedAt' => $timestamp
            ];
        }
    }

    if (!empty($migrated_rules)) {
        update_option('xhtheme_ai_toolbox_automate_rules', wp_json_encode($migrated_rules));
        set_transient('xhtheme_ai_toolbox_automate_migrated', 'load', 86400 * 7);
        return $migrated_rules;
    }

    return [];
}

/**
 * 获取主题名称
 */
function xh_themeName()
{
    if (defined('B2_VERSION')) {
        return '7B2';
    } elseif (defined('ZIB_ROOT_PATH')) {
        return 'Zibll';
    }
    $theme = wp_get_theme();
    $themeName = $theme->get('Name');
    if ($themeName == '子比主题') {
        return 'Zibll';
    } elseif ($themeName == 'B2 PRO') {
        return '7B2';
    }
    return $themeName;
}

/**
 * 主题适配提醒
 */
function xh_themefitter_notice()
{
    $hidden_notices = get_option('xhaitool_theme_notices', []);
    $fitters = [];
    if (defined('zck_plugin_path')) {
        if (!in_array('zck-v1', $hidden_notices)) {
            $fitters['zck-v1'] = [
                'title' => '<strong>智创客插件</strong> 如果遇到采集时无法触发自动任务，请按教程进行适配！',
                'url' => 'https://www.xhtheme.com/docs/aitoolbox/adaptation-custom/zybk-plugin',
            ];
        }
    }
    return $fitters;
}

/**
 * 过滤内容
 */
function xh_stripfilter_tags($content)
{
    if ($content === '') {
        return '';
    }

    return preg_replace(
        [
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<iframe\b[^>]*>.*?<\/iframe>/is',
        ],
        '',
        $content
    );
}


function xh_filterContent($content, $post = null)
{
    $content = xh_stripfilter_tags($content);
    $postContent = '';
    if ($post instanceof \WP_Post) {
        remove_filter('the_content', [XHThemeAi::getInstance(), 'ai_postexcerpt'], 99);
        remove_filter('the_content', [XHThread::getInstance(), 'threads_after_content']);
        $postContent = apply_filters(
            'the_content',
            get_the_content(null, false, $post)
        );
        $postContent = str_replace(']]>', ']]&gt;', $postContent);
        $postContent = xh_stripfilter_tags($postContent);
    }
    $contentLength     = mb_strlen(wp_strip_all_tags($content), 'UTF-8');
    $postContentLength = mb_strlen(wp_strip_all_tags($postContent), 'UTF-8');

    return $contentLength >= $postContentLength ? $content : $postContent;
}

/**
 * 图片转换为 Base64
 */
function xh_image_to_base64($image_url, $max_side = 768, $quality = 90)
{
    $max_file_size = 10 * 1024 * 1024;
    $upload_dir = wp_upload_dir();
    $image_path = '';
    $file_content = '';

    // 优先尝试本地文件
    if (strpos($image_url, $upload_dir['baseurl']) !== false) {
        $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
    }

    if (!empty($image_path) && file_exists($image_path)) {
        if (filesize($image_path) > $max_file_size) {
            return new \WP_Error('file_too_large', __('Image file exceeds maximum size limit', 'xhtheme-ai-toolbox'));
        }
        $file_content = file_get_contents($image_path);
    } else {
        $response = wp_remote_get($image_url, array(
            'timeout' => 15,
            'headers' => array(
                'Referer'    => home_url('/'),
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ),
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new \WP_Error('http_error', sprintf(__('Failed to fetch image, HTTP status: %d', 'xhtheme-ai-toolbox'), $status_code));
        }

        $file_content = wp_remote_retrieve_body($response);

        if (strlen($file_content) > $max_file_size) {
            return new \WP_Error('file_too_large', __('Image file exceeds maximum size limit', 'xhtheme-ai-toolbox'));
        }
    }

    if (empty($file_content)) {
        return new \WP_Error('empty_content', __('Image content is empty', 'xhtheme-ai-toolbox'));
    }

    if (!function_exists('wp_tempnam')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $tmp_file = wp_tempnam();
    file_put_contents($tmp_file, $file_content);

    $editor = wp_get_image_editor($tmp_file);
    $mime_type = 'image/jpeg';

    if (!is_wp_error($editor)) {
        $size = $editor->get_size();
        $editor->set_quality($quality);

        if ($size['width'] > $max_side || $size['height'] > $max_side) {
            $editor->resize($max_side, $max_side, false);
        }

        $saved = $editor->save($tmp_file, 'image/jpeg');

        if (!is_wp_error($saved) && file_exists($saved['path'])) {
            $processed_content = file_get_contents($saved['path']);
            if ($saved['path'] !== $tmp_file) {
                @unlink($saved['path']);
            }
        } else {
            $processed_content = file_get_contents($tmp_file);
        }
    } else {
        $processed_content = $file_content;
    }

    @unlink($tmp_file);

    return 'data:' . $mime_type . ';base64,' . base64_encode($processed_content);
}

/**
 * 上传图片到临时中转服务器（带缓存）
 * 
 * @param string $image_url 图片 URL
 */
function xh_upload_tmp_image($image_url)
{
    // 使用图片 URL 的 MD5 作为缓存键
    $cache_key = 'xh_tmpimg_' . md5($image_url);

    // 尝试从缓存读取
    $cached = get_transient($cache_key);
    if ($cached !== false && is_array($cached)) {
        return $cached;
    }

    $base64_image = xh_image_to_base64($image_url);

    if (is_wp_error($base64_image)) {
        return $base64_image;
    }

    $appid = xh_option('appId', '');
    $response = wp_remote_post(XHTHEME_AI_TOOLBOX_IMAGE_TMPURL, array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $appid
        ),
        'body'    => wp_json_encode(array('image' => $base64_image)),
        'timeout' => 10
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        return new \WP_Error('http_error', sprintf(__('Upload failed, HTTP status: %d', 'xhtheme-ai-toolbox'), $status_code));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($body['code']) || $body['code'] !== 0) {
        $message = isset($body['message']) ? $body['message'] : __('Upload failed', 'xhtheme-ai-toolbox');
        return new \WP_Error('upload_failed', $message);
    }

    // 服务器返回的过期秒数 (TTL)
    $expires_ttl = isset($body['data']['expires']) ? intval($body['data']['expires']) : 86400;

    $result = array(
        'type'    => 'filename',
        'data'    => $body['data']['filename'],
        'expires' => $expires_ttl,
    );

    // 缓存结果，留10分钟余量确保不会使用已过期的临时URL
    $cache_duration = max(0, $expires_ttl - 600);
    if ($cache_duration > 0) {
        set_transient($cache_key, $result, $cache_duration);
    }

    return $result;
}

/**
 * 调用临时图片
 */
function xh_get_imagevl($content)
{
    if (empty($content)) {
        return null;
    }
    $text_length = mb_strlen(wp_strip_all_tags($content), 'UTF-8');
    if ($text_length > 300) {
        return null;
    }

    // 提取第一张图片的 src
    if (!preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
        return null;
    }

    $image_url = $matches[1];
    if (empty($image_url)) {
        return null;
    }
    // 上传到临时服务器
    $result = xh_upload_tmp_image($image_url);
    if (is_wp_error($result)) {
        return null;
    }

    return $result;
}


/**
 * 任务优化
 */
function xh_clear_overduecrons()
{
    set_transient('xhaitoolbox_clear_overduecrons_2', time(), 86400);

    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checking cron option existence
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT option_id FROM $wpdb->options WHERE option_id = %d AND option_name = %s",
        1,
        'cron'
    ));
    if ($exists) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Deleting corrupted cron option
        $wpdb->delete(
            $wpdb->options,
            array('option_id' => 1, 'option_name' => 'cron'), // 删除条件
            array('%d', '%s')
        );
        _set_cron_array(array());
        return;
    }

    $crons = _get_cron_array();
    if (empty($crons) || !is_array($crons)) {
        return;
    }
    if (count($crons) > 2000) {
        _set_cron_array(array());
        return;
    }
    $has_changes = false;
    $deleted_count = 0;
    $repeatArr = [];
    foreach ($crons as $timestamp => $hooks) {
        $uidkey = md5(serialize($hooks));
        if (in_array($uidkey, $repeatArr)) {
            unset($crons[$timestamp]);
            $has_changes = true;
            $deleted_count++;
            continue;
        }
        $repeatArr[] = $uidkey;
    }
    if ($has_changes) {
        _set_cron_array($crons);
        do_action('xhaitoolbox_log_error', esc_html("已清理 {$deleted_count} 个重复的时间戳任务组。"));
    }
}
