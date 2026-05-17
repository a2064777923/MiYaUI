<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_Login_Monitor {
    
    private $ip_location;
    private $device_info;
    private $user_classification;
    
    public function __construct() {
        $this->ip_location = AAS_IP_Location::instance();
        $this->device_info = AAS_Device_Info::instance();
        $this->user_classification = AAS_User_Classification::instance();
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_login', array($this, 'on_user_login'), 10, 2);
        add_action('wp_logout', array($this, 'on_user_logout'));
        add_action('wp_authenticate', array($this, 'check_access_restrictions'), 10, 2);
        add_filter('authenticate', array($this, 'authenticate_user'), 30, 3);
        add_action('init', array($this, 'check_session_validity'));
        add_action('user_register', array($this, 'on_user_register'));
        add_action('login_message', array($this, 'display_ban_message'));
        
        // 添加额外的登录检测钩子，覆盖更多登录方式
        add_action('wp_loaded', array($this, 'check_login_status'), 5);
        add_action('set_logged_in_cookie', array($this, 'on_set_logged_in_cookie'), 10, 5);
        add_action('clear_auth_cookie', array($this, 'on_clear_auth_cookie'));
    }
    
    public function on_user_login($user_login, $user) {
        $user_id = $user->ID;
        $ip_address = $this->ip_location->get_user_ip();
        $location = $this->ip_location->get_location_by_ip($ip_address);
        $device_info = $this->device_info->get_device_info();
        
        if ($this->is_user_whitelisted($user_id)) {
            $this->log_login($user_id, $ip_address, $location, $device_info, 'whitelisted_user');
            return;
        }
        
        $this->log_login($user_id, $ip_address, $location, $device_info);
        
        $this->user_classification->update_user_classification($user_id);
        
        if (get_option('aas_prevent_simultaneous_login', true)) {
            $this->handle_simultaneous_login($user_id);
        } elseif (get_option('aas_enable_single_session', true)) {
            $this->enforce_single_session($user_id);
        }
        
        $this->check_auto_warning($user_id);
        $this->check_auto_ban($user_id);
    }
    
    public function on_user_logout() {
        $user_id = get_current_user_id();
        if ($user_id) {
            $this->update_logout_time($user_id);
        }
    }
    
    public function check_access_restrictions($username, $password) {
        if (empty($username) || empty($password)) {
            return;
        }
        
        $ip_address = $this->ip_location->get_user_ip();
        $location = $this->ip_location->get_location_by_ip($ip_address);
        
        if ($this->ip_location->is_ip_blocked($ip_address)) {
            wp_die(__('Your IP address has been blocked.', 'anti-account-sharing'));
        }
        
        if (get_option('aas_block_foreign_ip', false) && !$location['is_domestic']) {
            wp_die(__('Foreign IP addresses are not allowed.', 'anti-account-sharing'));
        }
        
        if (get_option('aas_block_domestic_ip', false) && $location['is_domestic']) {
            wp_die(__('Domestic IP addresses are not allowed.', 'anti-account-sharing'));
        }
        
        $this->check_registration_ip_limit($ip_address);
    }
    
    public function authenticate_user($user, $username, $password) {
        if (is_wp_error($user) || empty($username)) {
            return $user;
        }
        
        $user_obj = get_user_by('login', $username);
        if (!$user_obj) {
            return $user;
        }
        
        // 检查同时登录限制
        if (get_option('aas_prevent_simultaneous_login', true)) {
            $simultaneous_check = $this->check_simultaneous_login_restriction($user_obj->ID);
            if (is_wp_error($simultaneous_check)) {
                return $simultaneous_check;
            }
        }
        
        // 检查用户是否被封禁
        $ban_info = $this->get_user_ban_info($user_obj->ID);
        
        if ($ban_info && $ban_info->is_banned) {
            // 检查封禁是否已过期
            if (!empty($ban_info->ban_expires)) {
                $ban_expiry = strtotime($ban_info->ban_expires);
                if ($ban_expiry && $ban_expiry < time()) {
                    // 封禁已过期，自动解封
                    $this->unban_user_auto($user_obj->ID);
                    return $user; // 允许登录
                }
            }
            
            // 封禁仍然有效，拒绝登录
            $ban_reason = !empty($ban_info->ban_reason) ? $ban_info->ban_reason : '违反网站使用规定';
            
            // 清理封禁原因，移除已有的期限信息
            $ban_reason = preg_replace('/\s*\(期限:\s*\d+\)/', '', $ban_reason);
            $ban_reason = trim($ban_reason);
            
            // 计算封禁天数
            $ban_duration_text = '';
            if (!empty($ban_info->ban_expires)) {
                $ban_start = !empty($ban_info->ban_time) ? strtotime($ban_info->ban_time) : time();
                $ban_end = strtotime($ban_info->ban_expires);
                $ban_days = ceil(($ban_end - $ban_start) / (24 * 60 * 60));
                
                if ($ban_days > 0) {
                    $ban_duration_text = $ban_days . '天';
                } else {
                    $ban_duration_text = '不足1天';
                }
                
                $expiry_time = date('Y年m月d日 H:i', strtotime($ban_info->ban_expires));
                $ban_duration_text .= "（至{$expiry_time}）";
            } else {
                // 检查封禁原因中是否包含天数信息
                if (preg_match('/期限:\s*(\d+)/', $ban_info->ban_reason, $matches)) {
                    $ban_duration_text = $matches[1] . '天';
                } else {
                    $ban_duration_text = '永久';
                }
            }
            
            $error_message = sprintf(
                "很抱歉，由于您的账号因为「%s」被封禁%s。\n\n如有疑问，请联系网站管理员。",
                $ban_reason,
                $ban_duration_text
            );
            
            // 记录封禁用户的登录尝试
            $this->log_banned_user_attempt($user_obj->ID);
            
            return new WP_Error('user_banned', $error_message);
        }
        
        return $user;
    }
    
    public function check_session_validity() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $current_session = wp_get_session_token();
        
        // 检查用户是否被封禁
        $ban_info = $this->get_user_ban_info($user_id);
        
        if ($ban_info && $ban_info->is_banned) {
            // 检查封禁是否已过期
            if (!empty($ban_info->ban_expires)) {
                $ban_expiry = strtotime($ban_info->ban_expires);
                if ($ban_expiry && $ban_expiry < time()) {
                    // 封禁已过期，自动解封
                    $this->unban_user_auto($user_id);
                    return; // 继续保持登录状态
                }
            }
            
            // 用户被封禁，强制登出
            wp_logout();
            wp_redirect(wp_login_url('?banned=1'));
            exit;
        }
        
        // 检查会话有效性
        if (!$this->is_session_valid($user_id, $current_session)) {
            wp_logout();
            wp_redirect(wp_login_url());
            exit;
        }
    }
    
    public function on_user_register($user_id) {
        $ip_address = $this->ip_location->get_user_ip();
        $this->increment_registration_count($ip_address);
        
        // 检查注册滥用
        $registration_count = $this->get_registration_count($ip_address);
        $limit = get_option('aas_registration_ip_limit', 3);
        
        if ($limit > 0 && $registration_count >= $limit) {
            // 触发注册滥用管理员通知
            do_action('aas_registration_abuse_detected', $ip_address, $registration_count);
        }
    }
    
    private function log_login($user_id, $ip_address, $location, $device_info, $status = 'active') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $session_token = wp_get_session_token();
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'ip_address' => $ip_address,
                'country' => $location['country'],
                'region' => $location['region'],
                'city' => $location['city'],
                'device_info' => json_encode($device_info),
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'login_time' => current_time('mysql'),
                'session_token' => $session_token,
                'status' => $status
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    private function update_logout_time($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $session_token = wp_get_session_token();
        
        $wpdb->update(
            $table_name,
            array('status' => 'logged_out'),
            array(
                'user_id' => $user_id,
                'session_token' => $session_token,
                'status' => 'active'
            ),
            array('%s'),
            array('%d', '%s', '%s')
        );
    }
    
    private function enforce_single_session($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $current_session = wp_get_session_token();
        
        $wpdb->update(
            $table_name,
            array('status' => 'forced_logout'),
            array(
                'user_id' => $user_id,
                'status' => 'active'
            ),
            array('%s'),
            array('%d', '%s')
        );
        
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $all_sessions = $sessions->get_all();
        
        foreach ($all_sessions as $token => $session) {
            if ($token !== $current_session) {
                $sessions->destroy($token);
            }
        }
    }
    
    private function check_auto_warning($user_id) {
        if (!get_option('aas_enable_auto_warning', true)) {
            return;
        }
        
        $classification = $this->user_classification->get_user_classification($user_id);
        if (!$classification) {
            return;
        }
        
        $warning_threshold = get_option('aas_warning_threshold', 3);
        
        if ($classification->location_count >= $warning_threshold && $classification->warning_count == 0) {
            $this->send_warning($user_id);
            $this->increment_warning_count($user_id);
        }
    }
    
    private function check_auto_ban($user_id) {
        if (!get_option('aas_enable_auto_ban', true)) {
            return;
        }
        
        $classification = $this->user_classification->get_user_classification($user_id);
        if (!$classification) {
            return;
        }
        
        $ban_threshold = get_option('aas_ban_threshold', 5);
        
        if ($classification->location_count >= $ban_threshold && !$classification->is_banned) {
            $this->ban_user($user_id, 'Auto ban: Exceeded location threshold');
        }
    }
    
    private function send_warning($user_id) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = __('Account Security Warning', 'anti-account-sharing');
        $message = sprintf(
            __('Dear %s,\n\nWe have detected login activity from multiple locations for your account. If this was not you, please change your password immediately.\n\nThank you.', 'anti-account-sharing'),
            $user->display_name
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    private function ban_user($user_id, $reason) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $wpdb->update(
            $table_name,
            array(
                'is_banned' => 1,
                'ban_reason' => $reason,
                'ban_time' => current_time('mysql')
            ),
            array('user_id' => $user_id),
            array('%d', '%s', '%s'),
            array('%d')
        );
        
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $sessions->destroy_all();
        
        // 触发管理员通知
        do_action('aas_user_auto_banned', $user_id, $reason, array(
            'trigger_type' => 'auto_threshold',
            'classification' => $this->user_classification->get_user_classification($user_id)
        ));
    }
    
    private function increment_warning_count($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET warning_count = warning_count + 1 WHERE user_id = %d",
            $user_id
        ));
    }
    
    private function is_user_whitelisted($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return !empty($result);
    }
    
    private function get_user_ban_info($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_banned, ban_reason, ban_time, ban_expires FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return $result;
    }
    
    private function is_user_banned($user_id) {
        $ban_info = $this->get_user_ban_info($user_id);
        return !empty($ban_info) && $ban_info->is_banned == 1;
    }
    
    private function get_ban_reason($user_id) {
        $ban_info = $this->get_user_ban_info($user_id);
        return !empty($ban_info) && !empty($ban_info->ban_reason) ? $ban_info->ban_reason : __('No reason provided', 'anti-account-sharing');
    }
    
    private function unban_user_auto($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $wpdb->update(
            $table_name,
            array(
                'is_banned' => 0,
                'ban_reason' => '',
                'ban_time' => null,
                'ban_expires' => null
            ),
            array('user_id' => $user_id),
            array('%d', '%s', '%s', '%s'),
            array('%d')
        );
        
        // 清除缓存
        if (class_exists('AAS_Cache_Manager')) {
            $cache_manager = AAS_Cache_Manager::instance();
            $cache_manager->invalidate_user_cache($user_id);
        }
    }
    
    private function log_banned_user_attempt($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $ip_address = $this->ip_location->get_user_ip();
        $location = $this->ip_location->get_location_by_ip($ip_address);
        $device_info = $this->device_info->get_device_info();
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'ip_address' => $ip_address,
                'country' => $location['country'],
                'region' => $location['region'],
                'city' => $location['city'],
                'device_info' => json_encode($device_info),
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'login_time' => current_time('mysql'),
                'session_token' => '',
                'status' => 'banned_attempt'
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    private function is_session_valid($user_id, $session_token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND session_token = %s AND status = 'active'",
            $user_id,
            $session_token
        ));
        
        return !empty($result);
    }
    
    private function check_registration_ip_limit($ip_address) {
        $limit = get_option('aas_registration_ip_limit', 3);
        if ($limit <= 0) {
            return;
        }
        
        $count = $this->get_registration_count($ip_address);
        if ($count >= $limit) {
            wp_die(__('Registration limit exceeded for this IP address.', 'anti-account-sharing'));
        }
    }
    
    private function get_registration_count($ip_address) {
        return get_transient('aas_reg_count_' . md5($ip_address)) ?: 0;
    }
    
    private function increment_registration_count($ip_address) {
        $key = 'aas_reg_count_' . md5($ip_address);
        $count = get_transient($key) ?: 0;
        set_transient($key, $count + 1, 24 * HOUR_IN_SECONDS);
    }
    
    public function display_ban_message($message) {
        if (isset($_GET['banned']) && $_GET['banned'] == '1') {
            $message .= '<div id="login_error" style="background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 16px 0; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #d32f2f;">账户已被封禁</h4>
                <p style="margin: 0; color: #666;">很抱歉，您的账户因违反网站使用规定已被封禁。</p>
                <p style="margin: 8px 0 0 0; color: #666;">如有疑问，请联系网站管理员申诉。</p>
            </div>';
        } elseif (isset($_GET['simultaneous_login']) && $_GET['simultaneous_login'] == '1') {
            $message .= '<div id="login_error" style="background: #fff8e1; border-left: 4px solid #ff9800; padding: 15px; margin: 16px 0; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #f57c00;">同时登录被阻止</h4>
                <p style="margin: 0; color: #666;">检测到您的账户已在其他设备上登录，为了账户安全，不允许同时登录。</p>
                <p style="margin: 8px 0 0 0; color: #666;">如需在此设备登录，请先退出其他设备上的登录。</p>
            </div>';
        }
        return $message;
    }
    
    /**
     * 处理同时登录逻辑
     */
    private function handle_simultaneous_login($user_id) {
        $method = get_option('aas_simultaneous_login_method', 'force_logout');
        
        switch ($method) {
            case 'force_logout':
                $this->force_logout_other_sessions($user_id);
                break;
            case 'block_new':
                // 这种情况在authenticate阶段已经处理
                break;
            case 'warn_only':
                $this->log_simultaneous_login_warning($user_id);
                break;
        }
    }
    
    /**
     * 检查同时登录限制
     */
    private function check_simultaneous_login_restriction($user_id) {
        $method = get_option('aas_simultaneous_login_method', 'force_logout');
        
        if ($method === 'block_new') {
            $active_sessions = $this->get_active_sessions($user_id);
            
            if (!empty($active_sessions)) {
                return new WP_Error(
                    'simultaneous_login_blocked',
                    '检测到您的账户已在其他设备上登录。为了账户安全，不允许同时登录。请先退出其他设备上的登录。'
                );
            }
        }
        
        return true;
    }
    
    /**
     * 强制退出其他会话
     */
    private function force_logout_other_sessions($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $current_session = wp_get_session_token();
        
        // 更新数据库中的其他活跃会话状态
        $wpdb->update(
            $table_name,
            array('status' => 'forced_logout_simultaneous'),
            array(
                'user_id' => $user_id,
                'status' => 'active'
            ),
            array('%s'),
            array('%d', '%s')
        );
        
        // 销毁WordPress会话
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $all_sessions = $sessions->get_all();
        
        foreach ($all_sessions as $token => $session) {
            if ($token !== $current_session) {
                $sessions->destroy($token);
            }
        }
        
        // 记录操作日志
        $this->log_simultaneous_login_action($user_id, 'forced_logout', count($all_sessions) - 1);
    }
    
    /**
     * 获取用户的活跃会话
     */
    private function get_active_sessions($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        
        $active_sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT session_token, ip_address, login_time, browser, os 
             FROM $table_name 
             WHERE user_id = %d AND status = 'active' 
             ORDER BY login_time DESC",
            $user_id
        ));
        
        // 验证会话是否仍然有效
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $valid_sessions = $sessions->get_all();
        
        $result = array();
        foreach ($active_sessions as $session) {
            if (isset($valid_sessions[$session->session_token])) {
                $result[] = $session;
            } else {
                // 清理无效会话记录
                $wpdb->update(
                    $table_name,
                    array('status' => 'expired'),
                    array(
                        'user_id' => $user_id,
                        'session_token' => $session->session_token
                    ),
                    array('%s'),
                    array('%d', '%s')
                );
            }
        }
        
        return $result;
    }
    
    /**
     * 记录同时登录警告
     */
    private function log_simultaneous_login_warning($user_id) {
        global $wpdb;
        
        $active_sessions = $this->get_active_sessions($user_id);
        $session_count = count($active_sessions);
        
        if ($session_count > 1) {
            $events_table = $wpdb->prefix . 'aas_security_events';
            
            $event_data = array(
                'session_count' => $session_count,
                'sessions' => array_map(function($session) {
                    return array(
                        'ip_address' => $session->ip_address,
                        'login_time' => $session->login_time,
                        'browser' => $session->browser,
                        'os' => $session->os
                    );
                }, $active_sessions)
            );
            
            $wpdb->insert(
                $events_table,
                array(
                    'user_id' => $user_id,
                    'event_type' => 'simultaneous_login_warning',
                    'event_data' => json_encode($event_data),
                    'severity' => 'medium',
                    'ip_address' => $this->ip_location->get_user_ip(),
                    'created_time' => current_time('mysql'),
                    'processed' => 0
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%d')
            );
            
            // 发送警告邮件
            $this->send_simultaneous_login_warning($user_id, $session_count);
            
            // 触发管理员通知
            do_action('aas_simultaneous_login_detected', $user_id, $session_count, $event_data['sessions']);
        }
    }
    
    /**
     * 记录同时登录处理操作
     */
    private function log_simultaneous_login_action($user_id, $action, $affected_sessions) {
        error_log(sprintf(
            'AAS: Simultaneous login %s for user %d. Affected sessions: %d',
            $action,
            $user_id,
            $affected_sessions
        ));
        
        // 记录到安全事件表
        global $wpdb;
        $events_table = $wpdb->prefix . 'aas_security_events';
        
        $wpdb->insert(
            $events_table,
            array(
                'user_id' => $user_id,
                'event_type' => 'simultaneous_login_' . $action,
                'event_data' => json_encode(array(
                    'affected_sessions' => $affected_sessions,
                    'current_ip' => $this->ip_location->get_user_ip()
                )),
                'severity' => 'low',
                'ip_address' => $this->ip_location->get_user_ip(),
                'created_time' => current_time('mysql'),
                'processed' => 0
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d')
        );
    }
    
    /**
     * 发送同时登录警告邮件
     */
    private function send_simultaneous_login_warning($user_id, $session_count) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = '账户安全警告：检测到同时登录';
        $message = sprintf(
            "亲爱的 %s，\n\n" .
            "我们检测到您的账户目前在 %d 个设备/浏览器上同时登录。\n\n" .
            "如果这些登录都是您本人操作，您可以忽略此邮件。\n" .
            "如果您怀疑账户被他人使用，请立即：\n" .
            "1. 修改密码\n" .
            "2. 登录账户检查登录记录\n" .
            "3. 联系网站管理员\n\n" .
            "此邮件由系统自动发送，请勿回复。\n\n" .
            "网站安全团队",
            $user->display_name,
            $session_count
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    /**
     * 检查登录状态变化 - 用于捕获快捷登录等可能被遗漏的登录方式
     */
    public function check_login_status() {
        static $last_user_id = null;
        static $last_session_token = null;
        
        $current_user_id = get_current_user_id();
        $current_session_token = wp_get_session_token();
        
        // 检测新的登录会话
        if ($current_user_id && $current_session_token) {
            // 如果用户或会话令牌发生变化，说明可能有新的登录
            if ($last_user_id !== $current_user_id || $last_session_token !== $current_session_token) {
                // 检查这个会话是否已经记录过
                if (!$this->is_session_logged($current_user_id, $current_session_token)) {
                    // 记录这个可能被遗漏的登录
                    $this->log_detected_login($current_user_id, $current_session_token);
                }
            }
        }
        
        // 更新静态变量
        $last_user_id = $current_user_id;
        $last_session_token = $current_session_token;
    }
    
    /**
     * 处理 set_logged_in_cookie 钩子 - 捕获cookie设置事件
     */
    public function on_set_logged_in_cookie($logged_in_cookie, $expire, $expiration, $user_id, $scheme) {
        // 获取会话令牌
        $session_token = wp_get_session_token();
        
        // 如果这个会话还没有被记录，记录它
        if (!$this->is_session_logged($user_id, $session_token)) {
            $this->log_detected_login($user_id, $session_token, 'cookie_set');
        }
    }
    
    /**
     * 处理 clear_auth_cookie 钩子 - 处理登出事件
     */
    public function on_clear_auth_cookie() {
        $user_id = get_current_user_id();
        $session_token = wp_get_session_token();
        
        if ($user_id && $session_token) {
            $this->update_logout_time($user_id);
        }
    }
    
    /**
     * 检查会话是否已经被记录
     */
    private function is_session_logged($user_id, $session_token) {
        global $wpdb;
        
        if (empty($session_token)) {
            return false;
        }
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND session_token = %s",
            $user_id,
            $session_token
        ));
        
        return !empty($result);
    }
    
    /**
     * 记录检测到的登录（用于快捷登录等情况）
     */
    private function log_detected_login($user_id, $session_token, $detection_method = 'status_check') {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $ip_address = $this->ip_location->get_user_ip();
        $location = $this->ip_location->get_location_by_ip($ip_address);
        $device_info = $this->device_info->get_device_info();
        
        // 检查是否为白名单用户
        $status = $this->is_user_whitelisted($user_id) ? 'whitelisted_user' : 'active';
        
        // 记录登录
        global $wpdb;
        $table_name = $wpdb->prefix . 'aas_login_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'ip_address' => $ip_address,
                'country' => $location['country'],
                'region' => $location['region'],
                'city' => $location['city'],
                'device_info' => json_encode($device_info),
                'browser' => $device_info['browser'],
                'os' => $device_info['os'],
                'login_time' => current_time('mysql'),
                'session_token' => $session_token,
                'status' => $status . '_detected'
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        // 记录检测日志
        error_log("AAS: Detected login via {$detection_method} for user {$user_id} (session: {$session_token})");
        
        // 如果不是白名单用户，执行正常的登录后处理
        if ($status === 'active') {
            $this->user_classification->update_user_classification($user_id);
            
            if (get_option('aas_prevent_simultaneous_login', true)) {
                $this->handle_simultaneous_login($user_id);
            } elseif (get_option('aas_enable_single_session', true)) {
                $this->enforce_single_session($user_id);
            }
            
            $this->check_auto_warning($user_id);
            $this->check_auto_ban($user_id);
        }
    }
}