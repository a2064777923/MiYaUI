<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_Access_Control {
    
    private $ip_location;
    private $user_classification;
    
    public function __construct() {
        $this->ip_location = AAS_IP_Location::instance();
        $this->user_classification = AAS_User_Classification::instance();
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'check_ip_restrictions'), 1);
        add_action('wp_login', array($this, 'enforce_session_limits'), 10, 2);
        add_action('wp_head', array($this, 'add_device_tracking_script'));
        add_action('wp_footer', array($this, 'add_session_monitoring_script'));
        add_filter('authenticate', array($this, 'check_user_restrictions'), 25, 3);
        add_action('wp_ajax_aas_check_session', array($this, 'ajax_check_session'));
        add_action('wp_ajax_nopriv_aas_check_session', array($this, 'ajax_check_session'));
    }
    
    public function check_ip_restrictions() {
        if (wp_doing_ajax() || wp_doing_cron() || is_admin()) {
            return;
        }
        
        $current_ip = $this->ip_location->get_user_ip();
        
        if ($this->ip_location->is_ip_blocked($current_ip)) {
            $this->block_access('IP address blocked');
            return;
        }
        
        $location = $this->ip_location->get_location_by_ip($current_ip);
        
        if (get_option('aas_block_foreign_ip', false) && !$location['is_domestic']) {
            $this->block_access('Foreign IP addresses are not allowed');
            return;
        }
        
        if (get_option('aas_block_domestic_ip', false) && $location['is_domestic']) {
            $this->block_access('Domestic IP addresses are not allowed');
            return;
        }
        
        if (is_user_logged_in()) {
            $this->check_session_validity();
        }
    }
    
    public function enforce_session_limits($user_login, $user) {
        if (!get_option('aas_enable_single_session', true)) {
            return;
        }
        
        if ($this->is_user_whitelisted($user->ID)) {
            return;
        }
        
        $this->force_single_session($user->ID);
    }
    
    public function check_user_restrictions($user, $username, $password) {
        if (is_wp_error($user) || empty($username)) {
            return $user;
        }
        
        $user_obj = get_user_by('login', $username);
        if (!$user_obj) {
            return $user;
        }
        
        if ($this->is_user_banned($user_obj->ID)) {
            $ban_info = $this->get_user_ban_info($user_obj->ID);
            $ban_reason = $ban_info ? $ban_info->ban_reason : '违反网站使用规定';
            
            // 清理封禁原因，移除已有的期限信息
            $ban_reason = preg_replace('/\s*\(期限:\s*\d+\)/', '', $ban_reason);
            $ban_reason = trim($ban_reason);
            
            // 计算封禁天数
            $ban_duration_text = '';
            if ($ban_info && !empty($ban_info->ban_expires)) {
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
                if ($ban_info && preg_match('/期限:\s*(\d+)/', $ban_info->ban_reason, $matches)) {
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
            
            return new WP_Error('user_banned', $error_message);
        }
        
        if ($this->is_location_change_suspicious($user_obj->ID)) {
            $this->log_suspicious_activity($user_obj->ID, 'Suspicious location change');
            
            if (get_option('aas_enable_auto_warning', true)) {
                $this->send_security_alert($user_obj);
            }
            
            // 触发管理员通知
            $current_location = $this->ip_location->get_location_by_ip($this->ip_location->get_user_ip());
            do_action('aas_suspicious_login_detected', $user_obj->ID, $current_location, array('location_change'));
        }
        
        return $user;
    }
    
    private function check_session_validity() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }
        
        $current_session = wp_get_session_token();
        if (!$this->is_session_valid($user_id, $current_session)) {
            wp_logout();
            wp_redirect(wp_login_url() . '?session_expired=1');
            exit;
        }
        
        if ($this->is_concurrent_session_detected($user_id)) {
            if (get_option('aas_enable_single_session', true)) {
                wp_logout();
                wp_redirect(wp_login_url() . '?concurrent_session=1');
                exit;
            }
        }
    }
    
    private function force_single_session($user_id) {
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $all_sessions = $sessions->get_all();
        $current_session = wp_get_session_token();
        
        foreach ($all_sessions as $token => $session) {
            if ($token !== $current_session) {
                $sessions->destroy($token);
            }
        }
        
        $this->update_session_status($user_id, 'single_session_enforced');
    }
    
    private function is_location_change_suspicious($user_id) {
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
        
        if (count($recent_locations) < 2) {
            return false;
        }
        
        $location_changes = 0;
        $last_location = null;
        
        foreach ($recent_locations as $location) {
            $current_location = $location->country . '-' . $location->region . '-' . $location->city;
            
            if ($last_location && $last_location !== $current_location) {
                $location_changes++;
            }
            
            $last_location = $current_location;
        }
        
        return $location_changes >= 2;
    }
    
    private function is_concurrent_session_detected($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $time_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_token) 
             FROM $table_name 
             WHERE user_id = %d AND status = 'active' AND login_time >= %s",
            $user_id,
            $time_threshold
        ));
        
        return intval($active_sessions) > 1;
    }
    
    private function is_session_valid($user_id, $session_token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name 
             WHERE user_id = %d AND session_token = %s AND status = 'active'",
            $user_id,
            $session_token
        ));
        
        return !empty($result);
    }
    
    private function update_session_status($user_id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $current_session = wp_get_session_token();
        
        $wpdb->update(
            $table_name,
            array('status' => $status),
            array(
                'user_id' => $user_id,
                'session_token' => $current_session
            ),
            array('%s'),
            array('%d', '%s')
        );
    }
    
    private function log_suspicious_activity($user_id, $activity_type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_security_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'activity_type' => $activity_type,
                'ip_address' => $this->ip_location->get_user_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'timestamp' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    
    private function send_security_alert($user) {
        $subject = __('Security Alert - Unusual Login Activity', 'anti-account-sharing');
        $message = sprintf(
            __('Dear %s,\n\nWe detected unusual login activity on your account from a new location or device.\n\nIf this was you, you can ignore this message. If not, please change your password immediately.\n\nLogin Details:\nIP: %s\nTime: %s\n\nThank you.', 'anti-account-sharing'),
            $user->display_name,
            $this->ip_location->get_user_ip(),
            current_time('Y-m-d H:i:s')
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    private function block_access($reason) {
        status_header(403);
        wp_die(
            sprintf(__('Access Denied: %s', 'anti-account-sharing'), $reason),
            __('Access Denied', 'anti-account-sharing'),
            array('response' => 403)
        );
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
    
    private function is_user_banned($user_id) {
        $classification = $this->user_classification->get_user_classification($user_id);
        return $classification && $classification->is_banned == 1;
    }
    
    private function get_ban_reason($user_id) {
        $classification = $this->user_classification->get_user_classification($user_id);
        return $classification ? $classification->ban_reason : __('No reason provided', 'anti-account-sharing');
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
    
    public function add_device_tracking_script() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $device_info = AAS_Device_Info::instance();
        $device_info->add_device_info_scripts();
    }
    
    public function add_session_monitoring_script() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $check_interval = apply_filters('aas_session_check_interval', 60000); // 1 minute
        
        ?>
        <script>
        (function() {
            var checkInterval = <?php echo intval($check_interval); ?>;
            
            function checkSessionStatus() {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=aas_check_session&nonce=<?php echo wp_create_nonce('aas_session_check'); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && data.data.redirect) {
                        window.location.href = data.data.redirect;
                    }
                })
                .catch(error => {
                    console.log('Session check failed:', error);
                });
            }
            
            setInterval(checkSessionStatus, checkInterval);
        })();
        </script>
        <?php
    }
    
    public function ajax_check_session() {
        check_ajax_referer('aas_session_check', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('Not logged in', 'anti-account-sharing'),
                'redirect' => wp_login_url()
            ));
        }
        
        $user_id = get_current_user_id();
        $current_session = wp_get_session_token();
        
        if (!$this->is_session_valid($user_id, $current_session)) {
            wp_logout();
            wp_send_json_error(array(
                'message' => __('Session expired', 'anti-account-sharing'),
                'redirect' => wp_login_url() . '?session_expired=1'
            ));
        }
        
        if ($this->is_concurrent_session_detected($user_id) && get_option('aas_enable_single_session', true)) {
            wp_logout();
            wp_send_json_error(array(
                'message' => __('Concurrent session detected', 'anti-account-sharing'),
                'redirect' => wp_login_url() . '?concurrent_session=1'
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Session valid', 'anti-account-sharing')
        ));
    }
    
    public function get_access_statistics() {
        global $wpdb;
        
        $table_login = $wpdb->prefix . 'aas_login_logs';
        $table_classification = $wpdb->prefix . 'aas_user_classification';
        
        $stats = array();
        
        // Total logins today
        $stats['logins_today'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_login WHERE DATE(login_time) = CURDATE()"
        );
        
        // Blocked IPs count
        $table_restrictions = $wpdb->prefix . 'aas_ip_restrictions';
        $stats['blocked_ips'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_restrictions WHERE restriction_type = 'blocked'"
        );
        
        // Banned users count
        $stats['banned_users'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_classification WHERE is_banned = 1"
        );
        
        // High risk users count
        $stats['high_risk_users'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_classification WHERE classification = 'high_risk'"
        );
        
        return $stats;
    }
}