<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Diy
{
    //默认设置项
    public static $default_settings = [

    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Ji_Diy_tab1']);
    }
    //构造页面功能参数
    public function Ji_Diy_tab1()
    {
        $Diy= new_cmb2_box([
            'id'           => 'ji_diy_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_diy_main',
            'tab_group'    => 'b2_Jitheme_diy_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('极主题自定义','b2'),
            'menu_title'   => __('极主题自定义','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ]);
        $Diy->add_field([
            'name' => __('轮播图展示', 'b2'),
            'id' => 'dim_openq',
            'type' => 'select',
            // 'default'          => self::$default_settings['dim_open'],
            'options' => [
                1 => __('开启', 'b2'),
                0 => __('关闭', 'b2'),
            ],
            // 'description'=>'开启轮播部分内容，增加轮播与静态图',
        ]);
        self::Ji_Diy_tab2();
    }
    public function Ji_Diy_tab2(){
        $Diy= new_cmb2_box( array(
            'id'           => 'Ji_Diy_tab2',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'Ji_Diy_tab2',
            'tab_title'    => __('TAB2','b2'),
            'parent_slug'  => 'Ji_Diy_tab2',
            'tab_group'    => 'ji_diy_options',
            
        ) );
        $Diy->add_field([
            'name' => __('轮播图展示', 'b2'),
            'id' => 'dim_openqa',
            'type' => 'select',
            // 'default'          => self::$default_settings['dim_open'],
            'options' => [
                1 => __('开启', 'b2'),
                0 => __('关闭', 'b2'),
            ],
            // 'description'=>'开启轮播部分内容，增加轮播与静态图',
        ]);
    }
}
$list_Diy = new Diy();
$list_Diy->init();
