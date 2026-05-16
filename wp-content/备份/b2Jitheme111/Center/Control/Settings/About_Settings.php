<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class About
{
    //默认设置项
    public static $default_settings = [
        //关于我们
        'one_about_image'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'text_about'=>'终生会员限时特惠，错过等一年！',
        'text_about_desc'=>'庆祝极主题网正式上线！即日起开通年卡会员即可下载子主题源代码,不加密,无授权控制,放心下载放心使用,最牛的是你可以改成自己的子主题',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_about_page']);
    }
    //构造页面功能参数
    public function Jitheme_about_page(){
        $About = new_cmb2_box([
           'id'           => 'b2_Jitheme_about_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_about_main',
            'tab_group'    => 'b2_Jitheme_about_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('关于我们设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ]);
        $About->add_field([
            'name' => __('是否开启会员提醒', 'b2'),
            'id' => 'off',
            'type' => 'select',
            'options' => [
                1 => __('开启', 'b2'),
                0 => __('关闭', 'b2'),
            ],
            'desc'    => __( '设置内容请到<a href="/wp-admin/admin.php?page=b2_Mini_header"><span class="red">导航设置</span></a>页面设置', 'b2' ),
        ]);
        $About->add_field(array(
            'before_row'=>'<h2>关于我们 - 联系头部</h2>',
            'name' => __('联系框标题','b2'),
            'id'   => 'about_title',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('联系框描述','b2'),
            'id'   => 'about_desc',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name'    => __( '头部背景图片', 'b2' ),
            'id'=>'about_bg',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空','b2'),
            'default'=>'',
        ));
        $About->add_field(array(
            'name'    => __( '联系人微信二维码', 'b2' ),
            'id'=>'about_user_img',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空','b2'),
            'default'=>'',
        ));
        $About->add_field(array(
            'name'    => __( '联系人照片', 'b2' ),
            'id'=>'about_user_tx',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空','b2'),
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('联系微信号','b2'),
            'id'   => 'about_wx',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('联系人QQ','b2'),
            'id'   => 'about_qq',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('联系方式1说明','b2'),
            'id'   => 'about_sma',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('联系方式2说明','b2'),
            'id'   => 'about_smb',
            'type' => 'text',
            'default'=>'',
        ));
        
        $About->add_field(array(
            'before_row'=>'<h2>关于我们 - 区块部分</h2>',
            'name' => __('区块总标题','b2'),
            'id'   => 'about_quk_title',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('区块总描述','b2'),
            'id'   => 'about_quk_desc',
            'type' => 'text',
            'default'=>'',
        ));
        $about_qukuai_list = $About->add_field( array(
            'id'          => 'about_qukuai_list',
            'type'        => 'group',
            'description' => __('关于我们 - 头部区块 <span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加区块', 'b2' ),
                'remove_button'     => __( '删除区块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $About->add_group_field($about_qukuai_list, array(
            'name' => __('区块标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'name'    => __( '选择采用ICO图标/图片', 'b2' ),
            'desc'    => __( '用户可以选择用图片或者还是用ICO代码图标<br><span class="red">建议使用ICO图标,很漂亮的</span>', 'b2' ),
            'id'      =>  'ico_img',
            'type'    => 'radio_inline',
            'classes' => 'model-picked',
            'options' => array(
                1 => __( '图片', 'b2' ),
                0 => __( 'ICO图标', 'b2' ),
            ),
            'default' => 1, // 将默认值设置为1
        ));
        $About->add_group_field($about_qukuai_list, array(
            'before_row'=>'<div class="sliders-module cmb-row  1-module set-hidden">',
            'name' => __('区块图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => '',
            'desc'=>'必须选择采用图片方式才可以显示，否则失效',
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'before_row'=>'</div><div class="html-module cmb-row 0-module set-hidden">',
            'name' => __('图标前缀','b2'),
            'id'   => 'img_xmmc',
            'type' => 'text',
            'default'=> 'jitheme',
            'desc'=>sprintf(__( '必须<span class="red">%s</span>，<code class="red">jitheme</code>为此行内容，必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a><br><span class="red">上方必须选择ICO图标否则失效</span>', 'b2' ),'<code>'.htmlspecialchars('<i class="jitheme ji-mail-send-line"></i>').'</code>'),
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'name' => __('图标名称','b2'),
            'id'   => 'qukuai_img_ico',
            'type' => 'text',
            'default'=> 'ji-gallery-line',
            'desc'=>sprintf(__( '必须<span class="red">%s</span>，<code class="red">ji-mail-send-line</code>为此行内容，必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a><br><span class="red">上方必须选择ICO图标否则失效</span>', 'b2' ),'<code>'.htmlspecialchars('<i class="jitheme ji-mail-send-line"></i>').'</code>'),
            'after_row'=>'</div>',
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'name' => __('区块连接','b2'),
            'id'   => 'index_qukuai_links',
            'type' => 'text',
            'default'          => '',
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'name' => __('区块标题后缀','b2'),
            'id'   => 'index_qukuai_title_hz',
            'desc'    => __( '标题后方的角标,为空则不显示。', 'b2' ),
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_qukuai_list, array(
            'name'    => __( '区块后缀背景色', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色。', 'b2' ),
            'id'=>'index_qukuai_title_hz_color',
            'type'=>'colorpicker',
            'default' => '',
        ));
        $About->add_group_field($about_qukuai_list, array(
            'name' => __('区块描述','b2'),
            'id'   => 'index_qukuai_desc',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_field(array(
            'before_row'=>'<h2>关于我们 - 左大右2小</h2>',
            'name' => __('模块标题','b2'),
            'id'   => 'team_title',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('模块描述','b2'),
            'id'   => 'team_desc',
            'type' => 'text',
            'default'=>'',
        ));
        $about_team_list = $About->add_field( array(
            'id'          => 'about_team_list',
            'type'        => 'group',
            'description' => __('关于我们 - 左大右2小 <span class="red">注意：每个模块的必填项必须填写，否则无法保存，最多3个</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加区块', 'b2' ),
                'remove_button'     => __( '删除区块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $About->add_group_field($about_team_list, array(
            'name' => __('团队Key','b2'),
            'id'   => 'Key',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_team_list, array(
            'name' => __('团队标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_team_list, array(
            'name' => __('团队图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => '',
            'desc'=>'选择一个图片',
        ) );
        $About->add_group_field($about_team_list, array(
            'name' => __('团队描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          =>'',
        ) );
        $about_liea_list = $About->add_field( array(
            'id'          => 'about_liea_list',
            'type'        => 'group',
            'description' => __('关于我们 - 左1的三个小区块 <span class="red">注意：每个模块的必填项必须填写，否则无法保存，最多3个</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加区块', 'b2' ),
                'remove_button'     => __( '删除区块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $About->add_group_field($about_liea_list, array(
            'name' => __('服务标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_liea_list, array(
            'name' => __('服务图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => '',
            'desc'=>'选择一个图片',
        ) );
        $About->add_group_field($about_liea_list, array(
            'name' => __('服务描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_field(array(
            'before_row'=>'<h2>关于我们 - 服务流程</h2>',
            'name' => __('模块标题','b2'),
            'id'   => 'serve_title',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('模块描述','b2'),
            'id'   => 'serve_desc',
            'type' => 'text',
            'default'=>'',
        ));
        $about_serve_list = $About->add_field( array(
            'id'          => 'about_serve_list',
            'type'        => 'group',
            'description' => __('关于我们 - 服务流程 <span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加区块', 'b2' ),
                'remove_button'     => __( '删除区块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $About->add_group_field($about_serve_list, array(
            'name' => __('服务标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_serve_list, array(
            'name' => __('服务图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => '',
            'desc'=>'选择一个图片',
        ) );
        $About->add_group_field($about_serve_list, array(
            'name' => __('服务描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_field(array(
            'before_row'=>'<h2>关于我们 - 合作伙伴</h2>',
            'name' => __('模块标题','b2'),
            'id'   => 'hezuo_title',
            'type' => 'text',
            'default'=>'',
        ));
        $About->add_field(array(
            'name' => __('模块描述','b2'),
            'id'   => 'hezuo_desc',
            'type' => 'text',
            'default'=>'',
        ));
        $about_hezuo_list = $About->add_field( array(
            'id'          => 'about_hezuo_list',
            'type'        => 'group',
            'description' => __('关于我们 - 合作伙伴 <span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加区块', 'b2' ),
                'remove_button'     => __( '删除区块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $About->add_group_field($about_hezuo_list, array(
            'name' => __('合作公司标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          =>'',
        ) );
        $About->add_group_field($about_hezuo_list, array(
            'name' => __('合作公司标志','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => '',
            'desc'=>'选择一个图片',
        ) );
        $About->add_group_field($about_hezuo_list, array(
            'name' => __('合作公司简介','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          =>'',
        ) );
        
    }
}
$list_About = new About();
$list_About->init();
