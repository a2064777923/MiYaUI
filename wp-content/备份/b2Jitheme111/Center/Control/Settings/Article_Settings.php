<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Archive
{
    //默认设置项
    public static $default_settings = [
        'onecad_Archive_list_color'=>'',
        'text_footer_desc_ysa'=>'极主题-7B2主题的子主题-超简洁-超美观-完美后台的子主题',
        'onecad_padding'=>'16',
        'off'=>0,
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_archive_page']);
    }
    //构造页面功能参数
    public function Jitheme_archive_page(){
        $jitheme_archive = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_Archive_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_Archive_main',
            'tab_group'    => 'b2_Jitheme_Archive_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('分类设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $jitheme_archive->add_field(array(
            'before_row'=>'<h2>分类列表图标</h2>',
            'name'    => __( '是否显示分类小图标', 'b2' ),
            'desc'    => __( '分类页中分类名称前面会有一个好看的图标/图片,目前使用的分类页无效，分类页样式备份样式。', 'b2' ),
            'id'      =>  'Archive_ico_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
            'default'=>self::$default_settings['off'],
        ));
        $jitheme_archive->add_field(array(
            'before_row'=>'<h2>分类页面</h2>',
            'name'    => __( '是否显示分类页顶部大背景图片', 'b2' ),
            'desc'    => __( '分类导航上方会有个漂亮的大背景图片,目前使用的分类页无效，分类页样式备份样式。', 'b2' ),
            'id'      =>  'Archive_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
            
        ));
        $jitheme_archive->add_field(array(
            'name'    => __( '分类页顶部大背景图片', 'b2' ),
            'id'=>'Archive_image',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空','b2'),
            'default'=>'',
        ));
        $jitheme_archive->add_field(array(
            'name' => __('分类副标题','b2'),
            'id'   => 'onecad_Archive_desc',
            'type' => 'text',
            'desc'    => sprintf(__( '分类名称下面的副标题', 'b2' )),
            'default'          => self::$default_settings['text_footer_desc_ysa'],
        ));
        $jitheme_archive->add_field(array(
            'before_row'=>'<h2>列表布局</h2>',
            'name'    => __( '是否显示列表渐变背景', 'b2' ),
            'desc'    => __( '分类文章列表有一个漂亮的渐变背景。', 'b2' ),
            'id'      =>  'Onecad_listbj_off',
            'type'             => 'select',
            'options'          => array(
                2 => __( '排除文章列表', 'b2' ),
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $jitheme_archive->add_field(array(
            'name'    => __( '统一渐变背景颜色', 'b2' ),
            'desc'    => __( '分类文章列表有一个漂亮的渐变背景。', 'b2' ),
            'id'      =>  'Onecad_tybj_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '同一色', 'b2' ),
                0   => __( '随机色', 'b2' ),
            ),
            'desc'    => '如果您选择统一颜色，请在下方的颜色代码中填写渐变代码',
        ));
        $jitheme_archive->add_field(array(
            'name' => __('列表背景渐变颜色代码','b2'),
            'id'   => 'onecad_Archive_list_color',
            'type' => 'text',
            'desc'    => sprintf(__( '您可以到此网站去查看渐变色代码,复制代码粘贴此处即可<a target="_blank" href="http://color.oulu.me/">渐变色代码</a>', 'b2' )),
            'default'  => self::$default_settings['onecad_Archive_list_color'],
        ));
    }
}
$list_Archive = new Archive();
$list_Archive->init();
