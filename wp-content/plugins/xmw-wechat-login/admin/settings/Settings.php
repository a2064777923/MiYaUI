<?php
use B2\Modules\Common\User;
use B2\Modules\Common\Credit;
use B2\Modules\Common\Orders as Corders;

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('B2_Wechat_Login_Settings')):

    class B2_Wechat_Login_Settings
    {

        public function init()
        {

            add_action('cmb2_admin_init', array($this, 'b2_mb_page'), 50, 5);
        }

        public function b2_mb_page()
        {
            //常规设置
            $setting = new_cmb2_box(
                array(
                    'id' => 'xmw_wechat_login',
                    'object_types' => array('options-page'),
                    'option_key' => 'b2_xmw_wechat_login_bulid',
                    'tab_group' => 'xmw_wechat_login_options',
                    'parent_slug' => 'b2_main_options',
                    'tab_title' => __('微信订阅后登陆', 'b2'),
                    'menu_title' => __('微信订阅后登陆', 'b2'),
                    'save_button' => __('保存', 'b2'),
                )
            );
            
            
        
            
        $setting->add_field( array(
            'name'    => __( 'Appid', 'b2' ),
            'id'      => 'appid',
            'desc'=> __( '输入微信订阅号的Appid', 'b2' ),
            'type'    => 'text',
            'default'=>'',
            'before_row'=>'<h2>接口URL：'.get_bloginfo('url').'/wp-content/plugins/xmw-wechat-login/valid.php</h2>',
        ) );
            
        $setting->add_field( array(
            'name'    => __( 'AppSecret', 'b2' ),
            'id'      => 'appsecret',
            'desc'=> __( '输入微信订阅号的AppSecret', 'b2' ),
            'type'    => 'text',
            'default'=>'',
        ) );
            
        $setting->add_field( array(
            'name'    => __( 'Token', 'b2' ),
            'id'      => 'token',
            'desc'=> __( '输入微信订阅号的Token', 'b2' ),
            'type'    => 'text',
            'default'=>'',
        ) );
            
        // $setting->add_field( array(
        //     'name'    => __( 'EncodingAESKey', 'b2' ),
        //     'id'      => 'EncodingAESKey',
        //     'desc'=> __( '输入微信订阅号的EncodingAESKey', 'b2' ),
        //     'type'    => 'text',
        //     'default'=>'',
        // ) );

        $setting->add_field( array(
            'name'    => __( '订阅号二维码', 'b2' ),
            'id'      => 'wechat_or',
            'desc'=> __( '输入微信订阅号的二维码', 'b2' ),
            'type'    => 'file',
            'default'=>'',
        ) );
            
        $setting->add_field( array(
            'name'    => __( '回复获取验证码', 'b2' ),
            'id'      => 'wechat_huifu',
            'desc'=> __( '输入微信订阅号回复获取验证码', 'b2' ),
            'type'    => 'text',
            'default'=>'登陆',
        ) );
            
        // $setting->add_field( array(
        //     'name'    => __( '订阅号返回', 'b2' ),
        //     'id'      => 'wechat_fanhui',
        //     'desc'=> __( '输入微信订阅号返回验证码，{$code} 请不要更改', 'b2' ),
        //     'type'    => 'text',
        //     'default'=>'验证码：{$code}，有效期5分钟。',
        // ) );
            
            
            
        }

    }

    $setting = new B2_Wechat_Login_Settings();
    $setting->init();
endif;