<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_Admin_Notifications {
    
    private static $instance = null;
    private $ip_location;
    private $device_info;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->ip_location = AAS_IP_Location::instance();
        $this->device_info = AAS_Device_Info::instance();
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // 监听各种异常事件
        add_action('aas_user_auto_banned', array($this, 'notify_user_banned'), 10, 3);
        add_action('aas_high_risk_user_detected', array($this, 'notify_high_risk_user'), 10, 2);
        add_action('aas_suspicious_login_detected', array($this, 'notify_suspicious_login'), 10, 3);
        add_action('aas_simultaneous_login_detected', array($this, 'notify_simultaneous_login'), 10, 3);
        add_action('aas_multiple_locations_detected', array($this, 'notify_multiple_locations'), 10, 3);
        add_action('aas_registration_abuse_detected', array($this, 'notify_registration_abuse'), 10, 2);
    }
    
    /**
     * 检查是否应该发送管理员通知
     */
    private function should_send_notification($trigger_type, $user_id = null) {
        // 检查管理员通知是否启用
        if (!get_option('aas_enable_admin_notifications', true)) {
            return false;
        }
        
        // 检查触发条件是否启用
        $enabled_triggers = get_option('aas_notification_triggers', array('auto_ban', 'high_risk_user', 'suspicious_login'));
        if (!in_array($trigger_type, $enabled_triggers)) {
            return false;
        }
        
        // 检查频率限制
        if ($user_id) {
            $frequency = get_option('aas_notification_frequency', 'hourly');
            if (!$this->check_notification_frequency($trigger_type, $user_id, $frequency)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检查通知频率限制
     */
    private function check_notification_frequency($trigger_type, $user_id, $frequency) {
        if ($frequency === 'immediate') {
            return true;
        }
        
        $transient_key = "aas_notification_{$trigger_type}_{$user_id}";
        $last_sent = get_transient($transient_key);
        
        if ($last_sent) {
            return false; // 已在限制期内发送过
        }
        
        // 设置限制期
        $expiration_times = array(
            'hourly' => HOUR_IN_SECONDS,
            'daily' => DAY_IN_SECONDS,
            'weekly' => WEEK_IN_SECONDS
        );
        
        $expiration = isset($expiration_times[$frequency]) ? $expiration_times[$frequency] : HOUR_IN_SECONDS;
        set_transient($transient_key, time(), $expiration);
        
        return true;
    }
    
    /**
     * 获取管理员邮箱
     */
    private function get_admin_email() {
        $admin_email = get_option('aas_admin_notification_email', '');
        
        if (empty($admin_email)) {
            $admin_email = get_option('admin_email');
        }
        
        return $admin_email;
    }
    
    /**
     * 用户被自动封禁时通知
     */
    public function notify_user_banned($user_id, $reason, $trigger_data = array()) {
        if (!$this->should_send_notification('auto_ban', $user_id)) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '[安全警报] 用户已被自动封禁 - ' . get_bloginfo('name');
        
        $location_info = $this->get_user_location_summary($user_id);
        $device_info = $this->get_user_device_summary($user_id);
        
        $message = $this->format_notification_email(array(
            'title' => '用户自动封禁通知',
            'user_info' => array(
                '用户ID' => $user_id,
                '用户名' => $user->user_login,
                '邮箱' => $user->user_email,
                '显示名' => $user->display_name
            ),
            'incident_info' => array(
                '封禁原因' => $reason,
                '触发时间' => current_time('Y-m-d H:i:s'),
                '当前IP' => $this->ip_location->get_user_ip(),
                '地理位置' => $location_info,
                '设备信息' => $device_info
            ),
            'action_required' => '请登录后台查看详细信息并确认是否需要进一步处理。',
            'additional_data' => $trigger_data
        ));
        
        $this->send_admin_notification($subject, $message, 'auto_ban');
    }
    
    /**
     * 检测到高风险用户时通知
     */
    public function notify_high_risk_user($user_id, $risk_data) {
        if (!$this->should_send_notification('high_risk_user', $user_id)) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '[安全警报] 检测到高风险用户 - ' . get_bloginfo('name');
        
        $location_info = $this->get_user_location_summary($user_id);
        
        $message = $this->format_notification_email(array(
            'title' => '高风险用户检测通知',
            'user_info' => array(
                '用户ID' => $user_id,
                '用户名' => $user->user_login,
                '邮箱' => $user->user_email,
                '风险等级' => $risk_data['classification'] ?? '未知'
            ),
            'incident_info' => array(
                '检测时间' => current_time('Y-m-d H:i:s'),
                '登录地区数' => $risk_data['location_count'] ?? 0,
                '警告次数' => $risk_data['warning_count'] ?? 0,
                '最近登录位置' => $location_info
            ),
            'action_required' => '建议查看该用户的登录记录，必要时可将其加入白名单或进行封禁。'
        ));
        
        $this->send_admin_notification($subject, $message, 'high_risk_user');
    }
    
    /**
     * 检测到可疑登录时通知
     */
    public function notify_suspicious_login($user_id, $current_location, $suspicious_factors) {
        if (!$this->should_send_notification('suspicious_login', $user_id)) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '[安全警报] 检测到可疑登录活动 - ' . get_bloginfo('name');
        
        $message = $this->format_notification_email(array(
            'title' => '可疑登录活动通知',
            'user_info' => array(
                '用户ID' => $user_id,
                '用户名' => $user->user_login,
                '邮箱' => $user->user_email
            ),
            'incident_info' => array(
                '登录时间' => current_time('Y-m-d H:i:s'),
                '登录IP' => $this->ip_location->get_user_ip(),
                '登录位置' => $current_location['display_location'] ?? '未知',
                '可疑因素' => implode(', ', $suspicious_factors)
            ),
            'action_required' => '建议联系用户确认登录活动的合法性，必要时要求更改密码。'
        ));
        
        $this->send_admin_notification($subject, $message, 'suspicious_login');
    }
    
    /**
     * 检测到同时登录时通知
     */
    public function notify_simultaneous_login($user_id, $session_count, $session_details) {
        if (!$this->should_send_notification('simultaneous_login', $user_id)) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '[安全警报] 检测到同时登录 - ' . get_bloginfo('name');
        
        $session_list = array();
        foreach ($session_details as $session) {
            $session_list[] = sprintf(
                'IP: %s, 位置: %s, 设备: %s %s, 时间: %s',
                $session['ip_address'] ?? '未知',
                $session['location'] ?? '未知',
                $session['browser'] ?? '未知',
                $session['os'] ?? '',
                $session['login_time'] ?? '未知'
            );
        }
        
        $message = $this->format_notification_email(array(
            'title' => '同时登录检测通知',
            'user_info' => array(
                '用户ID' => $user_id,
                '用户名' => $user->user_login,
                '邮箱' => $user->user_email
            ),
            'incident_info' => array(
                '检测时间' => current_time('Y-m-d H:i:s'),
                '同时会话数' => $session_count,
                '会话详情' => implode("\n", $session_list)
            ),
            'action_required' => '请确认用户是否确实需要多设备登录，必要时联系用户确认账户安全。'
        ));
        
        $this->send_admin_notification($subject, $message, 'simultaneous_login');
    }
    
    /**
     * 检测到多地登录时通知
     */
    public function notify_multiple_locations($user_id, $locations, $time_span) {
        if (!$this->should_send_notification('multiple_locations', $user_id)) {
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '[安全警报] 检测到多地登录 - ' . get_bloginfo('name');
        
        $location_list = array();
        foreach ($locations as $location) {
            $location_list[] = sprintf(
                '%s (%s) - %s',
                $location['display_location'] ?? '未知位置',
                $location['ip_address'] ?? '未知IP',
                $location['login_time'] ?? '未知时间'
            );
        }
        
        $message = $this->format_notification_email(array(
            'title' => '多地登录检测通知',
            'user_info' => array(
                '用户ID' => $user_id,
                '用户名' => $user->user_login,
                '邮箱' => $user->user_email
            ),
            'incident_info' => array(
                '检测时间' => current_time('Y-m-d H:i:s'),
                '时间跨度' => $time_span,
                '登录地点数' => count($locations),
                '登录详情' => implode("\n", $location_list)
            ),
            'action_required' => '建议确认用户是否在短时间内确实从多个地点登录，必要时要求用户验证身份。'
        ));
        
        $this->send_admin_notification($subject, $message, 'multiple_locations');
    }
    
    /**
     * 检测到注册滥用时通知
     */
    public function notify_registration_abuse($ip_address, $registration_count) {
        if (!$this->should_send_notification('registration_abuse')) {
            return;
        }
        
        $subject = '[安全警报] 检测到IP注册滥用 - ' . get_bloginfo('name');
        
        $location = $this->ip_location->get_location_by_ip($ip_address);
        
        $message = $this->format_notification_email(array(
            'title' => 'IP注册滥用检测通知',
            'incident_info' => array(
                '检测时间' => current_time('Y-m-d H:i:s'),
                'IP地址' => $ip_address,
                '地理位置' => $location['display_location'] ?? '未知',
                '24小时内注册数' => $registration_count,
                '注册限制' => get_option('aas_registration_ip_limit', 3)
            ),
            'action_required' => '建议检查该IP的注册活动是否正常，必要时可将该IP加入黑名单。'
        ));
        
        $this->send_admin_notification($subject, $message, 'registration_abuse');
    }
    
    /**
     * 格式化通知邮件
     */
    private function format_notification_email($data) {
        $message = "==========================================\n";
        $message .= "网站安全监控通知\n";
        $message .= "网站: " . get_bloginfo('name') . " (" . home_url() . ")\n";
        $message .= "时间: " . current_time('Y-m-d H:i:s') . "\n";
        $message .= "==========================================\n\n";
        
        $message .= "【" . $data['title'] . "】\n\n";
        
        if (isset($data['user_info'])) {
            $message .= "用户信息:\n";
            foreach ($data['user_info'] as $key => $value) {
                $message .= "  {$key}: {$value}\n";
            }
            $message .= "\n";
        }
        
        if (isset($data['incident_info'])) {
            $message .= "事件详情:\n";
            foreach ($data['incident_info'] as $key => $value) {
                if (is_array($value)) {
                    $message .= "  {$key}:\n";
                    foreach ($value as $item) {
                        $message .= "    - {$item}\n";
                    }
                } else {
                    $message .= "  {$key}: {$value}\n";
                }
            }
            $message .= "\n";
        }
        
        if (isset($data['action_required'])) {
            $message .= "建议操作:\n";
            $message .= "  " . $data['action_required'] . "\n\n";
        }
        
        if (isset($data['additional_data']) && !empty($data['additional_data'])) {
            $message .= "附加数据:\n";
            $message .= "  " . print_r($data['additional_data'], true) . "\n";
        }
        
        $message .= "==========================================\n";
        $message .= "管理后台链接: " . admin_url('admin.php?page=anti-account-sharing') . "\n";
        $message .= "此邮件由防账号共享插件自动发送\n";
        $message .= "==========================================\n";
        
        return $message;
    }
    
    /**
     * 发送管理员通知
     */
    private function send_admin_notification($subject, $message, $notification_type) {
        $admin_email = $this->get_admin_email();
        
        if (empty($admin_email)) {
            error_log('AAS: Cannot send admin notification - no admin email configured');
            return false;
        }
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        $result = wp_mail($admin_email, $subject, $message, $headers);
        
        // 记录通知发送日志
        if ($result) {
            error_log("AAS: Admin notification sent - Type: {$notification_type}, Email: {$admin_email}");
        } else {
            error_log("AAS: Failed to send admin notification - Type: {$notification_type}, Email: {$admin_email}");
        }
        
        return $result;
    }
    
    /**
     * 获取用户位置摘要
     */
    private function get_user_location_summary($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $recent_locations = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT country, region, city, login_time 
             FROM $table_name 
             WHERE user_id = %d 
             ORDER BY login_time DESC 
             LIMIT 5",
            $user_id
        ));
        
        $locations = array();
        foreach ($recent_locations as $location) {
            $location_parts = array_filter(array($location->country, $location->region, $location->city));
            if (!empty($location_parts)) {
                $locations[] = implode(', ', $location_parts) . ' (' . $location->login_time . ')';
            }
        }
        
        return !empty($locations) ? implode('; ', $locations) : '无记录';
    }
    
    /**
     * 获取用户设备摘要
     */
    private function get_user_device_summary($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $recent_devices = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT browser, os, login_time 
             FROM $table_name 
             WHERE user_id = %d 
             ORDER BY login_time DESC 
             LIMIT 3",
            $user_id
        ));
        
        $devices = array();
        foreach ($recent_devices as $device) {
            $device_parts = array_filter(array($device->browser, $device->os));
            if (!empty($device_parts)) {
                $devices[] = implode(' / ', $device_parts);
            }
        }
        
        return !empty($devices) ? implode('; ', $devices) : '无记录';
    }
    
}