<?php
namespace B2APP;

use B2APP\common\restApi;
use B2APP\common\opt;

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('B2APP')):

    class B2APP
    {

        public function __construct()
        {

            register_activation_hook(__FILE__, array($this, 'plugin_activate'));

            spl_autoload_register(array($this, 'autoload'));

            add_action('init', array($this, 'init_rewrite'), 999);
            add_filter('template_include', array($this, 'template_include'));

            preg_match("#^\d.\d#", PHP_VERSION, $p_v);

            $p_v[0] = str_replace('.', '', $p_v[0]);

            // require B2_APP_PATH.'common'.DIRECTORY_SEPARATOR.'methods.php';

            // try{
            //     $ext = new \ReflectionExtension('swoole_loader');
            //     $ver = $ext->getVersion();
                
            //     if($ver == '3.2.0' && !B2_THEME_DEBUG){
                    
            //     }else{
            //         add_action('admin_notices', array($this, 'b2_admin_extension_warn'));
            //         return;
            //     }
                
            // }catch(\Throwable $th){
            //     add_action('admin_notices', array($this, 'b2_admin_extension_warn'));
            //     return;
            // }

            require_once B2_APP_PATH.'common'.DIRECTORY_SEPARATOR.'methods'.DIRECTORY_SEPARATOR.$p_v[0].DIRECTORY_SEPARATOR.'methods.php';


            // var_dump(B2_APP_PATH.'common'.DIRECTORY_SEPARATOR.'methods'.DIRECTORY_SEPARATOR.$p_v[0].DIRECTORY_SEPARATOR.'methods.php');
            $methods = new \b2methods();
            $methods->init();

            $data = apply_filters('b2_app_user_info', false);

            if (!($data['can'] ?? false)) {
                add_action('admin_notices', array($this, 'b2_admin_notice_warn'));
            } else {

                $restApi = new restApi();
                $restApi->init();

                $opt = new opt();
                $opt->init();

                add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2);
                add_filter('network_admin_plugin_action_links', array($this, 'add_settings_link'), 10, 2);
            }
        }

        // public function b2_admin_notice_install()
        // {
        //     preg_match("#^\d.\d#", PHP_VERSION, $p_v);
        //     $p_v[0] = str_replace('.', '', $p_v[0]);

        //     $path = B2_APP_PATH;
        //     $path = PATH_SEPARATOR != ':' ? str_replace('/', DIRECTORY_SEPARATOR, $path) : $path;
        //     $directory = B2_APP_PATH . 'common' . DIRECTORY_SEPARATOR . 'methods' . DIRECTORY_SEPARATOR . $p_v[0];
        //     $directory = PATH_SEPARATOR != ':' ? str_replace('/', DIRECTORY_SEPARATOR, $directory) : $directory;
        //     $licenseFiles = glob($directory . DIRECTORY_SEPARATOR . '*.license');

        //     echo '<div class="notice notice-warning is-dismissible">
        //       <p>B2主题-移动端插件的扩展未正确安装，请按照下面的提示操作：</p>
        //       <p>1、请打开 php.ini 文件（<code>' . php_ini_loaded_file() . '</code>） </p>
        //       <p>2、最下面找到类似这一行：<br><code>swoole_loader.license_files=/path/***123.license</code></p>
        //       <p>3、<span class="red">在它后面追加</span>下面的代码（注意下面代码第一个字符是英文逗号不要遗漏）：<br><code><span class="red">,</span>' . $licenseFiles[0] . '</code></p>
        //       <p>最终变成这种形式：<br><code>swoole_loader.license_files=/path/***123.license<span class="red">,</span>/path/***345.license</code></p>
        //       </div>';
        // }

        function plugin_activate()
        {
            delete_transient(md5(home_url() . 'wp_app_check'));
            delete_transient(md5(home_url() . 'wp_app_check_name'));
        }


        function b2_admin_notice_warn()
        {
            $domain = rtrim(preg_replace('#^https?://#', '', home_url()), '/');
            echo '<div class="notice notice-warning is-dismissible">
                <h2>B2主题-移动端插件：未授权</h2>
              <p>您当前的域名（<b class="red">' . $domain . '</b>）没有授权，请前往<a href="https://7b2.com/check/app" target="_blank">7b2官网</a>进行授权</p>
              <p>授权完毕后，需要重新下载插件，并安装到您的wp站点中。</p>
              </div>';
        }

        function add_settings_link($links, $file)
        {

            if ($file === 'b2-app/b2-app.php' && current_user_can('manage_options')) {
                // Prevent warnings in PHP 7.0+ when a plugin uses this filter incorrectly.
                $links = (array) $links;
                $links[] = sprintf('<a href="%s">%s</a>', admin_url('/admin.php?page=b2_app_options'), __('设置', 'b2'));
            }

            return $links;
        }

        private function app_plugin_setting($links)
        {
            $link = admin_url('admin.php?page=b2_app_options');

            if (is_multisite() && (!is_main_site() || !is_super_admin()))
                return $links;
            $link = array($link);
            return array_merge($link, $links);

        }

        public function init_rewrite()
        {
            add_rewrite_tag('%b2_app_page%', '([^&]+)');
            add_rewrite_rule('app/([^&]+)', 'index.php?b2_app_page=$matches[1]', 'top');
        }

        public function template_include($template)
        {

            $page_name = get_query_var('b2_app_page');

            if ($page_name) {
                if (!in_array($page_name, array('download', 'jili'))) {
                    wp_safe_redirect(home_url() . '/404');
                    exit;
                }
                include('template' . DIRECTORY_SEPARATOR . $page_name . '.php');
                exit;
            }
            return $template;
        }

        public function setup_scripts()
        {
            // wp_enqueue_script( 'b2-check', plugins_url('',__FILE__).'/static/check.js', array(), B2_VERSION , true );
        }

        public function autoload($class)
        {

            //主题模块
            if (strpos($class, 'B2APP\\') !== false) {
                $class = str_replace('B2APP\\', '', $class);

                require_once B2_APP_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
                return;
            }
        }

    }
    new B2APP();

endif;