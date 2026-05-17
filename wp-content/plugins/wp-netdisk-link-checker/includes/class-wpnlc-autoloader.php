<?php
/**
 * 自动加载器类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 自动加载器类
 */
class WPNLC_Autoloader {

    /**
     * 注册自动加载器
     */
    public static function register() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * 自动加载类文件
     *
     * @param string $class_name 类名
     */
    public static function autoload($class_name) {
        // 只处理我们插件的类
        if (strpos($class_name, 'WPNLC_') !== 0) {
            return;
        }

        // 将类名转换为文件名
        $file_name = self::get_file_name_from_class($class_name);
        
        // 构建文件路径
        $file_path = WPNLC_PLUGIN_DIR . 'includes/' . $file_name;

        // 如果文件存在则加载
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * 将类名转换为文件名
     *
     * @param string $class_name 类名
     * @return string 文件名
     */
    private static function get_file_name_from_class($class_name) {
        // 移除 WPNLC_ 前缀
        $class_name = substr($class_name, 6);
        
        // 将驼峰命名转换为连字符分隔的小写字符串
        $file_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name));
        
        // 添加 class- 前缀和 .php 后缀
        return 'class-wpnlc-' . $file_name . '.php';
    }
}

// 注册自动加载器
WPNLC_Autoloader::register();
