<?php
/**
 * 健康监控类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 健康监控类
 */
class WPNLC_Health_Monitor {

    /**
     * 监控数据缓存键
     */
    const HEALTH_DATA_KEY = 'wpnlc_health_data';

    /**
     * 警报历史键
     */
    const ALERT_HISTORY_KEY = 'wpnlc_alert_history';

    /**
     * 构造函数
     */
    public function __construct() {
        // 定期健康检查
        add_action('wpnlc_health_check', array($this, 'perform_health_check'));
        
        // 每小时执行一次健康检查
        if (!wp_next_scheduled('wpnlc_health_check')) {
            wp_schedule_event(time(), 'hourly', 'wpnlc_health_check');
        }
    }

    /**
     * 执行健康检查
     */
    public function perform_health_check() {
        $health_data = $this->collect_health_metrics();
        $this->save_health_data($health_data);
        
        // 检查是否需要发送警报
        $alerts = $this->check_health_alerts($health_data);
        if (!empty($alerts)) {
            $this->send_health_alerts($alerts);
        }

        // 清理旧的健康数据
        $this->cleanup_old_health_data();
    }

    /**
     * 收集健康指标
     *
     * @return array 健康数据
     */
    public function collect_health_metrics() {
        global $wpdb;
        
        $database = new WPNLC_Database();
        $settings = wpnlc_get_settings();
        
        $health_data = array(
            'timestamp' => current_time('timestamp'),
            'cron_status' => $this->check_cron_health(),
            'database_status' => $this->check_database_health(),
            'performance_metrics' => $this->collect_performance_metrics(),
            'error_rates' => $this->calculate_error_rates(),
            'system_resources' => $this->check_system_resources(),
            'plugin_status' => $this->check_plugin_status()
        );

        return $health_data;
    }

    /**
     * 检查定时任务健康状态
     *
     * @return array 定时任务状态
     */
    private function check_cron_health() {
        $cron = WPNLC_Core::get_instance()->get_cron();
        $cron_status = $cron->get_cron_status();
        
        $health = array(
            'main_task_enabled' => $cron_status['main_task']['enabled'],
            'main_task_next_run' => $cron_status['main_task']['next_run'],
            'quick_task_enabled' => $cron_status['quick_task']['enabled'],
            'quick_task_next_run' => $cron_status['quick_task']['next_run'],
            'wp_cron_enabled' => !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON,
            'last_execution' => $this->get_last_cron_execution()
        );

        // 检查定时任务是否正常运行
        $health['status'] = 'healthy';
        if (!$health['wp_cron_enabled']) {
            $health['status'] = 'critical';
            $health['message'] = 'WordPress Cron被禁用';
        } elseif ($health['main_task_enabled'] && !$health['main_task_next_run']) {
            $health['status'] = 'warning';
            $health['message'] = '主检测任务未安排';
        } elseif ($health['last_execution'] && (current_time('timestamp') - $health['last_execution']) > 86400) {
            $health['status'] = 'warning';
            $health['message'] = '超过24小时未执行检测任务';
        }

        return $health;
    }

    /**
     * 检查数据库健康状态
     *
     * @return array 数据库状态
     */
    private function check_database_health() {
        global $wpdb;
        
        $database = new WPNLC_Database();
        
        $health = array(
            'table_exists' => $database->table_exists(),
            'table_size' => 0,
            'record_count' => 0,
            'index_usage' => array(),
            'status' => 'healthy'
        );

        if ($health['table_exists']) {
            $table_name = $database->get_links_table();
            
            // 获取表大小
            $table_status = $wpdb->get_row($wpdb->prepare(
                "SHOW TABLE STATUS LIKE %s",
                $table_name
            ));
            
            if ($table_status) {
                $health['table_size'] = $table_status->Data_length + $table_status->Index_length;
                $health['record_count'] = $table_status->Rows;
            }

            // 检查索引使用情况
            $health['index_usage'] = $this->check_index_usage($table_name);
            
            // 检查数据库连接
            if (!$wpdb->check_connection()) {
                $health['status'] = 'critical';
                $health['message'] = '数据库连接失败';
            } elseif ($health['table_size'] > 100 * 1024 * 1024) { // 100MB
                $health['status'] = 'warning';
                $health['message'] = '数据表过大，建议清理';
            }
        } else {
            $health['status'] = 'warning';
            $health['message'] = '新数据表不存在，使用旧的meta存储';
        }

        return $health;
    }

