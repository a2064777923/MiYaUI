<?php
/**
 * AJAX处理类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX处理类
 */
class WPNLC_Ajax {

    /**
     * 构造函数
     */
    public function __construct() {
        $this->init();
    }

    /**
     * 初始化
     */
    private function init() {
        // 后台AJAX处理
        add_action('wp_ajax_wpnlc_check_single_post', array($this, 'check_single_post'));
        add_action('wp_ajax_wpnlc_batch_check', array($this, 'batch_check'));
        add_action('wp_ajax_wpnlc_get_statistics', array($this, 'get_statistics'));
        add_action('wp_ajax_wpnlc_reset_data', array($this, 'reset_data'));
        add_action('wp_ajax_wpnlc_cleanup_data', array($this, 'cleanup_data'));
        add_action('wp_ajax_wpnlc_export_data', array($this, 'export_data'));
        add_action('wp_ajax_wpnlc_get_link_details', array($this, 'get_link_details'));
        add_action('wp_ajax_wpnlc_export_invalid_links', array($this, 'export_invalid_links'));
        add_action('wp_ajax_wpnlc_replace_invalid_links', array($this, 'replace_invalid_links'));
        add_action('wp_ajax_wpnlc_preview_replace_links', array($this, 'preview_replace_links'));
        add_action('wp_ajax_wpnlc_execute_replace_links', array($this, 'execute_replace_links'));
        add_action('wp_ajax_wpnlc_debug_post_links', array($this, 'debug_post_links'));
        add_action('wp_ajax_wpnlc_debug_time', array($this, 'debug_time'));

        // 调试相关AJAX
        add_action('wp_ajax_wpnlc_debug_stats', array($this, 'debug_stats'));
        add_action('wp_ajax_wpnlc_debug_database', array($this, 'debug_database'));
        add_action('wp_ajax_wpnlc_debug_single_post', array($this, 'debug_single_post'));
        add_action('wp_ajax_wpnlc_debug_batch_check', array($this, 'debug_batch_check'));
        add_action('wp_ajax_wpnlc_debug_recent_checked', array($this, 'debug_recent_checked'));
        add_action('wp_ajax_wpnlc_migrate_database', array($this, 'migrate_database'));
        
        // 前台快速检测
        add_action('wp_ajax_wpnlc_quick_check_single', array($this, 'quick_check_single'));
        add_action('wp_ajax_nopriv_wpnlc_quick_check_single', array($this, 'quick_check_single'));
        
        // 前台手动检测
        add_action('wp_ajax_wpnlc_frontend_manual_check', array($this, 'frontend_manual_check'));
        add_action('wp_ajax_nopriv_wpnlc_frontend_manual_check', array($this, 'frontend_manual_check'));
        
        // 前台AJAX处理
        add_action('wp_ajax_wpnlc_frontend_check', array($this, 'frontend_check'));
        add_action('wp_ajax_nopriv_wpnlc_frontend_check', array($this, 'frontend_check'));
        add_action('wp_ajax_wpnlc_get_link_details', array($this, 'get_link_details'));
        add_action('wp_ajax_nopriv_wpnlc_get_link_details', array($this, 'get_link_details'));
        
        // 进度监控相关AJAX
        add_action('wp_ajax_wpnlc_get_progress', array($this, 'get_progress'));
        add_action('wp_ajax_wpnlc_start_batch_check_with_progress', array($this, 'start_batch_check_with_progress'));
    }

    /**
     * 检测单篇文章
     */
    public function check_single_post() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_check_single')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }
        
        // 执行检测
        $checker = WPNLC_Core::get_instance()->get_checker();
        $result = $checker->check_post_links($post_id, true); // 强制检测
        
        if ($result['status'] === 'error') {
            wp_send_json_error($result['message']);
        }
        
