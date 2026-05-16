<?php
/**
 * Plugin Name: 网站灵动岛插件
 * Plugin URI:  https://www.mengblog.cn/1749.html
 * Description: 显示带有上下文特定消息的动态岛通知。
 * Version: 1.0.0
 * Author: 胖虎
 * Author URI:  https://www.mengblog.cn
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; 
}

class DynamicIslandPlugin {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'add_dynamic_island_container']);
    }

    public function enqueue_scripts() {
        wp_register_script(
            'dynamic-island-js',
            plugin_dir_url(__FILE__) . 'js/dynamic-island.js',
            [],
            '1.1',
            true
        );

        $data = [
            'siteName' => get_bloginfo('name'),
            'currentPage' => $this->get_current_page_type(),
            'iconUrl' => plugin_dir_url(__FILE__) . 'assets/icon.png', 
        ];

        wp_localize_script('dynamic-island-js', 'DynamicIslandData', $data);

        wp_enqueue_script('dynamic-island-js');
    }

    public function add_dynamic_island_container() {
        echo '<div id="dynamic-island-container"></div>';
    }

    private function get_current_page_type() {
        if (is_home() || is_front_page()) {
            return 'home';
        } elseif (is_single()) {
            return 'single';
        } elseif (is_page()) {
            return 'page';
        } elseif (is_category()) {
            return 'category';
        } elseif (is_tag()) {
            return 'tag';
        } elseif (is_search()) {
            return 'search';
        } else {
            return 'other';
        }
    }
}

new DynamicIslandPlugin();
