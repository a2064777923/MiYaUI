<?php
/**
Plugin Name: b2私信弹窗强提醒
Plugin URI: https://www.devskyr.com/b2sxtz
Description: 检测未读私信并在首页展示弹窗。
Version: 1.0
Author: 软柠科技
Author URI:https://www.rutno.com/
*/
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'enqueue_direct_message_script' );

function enqueue_direct_message_script() {

    wp_enqueue_script( 'direct-message-checker', plugin_dir_url( __FILE__ ) . 'js/direct-message-checker.js', array('jquery'), null, true );

    wp_localize_script( 'direct-message-checker', 'dmChecker', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'user_id' => get_current_user_id(),
    ));
}







register_activation_hook( __FILE__, 'devskyrrn_activate' );
function devskyrrn_activate() {

}


register_uninstall_hook( __FILE__, 'devskyrrn_uninstall' );
function devskyrrn_uninstall() {

}

add_action( 'admin_menu', 'devskyrrn_create_custom_backend_menu' );
function devskyrrn_create_custom_backend_menu() {

    add_menu_page(
        '软柠消息插件介绍',
        '软柠科技-b2消息',
       'manage_options',
        'devskyrrn-custom-menu-slug',
        'devskyrrn_custom_menu_callback'
    );
}

function devskyrrn_custom_menu_callback() {
    echo '<div class="wrap">';
    echo '<h1>软柠消息插件介绍</h1>';
    echo '<p>感谢您下载我们的插件，更多免费插件我们将持续开发，感谢您！如有更新，会在下方通知！！！</p>';
    echo '       <script src="https://safe.rutno.com/safejs/tsfor.js"></script> <style>
        .iframeqr-rnkj {
    width: 100%;
    height: 100vh;
}
       </style> 

        <?php submit_button();?>
    </form>
            <p>
            <iframe class="iframeqr-rnkj" src="https://pages.static.devskyr.com/img-rnkj/rntz"></iframe>
        </p>';
    echo '</div>';
}







add_action( 'wp_ajax_check_direct_messages', 'check_direct_messages' );

function check_direct_messages() {
    global $wpdb;

    $user_id = intval( $_POST['user_id'] );

    $unread_messages = $wpdb->get_var( $wpdb->prepare("
        SELECT COUNT(*) FROM wp_zrz_directmessage 
        WHERE `to` = %d AND `status` = 0
    ", $user_id));
    if ($unread_messages > 0) {
        wp_send_json_success( array( 'has_unread' => true ) );
    } else {
        wp_send_json_success( array( 'has_unread' => false ) );
    }
    wp_die();
}

function add_message_modal() {
    ?>
    <style>

        #message-modal {
            display: none;
            position: fixed;
            top: 80px;
            right: 15px;
            z-index: 1000;
        }


        #message-modal div {
            background: white;
            padding: 20px;
            width: 300px;

            border: none;
                box-shadow: 0 2px 10px rgba(55, 99, 170, .06);
        }


        #message-modal.close {
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .pclass {
    padding-bottom: 10px;
    color: #4f4f4f;
}
        
    </style>
</head>


    <div id="message-modal">
        <div>
            <p class="pclass">✅ 你有新的私信</p>
            <p class="pclass">👉 请务必要查看您的私信哟~</p>
            <a href="/directmessage"><button>查看私信</button></a>
        </div>
    </div>



    <?php
}
add_action('wp_footer', 'add_message_modal');
?>





