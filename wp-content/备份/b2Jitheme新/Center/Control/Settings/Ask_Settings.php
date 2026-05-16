<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Ask
{
    //默认设置项
    public static $default_settings = [
        'ask_title'=>'总有个人知道您问题的答案',
        'title'=>'答案',
        
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
            'name'    => __( '是否开启分类栏', 'b2' ),
            'desc'    => __( '问答聚合页面右侧会有一个分类侧栏。', 'b2' ),
            'id'      =>  'cat',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'          => 0,
        ));
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
            'default'          => self::$default_settings['ask_title'],
        ));
        $Ask->add_field(array(
            'name' => __('侧栏头部连接','b2'),
            'id'   => 'title',
            'type' => 'text',
            'desc'    => __( '侧栏上方的全部问答连接名称,他是直接连接/ask这个地址的', 'b2' ),
            'default'          => self::$default_settings['title'],
        ) );
        $Ask->add_field(array(
            'name' => __('侧栏底部连接','b2'),
            'id'   => 'cat_db',
            'type' => 'textarea',
            'default' =>'',
            'desc'    => __( '代码实例 <span class="red">https://www.jitheme.com/shop/1306.html|jitheme jitheme-switch-line|买子主题</span>，没有侧留空,注意填写标准,多个连接请换行<br>注意：中间有个“|”作为分隔符的，书写规范： <span class="red">连接  |  图标  |  连接文字</span>，注意图标的用法，可以自己定义iconfont的样式', 'b2' ),
        ) );
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
