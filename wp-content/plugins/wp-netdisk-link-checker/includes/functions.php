<?php
/**
 * 工具函数
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取插件设置
 *
 * @return array
 */
function wpnlc_get_settings() {
    $default_settings = array(
        'cache_hours' => 6,
        'check_posts' => 'all',
        'check_frequency' => 'daily',
        'enable_notifications' => 'no',
        'notification_email' => '',
        'show_dashboard_widget' => 'no',
        'show_in_download_box' => 'no',
        'quick_check_interval' => 'disabled',
        'quick_check_batch' => 1,
        'show_check_time' => 'no',
        'enable_frontend_manual_check' => 'no',
        'frontend_check_permission' => 'logged_in',
    );

    $settings = get_option('wpnlc_settings', $default_settings);
    return wp_parse_args($settings, $default_settings);
}

/**
 * 获取状态文本
 *
 * @param string $status 状态
 * @return string
 */
function wpnlc_get_status_text($status) {
    switch ($status) {
        case 'valid':
            return __('有效', 'wp-netdisk-link-checker');
        case 'invalid':
            return __('失效', 'wp-netdisk-link-checker');
        case 'mixed':
            return __('部分有效', 'wp-netdisk-link-checker');
        case 'no_links':
            return __('无网盘链接', 'wp-netdisk-link-checker');
        case 'error':
            return __('检测错误', 'wp-netdisk-link-checker');
        default:
            return __('未检测', 'wp-netdisk-link-checker');
    }
}

/**
 * 检测网盘类型
 *
 * @param string $url 链接URL
 * @return string
 */
function wpnlc_detect_netdisk_type($url) {
    if (preg_match('/(pan|yun)\.baidu\.com/i', $url)) {
        return 'baidu';
    } elseif (preg_match('/lanzou[a-z\.]*\.com/i', $url)) {
        return 'lanzou';
    } elseif (preg_match('/cloud\.189\.cn/i', $url)) {
        return 'ty';
    } elseif (preg_match('/share\.weiyun\.com/i', $url)) {
        return 'weiyun';
    } elseif (preg_match('/(?:www\.)?aliyundrive\.com/i', $url)) {
        return 'aliyun';
    } elseif (preg_match('/(?:pan\.)?(?:quark|qua)\.cn/i', $url)) {
        return 'quark';
    } elseif (preg_match('/ctfile\.com/i', $url)) {
        return 'ctfile';
    } elseif (preg_match('/(?:www\.)?123(?:pan|yunpan)\.com/i', $url)) {
        return '123pan';
    } elseif (preg_match('/pan\.xunlei\.com/i', $url)) {
        return 'xunlei';
    } elseif (preg_match('/(?:drive|pan)\.uc\.cn/i', $url)) {
        return 'uc';
    } else {
        return 'unknown';
    }
}

/**
 * 获取网盘类型显示文本
 *
 * @param string|array $types 网盘类型或类型数组
 * @return string
 */
function wpnlc_get_netdisk_type_text($types) {
    if (empty($types)) {
        return '';
    }
    
    $type_names = array(
        'baidu' => '百度网盘',
        'lanzou' => '蓝奏云',
        'ty' => '天翼云盘',
        'weiyun' => '微云',
        'aliyun' => '阿里云盘',
        'quark' => '夸克网盘',
        'ctfile' => '城通网盘',
        '123pan' => '123云盘',
        'xunlei' => '迅雷网盘',
        'uc' => 'UC网盘',
        'unknown' => '未知网盘'
    );
    
    // 如果是字符串，直接返回对应文本
    if (is_string($types)) {
        return isset($type_names[$types]) ? $type_names[$types] : '未知网盘';
    }
    
    // 如果是数组，拼接所有类型
    if (!is_array($types)) {
        return '未知网盘';
    }
    
    $result = array();
    foreach ($types as $type) {
        if (isset($type_names[$type])) {
            $result[] = $type_names[$type];
        }
    }
    
    return implode(' ', $result);
}

/**
 * 获取正确格式化的下次检测时间
 *
 * @param string $event_hook 事件钩子名称
 * @return string
 */
function wpnlc_get_formatted_next_check_time($event_hook) {
    $timestamp = wp_next_scheduled($event_hook);

    if (!$timestamp) {
        return '未安排';
    }

    // 直接使用WordPress的date_i18n函数，它会自动处理时区
    return date_i18n('Y-m-d H:i:s', $timestamp);
}

/**
 * 获取文章检测统计数据
 *
 * @return array
 */
