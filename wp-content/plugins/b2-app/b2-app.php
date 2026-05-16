<?php
/*
Plugin Name: B2主题APP插件
Plugin URI: https://7b2.com
Description: B2主题APP插件
Version: 1.8.6
Author: Li Ruchun
Author URI: https://7b2.com
*/

define('B2_APP_VERSION', '1.8.6');
define('B2_APP_ENGLISH', false);
define('B2_APP_PATH', plugin_dir_path(__FILE__));
define('B2_APP_URI', plugin_dir_url(__FILE__));
define('B2_PLUGIN_WITH_CLASSES_FILE_', __FILE__);

$loader = extension_loaded('swoole_loader');
if($loader){
    $ext = new \ReflectionExtension('swoole_loader');
    $ver = $ext->getVersion();
    if($ver != '3.2.1'){
        $loader = false;
    }
}

if (($loader || (defined('B2_THEME_DEBUG') && B2_THEME_DEBUG)) && (file_exists(get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'Private' . DIRECTORY_SEPARATOR . '73' . DIRECTORY_SEPARATOR . 'private.php') || file_exists(get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'child.js'))) {

    require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'loader.php';
}else{

    add_action('admin_notices',  'b2_admin_notice_warn');
    
    function b2_admin_notice_warn()
    {
       echo '<div class="notice notice-warning is-dismissible">
                <h2>扩展不兼容</h2>
              <p>您当前B2主题使用的扩展版本和B2APP使用的扩展版本不一样，请将B2主题和B2APP插件都升级到最新版。</p>
              </div>';
    }
}
