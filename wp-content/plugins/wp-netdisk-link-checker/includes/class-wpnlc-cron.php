<?php
/**
 * 计划任务类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 计划任务类
 */
class WPNLC_Cron {

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
        // 添加计划任务钩子
        add_action('wpnlc_check_links_event', array($this, 'scheduled_check_links'));
        add_action('wpnlc_quick_check_event', array($this, 'quick_check_links'));
        
        // 添加自定义计划间隔
        add_filter('cron_schedules', array($this, 'add_custom_cron_intervals'));
        
        // 在设置更新时重新安排任务
        add_action('update_option_wpnlc_settings', array($this, 'reschedule_tasks'), 10, 2);
    }

    /**
     * 添加自定义计划间隔
     *
     * @param array $schedules 现有计划间隔
     * @return array 修改后的计划间隔
     */
    public function add_custom_cron_intervals($schedules) {
        // 添加快速检测间隔
        $schedules['wpnlc_30sec'] = array(
            'interval' => 30,
            'display' => __('每30秒', 'wp-netdisk-link-checker')
        );
        
        $schedules['wpnlc_1min'] = array(
            'interval' => 60,
            'display' => __('每分钟', 'wp-netdisk-link-checker')
        );
        
        $schedules['wpnlc_3min'] = array(
            'interval' => 180,
            'display' => __('每3分钟', 'wp-netdisk-link-checker')
        );
        
        $schedules['wpnlc_5min'] = array(
            'interval' => 300,
            'display' => __('每5分钟', 'wp-netdisk-link-checker')
        );
        
        $schedules['wpnlc_10min'] = array(
            'interval' => 600,
            'display' => __('每10分钟', 'wp-netdisk-link-checker')
        );
        
        return $schedules;
    }

    /**
     * 计划检测链接
     */
    public function scheduled_check_links() {
        $settings = wpnlc_get_settings();
        
        // 如果禁用了自动检查，则退出
        if ($settings['check_frequency'] === 'disabled') {
            return;
        }
        
        // 获取需要检测的文章
        $checker = WPNLC_Core::get_instance()->get_checker();
        $batch_size = isset($settings['auto_check_batch']) ? max(10, intval($settings['auto_check_batch'])) : 50;
        $post_ids = $checker->get_posts_to_check($batch_size);
        
        if (empty($post_ids)) {
            return;
        }
        
        // 执行检测
        $results = $checker->batch_check_posts($post_ids);
        
        // 发送通知（如果启用）
        if ($settings['enable_notifications'] === 'yes') {
            $this->send_notification_if_needed($results);
        }
        
        // 记录日志
        $this->log_check_results($results);
    }

    /**
     * 快速检测链接
     */
    public function quick_check_links() {
        $settings = wpnlc_get_settings();
        
        // 如果禁用了快速检测，则退出
        if ($settings['quick_check_interval'] === 'disabled') {
            return;
        }
        
        $batch_size = max(1, intval($settings['quick_check_batch']));
        
        // 获取需要检测的文章
        $checker = WPNLC_Core::get_instance()->get_checker();
        $post_ids = $checker->get_posts_to_check($batch_size);
        
        if (empty($post_ids)) {
            return;
        }
        
        // 执行检测
        $results = $checker->batch_check_posts($post_ids);
        
        // 记录日志
        $this->log_check_results($results, 'quick');
    }

    /**
     * 重新安排任务
     *
     * @param mixed $old_value 旧设置值
     * @param mixed $new_value 新设置值
     */
    public function reschedule_tasks($old_value, $new_value) {
        // 清除现有任务
        $this->clear_scheduled_tasks();
        
        // 重新安排任务
        $this->schedule_tasks($new_value);
    }

    /**
     * 安排任务
     *
     * @param array $settings 设置数组
     */
    public function schedule_tasks($settings = null) {
        if ($settings === null) {
            $settings = wpnlc_get_settings();
        }

        // 安排主检测任务
        if ($settings['check_frequency'] !== 'disabled') {
            if (!wp_next_scheduled('wpnlc_check_links_event')) {
                wp_schedule_event(current_time('timestamp'), $settings['check_frequency'], 'wpnlc_check_links_event');
            }
        }

        // 安排快速检测任务
        if ($settings['quick_check_interval'] !== 'disabled') {
            $interval_map = array(
                '30sec' => 'wpnlc_30sec',
                '1min' => 'wpnlc_1min',
                '3min' => 'wpnlc_3min',
                '5min' => 'wpnlc_5min',
                '10min' => 'wpnlc_10min'
            );

            $interval = isset($interval_map[$settings['quick_check_interval']])
                ? $interval_map[$settings['quick_check_interval']]
                : 'wpnlc_1min';

            if (!wp_next_scheduled('wpnlc_quick_check_event')) {
                wp_schedule_event(current_time('timestamp'), $interval, 'wpnlc_quick_check_event');
            }
        }
    }



    /**
     * 清除计划任务
     */
    public function clear_scheduled_tasks() {
        // 清除主检测任务
        $timestamp = wp_next_scheduled('wpnlc_check_links_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wpnlc_check_links_event');
        }
        
        // 清除快速检测任务
        $quick_timestamp = wp_next_scheduled('wpnlc_quick_check_event');
        if ($quick_timestamp) {
            wp_unschedule_event($quick_timestamp, 'wpnlc_quick_check_event');
        }
    }

    /**
     * 发送通知（如果需要）
     *
     * @param array $results 检测结果
     */
    private function send_notification_if_needed($results) {
        $invalid_posts = array();
        
        foreach ($results as $post_id => $result) {
            if ($result['status'] === 'invalid') {
                $invalid_posts[] = $post_id;
            }
        }
        
        if (empty($invalid_posts)) {
            return;
        }
        
        $settings = wpnlc_get_settings();
        $email = !empty($settings['notification_email']) 
            ? $settings['notification_email'] 
            : get_option('admin_email');
        
        $subject = sprintf(__('[%s] 发现失效的网盘链接', 'wp-netdisk-link-checker'), get_bloginfo('name'));
        
        $message = __('以下文章中发现了失效的网盘链接：', 'wp-netdisk-link-checker') . "\n\n";
        
        foreach ($invalid_posts as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $message .= sprintf(
                    "%s\n%s\n\n",
                    $post->post_title,
                    get_permalink($post_id)
                );
            }
        }
        
        $message .= sprintf(
            __('请登录后台查看详细信息：%s', 'wp-netdisk-link-checker'),
            admin_url('edit.php')
        );
        
        wp_mail($email, $subject, $message);
    }

    /**
     * 记录检测结果日志
     *
     * @param array $results 检测结果
     * @param string $type 检测类型
     */
    private function log_check_results($results, $type = 'scheduled') {
        $log_data = array(
            'time' => current_time('mysql'),
            'type' => $type,
            'checked_count' => count($results),
            'results' => array()
        );
        
        foreach ($results as $post_id => $result) {
            $log_data['results'][] = array(
                'post_id' => $post_id,
                'status' => $result['status'],
                'cached' => $result['cached']
            );
        }
        
        // 保存到选项表（保留最近10次记录）
        $logs = get_option('wpnlc_check_logs', array());
        array_unshift($logs, $log_data);
        $logs = array_slice($logs, 0, 10);
        update_option('wpnlc_check_logs', $logs);
    }

    /**
     * 获取检测日志
     *
     * @param int $limit 限制数量
     * @return array 日志数组
     */
    public function get_check_logs($limit = 10) {
        $logs = get_option('wpnlc_check_logs', array());
        return array_slice($logs, 0, $limit);
    }

    /**
     * 清除检测日志
     */
    public function clear_check_logs() {
        delete_option('wpnlc_check_logs');
    }

    /**
     * 获取下次检测时间
     *
     * @param string $event_name 事件名称
     * @return int|false 时间戳或false
     */
    public function get_next_check_time($event_name = 'wpnlc_check_links_event') {
        return wp_next_scheduled($event_name);
    }

    /**
     * 手动触发检测
     *
     * @param string $type 检测类型 ('scheduled' 或 'quick')
     * @return array 检测结果
     */
    public function manual_trigger_check($type = 'scheduled') {
        if ($type === 'quick') {
            $this->quick_check_links();
        } else {
            $this->scheduled_check_links();
        }
        
        // 返回最新的日志
        $logs = $this->get_check_logs(1);
        return !empty($logs) ? $logs[0] : array();
    }

    /**
     * 检查任务是否正常运行
     *
     * @return array 状态信息
     */
    public function get_cron_status() {
        $settings = wpnlc_get_settings();
        
        $status = array(
            'main_task' => array(
                'enabled' => $settings['check_frequency'] !== 'disabled',
                'next_run' => wp_next_scheduled('wpnlc_check_links_event'),
                'frequency' => $settings['check_frequency']
            ),
            'quick_task' => array(
                'enabled' => $settings['quick_check_interval'] !== 'disabled',
                'next_run' => wp_next_scheduled('wpnlc_quick_check_event'),
                'interval' => $settings['quick_check_interval']
            )
        );
        
        return $status;
    }
}