function wpnlc_get_post_check_statistics() {
    global $wpdb;
    $settings = wpnlc_get_settings();
    $check_post_type = $settings['check_posts'];
    $database = new WPNLC_Database();

    // 获取文章类型条件 - 修复SQL注入漏洞
    if ($check_post_type === 'all') {
        $post_types = get_post_types(array('public' => true));
        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
        $total_posts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM $wpdb->posts
             WHERE post_status = 'publish'
             AND post_type IN ($placeholders)",
            $post_types
        ));
    } else {
        $total_posts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM $wpdb->posts
             WHERE post_status = 'publish'
             AND post_type = %s",
            $check_post_type
        ));
    }

    // 获取已检测文章数 - 修复SQL注入漏洞
    if ($database->table_exists()) {
        $table_name = $database->get_links_table();
        if ($check_post_type === 'all') {
            $post_types = get_post_types(array('public' => true));
            $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
            $checked_posts = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM $wpdb->posts p
                 JOIN $table_name l ON p.ID = l.post_id
                 WHERE p.post_status = 'publish'
                 AND p.post_type IN ($placeholders)",
                $post_types
            ));
        } else {
            $checked_posts = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM $wpdb->posts p
                 JOIN $table_name l ON p.ID = l.post_id
                 WHERE p.post_status = 'publish'
                 AND p.post_type = %s",
                $check_post_type
            ));
        }
    } else {
        if ($check_post_type === 'all') {
            $post_types = get_post_types(array('public' => true));
            $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
            $checked_posts = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM $wpdb->posts p
                 JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                 WHERE p.post_status = 'publish'
                 AND p.post_type IN ($placeholders)",
                $post_types
            ));
        } else {
            $checked_posts = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM $wpdb->posts p
                 JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                 WHERE p.post_status = 'publish'
                 AND p.post_type = %s",
                $check_post_type
            ));
        }
    }

    // 计算未检测文章数
    $unchecked_posts = $total_posts - $checked_posts;

    return array(
        'total' => (int)$total_posts,
        'checked' => (int)$checked_posts,
        'unchecked' => (int)$unchecked_posts
    );
}

/**
 * 获取链接统计数据
 *
 * @return array
 */
function wpnlc_get_link_statistics() {
    global $wpdb;

    $database = new WPNLC_Database();

    // 检查是否使用新表
    if ($database->table_exists()) {
        $table_name = $database->get_links_table();

        // 从新表获取统计数据 - 按文章分组统计
        $post_stats = $wpdb->get_results(
            "SELECT post_id,
                    SUM(CASE WHEN link_status = 'valid' THEN 1 ELSE 0 END) as valid_links,
                    SUM(CASE WHEN link_status = 'invalid' THEN 1 ELSE 0 END) as invalid_links,
                    SUM(CASE WHEN link_status = 'no_links' THEN 1 ELSE 0 END) as no_links
             FROM $table_name
             GROUP BY post_id",
            ARRAY_A
        );

        $stats = array(
            'valid' => 0,
            'invalid' => 0,
            'mixed' => 0,
            'no_links' => 0
        );

        foreach ($post_stats as $post_stat) {
            if ($post_stat['no_links'] > 0) {
                $stats['no_links']++;
            } elseif ($post_stat['valid_links'] > 0 && $post_stat['invalid_links'] > 0) {
                $stats['mixed']++;
            } elseif ($post_stat['valid_links'] > 0) {
                $stats['valid']++;
            } elseif ($post_stat['invalid_links'] > 0) {
                $stats['invalid']++;
            }
        }

    } else {
        // 使用旧的meta表查询
        $valid_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
            '_wpnlc_link_status', 'valid'
        ));

        $invalid_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
            '_wpnlc_link_status', 'invalid'
        ));

        $mixed_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
            '_wpnlc_link_status', 'mixed'
        ));

        $stats = array(
            'valid' => (int)$valid_count,
            'invalid' => (int)$invalid_count,
            'mixed' => (int)$mixed_count,
            'no_links' => 0
        );
    }

    return $stats;
}

/**
 * 获取最近的失效链接
 *
 * @param int $limit 返回的最大数量
 * @return array
 */
