<?php
/**
 * Plugin Name: Disable Theme License Check
 * Description: Bypass local-only Jitheme wp_die() guards during reconstruction
 */

if ( ! defined( 'MIYAUI_LOCAL_ENV' ) || ! MIYAUI_LOCAL_ENV ) {
    return;
}

add_filter('wp_die_handler', function ($handler) {
    return function ($message = '', $title = '', $args = array()) {
        $msg = is_string($message) ? $message : (is_wp_error($message) ? $message->get_error_message() : '');
        $needles = array(
            '验证子主题授权',
            'jitheme.com',
            '系统维护中',
            '请登陆后台操作',
        );

        foreach ( $needles as $needle ) {
            if ( strpos( $msg, $needle ) !== false ) {
                if (!headers_sent()) {
                    status_header(200);
                }
                return;
            }
        }

        if (strpos($msg, '验证子主题授权') !== false || strpos($msg, 'jitheme.com') !== false) {
            if (!headers_sent()) {
                status_header(200);
            }
            return;
        }
        _default_wp_die_handler($message, $title, $args);
    };
}, 1, 1);

add_filter('body_class', function ($classes) {
    return is_array($classes) ? $classes : array();
}, 999);

add_action('wp_head', function () {
    echo '<script>var b2_search_data = {"users":[]};</script>';
}, 1);
