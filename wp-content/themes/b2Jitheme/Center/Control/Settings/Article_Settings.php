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
        'one_header_image_mr'=>B2_CHILD_URI.'/Center/Assets/images/confetti_wqhed2_o20.png',
        'onecad_padding'=>'16',
        'title'=>'极主题',
        'off'=>0,
        'Archive_list_ico'=>B2_CHILD_URI.'/Center/Assets/images/confetti_wqhed2_o20.png',
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
            'before_row'=>'<h2>分类列表样式</h2>',
            'name'    => __( '设置分类样式', 'b2' ),
            'desc'    => __( '分类列表项在左侧。', 'b2' ),
            'id'      =>  'Archive_list',
            'type'             => 'select',
            'options'          => array(
                1 => __( '上下结构', 'b2' ),
                0   => __( '左右结构', 'b2' ),
            ),
            'default'=>self::$default_settings['off'],
        ));
        // $jitheme_archive->add_field(array(
        //     'before_row'=>'<h2>分类页面</h2>',
        //     'name'    => __( '是否显示分类页顶部大背景图片', 'b2' ),
        //     'desc'    => __( '分类导航上方会有个漂亮的大背景图片,目前使用的分类页无效，分类页样式备份样式。', 'b2' ),
        //     'id'      =>  'Archive_off',
        //     'type'             => 'select',
        //     'options'          => array(
        //         1 => __( '显示', 'b2' ),
        //         0   => __( '隐藏', 'b2' ),
        //     ),
            
        // ));
        $jitheme_archive->add_field(array(
            'name'    => __( '分类页顶部大背景图片', 'b2' ),
            'id'=>'Archive_list_ico',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片，注：左右结构不生效<br><span class="red">'.self::$default_settings['one_header_image_mr'].'</span>','b2'),
            'default'=>'',
        ));
        $jitheme_archive->add_field(array(
            'name'    => __( '分类页顶部大背景图片', 'b2' ),
            'id'=>'Archive_image',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片，注：左右结构不生效<br><span class="red">'.self::$default_settings['one_header_image_mr'].'</span>','b2'),
            'default'=>'',
        ));
        $jitheme_archive->add_field(array(
            'before_row'=>'<h2>左右结构TOP设置项</h2>',
            'name' => __('分类主标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['title'],
            'desc'    => __( '左右结构才能显示。', 'b2' ),
        ));
        $jitheme_archive->add_field(array(
            'name' => __('分类副标题','b2'),
            'id'   => 'onecad_Archive_desc',
            'type' => 'text',
            'desc'    => sprintf(__( '分类名称下面的副标题，不显示则留空', 'b2' )),
            'default'          => self::$default_settings['text_footer_desc_ysa'],
            
        ));
       $left_top_gg = $jitheme_archive->add_field( array(
            'id'          => 'left_top_gg',
            'type'        => 'group',
            'description' => __('添加推荐链接<span class="red">注意：每个模块的必填项必须填写，否则无法保存,最多3个</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加链接-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加链接', 'b2' ),
                'remove_button'     => __( '删除链接', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_archive->add_group_field($left_top_gg, array(
            'name' => __('链接图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => ''
        ) );
        $jitheme_archive->add_group_field($left_top_gg, array(
            'name' => __('链接标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => ''
        ) );
        $jitheme_archive->add_group_field($left_top_gg, array(
            'name' => __('超级链接','b2'),
            'id'   => 'links',
            'type' => 'text',
            'default'          => ''
        ) );
        $jitheme_archive->add_field(array(
            'before_row'=>'<h2>左右结构设置项</h2>',
            'name'    => __( '公众号二维码', 'b2' ),
            'id'=>'Archive_gzhimg',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片<br><span class="red">'.self::$default_settings['one_header_image_mr'].'</span>','b2'),
            'default'=>'',
        ));
       $jitheme_left_fl = $jitheme_archive->add_field( array(
            'id'          => 'left_fl',
            'type'        => 'group',
            'description' => __('添加推荐链接<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加链接-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加链接', 'b2' ),
                'remove_button'     => __( '删除链接', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_archive->add_group_field($jitheme_left_fl, array(
            'name' => __('链接图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => ''
        ) );
        $jitheme_archive->add_group_field($jitheme_left_fl, array(
            'name' => __('链接标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => ''
        ) );
        $jitheme_archive->add_group_field($jitheme_left_fl, array(
            'name' => __('超级链接','b2'),
            'id'   => 'links',
            'type' => 'text',
            'default'          => ''
        ) );
        $jitheme_archive->add_group_field($jitheme_left_fl, array(
            'name' => __('链接描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          => ''
        ) );
        $jitheme_archive->add_field(array(
            'name' => __('公众号名称','b2'),
            'id'   => 'gzh_title',
            'type' => 'text',
            'default'          => self::$default_settings['title'],
        ));
        $jitheme_archive->add_field(array(
            'name' => __('公众号描述','b2'),
            'id'   => 'gzh_desc',
            'type' => 'text',
            'desc'    => sprintf(__( '分类名称下面的副标题，不显示则留空', 'b2' )),
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
