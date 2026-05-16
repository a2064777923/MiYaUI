<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Circle
{
    //默认设置项
    public static $default_settings = [
        'dim_open' => 0,
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_circle_page']);
    }
    //构造页面功能参数
    public function Jitheme_circle_page(){
        $Jitheme_circles = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_circle_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_circle_main',
            'tab_group'    => 'b2_Jitheme_circle_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('圈子设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Jitheme_circles->add_field(array(
            'before_row'=>'<h2>圈子首页</h2>',
            'name'    => __( '是否开启圈子头部幻灯片', 'b2' ),
            'desc'    => __( '可以增加圈子发布框上方的短代码调用功能。', 'b2' ),
            'id'      =>  'index_circles_top_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_circles->add_field(array(
            'name' => __('顶部图片切换','b2'),
            'id'   => 'index_circles_top_img',
            'type' => 'text',
            'default'          =>'',
            'desc'    => __( '输入一个短代码。', 'b2' ),
        ) );
        $Jitheme_circles->add_field(array(
            'name' => __('发布框下方广告','b2'),
            'id'   => 'index_circles_ct_img',
            'type' => 'text',
            'desc'    => __( '输入一个短代码。', 'b2' ),
            'default'          => '',
        ) );
    }
}
$list_Circle = new Circle();
$list_Circle->init();
