<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Ask
{
    //默认设置项
    public static $default_settings = [

    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Ji_Ask_tab1']);
    }
    //构造页面功能参数
    public function Ji_Ask_tab1()
    {
        $Ask= new_cmb2_box([
            'id'           => 'ji_Ask_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_Ask_main',
            'tab_group'    => 'b2_Jitheme_Ask_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('问答设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ]);
        $Ask->add_field(array(
            'before_row'=>'<h2>问答首页</h2>',
            'name'    => __( '问答首页幻灯片短代码', 'b2' ),
            'desc'    => __( '可以给问答增开一个幻灯片的搜索展示模块，幻灯片为B2首页增加幻灯片模块，并且采用段代码调用，将段代码粘贴到此处即可。', 'b2' ),
            'id'      =>  'ask-slider',
            'type' => 'text',
        ));
        $Ask->add_field(array(
            'name'    => __( '问答标题', 'b2' ),
            'desc'    => __( '搜索栏上显示的一个标题。', 'b2' ),
            'id'      =>  'ask-title',
            'type' => 'text',
        ));
        self::Ji_Ask_tab2();
    }
    public function Ji_Ask_tab2(){
        $Ask= new_cmb2_box( array(
            'id'           => 'Ji_Ask_tab2',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'Ji_Ask_tab2',
            'tab_title'    => __('TAB2','b2'),
            'parent_slug'  => 'Ji_Ask_tab2',
            'tab_group'    => 'ji_Ask_options',
            
        ) );
        $Ask->add_field([
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
$list_Ask = new Ask();
$list_Ask->init();