        wp_send_json_success(array(
            'status' => $result['status'],
            'message' => sprintf(__('检测完成，状态：%s', 'wp-netdisk-link-checker'), wpnlc_get_status_text($result['status']))
        ));
    }

    /**
     * 批量检测
     */
    public function batch_check() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_batch_check')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        // 获取需要检测的文章
        $checker = WPNLC_Core::get_instance()->get_checker();
        $batch_size = isset($_POST['batch_size']) ? max(20, min(100, intval($_POST['batch_size']))) : 50;
        $post_ids = $checker->get_posts_to_check($batch_size);
        
        if (empty($post_ids)) {
            wp_send_json_success(array(
                'message' => __('没有需要检测的文章', 'wp-netdisk-link-checker'),
                'checked_count' => 0
            ));
        }
        
        // 执行批量检测
        $results = $checker->batch_check_posts($post_ids, true);

        // 统计结果
        $valid_count = 0;
        $invalid_count = 0;
        $mixed_count = 0;
        $no_links_count = 0;

        foreach ($results as $result) {
            switch ($result['status']) {
                case 'valid':
                    $valid_count++;
                    break;
                case 'invalid':
                    $invalid_count++;
                    break;
                case 'mixed':
                    $mixed_count++;
                    break;
                case 'no_links':
                    $no_links_count++;
                    break;
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(__('已检测 %d 篇文章', 'wp-netdisk-link-checker'), count($results)),
            'checked_count' => count($results),
            'valid_count' => $valid_count,
            'invalid_count' => $invalid_count,
            'mixed_count' => $mixed_count,
            'no_links_count' => $no_links_count
        ));
    }

    /**
     * 获取统计数据
     */
    public function get_statistics() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wpnlc_ajax_nonce')) {
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('read')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $database = WPNLC_Core::get_instance()->get_database();
        $stats = $database->get_statistics();
        
        wp_send_json_success($stats);
    }

    /**
     * 重置数据
     */
    public function reset_data() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_reset_data')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $database = WPNLC_Core::get_instance()->get_database();
        $success = $database->reset_all_data();
        
        if ($success) {
            wp_send_json_success(__('数据重置成功', 'wp-netdisk-link-checker'));
        } else {
            wp_send_json_error(__('数据重置失败', 'wp-netdisk-link-checker'));
        }
    }

    /**
     * 清理数据
     */
    public function cleanup_data() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_cleanup_data')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        $database = WPNLC_Core::get_instance()->get_database();
        $deleted_count = $database->cleanup_old_data($days);
        
        wp_send_json_success(array(
            'message' => sprintf(__('已清理 %d 条过期记录', 'wp-netdisk-link-checker'), $deleted_count),
            'deleted_count' => $deleted_count
        ));
    }

    /**
     * 前台检测
     */
    public function frontend_check() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_frontend_check')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        $settings = wpnlc_get_settings();
        
        // 检查是否启用了前台检测
        if ($settings['enable_frontend_check'] !== 'yes') {
            wp_send_json_error(__('前台检测功能未启用', 'wp-netdisk-link-checker'));
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }
        
        // 检查文章是否存在且已发布
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            wp_send_json_error(__('文章不存在或未发布', 'wp-netdisk-link-checker'));
        }
        
        // 执行检测
        $checker = WPNLC_Core::get_instance()->get_checker();
        $result = $checker->check_post_links($post_id, true); // 强制检测
        
        if ($result['status'] === 'error') {
            wp_send_json_error($result['message']);
        }
        
        wp_send_json_success(array(
            'status' => $result['status'],
            'message' => sprintf(__('检测完成，状态：%s', 'wp-netdisk-link-checker'), wpnlc_get_status_text($result['status']))
        ));
    }

    /**
     * 获取文章检测状态
     */
    public function get_post_status() {
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }
        
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
        
        $response = array(
            'status' => $link_status,
            'status_text' => wpnlc_get_status_text($link_status),
            'links' => $links_data,
            'last_check' => $last_check,
            'last_check_formatted' => $last_check ? date_i18n('Y-m-d H:i:s', $last_check) : ''
        );
        
        wp_send_json_success($response);
    }

    /**
     * 获取检测进度
     */
    public function get_check_progress() {
        // 检查权限
        if (!current_user_can('read')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $post_stats = wpnlc_get_post_check_statistics();
        
        $progress = array(
            'total' => $post_stats['total'],
            'checked' => $post_stats['checked'],
            'unchecked' => $post_stats['unchecked'],
            'percentage' => $post_stats['total'] > 0 ? round(($post_stats['checked'] / $post_stats['total']) * 100, 1) : 0
        );
        
        wp_send_json_success($progress);
    }

    /**
     * 导出数据
     */
    public function export_data() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_export_data')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }

        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';

        $database = WPNLC_Core::get_instance()->get_database();
        $data = $database->export_data($format);

        $filename = 'wpnlc_export_' . date('Y-m-d_H-i-s') . '.' . $format;

        // 设置正确的Content-Type头，确保UTF-8编码
        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
        } else {
            header('Content-Type: application/json; charset=UTF-8');
        }

        wp_send_json_success(array(
            'data' => $data,
            'filename' => $filename,
            'format' => $format,
            'charset' => 'UTF-8'
        ));
    }

    /**
     * 获取计划任务状态
     */
    public function get_cron_status() {
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $cron = WPNLC_Core::get_instance()->get_cron();
        $status = $cron->get_cron_status();
        
        // 格式化时间
        if ($status['main_task']['next_run']) {
            $status['main_task']['next_run_formatted'] = wpnlc_get_formatted_next_check_time('wpnlc_check_links_event');
        }
        
        if ($status['quick_task']['next_run']) {
            $status['quick_task']['next_run_formatted'] = wpnlc_get_formatted_next_check_time('wpnlc_quick_check_event');
        }
        
        wp_send_json_success($status);
    }

    /**
     * 手动触发计划任务
     */
    public function trigger_cron() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_trigger_cron')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'scheduled';
        
        $cron = WPNLC_Core::get_instance()->get_cron();
        $result = $cron->manual_trigger_check($type);
        
        wp_send_json_success(array(
            'message' => __('计划任务已手动触发', 'wp-netdisk-link-checker'),
            'result' => $result
        ));
    }

    /**
     * 调试文章链接
     */
    public function debug_post_links() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_die(__('安全验证失败', 'wp-netdisk-link-checker'));
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }

        $post_id = intval($_POST['post_id']);

        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }

        $debug_info = wpnlc_debug_post_links($post_id);

        if (isset($debug_info['error'])) {
            wp_send_json_error($debug_info['error']);
        }

        wp_send_json_success($debug_info);
    }

    /**
     * 调试时间信息
     */
    public function debug_time() {
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }

        $time_info = wpnlc_get_time_debug_info();

        if (isset($time_info['error'])) {
            wp_send_json_error($time_info['error']);
        }

        wp_send_json_success($time_info);
    }

    /**
     * 获取链接详情
     */
    public function get_link_details() {
        // 添加调试日志
        error_log('WPNLC: get_link_details AJAX请求收到');
        error_log('WPNLC: POST数据: ' . print_r($_POST, true));

        // 验证nonce（可选，因为这是只读操作）
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'wpnlc_ajax_nonce')) {
            error_log('WPNLC: Nonce验证失败');
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }

        $post_id = intval($_POST['post_id']);
        $status_filter = isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : '';

        error_log('WPNLC: 处理文章ID: ' . $post_id);

        if (!$post_id) {
            error_log('WPNLC: 文章ID无效');
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }

        // 检查文章是否存在
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(__('文章不存在', 'wp-netdisk-link-checker'));
        }

        // 获取链接数据（优先从新表读取）
        $status_info = $this->get_post_status_info($post_id);
        
        $link_status = $status_info['status'];
        $links_data = $status_info['links_data'];
        $last_check = $status_info['last_check'];

        // 确保links_data是数组
        if (!is_array($links_data)) {
            $links_data = array();
        }

        // 根据状态过滤链接
        $filtered_links = array();
        if ($status_filter && $status_filter !== 'all') {
            foreach ($links_data as $link) {
                if ($link['status'] === $status_filter) {
                    $filtered_links[] = $link;
                }
            }
        } else {
            $filtered_links = $links_data;
        }

        // 统计各状态的链接数量
        $status_counts = array(
            'valid' => 0,
            'invalid' => 0,
            'error' => 0,
            'total' => count($links_data)
        );

        foreach ($links_data as $link) {
            if (isset($status_counts[$link['status']])) {
                $status_counts[$link['status']]++;
            }
        }

        // 格式化链接数据
        $formatted_links = array();
        foreach ($filtered_links as $link) {
            $formatted_links[] = array(
                'url' => $link['url'],
                'type' => $link['type'],
                'type_text' => wpnlc_get_netdisk_type_text($link['type']),
                'status' => $link['status'],
                'status_text' => wpnlc_get_status_text($link['status']),
                'message' => isset($link['message']) ? $link['message'] : '',
                'name' => isset($link['name']) ? $link['name'] : '',
                'password' => isset($link['password']) ? $link['password'] : '',
                'source' => isset($link['source']) ? $link['source'] : 'content'
            );
        }

        $response = array(
            'post_id' => $post_id,
            'post_title' => $post->post_title ?: '未知文章',
            'post_url' => get_permalink($post_id),
            'overall_status' => $link_status ?: 'no_links',
            'overall_status_text' => wpnlc_get_status_text($link_status ?: 'no_links'),
            'last_check' => $last_check ? date_i18n('Y-m-d H:i:s', $last_check) : '未检测',
            'last_check_timestamp' => $last_check ?: 0,
            'status_counts' => $status_counts,
            'links' => $formatted_links,
            'filter_applied' => $status_filter ?: 'all',
            'has_links' => !empty($formatted_links)
        );

        error_log('WPNLC: 准备发送响应: ' . print_r($response, true));
        wp_send_json_success($response);
    }

    /**
     * 导出失效链接
     */
    public function export_invalid_links() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_export_invalid')) {
            wp_send_json_error('安全验证失败');
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        global $wpdb;

        // 查询失效链接
        $invalid_posts = $wpdb->get_results(
            "SELECT p.ID, p.post_title, p.post_date,
                    pm1.meta_value as links_data,
                    pm2.meta_value as last_check
             FROM $wpdb->posts p
             LEFT JOIN $wpdb->postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_wpnlc_links_data'
             LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wpnlc_last_check'
             LEFT JOIN $wpdb->postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_wpnlc_link_status'
             WHERE p.post_status = 'publish'
             AND pm3.meta_value IN ('invalid', 'mixed')
             ORDER BY p.post_date DESC",
            ARRAY_A
        );

        // 生成CSV
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "文章ID,文章标题,发布日期,失效链接,网盘类型,检测时间\n";

        foreach ($invalid_posts as $post) {
            $links_data = maybe_unserialize($post['links_data']);
            $check_time = $post['last_check'] ? date('Y-m-d H:i:s', $post['last_check']) : '';

            if (is_array($links_data)) {
                foreach ($links_data as $link) {
                    if ($link['status'] === 'invalid') {
                        $csv .= sprintf(
                            "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                            $post['ID'],
                            str_replace('"', '""', $post['post_title']),
                            $post['post_date'],
                            $link['url'],
                            wpnlc_get_netdisk_type_text($link['type']),
                            $check_time
                        );
                    }
                }
            }
        }

        $filename = 'wpnlc_invalid_links_' . date('Y-m-d_H-i-s') . '.csv';

        wp_send_json_success(array(
            'data' => $csv,
            'filename' => $filename
        ));
    }

    /**
     * 替换失效链接
     */
    public function replace_invalid_links() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_replace_invalid')) {
            wp_send_json_error('安全验证失败');
        }

        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }

        $post_id = intval($_POST['post_id']);
        $old_url = sanitize_url($_POST['old_url']);
        $new_url = sanitize_url($_POST['new_url']);

        if (!$post_id || !$old_url || !$new_url) {
            wp_send_json_error('参数不完整');
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('文章不存在');
        }

        // 替换文章内容中的链接
        $content = $post->post_content;
        $new_content = str_replace($old_url, $new_url, $content);

        if ($content !== $new_content) {
            // 更新文章内容
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $new_content
            ));

            // 清除该文章的检测缓存，触发重新检测
            delete_post_meta($post_id, '_wpnlc_link_status');
            delete_post_meta($post_id, '_wpnlc_links_data');
            delete_post_meta($post_id, '_wpnlc_last_check');

            wp_send_json_success('链接替换成功，将在下次检测时更新状态');
        } else {
            wp_send_json_error('未找到要替换的链接');
        }
    }

    /**
     * 调试统计数据
     */
    public function debug_stats() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        global $wpdb;
        $database = new WPNLC_Database();

        // 获取统计数据
        $stats = wpnlc_get_link_statistics();
        $stats['total'] = $stats['valid'] + $stats['invalid'] + $stats['mixed'] + $stats['no_links'];

        // 获取原始数据
        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            $raw_data = $wpdb->get_results(
                "SELECT link_status as status, COUNT(DISTINCT post_id) as count
                 FROM $table_name
                 GROUP BY link_status",
                ARRAY_A
            );
        } else {
            $raw_data = $wpdb->get_results(
                "SELECT meta_value as status, COUNT(*) as count
                 FROM $wpdb->postmeta
                 WHERE meta_key = '_wpnlc_link_status'
                 GROUP BY meta_value",
                ARRAY_A
            );
        }

        $stats['raw_data'] = $raw_data;

        wp_send_json_success($stats);
    }

    /**
     * 调试数据库状态
     */
    public function debug_database() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        global $wpdb;
        $database = new WPNLC_Database();

        // 获取基本统计
        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish'");

        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            $checked_posts = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name");
            $status_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $links_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE link_url != ''");

            // 获取示例记录
            $sample_records = $wpdb->get_results(
                "SELECT post_id, link_status as status, last_check as check_time
                 FROM $table_name
                 ORDER BY last_check DESC
                 LIMIT 10",
                ARRAY_A
            );

            // 格式化检测时间
            foreach ($sample_records as &$record) {
                $record['check_time'] = $record['check_time'] ?: '未知';
            }
        } else {
            $checked_posts = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_link_status'");
            $status_records = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_link_status'");
            $links_records = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_links_data'");

            // 获取示例记录
            $sample_records = $wpdb->get_results(
                "SELECT p.ID as post_id, pm.meta_value as status, pm2.meta_value as check_time
                 FROM $wpdb->posts p
                 JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_link_status'
                 LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wpnlc_last_check'
                 ORDER BY pm2.meta_value DESC
                 LIMIT 10",
                ARRAY_A
            );

            // 格式化检测时间
            foreach ($sample_records as &$record) {
                $record['check_time'] = $record['check_time'] ? date('Y-m-d H:i:s', $record['check_time']) : '未知';
            }
        }

        wp_send_json_success(array(
            'total_posts' => (int)$total_posts,
            'checked_posts' => (int)$checked_posts,
            'status_records' => (int)$status_records,
            'links_records' => (int)$links_records,
            'sample_records' => $sample_records,
            'using_new_table' => $database->table_exists()
        ));
    }

    /**
     * 调试单篇文章检测
     */
    public function debug_single_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error('无效的文章ID');
        }

        $checker = WPNLC_Core::get_instance()->get_checker();
        $result = $checker->check_post_links($post_id, true);

        $post = get_post($post_id);
        $result['post_id'] = $post_id;
        $result['post_title'] = $post ? $post->post_title : '文章不存在';

        wp_send_json_success($result);
    }

    /**
     * 调试批量检测
     */
    public function debug_batch_check() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        $count = intval($_POST['count']);
        if ($count < 1 || $count > 20) {
            $count = 5;
        }

        $checker = WPNLC_Core::get_instance()->get_checker();
        $post_ids = $checker->get_posts_to_check($count);

        if (empty($post_ids)) {
            wp_send_json_error('没有需要检测的文章');
        }

        $results = $checker->batch_check_posts($post_ids, true);

        // 统计结果
        $valid_count = 0;
        $invalid_count = 0;
        $mixed_count = 0;
        $no_links_count = 0;

        foreach ($results as $post_id => $result) {
            $post = get_post($post_id);
            $result['post_title'] = $post ? $post->post_title : '未知';

            switch ($result['status']) {
                case 'valid':
                    $valid_count++;
                    break;
                case 'invalid':
                    $invalid_count++;
                    break;
                case 'mixed':
                    $mixed_count++;
                    break;
                case 'no_links':
                    $no_links_count++;
                    break;
            }
        }

        wp_send_json_success(array(
            'checked_count' => count($results),
            'valid_count' => $valid_count,
            'invalid_count' => $invalid_count,
            'mixed_count' => $mixed_count,
            'no_links_count' => $no_links_count,
            'results' => $results
        ));
    }

    /**
     * 调试最近检测的文章
     */
    public function debug_recent_checked() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        global $wpdb;
        $database = new WPNLC_Database();

        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            $recent_posts = $wpdb->get_results(
                "SELECT l.post_id, l.post_title,
                        GROUP_CONCAT(DISTINCT l.link_status) as status,
                        MAX(l.last_check) as check_time
                 FROM $table_name l
                 JOIN $wpdb->posts p ON l.post_id = p.ID
                 WHERE p.post_status = 'publish'
                 GROUP BY l.post_id, l.post_title
                 ORDER BY check_time DESC
                 LIMIT 20",
                ARRAY_A
            );

            // 格式化时间和状态
            foreach ($recent_posts as &$post) {
                $post['check_time'] = $post['check_time'] ?: '未知';
                // 如果有多个状态，显示为混合
                if (strpos($post['status'], ',') !== false) {
                    $post['status'] = 'mixed';
                }
            }
        } else {
            $recent_posts = $wpdb->get_results(
                "SELECT p.ID as post_id, p.post_title, pm.meta_value as status, pm2.meta_value as check_time
                 FROM $wpdb->posts p
                 JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_link_status'
                 LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wpnlc_last_check'
                 WHERE p.post_status = 'publish'
                 ORDER BY pm2.meta_value DESC
                 LIMIT 20",
                ARRAY_A
            );

            // 格式化时间
            foreach ($recent_posts as &$post) {
                $post['check_time'] = $post['check_time'] ? date('Y-m-d H:i:s', $post['check_time']) : '未知';
            }
        }

        wp_send_json_success($recent_posts);
    }

    /**
     * 数据库迁移
     */
    public function migrate_database() {
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_debug')) {
            wp_send_json_error('安全验证失败');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
        }

        $database = new WPNLC_Database();

        try {
            // 1. 创建新表
            $table_created = $database->create_new_tables();
            if (!$table_created) {
                wp_send_json_error('创建新表失败');
            }

            // 2. 检查表是否创建成功
            if (!$database->table_exists()) {
                wp_send_json_error('新表创建验证失败');
            }

            // 3. 清理旧的meta数据
            $deleted_count = $database->cleanup_old_meta_data();

            wp_send_json_success(array(
                'message' => '数据库迁移成功',
                'table_created' => true,
                'deleted_meta_records' => $deleted_count,
                'new_table' => $database->get_links_table()
            ));

        } catch (Exception $e) {
            wp_send_json_error('迁移过程中发生错误: ' . $e->getMessage());
        }
    }

    /**
     * 预览批量替换链接
     */
    public function preview_replace_links() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_replace_links')) {
            wp_send_json_error('安全验证失败');
        }

        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }

        $find_url = sanitize_text_field($_POST['find_url']);
        $replace_url = sanitize_url($_POST['replace_url']);
        $match_mode_raw = sanitize_text_field($_POST['match_mode']);
        $replace_content = isset($_POST['replace_content']) && $_POST['replace_content'] === 'true';
        $replace_b2_fields = isset($_POST['replace_b2_fields']) && $_POST['replace_b2_fields'] === 'true';

        // 验证match_mode参数
        $allowed_match_modes = array('exact', 'partial', 'regex');
        if (!in_array($match_mode_raw, $allowed_match_modes)) {
            wp_send_json_error('无效的匹配模式');
        }
        $match_mode = $match_mode_raw;

        if (empty($find_url) || empty($replace_url)) {
            wp_send_json_error('请填写查找和替换的链接');
        }
        
        // 如果是正则模式，验证正则表达式的安全性
        if ($match_mode === 'regex' && !$this->is_safe_regex($find_url)) {
            wp_send_json_error('不安全的正则表达式');
        }

        global $wpdb;
        $matches = array();
        $affected_posts = array();

        // 查询所有已发布的文章
        $posts = $wpdb->get_results(
            "SELECT ID, post_title, post_content FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'",
            ARRAY_A
        );

        foreach ($posts as $post) {
            $post_matches = array();
            $post_id = $post['ID'];

            // 检查文章内容
            if ($replace_content) {
                $content_matches = $this->find_matches_in_text($post['post_content'], $find_url, $replace_url, $match_mode);
                $post_matches = array_merge($post_matches, $content_matches);
            }

            // 检查B2主题字段
            if ($replace_b2_fields) {
                $b2_download_group = get_post_meta($post_id, 'b2_single_post_download_group', true);
                if (!empty($b2_download_group) && is_array($b2_download_group)) {
                    foreach ($b2_download_group as $index => $download_item) {
                        if (isset($download_item['url'])) {
                            $b2_matches = $this->find_matches_in_text($download_item['url'], $find_url, $replace_url, $match_mode);
                            $post_matches = array_merge($post_matches, $b2_matches);
                        }
                    }
                }
            }

            if (!empty($post_matches)) {
                $affected_posts[] = $post_id;
                foreach ($post_matches as $match) {
                    $matches[] = array(
                        'post_id' => $post_id,
                        'post_title' => $post['post_title'],
                        'old_url' => $match['old'],
                        'new_url' => $match['new']
                    );
                }
            }
        }

        wp_send_json_success(array(
            'total_matches' => count($matches),
            'affected_posts' => count($affected_posts),
            'matches' => array_slice($matches, 0, 20) // 只返回前20个匹配项用于预览
        ));
    }

    /**
     * 执行批量替换链接
     */
    public function execute_replace_links() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_replace_links')) {
            wp_send_json_error('安全验证失败');
        }

        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('权限不足');
        }

        $find_url = sanitize_text_field($_POST['find_url']);
        $replace_url = sanitize_url($_POST['replace_url']);
        $match_mode = sanitize_text_field($_POST['match_mode']);
        $replace_content = isset($_POST['replace_content']) && $_POST['replace_content'] === 'true';
        $replace_b2_fields = isset($_POST['replace_b2_fields']) && $_POST['replace_b2_fields'] === 'true';

        if (empty($find_url) || empty($replace_url)) {
            wp_send_json_error('请填写查找和替换的链接');
        }

        global $wpdb;
        $replaced_count = 0;
        $affected_posts = array();

        // 查询所有已发布的文章
        $posts = $wpdb->get_results(
            "SELECT ID, post_title, post_content FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'",
            ARRAY_A
        );

        foreach ($posts as $post) {
            $post_id = $post['ID'];
            $post_updated = false;

            // 替换文章内容
            if ($replace_content) {
                $new_content = $this->replace_matches_in_text($post['post_content'], $find_url, $replace_url, $match_mode);
                if ($new_content !== $post['post_content']) {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $new_content
                    ));
                    $post_updated = true;
                    $replaced_count += substr_count($post['post_content'], $find_url);
                }
            }

            // 替换B2主题字段
            if ($replace_b2_fields) {
                $b2_download_group = get_post_meta($post_id, 'b2_single_post_download_group', true);
                if (!empty($b2_download_group) && is_array($b2_download_group)) {
                    $updated_group = $b2_download_group;
                    foreach ($updated_group as $index => &$download_item) {
                        if (isset($download_item['url'])) {
                            $new_url = $this->replace_matches_in_text($download_item['url'], $find_url, $replace_url, $match_mode);
                            if ($new_url !== $download_item['url']) {
                                $download_item['url'] = $new_url;
                                $post_updated = true;
                                $replaced_count++;
                            }
                        }
                    }
                    if ($updated_group !== $b2_download_group) {
                        update_post_meta($post_id, 'b2_single_post_download_group', $updated_group);
                    }
                }
            }

            if ($post_updated) {
                $affected_posts[] = $post_id;
                // 清除该文章的检测缓存，触发重新检测
                $database = new WPNLC_Database();
                if ($database->table_exists()) {
                    global $wpdb;
                    $table_name = $database->get_links_table();
                    $wpdb->delete($table_name, array('post_id' => $post_id), array('%d'));
                } else {
                    delete_post_meta($post_id, '_wpnlc_link_status');
                    delete_post_meta($post_id, '_wpnlc_links_data');
                    delete_post_meta($post_id, '_wpnlc_last_check');
                }
            }
        }

        wp_send_json_success(array(
            'replaced_count' => $replaced_count,
            'affected_posts' => count($affected_posts)
        ));
    }

    /**
     * 在文本中查找匹配项 - 修复正则注入漏洞
     */
    private function find_matches_in_text($text, $find_url, $replace_url, $match_mode) {
        $matches = array();

        if ($match_mode === 'regex') {
            // 验证正则表达式的安全性
            if (!$this->is_safe_regex($find_url)) {
                return $matches; // 返回空数组，不执行不安全的正则
            }
            
            // 使用错误抑制防止无效正则导致错误
            if (@preg_match_all($find_url, $text, $regex_matches)) {
                foreach ($regex_matches[0] as $match) {
                    $new_url = @preg_replace($find_url, $replace_url, $match);
                    if ($new_url !== null) {
                        $matches[] = array('old' => $match, 'new' => $new_url);
                    }
                }
            }
        } else {
            if (strpos($text, $find_url) !== false) {
                $matches[] = array('old' => $find_url, 'new' => $replace_url);
            }
        }

        return $matches;
    }

    /**
     * 在文本中替换匹配项 - 修复正则注入漏洞
     */
    private function replace_matches_in_text($text, $find_url, $replace_url, $match_mode) {
        if ($match_mode === 'regex') {
            // 验证正则表达式的安全性
            if (!$this->is_safe_regex($find_url)) {
                return $text; // 返回原文本，不执行不安全的正则
            }
            
            $result = @preg_replace($find_url, $replace_url, $text);
            return $result !== null ? $result : $text;
        } else {
            return str_replace($find_url, $replace_url, $text);
        }
    }
    
    /**
     * 验证正则表达式的安全性
     */
    private function is_safe_regex($pattern) {
        // 检查是否为空或太短
        if (empty($pattern) || strlen($pattern) < 3) {
            return false;
        }
        
        // 检查是否有正则分隔符
        $first_char = $pattern[0];
        $valid_delimiters = array('/', '#', '~', '!', '@', '%', '|', '+', '=', '^', '`');
        
        if (!in_array($first_char, $valid_delimiters)) {
            return false;
        }
        
        // 检查结束分隔符
        $last_delimiter_pos = strrpos($pattern, $first_char);
        if ($last_delimiter_pos === 0 || $last_delimiter_pos === false) {
            return false;
        }
        
        // 禁止危险的正则修饰符
        $modifiers = substr($pattern, $last_delimiter_pos + 1);
        if (preg_match('/[^imsxADSUXJu]/', $modifiers)) {
            return false;
        }
        
        // 禁止危险的正则特性
        $regex_content = substr($pattern, 1, $last_delimiter_pos - 1);
        $dangerous_patterns = array(
            '\(\?\{', // PHP代码执行
            '\(\?e\)', // eval修饰符
            '\(\?\?\{', // 代码执行
            '\\0', // null字符
        );
        
        foreach ($dangerous_patterns as $dangerous) {
            if (preg_match('/' . $dangerous . '/i', $regex_content)) {
                return false;
            }
        }
        
        // 测试正则是否有效
        $test_result = @preg_match($pattern, '');
        if ($test_result === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 快速检测单篇文章（前台使用）
     */
    public function quick_check_single() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_quick_check')) {
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限（只允许管理员在前台进行检测）
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }
        
        // 执行检测
        $checker = WPNLC_Core::get_instance()->get_checker();
        $result = $checker->check_post_links($post_id, true); // 强制检测
        
        if ($result['status'] === 'error') {
            wp_send_json_error($result['message']);
        }
        
        wp_send_json_success(array(
            'status' => $result['status'],
            'links_count' => count($result['links']),
            'message' => sprintf(__('检测完成，状态：%s', 'wp-netdisk-link-checker'), wpnlc_get_status_text($result['status']))
        ));
    }
    
    /**
     * 前台手动检测（用户主动检测）
     */
    public function frontend_manual_check() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_frontend_check')) {
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }
        
        $settings = wpnlc_get_settings();
        
        // 检查是否启用了前台手动检测功能
        if ($settings['enable_frontend_manual_check'] !== 'yes') {
            wp_send_json_error(__('前台检测功能未启用', 'wp-netdisk-link-checker'));
        }
        
        // 检查权限
        if ($settings['frontend_check_permission'] === 'logged_in' && !is_user_logged_in()) {
            wp_send_json_error(__('请先登录后再进行检测', 'wp-netdisk-link-checker'));
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error(__('无效的文章ID', 'wp-netdisk-link-checker'));
        }
        
        // 检查文章是否存在
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(__('文章不存在', 'wp-netdisk-link-checker'));
        }
        
        // 执行检测
        $checker = WPNLC_Core::get_instance()->get_checker();
        $result = $checker->check_post_links($post_id, true); // 强制检测
        
        if ($result['status'] === 'error') {
            wp_send_json_error($result['message']);
        }
        
        // 返回更详细的检测结果
        $status_text = wpnlc_get_status_text($result['status']);
        $links_count = count($result['links']);
        
        wp_send_json_success(array(
            'status' => $result['status'],
            'status_text' => $status_text,
            'links_count' => $links_count,
            'links' => $result['links'],
            'cached' => $result['cached'],
            'message' => sprintf(__('检测完成，状态：%s，共检测 %d 个链接', 'wp-netdisk-link-checker'), $status_text, $links_count)
        ));
    }

    /**
     * 获取文章状态信息（优先从新表读取）
     */
    private function get_post_status_info($post_id) {
        global $wpdb;
        $database = new WPNLC_Database();
        
        $status_info = array(
            'status' => '',
            'types' => array(),
            'last_check' => 0,
            'links_data' => array()
        );
        
        // 优先从新表读取
        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            
            $links = $wpdb->get_results($wpdb->prepare(
                "SELECT link_url, link_type, link_status, last_check, status_message 
                 FROM $table_name 
                 WHERE post_id = %d 
                 ORDER BY last_check DESC",
                $post_id
            ), ARRAY_A);
            
            if (!empty($links)) {
                $valid_count = 0;
                $invalid_count = 0;
                $no_links_count = 0;
                $types = array();
                $latest_check = 0;
                
                foreach ($links as $link) {
                    if ($link['link_status'] === 'valid') {
                        $valid_count++;
                    } elseif ($link['link_status'] === 'invalid') {
                        $invalid_count++;
                    } elseif ($link['link_status'] === 'no_links') {
                        $no_links_count++;
                    }
                    
                    if (!empty($link['link_type']) && !in_array($link['link_type'], $types)) {
                        $types[] = $link['link_type'];
                    }
                    
                    if (!empty($link['link_url'])) {
                        $status_info['links_data'][] = array(
                            'url' => $link['link_url'],
                            'type' => $link['link_type'],
                            'status' => $link['link_status'],
                            'message' => $link['status_message']
                        );
                    }
                    
                    $check_time = strtotime($link['last_check']);
                    if ($check_time > $latest_check) {
                        $latest_check = $check_time;
                    }
                }
                
                // 确定整体状态
                if ($no_links_count > 0) {
                    $status_info['status'] = 'no_links';
                } elseif ($valid_count > 0 && $invalid_count > 0) {
                    $status_info['status'] = 'mixed';
                } elseif ($valid_count > 0) {
                    $status_info['status'] = 'valid';
                } elseif ($invalid_count > 0) {
                    $status_info['status'] = 'invalid';
                } else {
                    $status_info['status'] = 'no_links';
                }
                
                $status_info['types'] = $types;
                $status_info['last_check'] = $latest_check;
                
                return $status_info;
            }
        }
        
        // 如果新表没有数据，回退到meta字段
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);
        
        if (!empty($link_status)) {
            $status_info['status'] = $link_status;
            $status_info['last_check'] = $last_check;
            $status_info['links_data'] = $links_data;
            
            // 提取网盘类型
            if (!empty($links_data) && is_array($links_data)) {
                $types = array();
                foreach ($links_data as $link) {
                    if (!empty($link['type']) && !in_array($link['type'], $types)) {
                        $types[] = $link['type'];
                    }
                }
                $status_info['types'] = $types;
            }
        }
        
        return $status_info;
    }

    /**
     * 获取进度信息
     */
    public function get_progress() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_ajax_nonce')) {
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }

        $session_id = sanitize_text_field($_POST['session_id']);
        if (empty($session_id)) {
            wp_send_json_error(__('会话ID不能为空', 'wp-netdisk-link-checker'));
        }

        $progress = new WPNLC_Progress();
        $progress_data = $progress->get_progress($session_id);

        if (!$progress_data) {
            wp_send_json_error(__('找不到进度信息', 'wp-netdisk-link-checker'));
        }

        $formatted_data = $progress->format_progress_display($progress_data);
        wp_send_json_success($formatted_data);
    }

    /**
     * 启动带进度监控的批量检测
     */
    public function start_batch_check_with_progress() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpnlc_batch_check')) {
            wp_send_json_error(__('安全验证失败', 'wp-netdisk-link-checker'));
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('权限不足', 'wp-netdisk-link-checker'));
        }

        $batch_size = isset($_POST['batch_size']) ? max(20, min(100, intval($_POST['batch_size']))) : 50;
        
        // 获取需要检测的文章
        $checker = WPNLC_Core::get_instance()->get_checker();
        $post_ids = $checker->get_posts_to_check($batch_size);

        if (empty($post_ids)) {
            wp_send_json_success(array(
                'message' => __('没有需要检测的文章', 'wp-netdisk-link-checker'),
                'checked_count' => 0
            ));
        }

        // 创建进度跟踪会话
        $progress = new WPNLC_Progress();
        $session_id = $progress->generate_session_id('batch_check');
        
        if (!$progress->create_session($session_id, count($post_ids), 'batch_check')) {
            wp_send_json_error(__('无法创建进度跟踪会话', 'wp-netdisk-link-checker'));
        }

        // 启动后台任务进行检测
        wp_schedule_single_event(time(), 'wpnlc_background_batch_check', array($session_id, $post_ids));

        wp_send_json_success(array(
            'session_id' => $session_id,
            'total_posts' => count($post_ids),
            'message' => __('批量检测已启动，正在后台处理...', 'wp-netdisk-link-checker')
        ));
    }

    /**
     * 后台批量检测处理器
     *
     * @param string $session_id 会话ID
     * @param array $post_ids 文章ID数组
     */
    public function background_batch_check($session_id, $post_ids) {
        $progress = new WPNLC_Progress();
        $checker = WPNLC_Core::get_instance()->get_checker();

        try {
            foreach ($post_ids as $index => $post_id) {
                // 更新当前处理项目
                $post = get_post($post_id);
                $current_item = $post ? $post->post_title : "文章 ID: $post_id";
                
                $progress->update_progress($session_id, array(
                    'current_item' => $current_item
                ));

                // 执行检测
                $result = $checker->check_post_links($post_id, true);

                // 更新统计信息
                $statistics = array();
                if ($result['status'] === 'valid') {
                    $statistics['valid_links'] = 1;
                } elseif ($result['status'] === 'invalid') {
                    $statistics['invalid_links'] = 1;
                } elseif ($result['status'] === 'error') {
                    $statistics['error_links'] = 1;
                }

                // 计算响应时间
                if (isset($result['links']) && is_array($result['links'])) {
                    $total_response_time = 0;
                    foreach ($result['links'] as $link) {
                        if (isset($link['response_time'])) {
                            $total_response_time += $link['response_time'];
                        }
                    }
                    $statistics['total_response_time'] = $total_response_time;
                }

                // 更新进度
                $update_data = array(
                    'completed_items' => $index + 1,
                    'statistics' => $statistics
                );

                if ($result['status'] === 'error') {
                    $update_data['failed_items'] = 1;
                    $update_data['error'] = "文章 {$current_item} 检测失败: " . (isset($result['message']) ? $result['message'] : '未知错误');
                }

                $progress->update_progress($session_id, $update_data);

                // 添加短暂延迟，避免过于频繁的请求
                usleep(100000); // 0.1秒
            }

            // 完成检测
            $progress->complete_session($session_id, 'completed');

        } catch (Exception $e) {
            // 处理异常
            $progress->update_progress($session_id, array(
                'error' => '批量检测过程中发生错误: ' . $e->getMessage()
            ));
            $progress->complete_session($session_id, 'error');
        }
    }
}
