<?php
/**
 * 进度监控类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 进度监控类
 */
class WPNLC_Progress {

    /**
     * 进度缓存key前缀
     */
    const PROGRESS_KEY_PREFIX = 'wpnlc_progress_';

    /**
     * 默认进度数据TTL（秒）
     */
    const DEFAULT_TTL = 300; // 5分钟

    /**
     * 创建进度跟踪会话
     *
     * @param string $session_id 会话ID
     * @param int $total_items 总项目数
     * @param string $task_type 任务类型
     * @return bool 是否成功
     */
    public function create_session($session_id, $total_items, $task_type = 'check') {
        $progress_data = array(
            'session_id' => $session_id,
            'task_type' => $task_type,
            'total_items' => $total_items,
            'completed_items' => 0,
            'failed_items' => 0,
            'current_item' => '',
            'percentage' => 0,
            'start_time' => current_time('timestamp'),
            'last_update' => current_time('timestamp'),
            'estimated_remaining' => 0,
            'status' => 'running',
            'statistics' => array(
                'valid_links' => 0,
                'invalid_links' => 0,
                'error_links' => 0,
                'total_response_time' => 0,
                'avg_response_time' => 0
            ),
            'errors' => array(),
            'warnings' => array()
        );

        return $this->save_progress($session_id, $progress_data);
    }

    /**
     * 更新进度
     *
     * @param string $session_id 会话ID
     * @param array $update_data 更新数据
     * @return bool 是否成功
     */
    public function update_progress($session_id, $update_data) {
        $progress_data = $this->get_progress($session_id);
        
        if (!$progress_data) {
            return false;
        }

        // 更新基本信息
        foreach (array('completed_items', 'failed_items', 'current_item', 'status') as $key) {
            if (isset($update_data[$key])) {
                $progress_data[$key] = $update_data[$key];
            }
        }

        // 计算百分比
        if ($progress_data['total_items'] > 0) {
            $progress_data['percentage'] = min(100, round(($progress_data['completed_items'] / $progress_data['total_items']) * 100, 1));
        }

        // 更新统计信息
        if (isset($update_data['statistics'])) {
            foreach ($update_data['statistics'] as $key => $value) {
                if (isset($progress_data['statistics'][$key])) {
                    $progress_data['statistics'][$key] += $value;
                }
            }
            
            // 计算平均响应时间
            $completed = $progress_data['completed_items'];
            if ($completed > 0 && $progress_data['statistics']['total_response_time'] > 0) {
                $progress_data['statistics']['avg_response_time'] = 
                    $progress_data['statistics']['total_response_time'] / $completed;
            }
        }

        // 估算剩余时间
        $progress_data['estimated_remaining'] = $this->calculate_remaining_time($progress_data);
        $progress_data['last_update'] = current_time('timestamp');

        // 添加错误信息
        if (isset($update_data['error'])) {
            $progress_data['errors'][] = array(
                'time' => current_time('mysql'),
                'message' => $update_data['error']
            );
        }

        // 添加警告信息
        if (isset($update_data['warning'])) {
            $progress_data['warnings'][] = array(
                'time' => current_time('mysql'),
                'message' => $update_data['warning']
            );
        }

        return $this->save_progress($session_id, $progress_data);
    }

    /**
     * 完成进度跟踪
     *
     * @param string $session_id 会话ID
     * @param string $final_status 最终状态
     * @return bool 是否成功
     */
    public function complete_session($session_id, $final_status = 'completed') {
        $progress_data = $this->get_progress($session_id);
        
        if (!$progress_data) {
            return false;
        }

        $progress_data['status'] = $final_status;
        $progress_data['percentage'] = 100;
        $progress_data['completed_items'] = $progress_data['total_items'];
        $progress_data['last_update'] = current_time('timestamp');
        $progress_data['end_time'] = current_time('timestamp');
        $progress_data['duration'] = $progress_data['end_time'] - $progress_data['start_time'];

        return $this->save_progress($session_id, $progress_data);
    }

    /**
     * 获取进度信息
     *
     * @param string $session_id 会话ID
     * @return array|false 进度数据或false
     */
    public function get_progress($session_id) {
        $cache_key = $this->get_cache_key($session_id);
        
        // 优先从对象缓存获取
        $progress_data = wp_cache_get($cache_key, 'wpnlc_progress');
        
        if (false === $progress_data) {
            // 从瞬态缓存获取
            $progress_data = get_transient($cache_key);
        }

        return $progress_data;
    }

    /**
     * 删除进度跟踪会话
     *
     * @param string $session_id 会话ID
     * @return bool 是否成功
     */
    public function delete_session($session_id) {
        $cache_key = $this->get_cache_key($session_id);
        
        // 从对象缓存删除
        wp_cache_delete($cache_key, 'wpnlc_progress');
        
        // 从瞬态缓存删除
        return delete_transient($cache_key);
    }

