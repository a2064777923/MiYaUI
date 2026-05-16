<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Footer
{
    //默认设置项
    public static $default_settings = [
        //底部样式2
        'onecad_footer_off'=>1,
        'onecad_footer_kefu'=>'010-123456789',
        'onecad_footer_email'=>'123456789@qq.com',
        'onecad_footer_title'=>'帮助中心',
        'onecad_footer_link'=>'https://www.jitheme.com/',
        'onecad_footer_qq'=>'1234567890',
        'onecad_footer_jiandu'=>'010-123456789',
        'onecad_footer_jiandu_yx'=>'123456789@qq.com',
        'onecad_footer_ysb_ewm_ico'=>B2_CHILD_URI.'/Center/Assets/images/ewm.svg',
        'onecad_footer_ysb_htsmz'=>'扫码添加粉丝群，获取更多活动资讯',
        'onecad_footer_ysb_htsmf'=>'获取更多优惠信息请扫右侧二维码 >>',
        'onecad_footer_ysb_qewm'=>B2_CHILD_URI.'/Center/Assets/images/onecad_qewm.png',
        'onecad_footer_ysb_bg'=>B2_CHILD_URI.'/Center/Assets/images/links_search_img.jpg',  
        'footer_color'=>'#ffffff',
        'footer_text_color'=>'#121212',
        'footer_nav_color'=>'#ffffff',
        'footer_img'=>B2_CHILD_URI.'/Center/Assets/images/footer-bg.svg',
        //底部样式1
        'footer_desc_ysa_kf_img'=>B2_CHILD_URI.'/Center/Assets/images/kefu.png',
        'footer_desc_ysa_kf_name'=>'<h4>职业介绍</h4><h4>名字</h4>',
        'footer_desc_ysa_kf_desc'=>'<p>极主题-7B2主题的子主题-超简洁-超美观-完美后台的子主题</p>',
        'footer_desc_ysa_kf_title'=>'极主题 jitheme.com',
        'text_footer_title_ysa'=>'关于我们 Jitheme',
        'text_footer_desc_ysa_sz'=>'1000W',
        'text_footer_desc_ysa_ms'=>'极主题等你来关注',
        'text_footer_desc_ysa_qqm'=>'极主题官方QQ',
        'text_footer_desc_ysa_qqun'=>'群面板中查看分享链接',
        'text_footer_desc_ysa'=>'极主题-7B2主题的子主题-超简洁-超美观-完美后台的子主题',
        'ysa_links_title'=>'支持与服务',
        'ysa_links_desc'=>'<li><a href="https://www.jitheme.com/" target="_blank">极主题</a> </li> ',
        'ysa_kefu_title'=>'QQ/微信',
        'ysa_kefu_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
        'ysa_kefu_desc'=>'<h4>极主题,超级好看,开源,可自定义的子主题</h4>',
        'ysa_kefu_url'=>'<a href="https://www.jitheme.com/" target="_blank">极主题</a>',
        'ysa_gg_url'=>'https://www.jitheme.com',
        'ysa_gg_title'=>'极主题',
        'ysa_gg_img'=>B2_CHILD_URI.'/Center/Assets/images/logo.svg',
        'index_qukuai_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_footer_page']);
    }
    //构造页面功能参数
    public function Jitheme_footer_page(){
        $jitheme_footer = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_footer_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_footer_main',
            'tab_group'    => 'b2_Jitheme_footer_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('综合设置','b2'),
            'menu_title'   => __('底部设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $jitheme_footer->add_field(array(
            'name'    => __( '选择一个底部样式', 'b2' ),
            'desc'    => __( '本子主题共提供2种底部文件选择。', 'b2' ),
            'id'      =>  'onecad_footer_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '黑色模式', 'b2' ),
                0   => __( '白色模式', 'b2' ),
                2 => __( '简洁模式', 'b2' ),
            ),
            'default'=> self::$default_settings['onecad_footer_off'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '底部第一层背景图片', 'b2' ),
            'id'=>'footer_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=>__('如果不设置，请填写<span class="red">none</span>后保存<br>也可使用子主题推荐的背景图片<span class="red">'.self::$default_settings['footer_img'].'</span>','b2'),
            'default'          => self::$default_settings['footer_img'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '底部H标题颜色', 'b2' ),
            'id'=>'footer_h_color',
            'type'             => 'colorpicker',
            'default'          => self::$default_settings['footer_text_color'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '底部背景颜色', 'b2' ),
            'id'=>'footer_color',
            'type'             => 'colorpicker',
            'default'          => self::$default_settings['footer_color'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '底部文字颜色', 'b2' ),
            'id'=>'footer_text_color',
            'type'             => 'colorpicker',
            'default'          => self::$default_settings['footer_text_color'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '客服电话', 'b2' ),
            'desc'    => __( '客服电话。', 'b2' ),
            'id'      =>'onecad_footer_kefu',
            'type' => 'text',
            'default'          => self::$default_settings['onecad_footer_kefu'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '客服邮箱', 'b2' ),
            'desc'    => __( '客服邮箱。', 'b2' ),
            'id'      =>'onecad_footer_email',
            'type' => 'text',
            'default'          =>self::$default_settings['onecad_footer_email'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( 'QQ在线客服', 'b2' ),
            'desc'    => __( '请输入您的QQ号。', 'b2' ),
            'id'      =>'onecad_footer_qq',
            'type' => 'text',
            'default'          =>self::$default_settings['onecad_footer_qq'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '举报电话', 'b2' ),
            'desc'    => __( '举报监督电话。', 'b2' ),
            'id'      =>'onecad_footer_jiandu',
            'type' => 'text',
            'default'          =>self::$default_settings['onecad_footer_jiandu'],
        ));
        $jitheme_footer->add_field(array(
            'name'    => __( '举报邮箱', 'b2' ),
            'desc'    => __( '请输入您的邮箱。', 'b2' ),
            'id'      =>'onecad_footer_jiandu_yx',
            'type' => 'text',
            'default'          =>self::$default_settings['onecad_footer_jiandu_yx'],
        ));
        $jitheme_footer_ggz = $jitheme_footer->add_field( array(
            'id'          => 'onecad_footer_ggz',
            'type'        => 'group',
            'description' => __('添加图片广告组：建议不要超过6组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '图片广告组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新图片广告组', 'b2' ),
                'remove_button'     => __( '删除图片广告组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个广告组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_footer->add_group_field($jitheme_footer_ggz, array(
            'name' => __('图片广告组标题','b2'),
            'id'   => 'onecad_footer_ggz_title',
            'type' => 'text',
            'default' => self::$default_settings['ysa_gg_title'],
        ) );
        $jitheme_footer->add_group_field($jitheme_footer_ggz, array(
            'name' => __('图片广告组链接','b2'),
            'id'   => 'onecad_footer_ggz_url',
            'type' => 'text',
            'default' => self::$default_settings['ysa_gg_url'],
        ) );
        $jitheme_footer->add_group_field($jitheme_footer_ggz, array(
            'name' => __('图片LOGO展示','b2'),
            'id'   => 'onecad_footer_ggz_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['ysa_gg_img'],
        ) );
        self::Jitheme_footer_settings_ysa();
        self::Jitheme_footer_settings_ysb();
        self::Jitheme_footer_jj();
        self::Jitheme_footer_celan();
        self::Jitheme_footer_tsk();
    }
    public function Jitheme_footer_settings_ysa() {
        $jitheme_footer_ysa= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_footer_settings_ysa',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_footer_tab2', // The option key and admin menu page slug.            
            'tab_title'    => __('黑色模式','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_footer_tab2',
            'tab_group'    => 'b2_Jitheme_footer_options',
        ) );
        $jitheme_footer_ysa->add_field(array(
            //'before_row'=>'<img class="sort-config-icon" src="'.B2_CHILD_URI.'/Assets/Center/Assets/images/footer_ysa.png" alt="">',
            'name'    => __( '是否显示客户标语栏', 'b2' ),
            'desc'    => __( '一个漂亮的客户标语栏。', 'b2' ),
            'id'      =>  'Onecad_footer_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '客户标语栏背景颜色', 'b2' ),
            'id'=>'footer_fav_color',
            'type'             => 'colorpicker',
            'default'          => self::$default_settings['footer_text_color'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '客服介绍标题', 'b2' ),
            'desc'    => __( '比如：极主题 Jitheme.com', 'b2' ),
            'id'=>'footer_desc_ysa_kf_title',
            'type'=>'text',
            'default' => self::$default_settings['footer_desc_ysa_kf_title'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '客服介绍描述', 'b2' ),
            'desc'    => __( '比如：ONE · 壹家CAD图库-专注收集精品室内设计资料、提供室内设计资料,', 'b2' ),
            'id'=>'footer_desc_ysa_kf_desc',
            'type'=>'textarea',
            'default' => self::$default_settings['footer_desc_ysa_kf_desc'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签P标签是换行。', 'b2' ),'<code>'.htmlspecialchars('<p>标题</p><p>描述</p>').'</code>'),
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '客服名字', 'b2' ),
            'desc'    => __( '比如：小C', 'b2' ),
            'id'=>'footer_desc_ysa_kf_name',
            'type'=>'textarea',
            'default' => self::$default_settings['footer_desc_ysa_kf_name'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<h4>职业介绍</h4><h4>名字</h4>').'</code>'),
        ));
        $jitheme_footer_ysa->add_field(array(
            'name' => __('客服图片','b2'),
            'id'   => 'footer_desc_ysa_kf_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['footer_desc_ysa_kf_img'],
        ) );
        $img_logo = b2_get_option('normal_main','img_logo_white');
        $jitheme_footer_ysa->add_field(array(
            'name' => __('展示LOGO','b2'),
            'id'   => 'footer_desc_ysa_kf_logo',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'    => __( '比如调用网站深色LOGO：<span class="red">'.$img_logo.'</span>', 'b2' ),
        ) );
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '介绍标题', 'b2' ),
            'desc'    => __( '比如：关于我们', 'b2' ),
            'id'=>'text_footer_title_ysa',
            'type'=>'text',
            'default' => self::$default_settings['text_footer_title_ysa'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '介绍内容', 'b2' ),
            'desc'    => __( '比如：公司简介', 'b2' ),
            'id'=>'text_footer_desc_ysa',
            'type'=>'textarea',
            'default' => self::$default_settings['text_footer_desc_ysa'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '大标题数字', 'b2' ),
            'desc'    => __( '1000W', 'b2' ),
            'id'=>'text_footer_desc_ysa_sz',
            'type'=>'text',
            'default' => self::$default_settings['text_footer_desc_ysa_sz'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '大标题描述', 'b2' ),
            'desc'    => __( '比如：极主题等你来关注', 'b2' ),
            'id'=>'text_footer_desc_ysa_ms',
            'type'=>'text',
            'default' => self::$default_settings['text_footer_desc_ysa_ms'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '加群按钮名称', 'b2' ),
            'desc'    => __( '比如：Onecad官方QQ群', 'b2' ),
            'id'=>'text_footer_desc_ysa_qqm',
            'type'=>'text',
            'default' => self::$default_settings['text_footer_desc_ysa_qqm'],
        ));
        $jitheme_footer_ysa->add_field(array(
            'name'    => __( '官方QQ群链接', 'b2' ),
            'desc'    => __( '群面板中查看分享链接', 'b2' ),
            'id'=>'text_footer_desc_ysa_qqun',
            'type'=>'textarea',
            'default' => self::$default_settings['text_footer_desc_ysa_qqun'],
        ));
        $one_ysa_links = $jitheme_footer_ysa->add_field( array(
            'id'          => 'one_ysa_links',
            'type'        => 'group',
            'description' => __('添加底部链接组：建议不要超过4组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '链接组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新链接组', 'b2' ),
                'remove_button'     => __( '删除链接组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_footer_ysa->add_group_field($one_ysa_links, array(
            'name' => __('连接组标题','b2'),
            'id'   => 'ysa_links_title',
            'type' => 'text',
            'default' => self::$default_settings['ysa_links_title'],
        ) );
        $jitheme_footer_ysa->add_group_field($one_ysa_links, array(
            'name' => __('连接组内容','b2'),
            'id'   => 'ysa_links_desc',
            'type' => 'textarea',
            'default' => self::$default_settings['ysa_links_desc'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<li><a href="链接地址" target="_blank">链接名称</a></li>').'</code>'),
        ) );
        $one_ysa_kefu = $jitheme_footer_ysa->add_field( array(
            'id'          => 'one_ysa_kefu',
            'type'        => 'group',
            'description' => __('添加客服组：建议不要超过3组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '客服组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新客服组', 'b2' ),
                'remove_button'     => __( '删除客服组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个客服组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_footer_ysa->add_group_field($one_ysa_kefu, array(
            'name' => __('客服组图标','b2'),
            'id'   => 'ysa_kefu_title_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['ysa_kefu_img'],
        ) );
        $jitheme_footer_ysa->add_group_field($one_ysa_kefu, array(
            'name' => __('客服组标题','b2'),
            'id'   => 'ysa_kefu_title',
            'type' => 'text',
            'default' => self::$default_settings['ysa_kefu_title'],
        ) );
        $jitheme_footer_ysa->add_group_field($one_ysa_kefu, array(
            'name' => __('展示图片','b2'),
            'id'   => 'ysa_kefu_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['ysa_kefu_img'],
        ) );
        $jitheme_footer_ysa->add_group_field($one_ysa_kefu, array(
            'name' => __('客服组描述','b2'),
            'id'   => 'ysa_kefu_desc',
            'type' => 'textarea',
            'default' => self::$default_settings['ysa_kefu_desc'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<h4>极主题,超级好看,开源,可自定义的子主题</h4>').'</code>'),
        ) );
        $jitheme_footer_ysa->add_group_field($one_ysa_kefu, array(
            'name' => __('客服组链接','b2'),
            'id'   => 'ysa_kefu_url',
            'type' => 'textarea',
            'default' => self::$default_settings['ysa_kefu_url'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签,建议给一个就行。', 'b2' ),'<code>'.htmlspecialchars('<a href="客服链接" target="_blank">客服名称 </a>').'</code>'),
        ) );
    }
    public function Jitheme_footer_settings_ysb() {
        $jitheme_footer_ysb= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_footer_settings_ysb',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_footer_tab3', // The option key and admin menu page slug.            
        'tab_title'    => __('白色模式','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_footer_tab3',
        'tab_group'    => 'b2_Jitheme_footer_options',
        ) );
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '关闭底部导视栏', 'b2' ),
            'desc'    => __( '可以关闭白色模式下的图片导视模块。', 'b2' ),
            'id'      =>  'onecad_footer_ysb_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'=> self::$default_settings['onecad_footer_off'],
        ));
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '是否显示简洁模式', 'b2' ),
            'desc'    => __( '隐藏不必要的功能模块。', 'b2' ),
            'id'      =>  'onecad_footer_ysb_jj',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default' => 0,
        ));
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '是否显示伙伴和友链', 'b2' ),
            'desc'    => __( '隐藏不必要的功能模块。', 'b2' ),
            'id'      =>  'onecad_footer_ysb_yl',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default' => 0,
        ));
        $jitheme_footer_ysb->add_field(array(
            'name' => __('横条背景图片','b2'),
            'id'   => 'onecad_footer_ysb_bg',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_bg'],
        ) );
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '横条引导说明主标题', 'b2' ),
            'id'=>'onecad_footer_ysb_htsmz',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_ysb_htsmz'],
        ) );
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '横条引导说明副标题', 'b2' ),
            'id'=>'onecad_footer_ysb_htsmf',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_ysb_htsmf'],
        ) );
        $jitheme_footer_ysb->add_field(array(
            'name' => __('加群二维码','b2'),
            'id'   => 'onecad_footer_ysb_qewm',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_ewm_ico'],
        ) );
        $jitheme_footer_ysb_ewm = $jitheme_footer_ysb->add_field( array(
            'id'          => 'onecad_footer_ysb_ewm',
            'type'        => 'group',
            'description' => __('添加二维码组：建议不要超过2组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '二维码组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新二维码组', 'b2' ),
                'remove_button'     => __( '删除二维码组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个二维码组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_footer_ysb->add_group_field($jitheme_footer_ysb_ewm, array(
            'name' => __('二维码图标','b2'),
            'id'   => 'onecad_footer_ysb_ewm_ico',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_ewm_ico'],
        ) );
        $jitheme_footer_ysb->add_group_field($jitheme_footer_ysb_ewm, array(
            'name' => __('二维码组标题','b2'),
            'id'   => 'onecad_footer_ysb_ewm_title',
            'type' => 'text',
            'default' => self::$default_settings['ysa_links_title'],
        ) );
        $jitheme_footer_ysb->add_group_field($jitheme_footer_ysb_ewm, array(
            'name' => __('二维码图片','b2'),
            'id'   => 'onecad_footer_ysb_ewm_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['index_qukuai_img'],
        ) );
        $jitheme_footer_ysb_links = $jitheme_footer_ysb->add_field( array(
            'id'          => 'onecad_footer_ysb_links',
            'type'        => 'group',
            'description' => __('添加链接组：建议不要超过4组，不显示则全部删掉，为空即可','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '链接组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新链接组', 'b2' ),
                'remove_button'     => __( '删除链接组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $jitheme_footer_ysb->add_group_field($jitheme_footer_ysb_links, array(
            'name' => __('连接组内容','b2'),
            'id'   => 'onecad_footer_ysb_links_desc',
            'type' => 'textarea',
            'default' =>'',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<a href="链接地址" target="_blank">链接名称</a>').'</code>'),
        ) );
        $jitheme_footer_ysb->add_field(array(
            'name'    => __( '底部说明', 'b2' ),
            'desc'    => __( '比如：ONE · 壹家CAD图库-专注收集精品室内设计资料、提供室内设计资料,', 'b2' ),
            'id'=>'onecad_footer_ysb_sm',
            'type'=>'textarea',
            'default' => self::$default_settings['footer_desc_ysa_kf_desc'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签P标签是换行。', 'b2' ),'<code>'.htmlspecialchars('<p>标题</p><p>描述</p>').'</code>'),
        ));
    }
    public function Jitheme_footer_celan() {
        $Jitheme_footer_celan= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_footer_celan',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_footer_tab4', // The option key and admin menu page slug.            
        'tab_title'    => __('右侧跟随工具条','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_footer_tab4',
        'tab_group'    => 'b2_Jitheme_footer_options',
        ) );
        $Jitheme_footer_celan->add_field(array(
            'name'    => __( '是否开启右侧跟随工具条栏', 'b2' ),
            'desc'    => __( '子主题独立开发的右侧边栏。', 'b2' ),
            'id'      =>  'onecad_footer_celan_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_footer_celan->add_field(array(
            'before_row'=>'<div id="onecad_footer_celan" class="cmb-row">',
            'name'    => __( '是否显示VIP按钮', 'b2' ),
            'desc'    => __( '一个VIP按钮链接。', 'b2' ),
            'id'      =>  'onecad_footer_celan_vip',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_footer_celan_kf = $Jitheme_footer_celan->add_field( array(
            'id'          => 'Jitheme_footer_celan',
            'type'        => 'group',
            'description' => __('添加客服组：建议不要超过3组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '客服组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新客服组', 'b2' ),
                'remove_button'     => __( '删除客服组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个客服组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $Jitheme_footer_celan->add_group_field($Jitheme_footer_celan_kf, array(
            'name' => __('描述','b2'),
            'id'   => 'celan_kf_desc',
            'type' => 'text',
            'default' => '极主题，美化最好看的7B2子主题，现在购买尊享优惠',
        ) );
        $Jitheme_footer_celan->add_group_field($Jitheme_footer_celan_kf, array(
            'name' => __('链接文字','b2'),
            'id'   => 'celan_kf_title',
            'type' => 'text',
            'default' => '极主题官网',
        ) );
        $Jitheme_footer_celan->add_group_field($Jitheme_footer_celan_kf, array(
            'name' => __('链接地址','b2'),
            'id'   => 'celan_kf_link',
            'type' => 'text',
            'default' =>'https://www.jitheme.com/',
        ) );
        $Jitheme_footer_celan->add_group_field($Jitheme_footer_celan_kf, array(
            'name' => __('展示图片','b2'),
            'id'   => 'celan_kf_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['ysa_kefu_img'],
        ) );}
    public function Jitheme_footer_jj() {
        $footer_new= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_footer_jj',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_footer_jj', // The option key and admin menu page slug.            
        'tab_title'    => __('简洁版底部','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_footer_jj',
        'tab_group'    => 'b2_Jitheme_footer_options',
        ) );
        $footer_new->add_field(array(
            'name'    => __( '关闭底部导视栏', 'b2' ),
            'desc'    => __( '可以关闭简洁模式下的图片导视模块。。', 'b2' ),
            'id'      =>  'jj_ds_offa',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $footer_new->add_field(array(
            'name'    => __( '横条主标题', 'b2' ),
            'id'=>'fot_title',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_ysb_htsmz'],
        ) );
        $footer_new->add_field(array(
            'name'    => __( '横条副标题', 'b2' ),
            'id'=>'desc_title',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_ysb_htsmf'],
        ) );
        $footer_new->add_field(array(
            'name' => __('横条背景图片','b2'),
            'id'   => 'fot_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_bg'],
        ) );
        $footer_new->add_field(array(
            'name' => __('加群二维码','b2'),
            'id'   => 'ewm',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_ewm_ico'],
        ) );
        $footer_new->add_field(array(
            'name'    => __( '介绍标题', 'b2' ),
            'desc'    => __( '比如：关于我们', 'b2' ),
            'id'=>'about_title',
            'type'=>'text',
            'default' => self::$default_settings['text_footer_title_ysa'],
        ));
        $footer_new->add_field(array(
            'name'    => __( '介绍内容', 'b2' ),
            'desc'    => __( '比如：公司简介', 'b2' ),
            'id'=>'jj_about',
            'type'=>'textarea',
            'default' => self::$default_settings['text_footer_desc_ysa'],
        ));
        $jj_new_links = $footer_new->add_field( array(
            'id'          => 'jj_new_links',
            'type'        => 'group',
            'description' => __('添加底部链接组：建议不要超过3组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '链接组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新链接组', 'b2' ),
                'remove_button'     => __( '删除链接组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $footer_new->add_group_field($jj_new_links, array(
            'name' => __('连接组标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default' => self::$default_settings['ysa_links_title'],
        ) );
        $footer_new->add_group_field($jj_new_links, array(
            'name' => __('连接组内容','b2'),
            'id'   => 'links',
            'type' => 'textarea',
            'default' => self::$default_settings['ysa_links_desc'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<li><a href="链接地址" target="_blank">链接名称</a></li>').'</code>'),
        ) );
        $jj_new_ewm = $footer_new->add_field( array(
            'id'          => 'jj_new_ewm',
            'type'        => 'group',
            'description' => __('添加底部二维码组：建议不要超过3组','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '链接组设置-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新链接组', 'b2' ),
                'remove_button'     => __( '删除链接组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接组吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $footer_new->add_group_field($jj_new_ewm, array(
            'name'    => __( '横条副标题', 'b2' ),
            'id'=>'title',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_ysb_htsmf'],
        ) );
        $footer_new->add_group_field($jj_new_ewm, array(
            'name' => __('加群二维码','b2'),
            'id'   => 'ewm',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default' => self::$default_settings['onecad_footer_ysb_ewm_ico'],
        ) );
        
    }
    public function Jitheme_footer_tsk() {
        $footer_tsk= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_footer_tsk',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_footer_tsk', // The option key and admin menu page slug.            
        'tab_title'    => __('底部提示框','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_footer_tsk',
        'tab_group'    => 'b2_Jitheme_footer_options',
        ) );
        $footer_tsk->add_field(array(
            'name'    => __( '关闭底部提示框', 'b2' ),
            'desc'    => __( '可以关闭页面底部的提示框。<br><span class="red">注意此提示框上方有倒计时，与首页模块调用的为同一个时间，2个功能只能兼显示一个。</span>', 'b2' ),
            'id'      =>  'tsk_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $footer_tsk->add_field(array(
            'name'    => __( '倒计时关键词', 'b2' ),
            'id'=>'djs',
            'type' => 'text',
            'default' =>'',
        ) );
        $footer_tsk->add_field(array(
            'name'    => __( '主标题', 'b2' ),
            'id'=>'title',
            'type' => 'text',
            'default' => self::$default_settings['text_footer_desc_ysa_ms'],
        ) );
        $footer_tsk->add_field(array(
            'name' => __('展示图片','b2'),
            'id'   => 'tsk_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('如果不设置，请填写<span class="red">none</span>后保存或者直接留空<br>','b2'),
            'default' =>'',
        ) );
        $footer_tsk->add_field(array(
            'name'    => __( '副标题', 'b2' ),
            'id'=>'desc_title',
            'type' => 'text',
            'default' => self::$default_settings['text_footer_desc_ysa_qqun'],
        ) );
        $footer_tsk->add_field(array(
            'name'    => __( '按钮文字', 'b2' ),
            'id'=>'btn_text',
            'type' => 'text',
            'default' => self::$default_settings['ysa_gg_title'],
        ) );
        $footer_tsk->add_field(array(
            'name'    => __( '按钮链接', 'b2' ),
            'id'=>'link',
            'type' => 'text',
            'default' => self::$default_settings['onecad_footer_link'],
        ) );
    }
}
$list_Footer = new Footer();
$list_Footer->init();
