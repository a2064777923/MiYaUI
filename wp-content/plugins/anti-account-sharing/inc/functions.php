<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 防账号共享插件 - 辅助函数
 * 
 * @package Anti Account Sharing
 * @version 1.0.0
 */

/**
 * 获取用户的真实IP地址
 */
function aas_get_user_ip() {
    $ip_keys = array(
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_REAL_IP',            // Nginx代理
        'HTTP_X_FORWARDED_FOR',      // 标准代理头
        'HTTP_X_FORWARDED',          // 代理变体
        'HTTP_X_CLUSTER_CLIENT_IP',  // 集群代理
        'HTTP_CLIENT_IP',            // 客户端IP
        'HTTP_FORWARDED_FOR',        // 转发头
        'HTTP_FORWARDED',            // RFC 7239
        'REMOTE_ADDR'                // 最后备选
    );
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true && !empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            // 基本IP格式验证
            if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                // 排除明显的无效IP
                if ($ip !== '0.0.0.0' && $ip !== '127.0.0.1' && $ip !== '::1') {
                    return $ip;
                }
            }
        }
    }
    
    // 如果以上都没有找到有效IP，返回REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/**
 * 格式化字节大小
 */
function aas_format_bytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

/**
 * 检查是否为本地IP
 */
function aas_is_local_ip($ip) {
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
        return true;
    }
    
    // 检查私有IP范围
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return true;
    }
    
    return false;
}

/**
 * 生成随机字符串
 */
function aas_generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * 验证邮箱格式
 */
function aas_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 记录插件日志
 */
function aas_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("AAS Plugin [{$level}]: " . $message);
    }
}

/**
 * 获取插件版本号
 */
function aas_get_plugin_version() {
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_data = get_plugin_data(AAS_PLUGIN_FILE);
    return $plugin_data['Version'] ?? '1.0.0';
}

/**
 * 检查插件依赖
 */
function aas_check_requirements() {
    $requirements = array(
        'php_version' => '7.4',
        'wp_version' => '5.0',
        'mysql_version' => '5.6'
    );
    
    $errors = array();
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
        $errors[] = sprintf('PHP版本需要 %s 或更高版本，当前版本：%s', $requirements['php_version'], PHP_VERSION);
    }
    
    // 检查WordPress版本
    global $wp_version;
    if (version_compare($wp_version, $requirements['wp_version'], '<')) {
        $errors[] = sprintf('WordPress版本需要 %s 或更高版本，当前版本：%s', $requirements['wp_version'], $wp_version);
    }
    
    return $errors;
}