    /**
     * 获取所有活跃会话
     *
     * @return array 活跃会话列表
     */
    public function get_active_sessions() {
        global $wpdb;
        
        $sessions = array();
        $prefix = $this->get_cache_key('');
        
        // 查询数据库中的瞬态数据
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value 
             FROM $wpdb->options 
             WHERE option_name LIKE %s 
             AND option_name NOT LIKE %s
             ORDER BY option_id DESC",
            '_transient_' . $prefix . '%',
            '_transient_timeout_%'
        ));

        foreach ($results as $result) {
            $session_id = str_replace('_transient_' . $prefix, '', $result->option_name);
            $data = maybe_unserialize($result->option_value);
            
            if ($data && isset($data['status']) && $data['status'] === 'running') {
                $sessions[$session_id] = $data;
            }
        }

        return $sessions;
    }

    /**
     * 清理过期会话
     *
     * @param int $max_age 最大年龄（秒）
     * @return int 清理的会话数
     */
    public function cleanup_expired_sessions($max_age = 3600) {
        $sessions = $this->get_active_sessions();
        $cleaned = 0;
        $cutoff_time = current_time('timestamp') - $max_age;

        foreach ($sessions as $session_id => $data) {
            if (isset($data['last_update']) && $data['last_update'] < $cutoff_time) {
                $this->delete_session($session_id);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * 生成会话ID
     *
     * @param string $prefix 前缀
     * @return string 会话ID
     */
    public function generate_session_id($prefix = 'batch') {
        return $prefix . '_' . wp_generate_uuid4();
    }

    /**
     * 保存进度数据
     *
     * @param string $session_id 会话ID
     * @param array $progress_data 进度数据
     * @return bool 是否成功
     */
    private function save_progress($session_id, $progress_data) {
        $cache_key = $this->get_cache_key($session_id);
        
        // 保存到对象缓存
        wp_cache_set($cache_key, $progress_data, 'wpnlc_progress', self::DEFAULT_TTL);
        
        // 保存到瞬态缓存（持久化）
        return set_transient($cache_key, $progress_data, self::DEFAULT_TTL);
    }

    /**
     * 获取缓存键名
     *
     * @param string $session_id 会话ID
     * @return string 缓存键名
     */
    private function get_cache_key($session_id) {
        return self::PROGRESS_KEY_PREFIX . $session_id;
    }

    /**
     * 计算剩余时间
     *
     * @param array $progress_data 进度数据
     * @return int 剩余时间（秒）
     */
    private function calculate_remaining_time($progress_data) {
        if ($progress_data['completed_items'] <= 0 || $progress_data['percentage'] >= 100) {
            return 0;
        }

        $elapsed_time = current_time('timestamp') - $progress_data['start_time'];
        $items_per_second = $progress_data['completed_items'] / max(1, $elapsed_time);
        $remaining_items = $progress_data['total_items'] - $progress_data['completed_items'];

        return $remaining_items > 0 ? round($remaining_items / max(0.1, $items_per_second)) : 0;
    }

    /**
     * 格式化进度信息用于显示
     *
     * @param array $progress_data 进度数据
     * @return array 格式化后的数据
     */
    public function format_progress_display($progress_data) {
        if (!$progress_data) {
            return array();
        }

        $formatted = array(
            'session_id' => $progress_data['session_id'],
            'task_type' => $progress_data['task_type'],
            'percentage' => $progress_data['percentage'],
            'status' => $progress_data['status'],
            'current_item' => $progress_data['current_item'],
            'completed' => $progress_data['completed_items'],
            'total' => $progress_data['total_items'],
            'failed' => $progress_data['failed_items'],
            'estimated_remaining' => $this->format_time($progress_data['estimated_remaining']),
            'elapsed_time' => $this->format_time(current_time('timestamp') - $progress_data['start_time']),
            'statistics' => $progress_data['statistics'],
            'has_errors' => !empty($progress_data['errors']),
            'has_warnings' => !empty($progress_data['warnings']),
            'error_count' => count($progress_data['errors']),
            'warning_count' => count($progress_data['warnings'])
        );

        // 添加速度信息
        if (isset($progress_data['statistics']['avg_response_time'])) {
            $formatted['avg_response_time'] = round($progress_data['statistics']['avg_response_time'], 2) . 's';
        }

        return $formatted;
    }

    /**
     * 格式化时间显示
     *
     * @param int $seconds 秒数
     * @return string 格式化的时间
     */
    private function format_time($seconds) {
        if ($seconds < 60) {
            return $seconds . '秒';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . '分钟';
        } else {
            return round($seconds / 3600, 1) . '小时';
        }
    }
}