    /**
     * 收集性能指标
     *
     * @return array 性能数据
     */
    private function collect_performance_metrics() {
        $logs = $this->get_recent_check_logs(100);
        
        $metrics = array(
            'avg_response_time' => 0,
            'max_response_time' => 0,
            'min_response_time' => PHP_INT_MAX,
            'total_checks' => 0,
            'concurrent_usage' => 0,
            'cache_hit_rate' => 0
        );

        if (!empty($logs)) {
            $total_time = 0;
            $cached_count = 0;
            
            foreach ($logs as $log) {
                if (isset($log['results']) && is_array($log['results'])) {
                    foreach ($log['results'] as $result) {
                        $metrics['total_checks']++;
                        
                        if (isset($result['cached']) && $result['cached']) {
                            $cached_count++;
                        }
                        
                        if (isset($result['response_time'])) {
                            $response_time = floatval($result['response_time']);
                            $total_time += $response_time;
                            $metrics['max_response_time'] = max($metrics['max_response_time'], $response_time);
                            $metrics['min_response_time'] = min($metrics['min_response_time'], $response_time);
                        }
                    }
                }
            }

            if ($metrics['total_checks'] > 0) {
                $metrics['avg_response_time'] = $total_time / $metrics['total_checks'];
                $metrics['cache_hit_rate'] = ($cached_count / $metrics['total_checks']) * 100;
            }

            if ($metrics['min_response_time'] === PHP_INT_MAX) {
                $metrics['min_response_time'] = 0;
            }
        }

        // 检查并发使用情况
        $progress = new WPNLC_Progress();
        $active_sessions = $progress->get_active_sessions();
        $metrics['concurrent_usage'] = count($active_sessions);

        return $metrics;
    }

