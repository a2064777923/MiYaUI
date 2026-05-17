<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_Device_Info {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get_device_info() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        return array(
            'user_agent' => $user_agent,
            'browser' => $this->get_browser($user_agent),
            'browser_version' => $this->get_browser_version($user_agent),
            'os' => $this->get_operating_system($user_agent),
            'device_type' => $this->get_device_type($user_agent),
            'is_mobile' => $this->is_mobile_device($user_agent),
            'screen_resolution' => $this->get_screen_resolution(),
            'timezone' => $this->get_timezone(),
            'language' => $this->get_language(),
            'fingerprint' => $this->generate_device_fingerprint($user_agent)
        );
    }
    
    private function get_browser($user_agent) {
        $browsers = array(
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Safari\/([0-9.]+)/',
            'Edge' => '/Edg\/([0-9.]+)/',
            'Internet Explorer' => '/MSIE ([0-9.]+)/',
            'Opera' => '/Opera\/([0-9.]+)/',
            'WeChat' => '/MicroMessenger\/([0-9.]+)/',
            'QQ Browser' => '/QQBrowser\/([0-9.]+)/',
            'UC Browser' => '/UCBrowser\/([0-9.]+)/',
            '360 Browser' => '/360SE/',
            'Sogou Browser' => '/SE ([0-9.]+)/',
            'Baidu Browser' => '/baidubrowser\/([0-9.]+)/'
        );
        
        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $browser;
            }
        }
        
        return 'Unknown';
    }
    
    private function get_browser_version($user_agent) {
        $patterns = array(
            '/Chrome\/([0-9.]+)/' => 'Chrome',
            '/Firefox\/([0-9.]+)/' => 'Firefox',
            '/Safari\/([0-9.]+)/' => 'Safari',
            '/Edg\/([0-9.]+)/' => 'Edge',
            '/MSIE ([0-9.]+)/' => 'IE',
            '/Opera\/([0-9.]+)/' => 'Opera',
            '/MicroMessenger\/([0-9.]+)/' => 'WeChat',
            '/QQBrowser\/([0-9.]+)/' => 'QQ',
            '/UCBrowser\/([0-9.]+)/' => 'UC',
            '/baidubrowser\/([0-9.]+)/' => 'Baidu'
        );
        
        foreach ($patterns as $pattern => $browser) {
            if (preg_match($pattern, $user_agent, $matches)) {
                return isset($matches[1]) ? $matches[1] : 'Unknown';
            }
        }
        
        return 'Unknown';
    }
    
    private function get_operating_system($user_agent) {
        $os_array = array(
            'Windows 11' => '/Windows NT 10.0; Win64; x64.*(?=.*Windows NT 10.0).*(?=.*Build 22)/',
            'Windows 10' => '/Windows NT 10.0/',
            'Windows 8.1' => '/Windows NT 6.3/',
            'Windows 8' => '/Windows NT 6.2/',
            'Windows 7' => '/Windows NT 6.1/',
            'Windows Vista' => '/Windows NT 6.0/',
            'Windows XP' => '/Windows NT 5.1/',
            'macOS' => '/Mac OS X|macOS/',
            'iOS' => '/iPhone|iPad|iPod/',
            'Android' => '/Android/',
            'Linux' => '/Linux/',
            'Ubuntu' => '/Ubuntu/',
            'Unix' => '/Unix/',
            'FreeBSD' => '/FreeBSD/',
            'OpenBSD' => '/OpenBSD/',
            'NetBSD' => '/NetBSD/',
            'Symbian' => '/Symbian/',
            'BlackBerry' => '/BlackBerry/',
            'Windows Phone' => '/Windows Phone/',
            'Chrome OS' => '/CrOS/'
        );
        
        foreach ($os_array as $os => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $os;
            }
        }
        
        return 'Unknown';
    }
    
    private function get_device_type($user_agent) {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent)) {
            return 'Tablet';
        }
        
        if (preg_match('/(mobile|phone|iphone|ipod|android|blackberry|mini|windows ce|palm)/i', $user_agent)) {
            return 'Mobile';
        }
        
        if (preg_match('/(smart|television|tv)/i', $user_agent)) {
            return 'Smart TV';
        }
        
        return 'Desktop';
    }
    
    private function is_mobile_device($user_agent) {
        return preg_match('/(mobile|phone|iphone|ipod|android|blackberry|mini|windows ce|palm)/i', $user_agent);
    }
    
    private function get_screen_resolution() {
        return isset($_COOKIE['aas_screen_resolution']) ? sanitize_text_field($_COOKIE['aas_screen_resolution']) : 'Unknown';
    }
    
    private function get_timezone() {
        return isset($_COOKIE['aas_timezone']) ? sanitize_text_field($_COOKIE['aas_timezone']) : 'Unknown';
    }
    
    private function get_language() {
        $accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        
        if (empty($accept_language)) {
            return 'Unknown';
        }
        
        $languages = explode(',', $accept_language);
        $primary_language = trim(explode(';', $languages[0])[0]);
        
        return $primary_language;
    }
    
    private function generate_device_fingerprint($user_agent) {
        $fingerprint_data = array(
            'user_agent' => $user_agent,
            'accept_language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
            'accept_encoding' => isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '',
            'accept' => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '',
            'connection' => isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '',
            'screen_resolution' => $this->get_screen_resolution(),
            'timezone' => $this->get_timezone()
        );
        
        return md5(json_encode($fingerprint_data));
    }
    
    public function get_device_stats($user_id, $days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                browser,
                os,
                COUNT(*) as usage_count,
                MAX(login_time) as last_used
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s
             GROUP BY browser, os
             ORDER BY usage_count DESC",
            $user_id,
            $date_limit
        ));
        
        return $results;
    }
    
    public function detect_suspicious_device_activity($user_id) {
        $recent_devices = $this->get_recent_devices($user_id);
        $unique_devices = count($recent_devices);
        
        if ($unique_devices > 5) {
            return array(
                'is_suspicious' => true,
                'reason' => 'Too many different devices used recently',
                'device_count' => $unique_devices
            );
        }
        
        $simultaneous_sessions = $this->get_simultaneous_device_sessions($user_id);
        if ($simultaneous_sessions > 3) {
            return array(
                'is_suspicious' => true,
                'reason' => 'Multiple simultaneous device sessions',
                'session_count' => $simultaneous_sessions
            );
        }
        
        return array(
            'is_suspicious' => false,
            'device_count' => $unique_devices,
            'session_count' => $simultaneous_sessions
        );
    }
    
    private function get_recent_devices($user_id, $hours = 24) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT browser, os, device_info
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s",
            $user_id,
            $date_limit
        ));
        
        return $results;
    }
    
    private function get_simultaneous_device_sessions($user_id, $minutes = 5) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $time_limit = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(browser, '-', os)) 
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s AND status = 'active'",
            $user_id,
            $time_limit
        ));
        
        return intval($result);
    }
    
    public function get_device_fingerprint_conflicts($fingerprint, $user_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        
        $sql = "SELECT DISTINCT user_id, COUNT(*) as usage_count 
                FROM $table_name 
                WHERE JSON_EXTRACT(device_info, '$.fingerprint') = %s";
        
        $params = array($fingerprint);
        
        if ($user_id) {
            $sql .= " AND user_id != %d";
            $params[] = $user_id;
        }
        
        $sql .= " GROUP BY user_id ORDER BY usage_count DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    public function is_trusted_device($user_id, $device_fingerprint) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $days_threshold = 30;
        $usage_threshold = 5;
        
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days_threshold} days"));
        
        $usage_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM $table_name 
             WHERE user_id = %d 
             AND JSON_EXTRACT(device_info, '$.fingerprint') = %s 
             AND login_time >= %s",
            $user_id,
            $device_fingerprint,
            $date_limit
        ));
        
        return intval($usage_count) >= $usage_threshold;
    }
    
    public function add_device_info_scripts() {
        ?>
        <script>
        (function() {
            function setDeviceInfo() {
                var screenRes = screen.width + 'x' + screen.height;
                var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                
                document.cookie = 'aas_screen_resolution=' + screenRes + '; path=/';
                document.cookie = 'aas_timezone=' + timezone + '; path=/';
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setDeviceInfo);
            } else {
                setDeviceInfo();
            }
        })();
        </script>
        <?php
    }
}