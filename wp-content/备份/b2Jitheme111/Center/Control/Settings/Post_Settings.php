<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Post
{
    //默认设置项
    public static $default_settings = [
        //文章内页设置
        'index_Single_title'=>'免费,VIP,推荐,原创,热门,精品',
        'Single_desc'=>'站长很懒，还没有对专题写描述呢！没办法，我是极主题!。',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_post_page']);
    }
    //构造页面功能参数
    public function Jitheme_post_page(){
        $Jitheme_Single = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_post_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_post_main',
            'tab_group'    => 'b2_Jitheme_post_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('文章内页设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Jitheme_Single->add_field(array(
            'name'    => __( '文章页头部下载模块美化', 'b2' ),
            'desc'    => __( '文章下载页将会有个漂亮的下载模块显示。', 'b2' ),
            'id'      =>  'Single_down',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_Single->add_field(array(
            'name'    => __( '是否开启首页列表角标', 'b2' ),
            'desc'    => __( '首页文章列表显示一个漂亮的角标。', 'b2' ),
            'id'      =>  'Single_jiaobiao',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_Single->add_field(array(
            'name' => __('角标分类列表','b2'),
            'id'   => 'index_Single_title',
            'type' => 'text',
            'desc'    => __( '注意：最多6个分类,输入方式为<span class="red">角标,角标,角标......</span>之间使用英文逗号隔开,超出不显示', 'b2' ),
            'default'          => self::$default_settings['index_Single_title'],
        ) );
        $Jitheme_Single->add_field(array(
            'name' => __('默认文章摘要描述','b2'),
            'id'   => 'Single_desc',
            'type' => 'textarea',
            'desc'    => __( '如开启了描述显示，如果文章没有描述，则显示此内容', 'b2' ),
            'default'          => self::$default_settings['Single_desc'],
        ) );
        $Jitheme_Single->add_field(array(
            'name'    => __( '是否启用文章版权声明', 'b2' ),
            'desc'    => __( '文章底部会有一个极主题自定义的一个版权声明。', 'b2' ),
            'id'      =>  'Single_banquan',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'          => 1,
        ));
        $Jitheme_Single->add_field(array(
            'name' => __('文章内页的版权声明','b2'),
            'id'   => 'Single_wysiwyg',
            'type' => 'textarea_code',
            'desc'    => __( '注意：不要再可视化编辑器中编辑，否则部分标签无法保存', 'b2' ),
            'default'          =>'',
        ) );
        self::Jitheme_post_page2();
    }
    public function Jitheme_post_page2(){
        $Jitheme_Single_gg = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_post_options_gg',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_post_tab2',
            'tab_title'    => __('文章内页广告','b2'),
            'parent_slug'  => 'b2_Jitheme_post_tab2',
            'tab_group'    => 'b2_Jitheme_post_options',
            
        ) );
        $Jitheme_Single_gg->add_field(array(
            'name'    => __( '文章头部广告', 'b2' ),
            'desc'    => __( '广告代码 <span class="red">纯HTML代码</span>，没有侧留空', 'b2' ),
            'id'=>'Single_topgg',
            'type' => 'textarea_code',
            'default'=>'',
        ));
        $Jitheme_Single_gg->add_field(array(
            'name'    => __( '文章底部广告', 'b2' ),
            'desc'    => __( '广告代码 <span class="red">纯HTML代码</span>，没有侧留空', 'b2' ),
            'id'=>'Single_dowgg',
            'type' => 'textarea_code',
            'default'=>'',
        ));
    }
}
$list_Post = new Post();
$list_Post->init();
