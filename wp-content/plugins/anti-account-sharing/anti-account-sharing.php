<?php
/**
 * Plugin Name: Anti Account Sharing
 * Plugin URI: https://mewcg.com/
 * Description: WordPress插件，防止账号共享，通过检测登录IP位置信息、设备信息等进行用户分类管理，支持自动警告、自动封号、IP限制等功能。
 * Version: 1.0.0
 * Author: 喵CG
 * License: GPL2
 * Text Domain: anti-account-sharing
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AAS_PLUGIN_FILE', __FILE__);
define('AAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AAS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AAS_VERSION', '1.0.0');

class AntiAccountSharing {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    public function init() {
        $this->includes();
        $this->init_database();
        $this->init_hooks_after_includes();
    }
    
    public function includes() {
        require_once AAS_PLUGIN_PATH . 'inc/codestar-framework/codestar-framework.php';
        require_once AAS_PLUGIN_PATH . 'inc/functions.php';
        require_once AAS_PLUGIN_PATH . 'inc/options/admin-options.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-ip-location.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-login-monitor.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-user-classification.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-device-info.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-access-control.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-cache-manager.php';
        require_once AAS_PLUGIN_PATH . 'inc/class-admin-notifications.php';
    }
    
    public function init_database() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_login_logs = $wpdb->prefix . 'aas_login_logs';
        $sql_login_logs = "CREATE TABLE $table_login_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            ip_address varchar(45) NOT NULL,
            country varchar(100) DEFAULT '',
            region varchar(100) DEFAULT '',
            city varchar(100) DEFAULT '',
            device_info text,
            browser varchar(100) DEFAULT '',
            os varchar(100) DEFAULT '',
            login_time datetime DEFAULT CURRENT_TIMESTAMP,
            session_token varchar(255) DEFAULT '',
            status varchar(20) DEFAULT 'active',
            risk_score tinyint(3) DEFAULT 0,
            location_hash varchar(64) DEFAULT '',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY ip_address (ip_address),
            KEY login_time (login_time),
            KEY location_hash (location_hash),
            KEY user_status (user_id, status),
            KEY risk_analysis (user_id, login_time, risk_score)
        ) $charset_collate;";
        
        $table_user_classification = $wpdb->prefix . 'aas_user_classification';
        $sql_user_classification = "CREATE TABLE $table_user_classification (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            classification varchar(50) NOT NULL,
            location_count int(11) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            warning_count int(11) DEFAULT 0,
            is_banned tinyint(1) DEFAULT 0,
            ban_reason text,
            ban_time datetime NULL,
            ban_expires datetime NULL,
            trust_score int(11) DEFAULT 50,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY classification_status (classification, is_banned),
            KEY ban_expires (ban_expires)
        ) $charset_collate;";
        
        $table_ip_restrictions = $wpdb->prefix . 'aas_ip_restrictions';
        $sql_ip_restrictions = "CREATE TABLE $table_ip_restrictions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            restriction_type varchar(20) NOT NULL,
            reason text,
            created_time datetime DEFAULT CURRENT_TIMESTAMP,
            expires_time datetime NULL,
            attempts_count int(11) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY ip_address (ip_address),
            KEY restriction_type (restriction_type),
            KEY expires_time (expires_time)
        ) $charset_collate;";
        
        $table_whitelist = $wpdb->prefix . 'aas_whitelist';
        $sql_whitelist = "CREATE TABLE $table_whitelist (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            added_by bigint(20) NOT NULL,
            reason text,
            created_time datetime DEFAULT CURRENT_TIMESTAMP,
            expires_time datetime NULL,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY expires_time (expires_time),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        $table_security_events = $wpdb->prefix . 'aas_security_events';
        $sql_security_events = "CREATE TABLE $table_security_events (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data text,
            severity varchar(20) DEFAULT 'medium',
            ip_address varchar(45) DEFAULT '',
            created_time datetime DEFAULT CURRENT_TIMESTAMP,
            processed tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY severity (severity),
            KEY created_time (created_time),
            KEY processed (processed)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_login_logs);
        dbDelta($sql_user_classification);
        dbDelta($sql_ip_restrictions);
        dbDelta($sql_whitelist);
        dbDelta($sql_security_events);
        
        // 添加定时清理任务
        if (!wp_next_scheduled('aas_cleanup_old_logs')) {
            wp_schedule_event(time(), 'daily', 'aas_cleanup_old_logs');
        }
    }
    
    private function init_hooks_after_includes() {
        if (class_exists('AAS_Login_Monitor')) {
            new AAS_Login_Monitor();
        }
        if (class_exists('AAS_Access_Control')) {
            new AAS_Access_Control();
        }
        if (class_exists('AAS_Admin_Notifications')) {
            AAS_Admin_Notifications::instance();
        }
        
        // 添加AJAX处理函数
        add_action('wp_ajax_aas_search_user', array($this, 'ajax_search_user'));
        add_action('wp_ajax_aas_add_whitelist', array($this, 'ajax_add_whitelist'));
        add_action('wp_ajax_aas_remove_whitelist', array($this, 'ajax_remove_whitelist'));
        add_action('wp_ajax_aas_get_whitelist', array($this, 'ajax_get_whitelist'));
        add_action('wp_ajax_aas_manual_ban_user', array($this, 'ajax_manual_ban_user'));
        add_action('wp_ajax_aas_batch_ban_users', array($this, 'ajax_batch_ban_users'));
        add_action('wp_ajax_aas_get_ip_details', array($this, 'ajax_get_ip_details'));
        add_action('wp_ajax_aas_unban_user', array($this, 'ajax_unban_user'));
        add_action('wp_ajax_aas_batch_unban_users', array($this, 'ajax_batch_unban_users'));
        add_action('wp_ajax_aas_get_ban_details', array($this, 'ajax_get_ban_details'));
        add_action('wp_ajax_aas_get_banned_users', array($this, 'ajax_get_banned_users'));
        
        // 添加定时任务处理
        add_action('aas_cleanup_old_logs', array($this, 'cleanup_old_logs'));
        add_action('aas_process_security_events', array($this, 'process_security_events'));
        add_action('aas_check_expired_bans', array($this, 'check_expired_bans'));
        
        // 调度定时任务
        if (!wp_next_scheduled('aas_check_expired_bans')) {
            wp_schedule_event(time(), 'hourly', 'aas_check_expired_bans');
        }
        
        // 添加性能优化钩子
        add_action('wp_login', array($this, 'optimize_user_session'), 20, 2);
    }
    
    public function activate() {
        $this->init_database();
        
        $default_options = array(
            'aas_enable_location_check' => true,
            'aas_enable_device_check' => true,
            'aas_warning_threshold' => 3,
            'aas_ban_threshold' => 5,
            'aas_enable_single_session' => true,
            'aas_prevent_simultaneous_login' => true,
            'aas_simultaneous_login_method' => 'force_logout',
            'aas_registration_ip_limit' => 3,
            'aas_block_foreign_ip' => false,
            'aas_block_domestic_ip' => false,
            'aas_enable_auto_warning' => true,
            'aas_enable_auto_ban' => true,
            'aas_enable_admin_notifications' => true,
            'aas_admin_notification_email' => '',
            'aas_notification_triggers' => array('auto_ban', 'high_risk_user', 'suspicious_login'),
            'aas_notification_frequency' => 'hourly',
            'aas_log_retention_days' => 90,
            'aas_max_user_sessions' => 3,
            'aas_enable_cache' => true,
            'aas_cache_expiry' => 3600,
        );
        
        foreach ($default_options as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        
        // 强制刷新重写规则
        flush_rewrite_rules();
        
        // 记录激活日志
        error_log('AAS Plugin activated successfully');
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('aas_cleanup_old_logs');
        wp_clear_scheduled_hook('aas_check_expired_bans');
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('anti-account-sharing', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function ajax_search_user() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_search_user')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $search_term = sanitize_text_field($_POST['search_term']);
        
        if (empty($search_term)) {
            wp_send_json_error('搜索词不能为空');
            return;
        }
        
        // 搜索用户
        $users = get_users(array(
            'search' => '*' . $search_term . '*',
            'search_columns' => array('user_login', 'user_email', 'display_name'),
            'number' => 10,
            'fields' => array('ID', 'user_login', 'user_email', 'display_name')
        ));
        
        $result = array();
        foreach ($users as $user) {
            $result[] = array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => $user->display_name
            );
        }
        
        wp_send_json_success($result);
    }
    
    public function ajax_add_whitelist() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_whitelist_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_id = intval($_POST['user_id']);
        $reason = sanitize_text_field($_POST['reason']);
        
        if (!$user_id) {
            wp_send_json_error('请输入有效的用户ID');
            return;
        }
        
        if (!get_user_by('ID', $user_id)) {
            wp_send_json_error('用户ID不存在');
            return;
        }
        
        // 检查是否已在白名单中
        global $wpdb;
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            wp_send_json_error('该用户已在白名单中');
            return;
        }
        
        // 添加到白名单
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $result = $user_classification->add_user_to_whitelist($user_id, get_current_user_id(), $reason);
        
        if ($result === true) {
            $user = get_user_by('ID', $user_id);
            wp_send_json_success(array(
                'message' => sprintf('用户 %s (ID: %d) 已成功添加到白名单', $user->user_login, $user_id)
            ));
        } else {
            // 获取详细错误信息
            if (!empty($wpdb->last_error)) {
                wp_send_json_error('数据库错误：' . $wpdb->last_error);
            } else {
                wp_send_json_error('添加失败，请稍后重试');
            }
        }
    }
    
    public function ajax_remove_whitelist() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_whitelist_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_id = intval($_POST['user_id']);
        
        if (!$user_id) {
            wp_send_json_error('无效的用户ID');
            return;
        }
        
        // 检查用户是否在白名单中
        global $wpdb;
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if (!$existing) {
            wp_send_json_error('该用户不在白名单中');
            return;
        }
        
        // 从白名单移除
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $result = $user_classification->remove_user_from_whitelist($user_id);
        
        if ($result === true) {
            $user = get_user_by('ID', $user_id);
            $username = $user ? $user->user_login : "ID: $user_id";
            wp_send_json_success(array(
                'message' => sprintf('用户 %s 已从白名单移除', $username)
            ));
        } else {
            // 获取详细错误信息
            if (!empty($wpdb->last_error)) {
                wp_send_json_error('数据库错误：' . $wpdb->last_error);
            } else {
                wp_send_json_error('移除失败，请稍后重试');
            }
        }
    }
    
    public function ajax_get_whitelist() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_get_whitelist')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $users_table = $wpdb->users;
        
        $results = $wpdb->get_results(
            "SELECT w.user_id, w.created_time, w.reason, u.user_login, u.user_email, u.display_name
             FROM $table_name w
             LEFT JOIN $users_table u ON w.user_id = u.ID
             ORDER BY w.created_time DESC
             LIMIT 50"
        );
        
        $html = '';
        if (empty($results)) {
            $html = '<tr><td colspan="6">暂无白名单用户</td></tr>';
        } else {
            foreach ($results as $row) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($row->user_id) . '</td>';
                $html .= '<td>' . esc_html($row->user_login ?: '用户不存在') . '</td>';
                $html .= '<td>' . esc_html($row->user_email ?: '-') . '</td>';
                $html .= '<td>' . esc_html($row->reason ?: '-') . '</td>';
                $html .= '<td>' . esc_html($row->created_time) . '</td>';
                $html .= '<td>';
                $html .= '<button type="button" class="button button-small remove-whitelist-btn" data-user-id="' . $row->user_id . '" data-username="' . esc_attr($row->user_login) . '">移除</button>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function ajax_manual_ban_user() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_manual_ban_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_id = intval($_POST['user_id']);
        $reason = sanitize_text_field($_POST['reason']);
        $duration = sanitize_text_field($_POST['duration']);
        
        if (!$user_id) {
            wp_send_json_error('请输入有效的用户ID');
            return;
        }
        
        if (!get_user_by('ID', $user_id)) {
            wp_send_json_error('用户ID不存在');
            return;
        }
        
        // 检查用户是否已被封禁
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $existing_classification = $user_classification->get_user_classification($user_id);
        
        if ($existing_classification && $existing_classification->is_banned) {
            wp_send_json_error('该用户已被封禁');
            return;
        }
        
        // 解析封禁天数
        $duration_days = null;
        if (!empty($duration) && $duration !== 'permanent' && is_numeric($duration)) {
            $duration_days = intval($duration);
        }
        
        // 执行封禁（不在reason中包含天数，而是单独传递天数参数）
        $user_classification->ban_user($user_id, $reason, $duration_days);
        
        // 记录封禁日志
        $log_reason = $reason;
        if ($duration_days) {
            $log_reason .= sprintf(' (期限: %d天)', $duration_days);
        }
        error_log(sprintf('AAS: User %d manually banned by admin %d. Reason: %s', 
            $user_id, get_current_user_id(), $log_reason));
        
        $user = get_user_by('ID', $user_id);
        wp_send_json_success(array(
            'message' => sprintf('用户 %s (ID: %d) 已成功封禁', $user->user_login, $user_id)
        ));
    }
    
    public function ajax_batch_ban_users() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_batch_ban_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_ids_input = sanitize_text_field($_POST['user_ids']);
        $reason = sanitize_text_field($_POST['reason']);
        $duration = sanitize_text_field($_POST['duration']);
        
        if (empty($user_ids_input)) {
            wp_send_json_error('请输入用户ID列表');
            return;
        }
        
        // 解析用户ID列表
        $user_ids = array();
        $raw_ids = preg_split('/[,\s\n]+/', $user_ids_input, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($raw_ids as $id) {
            $id = intval(trim($id));
            if ($id > 0 && get_user_by('ID', $id)) {
                $user_ids[] = $id;
            }
        }
        
        if (empty($user_ids)) {
            wp_send_json_error('没有找到有效的用户ID');
            return;
        }
        
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $success_count = 0;
        $already_banned = 0;
        $failed_users = array();
        
        // 解析封禁天数
        $duration_days = null;
        if (!empty($duration) && $duration !== 'permanent' && is_numeric($duration)) {
            $duration_days = intval($duration);
        }
        
        // 构建日志原因
        $log_reason = $reason;
        if ($duration_days) {
            $log_reason .= sprintf(' (期限: %d天)', $duration_days);
        }
        
        foreach ($user_ids as $user_id) {
            // 检查是否已被封禁
            $existing_classification = $user_classification->get_user_classification($user_id);
            if ($existing_classification && $existing_classification->is_banned) {
                $already_banned++;
                continue;
            }
            
            // 执行封禁
            try {
                $user_classification->ban_user($user_id, $reason, $duration_days);
                $success_count++;
                
                // 记录日志
                error_log(sprintf('AAS: User %d batch banned by admin %d. Reason: %s', 
                    $user_id, get_current_user_id(), $log_reason));
                    
            } catch (Exception $e) {
                $user = get_user_by('ID', $user_id);
                $failed_users[] = $user ? $user->user_login : "ID: $user_id";
            }
        }
        
        // 构建结果消息
        $message = sprintf('批量封禁完成：成功 %d 个', $success_count);
        if ($already_banned > 0) {
            $message .= sprintf('，已封禁 %d 个', $already_banned);
        }
        if (!empty($failed_users)) {
            $message .= sprintf('，失败 %d 个 (%s)', count($failed_users), implode(', ', $failed_users));
        }
        
        wp_send_json_success(array('message' => $message));
    }
    
    public function cleanup_old_logs() {
        global $wpdb;
        
        $retention_days = get_option('aas_log_retention_days', 90);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // 清理旧的登录日志
        $login_logs_table = $wpdb->prefix . 'aas_login_logs';
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $login_logs_table WHERE login_time < %s AND status = 'inactive'",
            $cutoff_date
        ));
        
        // 清理已处理的安全事件
        $events_table = $wpdb->prefix . 'aas_security_events';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $events_table WHERE created_time < %s AND processed = 1",
            $cutoff_date
        ));
        
        // 清理过期的IP限制
        $ip_restrictions_table = $wpdb->prefix . 'aas_ip_restrictions';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $ip_restrictions_table WHERE expires_time IS NOT NULL AND expires_time < %s",
            current_time('mysql')
        ));
        
        // 清理过期的白名单
        $whitelist_table = $wpdb->prefix . 'aas_whitelist';
        $wpdb->query($wpdb->prepare(
            "UPDATE $whitelist_table SET is_active = 0 WHERE expires_time IS NOT NULL AND expires_time < %s",
            current_time('mysql')
        ));
        
        // 清理过期的封禁
        $classification_table = $wpdb->prefix . 'aas_user_classification';
        $wpdb->query($wpdb->prepare(
            "UPDATE $classification_table SET is_banned = 0, ban_reason = '', ban_expires = NULL 
             WHERE ban_expires IS NOT NULL AND ban_expires < %s AND is_banned = 1",
            current_time('mysql')
        ));
        
        // 记录清理日志
        error_log("AAS: Cleaned up old logs. Deleted $deleted login records.");
    }
    
    public function process_security_events() {
        global $wpdb;
        
        $events_table = $wpdb->prefix . 'aas_security_events';
        $events = $wpdb->get_results(
            "SELECT * FROM $events_table WHERE processed = 0 ORDER BY created_time ASC LIMIT 50"
        );
        
        foreach ($events as $event) {
            $this->handle_security_event($event);
            
            // 标记为已处理
            $wpdb->update(
                $events_table,
                array('processed' => 1),
                array('id' => $event->id),
                array('%d'),
                array('%d')
            );
        }
    }
    
    private function handle_security_event($event) {
        switch ($event->event_type) {
            case 'suspicious_login':
                $this->handle_suspicious_login($event);
                break;
            case 'multiple_locations':
                $this->handle_multiple_locations($event);
                break;
            case 'device_change':
                $this->handle_device_change($event);
                break;
        }
    }
    
    private function handle_suspicious_login($event) {
        if (!class_exists('AAS_User_Classification')) return;
        
        $user_classification = AAS_User_Classification::instance();
        $classification = $user_classification->get_user_classification($event->user_id);
        
        if ($classification && $classification->warning_count >= 2) {
            // 发送邮件警告
            $user = get_user_by('ID', $event->user_id);
            if ($user) {
                wp_mail(
                    $user->user_email,
                    '账户安全警告',
                    "您的账户在异常地点登录，请检查账户安全。\n\n如非本人操作，请立即修改密码。"
                );
            }
        }
    }
    
    private function handle_multiple_locations($event) {
        // 处理多地登录事件
        $event_data = json_decode($event->event_data, true);
        if ($event_data && isset($event_data['location_count']) && $event_data['location_count'] >= 3) {
            $this->log_security_event($event->user_id, 'high_risk_detected', array(
                'location_count' => $event_data['location_count'],
                'auto_flagged' => true
            ), 'high');
        }
    }
    
    private function handle_device_change($event) {
        // 处理设备变更事件
        $event_data = json_decode($event->event_data, true);
        if ($event_data && isset($event_data['new_device'])) {
            // 记录设备变更历史
            error_log("AAS: Device change detected for user {$event->user_id}");
        }
    }
    
    public function log_security_event($user_id, $event_type, $event_data = array(), $severity = 'medium') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_security_events';
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'event_type' => $event_type,
                'event_data' => json_encode($event_data),
                'severity' => $severity,
                'ip_address' => $this->get_user_ip(),
                'created_time' => current_time('mysql'),
                'processed' => 0
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d')
        );
    }
    
    public function optimize_user_session($user_login, $user) {
        // 限制单用户同时在线会话数
        $max_sessions = get_option('aas_max_user_sessions', 3);
        
        $sessions = WP_Session_Tokens::get_instance($user->ID);
        $all_sessions = $sessions->get_all();
        
        if (count($all_sessions) > $max_sessions) {
            $sorted_sessions = array();
            foreach ($all_sessions as $token => $session) {
                $sorted_sessions[$token] = $session['login'];
            }
            
            // 按登录时间排序，保留最新的会话
            asort($sorted_sessions);
            $sessions_to_remove = array_slice(array_keys($sorted_sessions), 0, -$max_sessions, true);
            
            foreach ($sessions_to_remove as $token) {
                $sessions->destroy($token);
            }
        }
    }
    
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
    
    public function ajax_get_ip_details() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_get_ip_details')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $ip_address = sanitize_text_field($_POST['ip_address']);
        
        if (empty($ip_address)) {
            wp_send_json_error('IP地址不能为空');
            return;
        }
        
        // 验证IP地址格式
        if (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
            wp_send_json_error('无效的IP地址格式');
            return;
        }
        
        if (!class_exists('AAS_IP_Location')) {
            wp_send_json_error('IP位置服务未加载');
            return;
        }
        
        $ip_location = AAS_IP_Location::instance();
        $popup_html = $ip_location->get_ip_info_popup($ip_address);
        
        wp_send_json_success(array('html' => $popup_html));
    }
    
    
    public function ajax_unban_user() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_unban_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_id = intval($_POST['user_id']);
        
        if (!$user_id) {
            wp_send_json_error('请输入有效的用户ID');
            return;
        }
        
        if (!get_user_by('ID', $user_id)) {
            wp_send_json_error('用户ID不存在');
            return;
        }
        
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $existing_classification = $user_classification->get_user_classification($user_id);
        
        if (!$existing_classification || !$existing_classification->is_banned) {
            wp_send_json_error('该用户未被封禁');
            return;
        }
        
        // 执行解封
        $user_classification->unban_user($user_id);
        
        // 记录解封日志
        error_log(sprintf('AAS: User %d manually unbanned by admin %d', 
            $user_id, get_current_user_id()));
        
        $user = get_user_by('ID', $user_id);
        wp_send_json_success(array(
            'message' => sprintf('用户 %s (ID: %d) 已成功解封', $user->user_login, $user_id)
        ));
    }
    
    public function ajax_batch_unban_users() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_batch_unban_action')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_ids_input = sanitize_text_field($_POST['user_ids']);
        
        if (empty($user_ids_input)) {
            wp_send_json_error('请输入用户ID列表');
            return;
        }
        
        // 解析用户ID列表
        $user_ids = array();
        $raw_ids = preg_split('/[,\s\n]+/', $user_ids_input, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($raw_ids as $id) {
            $id = intval(trim($id));
            if ($id > 0 && get_user_by('ID', $id)) {
                $user_ids[] = $id;
            }
        }
        
        if (empty($user_ids)) {
            wp_send_json_error('没有找到有效的用户ID');
            return;
        }
        
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $success_count = 0;
        $not_banned = 0;
        $failed_users = array();
        
        foreach ($user_ids as $user_id) {
            // 检查是否被封禁
            $existing_classification = $user_classification->get_user_classification($user_id);
            if (!$existing_classification || !$existing_classification->is_banned) {
                $not_banned++;
                continue;
            }
            
            // 执行解封
            try {
                $user_classification->unban_user($user_id);
                $success_count++;
                
                // 记录日志
                error_log(sprintf('AAS: User %d batch unbanned by admin %d', 
                    $user_id, get_current_user_id()));
                    
            } catch (Exception $e) {
                $user = get_user_by('ID', $user_id);
                $failed_users[] = $user ? $user->user_login : "ID: $user_id";
            }
        }
        
        // 构建结果消息
        $message = sprintf('批量解封完成：成功 %d 个', $success_count);
        if ($not_banned > 0) {
            $message .= sprintf('，未封禁 %d 个', $not_banned);
        }
        if (!empty($failed_users)) {
            $message .= sprintf('，失败 %d 个 (%s)', count($failed_users), implode(', ', $failed_users));
        }
        
        wp_send_json_success(array('message' => $message));
    }
    
    public function ajax_get_ban_details() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_ban_details')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        $user_id = intval($_POST['user_id']);
        
        if (!$user_id) {
            wp_send_json_error('请输入有效的用户ID');
            return;
        }
        
        if (!class_exists('AAS_User_Classification')) {
            wp_send_json_error('用户分类系统未加载');
            return;
        }
        
        $user_classification = AAS_User_Classification::instance();
        $classification = $user_classification->get_user_classification($user_id);
        $user = get_user_by('ID', $user_id);
        
        if (!$classification || !$classification->is_banned) {
            wp_send_json_error('该用户未被封禁');
            return;
        }
        
        $html = '<div class="ban-info">';
        $html .= '<div class="ban-info-header">用户封禁详情</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">用户ID:</span>';
        $html .= '<span class="value">' . esc_html($user_id) . '</span>';
        $html .= '</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">用户名:</span>';
        $html .= '<span class="value">' . esc_html($user ? $user->user_login : '用户不存在') . '</span>';
        $html .= '</div>';
        
        if ($user && $user->user_email) {
            $html .= '<div class="ban-info-row">';
            $html .= '<span class="label">邮箱:</span>';
            $html .= '<span class="value">' . esc_html($user->user_email) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">封禁原因:</span>';
        $html .= '<span class="value">' . esc_html($classification->ban_reason ?: '无原因') . '</span>';
        $html .= '</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">封禁时间:</span>';
        $html .= '<span class="value">' . esc_html($classification->ban_time ?: $classification->last_updated) . '</span>';
        $html .= '</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">过期时间:</span>';
        if (!empty($classification->ban_expires)) {
            $expires_time = strtotime($classification->ban_expires);
            $current_time = time();
            if ($expires_time > $current_time) {
                $html .= '<span class="value">' . esc_html($classification->ban_expires) . '</span>';
            } else {
                $html .= '<span class="value" style="color: #d63384;">已过期</span>';
            }
        } else {
            $html .= '<span class="value">永久封禁</span>';
        }
        $html .= '</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">风险等级:</span>';
        $html .= '<span class="value">' . esc_html($classification->classification) . '</span>';
        $html .= '</div>';
        
        $html .= '<div class="ban-info-row">';
        $html .= '<span class="label">警告次数:</span>';
        $html .= '<span class="value">' . esc_html($classification->warning_count) . '</span>';
        $html .= '</div>';
        
        // 添加关闭按钮
        $html .= '<div style="text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">';
        $html .= '<button type="button" class="button" onclick="jQuery(\'#ban-details-modal\').hide()">关闭</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function ajax_get_banned_users() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aas_get_banned_users')) {
            wp_send_json_error('安全验证失败');
            return;
        }
        
        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error('权限不足');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'aas_user_classification';
        $users_table = $wpdb->users;
        
        $results = $wpdb->get_results(
            "SELECT c.user_id, c.ban_reason, c.ban_time, c.ban_expires, c.last_updated, u.user_login, u.user_email, u.display_name
             FROM $table_name c
             LEFT JOIN $users_table u ON c.user_id = u.ID
             WHERE c.is_banned = 1
             ORDER BY c.last_updated DESC"
        );
        
        $html = '';
        if (empty($results)) {
            $html = '<tr><td colspan="7">暂无封禁用户</td></tr>';
        } else {
            foreach ($results as $row) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($row->user_id) . '</td>';
                $html .= '<td>' . esc_html($row->user_login ?: '用户不存在') . '</td>';
                $html .= '<td>' . esc_html($row->user_email ?: '-') . '</td>';
                $html .= '<td title="' . esc_attr($row->ban_reason) . '">' . esc_html(mb_substr($row->ban_reason ?: '无原因', 0, 30) . (mb_strlen($row->ban_reason ?: '') > 30 ? '...' : '')) . '</td>';
                $html .= '<td>' . esc_html($row->ban_time ?: $row->last_updated) . '</td>';
                
                // 过期时间处理
                $expires_display = '永久';
                if (!empty($row->ban_expires)) {
                    $expires_time = strtotime($row->ban_expires);
                    $current_time = time();
                    if ($expires_time > $current_time) {
                        $expires_display = esc_html($row->ban_expires);
                    } else {
                        $expires_display = '<span style="color: #d63384;">已过期</span>';
                    }
                }
                $html .= '<td>' . $expires_display . '</td>';
                
                $html .= '<td>';
                $html .= '<button type="button" class="button button-small unban-user-btn" data-user-id="' . $row->user_id . '" data-username="' . esc_attr($row->user_login) . '">解封</button>';
                $html .= ' <button type="button" class="button button-small view-ban-details" data-user-id="' . $row->user_id . '">详情</button>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function check_expired_bans() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        $current_time = current_time('mysql');
        
        // 查找过期的封禁
        $expired_bans = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, ban_reason FROM $table_name 
             WHERE is_banned = 1 AND ban_expires IS NOT NULL AND ban_expires <= %s",
            $current_time
        ));
        
        if (!empty($expired_bans)) {
            $user_classification = AAS_User_Classification::instance();
            
            foreach ($expired_bans as $ban) {
                // 自动解封
                $user_classification->unban_user($ban->user_id);
                
                // 记录日志
                error_log(sprintf('AAS: User %d automatically unbanned due to expiration. Original reason: %s', 
                    $ban->user_id, $ban->ban_reason));
            }
            
            error_log(sprintf('AAS: Automatically unbanned %d expired users', count($expired_bans)));
        }
    }
    
}

function AAS() {
    return AntiAccountSharing::instance();
}

AAS();