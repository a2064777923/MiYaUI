<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Diy
{
    //默认设置项
    public static $default_settings = [
        'dim_open' => 0,
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'B2_Jitheme_diylist']);
    }
    //构造页面功能参数
    public function B2_Jitheme_diylist()
    {
        $Modular = new_cmb2_box([
            'id' => 'b2_Jitheme_diy_page',
            'object_types' => ['options-page'],
            'option_key' => 'Jitheme_diy',
            'tab_group' => 'Jitheme_diy_options',
            'parent_slug' => 'Jitheme',
            'tab_title' => __('极主题自定义', 'b2'),
            'menu_title' => __('自定义设置组', 'b2'),
        ]);
        $Modular->add_field([
            'name' => __('轮播图展示', 'b2'),
            'id' => 'dim_open',
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
