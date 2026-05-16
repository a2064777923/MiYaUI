<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Shop
{
    //默认设置项
    public static $default_settings = [
        //Shop
        'shop_img_off'=>1,
        'shop_list_img'=>'4/3',
        
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_shop_page']);
    }
    //构造页面功能参数
    public function Jitheme_shop_page(){
        $Jitheme_shop = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_shop_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_shop_main',
            'tab_group'    => 'b2_Jitheme_shop_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('商城设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Jitheme_shop->add_field(array(
            'name'    => __( '是否启用自定义缩略图尺寸', 'b2' ),
            'desc'    => __( '调整商城缩略图的比例，原始为1/1。', 'b2' ),
            'id'      =>  'shop_img_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'=>self::$default_settings['shop_img_off']
        ));
        $Jitheme_shop->add_field(array(
            'name'    =>  __( '商城列表缩略图背景比例', 'b2' ),
            'id'=>'shop_list_img',
            'type'=>'text',
            'default'=>self::$default_settings['shop_list_img']
        ));
    }
}
$list_Shop = new Shop();
$list_Shop->init();