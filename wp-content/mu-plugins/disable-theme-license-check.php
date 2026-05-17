<?php
/**
 * Plugin Name: Disable Theme License Check
 * Description: Bypass local-only Jitheme wp_die() guards during reconstruction
 */

if ( ! defined( 'MIYAUI_LOCAL_ENV' ) || ! MIYAUI_LOCAL_ENV ) {
    return;
}

function miyaui_is_local_theme_guard_message( $message ) {
    $msg = is_string( $message ) ? $message : ( is_wp_error( $message ) ? $message->get_error_message() : '' );
    $needles = array(
        '验证子主题授权',
        'jitheme.com',
        '系统维护中',
        '请登陆后台操作',
    );

    foreach ( $needles as $needle ) {
        if ( false !== strpos( $msg, $needle ) ) {
            return true;
        }
    }

    return false;
}

function miyaui_local_die_bypass_handler( $fallback_handler ) {
    return function ( $message = '', $title = '', $args = array() ) use ( $fallback_handler ) {
        if ( miyaui_is_local_theme_guard_message( $message ) ) {
            if ( ! headers_sent() ) {
                status_header( 200 );
            }
            return;
        }

        call_user_func( $fallback_handler, $message, $title, $args );
    };
}

add_filter(
    'wp_die_handler',
    function ( $handler ) {
        return miyaui_local_die_bypass_handler( $handler );
    },
    1,
    1
);

add_filter(
    'wp_die_json_handler',
    function ( $handler ) {
        return miyaui_local_die_bypass_handler( $handler );
    },
    1,
    1
);

add_filter('body_class', function ($classes) {
    return is_array($classes) ? $classes : array();
}, 999);

add_action('wp_head', function () {
    echo '<script>var b2_search_data = {"users":[]};</script>';
}, 1);
