<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_Cache_Manager {
    
    private static $instance = null;
    private $cache_prefix = 'aas_cache_';
    private $default_expiry = 3600; // 1小时
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key) {
        $cache_key = $this->cache_prefix . $key;
        
        // 优先使用对象缓存
        if (function_exists('wp_cache_get')) {
            $cached = wp_cache_get($cache_key, 'aas');
            if ($cached !== false) {
                return $cached;
            }
        }
        
        // 回退到瞬态缓存
        return get_transient($cache_key);
    }
    
    public function set($key, $value, $expiry = null) {
        if ($expiry === null) {
            $expiry = $this->default_expiry;
        }
        
        $cache_key = $this->cache_prefix . $key;
        
        // 对象缓存
        if (function_exists('wp_cache_set')) {
            wp_cache_set($cache_key, $value, 'aas', $expiry);
        }
        
        // 瞬态缓存作为备份
        set_transient($cache_key, $value, $expiry);
        
        return true;
    }
    
    public function delete($key) {
        $cache_key = $this->cache_prefix . $key;
        
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete($cache_key, 'aas');
        }
        
        delete_transient($cache_key);
        
        return true;
    }
    
    public function flush() {
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('aas');
        }
        
        // 清理所有AAS相关的瞬态
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $this->cache_prefix . '%'
            )
        );
        
        return true;
    }
    
    public function get_user_classification_cached($user_id) {
        $cache_key = "user_classification_{$user_id}";
        $cached = $this->get($cache_key);
        
        if ($cached === false) {
            if (!class_exists('AAS_User_Classification')) {
                return null;
            }
            
            $user_classification = AAS_User_Classification::instance();
            $classification = $user_classification->get_user_classification($user_id);
            
            // 缓存10分钟
            $this->set($cache_key, $classification, 600);
            return $classification;
        }
        
        return $cached;
    }
    
    public function get_user_login_locations_cached($user_id, $limit = 10) {
        $cache_key = "user_locations_{$user_id}_{$limit}";
        $cached = $this->get($cache_key);
        
        if ($cached === false) {
            if (!class_exists('AAS_User_Classification')) {
                return array();
            }
            
            $user_classification = AAS_User_Classification::instance();
            $locations = $user_classification->get_recent_login_locations($user_id, $limit);
            
            // 缓存5分钟
            $this->set($cache_key, $locations, 300);
            return $locations;
        }
        
        return $cached;
    }
    
    public function get_ip_location_cached($ip_address) {
        $cache_key = "ip_location_" . md5($ip_address);
        $cached = $this->get($cache_key);
        
        if ($cached === false) {
            if (!class_exists('AAS_IP_Location')) {
                return null;
            }
            
            $ip_location = AAS_IP_Location::instance();
            $location = $ip_location->get_location($ip_address);
            
            // IP位置信息缓存24小时
            $this->set($cache_key, $location, 86400);
            return $location;
        }
        
        return $cached;
    }
    
    public function invalidate_user_cache($user_id) {
        $keys_to_delete = array(
            "user_classification_{$user_id}",
            "user_locations_{$user_id}_5",
            "user_locations_{$user_id}_10",
            "user_risk_score_{$user_id}",
            "user_whitelist_status_{$user_id}"
        );
        
        foreach ($keys_to_delete as $key) {
            $this->delete($key);
        }
    }
    
    public function get_statistics_cached() {
        $cache_key = 'classification_statistics';
        $cached = $this->get($cache_key);
        
        if ($cached === false) {
            if (!class_exists('AAS_User_Classification')) {
                return array();
            }
            
            $user_classification = AAS_User_Classification::instance();
            $stats = $user_classification->get_classification_statistics();
            
            // 统计数据缓存30分钟
            $this->set($cache_key, $stats, 1800);
            return $stats;
        }
        
        return $cached;
    }
    
    public function preload_frequent_data() {
        // 预加载经常访问的数据
        $this->get_statistics_cached();
        
        // 预加载最近活跃用户的分类信息
        global $wpdb;
        $recent_users = $wpdb->get_col(
            "SELECT DISTINCT user_id FROM {$wpdb->prefix}aas_login_logs 
             WHERE login_time > DATE_SUB(NOW(), INTERVAL 1 HOUR) 
             LIMIT 20"
        );
        
        foreach ($recent_users as $user_id) {
            $this->get_user_classification_cached($user_id);
        }
    }
}