    /**
     * 计算错误率
     *
     * @return array 错误率数据
     */
    private function calculate_error_rates() {
        global $wpdb;
        
        $database = new WPNLC_Database();
        $error_rates = array(
            'overall_error_rate' => 0,
            'network_error_rate' => 0,
            'timeout_error_rate' => 0,
            'recent_errors' => array()
        );

        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            $one_hour_ago = date('Y-m-d H:i:s', current_time('timestamp') - 3600);

            // 计算总体错误率
            $total_checks = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE last_check > %s",
                $one_hour_ago
            ));

            $error_checks = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE last_check > %s AND link_status = 'error'",
                $one_hour_ago
            ));

            if ($total_checks > 0) {
                $error_rates['overall_error_rate'] = ($error_checks / $total_checks) * 100;
            }

            // 获取最近的错误
            $recent_errors = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, link_url, status_message, last_check 
                 FROM $table_name 
                 WHERE link_status = 'error' AND last_check > %s 
                 ORDER BY last_check DESC 
                 LIMIT 10",
                $one_hour_ago
            ), ARRAY_A);

            $error_rates['recent_errors'] = $recent_errors;

            // 分析错误类型
            $network_errors = 0;
            $timeout_errors = 0;
            
            foreach ($recent_errors as $error) {
                $message = strtolower($error['status_message']);
                if (strpos($message, 'timeout') !== false || strpos($message, '超时') !== false) {
                    $timeout_errors++;
                } elseif (strpos($message, 'network') !== false || strpos($message, '网络') !== false) {
                    $network_errors++;
                }
            }

            if ($error_checks > 0) {
                $error_rates['network_error_rate'] = ($network_errors / $error_checks) * 100;
                $error_rates['timeout_error_rate'] = ($timeout_errors / $error_checks) * 100;
            }
        }

        return $error_rates;
    }

    /**
     * 检查系统资源
     *
     * @return array 系统资源状态
     */
    private function check_system_resources() {
        $resources = array(
            'memory_usage' => 0,
            'memory_limit' => 0,
            'memory_percentage' => 0,
            'disk_space' => 0,
            'php_version' => PHP_VERSION,
            'max_execution_time' => ini_get('max_execution_time'),
            'status' => 'healthy'
        );

        // 内存使用情况
        $resources['memory_usage'] = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        
        if ($memory_limit !== '-1') {
            $resources['memory_limit'] = $this->convert_to_bytes($memory_limit);
            if ($resources['memory_limit'] > 0) {
                $resources['memory_percentage'] = ($resources['memory_usage'] / $resources['memory_limit']) * 100;
                
                if ($resources['memory_percentage'] > 90) {
                    $resources['status'] = 'critical';
                    $resources['message'] = '内存使用率过高';
                } elseif ($resources['memory_percentage'] > 75) {
                    $resources['status'] = 'warning';
                    $resources['message'] = '内存使用率较高';
                }
            }
        }

        // 磁盘空间
        $upload_dir = wp_upload_dir();
        if (isset($upload_dir['basedir'])) {
            $resources['disk_space'] = disk_free_space($upload_dir['basedir']);
            
            if ($resources['disk_space'] < 100 * 1024 * 1024) { // 100MB
                $resources['status'] = 'warning';
                $resources['message'] = '磁盘空间不足';
            }
        }

        return $resources;
    }

    /**
     * 检查插件状态
     *
     * @return array 插件状态
     */
    private function check_plugin_status() {
        $status = array(
            'version' => WPNLC_VERSION,
            'active_modules' => array(),
            'configuration_issues' => array(),
            'status' => 'healthy'
        );

        // 检查必要的PHP扩展
        $required_extensions = array('curl', 'json', 'mbstring');
        $missing_extensions = array();
        
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        if (!empty($missing_extensions)) {
            $status['status'] = 'critical';
            $status['configuration_issues'][] = '缺少必要的PHP扩展: ' . implode(', ', $missing_extensions);
        }

        // 检查设置配置
        $settings = wpnlc_get_settings();
        if ($settings['check_frequency'] === 'disabled') {
            $status['configuration_issues'][] = '自动检测已禁用';
        }

        if (!empty($status['configuration_issues'])) {
            $status['status'] = 'warning';
        }

        return $status;
    }

    /**
     * 检查健康警报
     *
     * @param array $health_data 健康数据
     * @return array 警报列表
     */
    private function check_health_alerts($health_data) {
        $alerts = array();
        $settings = wpnlc_get_settings();

        // 定时任务警报
        if ($health_data['cron_status']['status'] === 'critical') {
            $alerts[] = array(
                'type' => 'critical',
                'category' => 'cron',
                'message' => $health_data['cron_status']['message'],
                'timestamp' => $health_data['timestamp']
            );
        }

        // 高错误率警报
        if ($health_data['error_rates']['overall_error_rate'] > 50) {
            $alerts[] = array(
                'type' => 'warning',
                'category' => 'error_rate',
                'message' => sprintf('检测错误率过高: %.1f%%', $health_data['error_rates']['overall_error_rate']),
                'timestamp' => $health_data['timestamp']
            );
        }

        // 系统资源警报
        if ($health_data['system_resources']['status'] === 'critical') {
            $alerts[] = array(
                'type' => 'critical',
                'category' => 'resources',
                'message' => $health_data['system_resources']['message'],
                'timestamp' => $health_data['timestamp']
            );
        }

        // 数据库警报
        if ($health_data['database_status']['status'] === 'critical') {
            $alerts[] = array(
                'type' => 'critical',
                'category' => 'database',
                'message' => $health_data['database_status']['message'],
                'timestamp' => $health_data['timestamp']
            );
        }

        return $alerts;
    }

    /**
     * 发送健康警报
     *
     * @param array $alerts 警报列表
     */
    private function send_health_alerts($alerts) {
        $settings = wpnlc_get_settings();
        
        if ($settings['enable_notifications'] !== 'yes') {
            return;
        }

        $email = !empty($settings['notification_email']) 
            ? $settings['notification_email'] 
            : get_option('admin_email');

        $critical_alerts = array_filter($alerts, function($alert) {
            return $alert['type'] === 'critical';
        });

        if (!empty($critical_alerts)) {
            $subject = sprintf('[%s] 网盘链接检测插件严重警报', get_bloginfo('name'));
            $message = "检测到以下严重问题：\n\n";
            
            foreach ($critical_alerts as $alert) {
                $message .= sprintf("• %s: %s\n", $alert['category'], $alert['message']);
            }
            
            $message .= "\n请及时处理这些问题以确保插件正常运行。";
            
            wp_mail($email, $subject, $message);
        }

        // 保存警报历史
        $this->save_alert_history($alerts);
    }

    /**
     * 获取健康状态摘要
     *
     * @return array 健康状态摘要
     */
    public function get_health_summary() {
        $health_data = $this->get_latest_health_data();
        
        if (!$health_data) {
            return array(
                'overall_status' => 'unknown',
                'message' => '暂无健康数据'
            );
        }

        $critical_issues = 0;
        $warnings = 0;

        // 检查各个组件状态
        $components = array('cron_status', 'database_status', 'system_resources', 'plugin_status');
        
        foreach ($components as $component) {
            if (isset($health_data[$component]['status'])) {
                if ($health_data[$component]['status'] === 'critical') {
                    $critical_issues++;
                } elseif ($health_data[$component]['status'] === 'warning') {
                    $warnings++;
                }
            }
        }

        // 确定整体状态
        if ($critical_issues > 0) {
            $overall_status = 'critical';
            $message = sprintf('发现 %d 个严重问题', $critical_issues);
        } elseif ($warnings > 0) {
            $overall_status = 'warning';
            $message = sprintf('发现 %d 个警告', $warnings);
        } else {
            $overall_status = 'healthy';
            $message = '运行正常';
        }

        return array(
            'overall_status' => $overall_status,
            'message' => $message,
            'critical_issues' => $critical_issues,
            'warnings' => $warnings,
            'last_check' => $health_data['timestamp']
        );
    }

    /**
     * 保存健康数据
     *
     * @param array $health_data 健康数据
     */
    private function save_health_data($health_data) {
        $stored_data = get_option(self::HEALTH_DATA_KEY, array());
        
        // 保留最近24小时的数据
        $cutoff_time = current_time('timestamp') - 86400;
        $stored_data = array_filter($stored_data, function($data) use ($cutoff_time) {
            return $data['timestamp'] > $cutoff_time;
        });

        $stored_data[] = $health_data;
        update_option(self::HEALTH_DATA_KEY, $stored_data);
    }

    /**
     * 获取最新健康数据
     *
     * @return array|false 最新健康数据
     */
    private function get_latest_health_data() {
        $stored_data = get_option(self::HEALTH_DATA_KEY, array());
        
        if (empty($stored_data)) {
            return false;
        }

        return end($stored_data);
    }

    /**
     * 保存警报历史
     *
     * @param array $alerts 警报列表
     */
    private function save_alert_history($alerts) {
        $history = get_option(self::ALERT_HISTORY_KEY, array());
        
        foreach ($alerts as $alert) {
            $history[] = $alert;
        }

        // 保留最近100条警报
        $history = array_slice($history, -100);
        update_option(self::ALERT_HISTORY_KEY, $history);
    }

    /**
     * 获取最近的检测日志
     *
     * @param int $limit 限制数量
     * @return array 检测日志
     */
    private function get_recent_check_logs($limit = 10) {
        $cron = WPNLC_Core::get_instance()->get_cron();
        return $cron->get_check_logs($limit);
    }

    /**
     * 获取最后执行时间
     *
     * @return int|false 最后执行时间戳
     */
    private function get_last_cron_execution() {
        $logs = $this->get_recent_check_logs(1);
        
        if (!empty($logs) && isset($logs[0]['time'])) {
            return strtotime($logs[0]['time']);
        }

        return false;
    }

    /**
     * 检查索引使用情况
     *
     * @param string $table_name 表名
     * @return array 索引使用情况
     */
    private function check_index_usage($table_name) {
        global $wpdb;
        
        // 这里可以添加索引使用情况的检查
        // 为简化，返回空数组
        return array();
    }

    /**
     * 转换内存大小为字节
     *
     * @param string $size 内存大小（如 "128M"）
     * @return int 字节数
     */
    private function convert_to_bytes($size) {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;

        switch ($last) {
            case 'g':
                $size *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $size *= 1024 * 1024;
                break;
            case 'k':
                $size *= 1024;
                break;
        }

        return $size;
    }

    /**
     * 清理旧的健康数据
     */
    private function cleanup_old_health_data() {
        // 清理超过7天的健康数据
        $cutoff_time = current_time('timestamp') - (7 * 86400);
        
        $stored_data = get_option(self::HEALTH_DATA_KEY, array());
        $stored_data = array_filter($stored_data, function($data) use ($cutoff_time) {
            return $data['timestamp'] > $cutoff_time;
        });
        
        update_option(self::HEALTH_DATA_KEY, $stored_data);

        // 清理旧的警报历史
        $alert_history = get_option(self::ALERT_HISTORY_KEY, array());
        $alert_history = array_filter($alert_history, function($alert) use ($cutoff_time) {
            return $alert['timestamp'] > $cutoff_time;
        });
        
        update_option(self::ALERT_HISTORY_KEY, $alert_history);
    }
}