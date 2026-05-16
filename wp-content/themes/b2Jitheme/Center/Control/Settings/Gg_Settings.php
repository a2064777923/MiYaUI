<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Gg
{
    //默认设置项
    public static $default_settings = [

    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Ji_Gg_tab1']);
    }
    //构造页面功能参数
    public function Ji_Gg_tab1()
    {
        $Diy= new_cmb2_box([
            'id'           => 'ji_gg_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_gg_main',
            'tab_group'    => 'b2_Jitheme_gg_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('自助广告(待更新)','b2'),
            'menu_title'   => __('自助广告(待更新)','b2'),
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
        $Diy2= new_cmb2_box( array(
            'id'           => 'Ji_gg_tab2',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'Ji_Gg_tab2',
            'tab_title'    => __('TAB2','b2'),
            'parent_slug'  => 'Ji_ggtab2',
            'tab_group'    => 'ji_gg_options',
            
        ) );
        $Diy2->add_field([
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
$list_Diy = new Gg();
$list_Diy->init();