function wpnlc_get_recent_invalid_links($limit = 5) {
    global $wpdb;
    $database = new WPNLC_Database();

    if ($database->table_exists()) {
        $table_name = $database->get_links_table();

        // 从新表获取最近失效的链接
        $recent_invalid_links = $wpdb->get_results($wpdb->prepare(
            "SELECT l.post_id, l.post_title, l.link_url, l.last_check
             FROM $table_name l
             JOIN $wpdb->posts p ON l.post_id = p.ID
             WHERE p.post_status = 'publish' AND l.link_status = 'invalid'
             ORDER BY l.last_check DESC
             LIMIT %d",
            $limit
        ));

        $invalid_links = array();

        foreach ($recent_invalid_links as $link) {
            $invalid_links[] = array(
                'post_id' => $link->post_id,
                'title' => $link->post_title,
                'url' => $link->link_url,
                'check_time' => $link->last_check
            );
        }
    } else {
        // 使用旧的meta表查询
        $recent_invalid_posts = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, pm.meta_value, pm2.meta_value as last_check
             FROM $wpdb->posts p
             JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = %s AND pm.meta_value = %s
             LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = %s
             ORDER BY pm2.meta_value DESC
             LIMIT %d",
            '_wpnlc_link_status', 'invalid', '_wpnlc_last_check', $limit
        ));

        $invalid_links = array();

        foreach ($recent_invalid_posts as $post) {
            // 获取链接URL
            $link_url = get_post_meta($post->ID, '_wpnlc_link_url', true);

            $invalid_links[] = array(
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'url' => $link_url,
                'check_time' => $post->last_check
            );
        }
    }

    return $invalid_links;
}

/**
 * 调试函数：显示文章的网盘链接检测信息
 *
 * @param int $post_id 文章ID
 * @return array 调试信息
 */
function wpnlc_debug_post_links($post_id) {
    if (!current_user_can('manage_options')) {
        return array('error' => '权限不足');
    }

    global $wpdb;
    $checker = WPNLC_Core::get_instance()->get_checker();
    $database = new WPNLC_Database();

    // 获取文章内容中的链接
    $post = get_post($post_id);
    $content_links = array();
    if ($post) {
        $content_links = $checker->extract_netdisk_links($post->post_content);
    }

    // 获取B2主题字段中的链接
    $b2_links = $checker->extract_b2_download_links($post_id);

    // 获取存储的检测结果
    if ($database->table_exists()) {
        $table_name = $database->get_links_table();
        $stored_links = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d ORDER BY last_check DESC",
            $post_id
        ), ARRAY_A);

        $stored_status = 'no_links';
        $last_check = null;

        if (!empty($stored_links)) {
            $valid_count = 0;
            $invalid_count = 0;

            foreach ($stored_links as $link) {
                if ($link['link_status'] === 'valid') {
                    $valid_count++;
                } elseif ($link['link_status'] === 'invalid') {
                    $invalid_count++;
                }

                if (!$last_check || $link['last_check'] > $last_check) {
                    $last_check = $link['last_check'];
                }
            }

            if ($valid_count > 0 && $invalid_count > 0) {
                $stored_status = 'mixed';
            } elseif ($valid_count > 0) {
                $stored_status = 'valid';
            } elseif ($invalid_count > 0) {
                $stored_status = 'invalid';
            }
        }
    } else {
        $stored_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $stored_links = get_post_meta($post_id, '_wpnlc_links_data', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
        $last_check = $last_check ? date('Y-m-d H:i:s', $last_check) : null;
    }

    // 获取B2字段原始数据
    $b2_download_group = get_post_meta($post_id, 'b2_single_post_download_group', true);
    $b2_download_old = get_post_meta($post_id, 'b2_download_info', true);

    return array(
        'post_id' => $post_id,
        'post_title' => $post ? $post->post_title : '文章不存在',
        'content_links' => $content_links,
        'b2_links' => $b2_links,
        'stored_status' => $stored_status,
        'stored_links' => $stored_links,
        'last_check' => $last_check ?: '未检测',
        'using_new_table' => $database->table_exists(),
        'b2_raw_data' => array(
            'download_group' => $b2_download_group,
            'download_old' => $b2_download_old
        )
    );
}

/**
 * 检查B2主题是否激活
 *
 * @return bool
 */
function wpnlc_is_b2_theme_active() {
    $theme = wp_get_theme();
    return (strpos(strtolower($theme->get('Name')), 'b2') !== false) ||
           (strpos(strtolower($theme->get_template()), 'b2') !== false);
}

/**
 * 获取当前WordPress时间信息（用于调试时间问题）
 *
 * @return array 时间信息
 */
function wpnlc_get_time_debug_info() {
    if (!current_user_can('manage_options')) {
        return array('error' => '权限不足');
    }

    return array(
        'server_time' => time(),
        'server_time_formatted' => date('Y-m-d H:i:s', time()),
        'wp_current_time' => current_time('timestamp'),
        'wp_current_time_formatted' => current_time('Y-m-d H:i:s'),
        'wp_gmt_offset' => get_option('gmt_offset'),
        'wp_timezone_string' => get_option('timezone_string'),
        'time_difference' => current_time('timestamp') - time(),
        'date_i18n_now' => date_i18n('Y-m-d H:i:s'),
        'date_i18n_timestamp' => date_i18n('Y-m-d H:i:s', current_time('timestamp'))
    );
}
