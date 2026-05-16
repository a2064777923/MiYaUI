<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Head
{
    //默认设置项
    public static $default_settings = [
        //顶部VIP设置
        'one_logo_saog'=>0,
        'vipjb'=>'-25px',
        'onecad_vips_title'=>'欢迎加入极主题VIP，开通会员尊享特权',
        'index_onecad_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad-home-vip-bg.png',
        'index_onecad_vip_title'=>'会员尊享权益圈子',
        'one_template_top_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg',
        'index_onecad_vip_desc'=>'成为我们的VIP会员，尊享无限免费下载使用，更享受全面的服务与福利',
        'index_onecad_vip_tj_title'=>'推荐购买',
        'index_wuzu_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
        'index_wuzu_jiaob'=>0,
        'index_onecad_search'=>1,
        'index_wuzu_desc'=>'https://www.jitheme.com/',
        'index_wuzu_title'=>'极主题',
        'index_diy_search_list_img'=>B2_CHILD_URI.'/Center/Assets/images/icon.svg',
        //登录框优化
        'Jitheme_top_login_img'=>B2_CHILD_URI.'/Center/Assets/images/denglu_img.png',
        'Jitheme_top_login_title'=>'极主题标题',
        'Jitheme_top_login_desc'=>'极主题登录框副标题',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_top_page']);
    }
    //构造页面功能参数
    public function Jitheme_top_page(){
        $Jitheme_top = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_top_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_top_main',
            'tab_group'    => 'b2_Jitheme_top_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('顶部设置','b2'),
            'menu_title'   => __('顶部设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Jitheme_top->add_field(array(
            'name'    => __( '是否启用顶部菜单', 'b2' ),
            'desc'    => __( '上面增加一个顶部菜单栏（恢复原生菜单）。', 'b2' ),
            'id'      =>  'head_top_cd_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        )); 
        $Jitheme_top->add_field(array(
            'name' => __('顶部渐变色','b2'),
            'desc'    => __( '如果想设置为图片，请保留为空，然后去<a target="_blank" href="'.admin_url('/admin.php?page=b2_template_top').'">父主题-模块设置-顶部</a>选项的顶部背景图片设置图片及链接颜色。<br>您可以到此网站去查看渐变色代码,复制代码粘贴此处即可<a target="_blank" href="http://color.oulu.me/">渐变色代码</a>', 'b2' ),
            'id'   => 'head_top_cd_ys',
            'type' => 'text',
            'default'          => '',
        ) );
        $Jitheme_top->add_field(array(
            'before_row'=>'<h2>导航VIP按钮</h2>',
            'name'    => __( '是否显示VIP图标', 'b2' ),
            'desc'    => __( '一个漂亮的VIP图标。', 'b2' ),
            'id'      =>  'one_template_top_vip',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        )); 
        $Jitheme_top->add_field(array(
            'name' => __('VIP图标','b2'),
            'id'   => 'one_template_top_vip_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['one_template_top_vip_img'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP角标文字','b2'),
            'id'   => 'Jitheme_template_top_vip_jiaob',
            'type' => 'text',
            'default'          => '开通',
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP角标颜色','b2'),
            'id'   => 'vip_jb_color',
            'type'             => 'select',
            'options'          => array(
                'jiaobiao_color1' => __( '角标色1', 'b2' ),
                'jiaobiao_color2' => __( '角标色2', 'b2' ),
                'jiaobiao_color3' => __( '角标色3', 'b2' ),
                'jiaobiao_color4' => __( '角标色4', 'b2' ),
                'jiaobiao_color5' => __( '角标色5', 'b2' ),
                'jiaobiao_color6' => __( '角标色6', 'b2' ),
            ),
            'desc'=> sprintf(__('您可以前往%s设置角标颜色','b2'),'<a href="'.admin_url('/admin.php?page=b2_Jitheme_main_tab2').'" target="_blank">'.__('颜色设置','b2').'</a>'),
            'default'          => 'index_jiaobiao_color1',
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP角标左右位置','b2'),
            'id'   => 'vipjb',
            'type' => 'text',
            'default'          => self::$default_settings['vipjb'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP角标链接','b2'),
            'id'   => 'one_template_top_vip_link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP块标题','b2'),
            'id'   => 'one_template_top_vip_wz',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('VIP块小标题','b2'),
            'id'   => 'one_template_top_vip_xwz',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Jitheme_top_vip = $Jitheme_top->add_field( array(
            'id'          => 'one_template_top_vip_list',
            'type'        => 'group',
            'description' => __('VIP导航3组<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过3组</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加链接导航5组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加链接导航5组', 'b2' ),
                'remove_button'     => __( '删除链接导航5组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接导航吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $Jitheme_top->add_group_field($Jitheme_top_vip, array(
            'name' => __('图标','b2'),
            'id'   => 'one_template_top_vip_list_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_diy_search_list_img'],
        ) );
        $Jitheme_top->add_group_field($Jitheme_top_vip, array(
            'name' => __('文字','b2'),
            'id'   => 'one_template_top_vip_list_xwz',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Jitheme_top->add_group_field($Jitheme_top_vip, array(
            'name' => __('描述','b2'),
            'id'   => 'one_template_top_vip_list_desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('按钮文字','b2'),
            'id'   => 'one_template_top_vip_list_xwz',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Jitheme_top->add_field(array(
            'name' => __('按钮角标文字','b2'),
            'id'   => 'one_template_top_vip_list_jbwz',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        self::Jitheme_top_login();
    }
    public function Jitheme_top_login(){
        $Jitheme_top_login = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_top_options_login',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_top_login',
            'tab_group'    => 'b2_Jitheme_top_options',
            'parent_slug'  => 'b2_Jitheme_top_login',
            'tab_title'    => __('登录框','b2'),
        ) ); 
        $Jitheme_top_login->add_field(array(
            'name'    => __( '开启登录框美化', 'b2' ),
            'desc'    => __( '登录框左侧有个展示图片。', 'b2' ),
            'id'      =>'Jitheme_top_login_off',
            'type'             => 'select',
            'default'          => self::$default_settings['one_logo_saog'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $Jitheme_top_login->add_field(array(
            'name'    => __( '登录框背景图片', 'b2' ),
            'id'=>'Jitheme_top_login_img',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('如果不设置，请填写<span class="red">none</span>后保存或者直接留空<br>','b2'),
            'default'=>'',
        ));
        $Jitheme_top_login->add_field(array(
            'name' => __('登录框标题','b2'),
            'id'   => 'Jitheme_top_login_titlea',
            'type' => 'text',
            'default'          => self::$default_settings['Jitheme_top_login_title'],
        ));
        $Jitheme_top_login->add_field(array(
            'name' => __('登录框副标题','b2'),
            'id'   => 'Jitheme_top_login_desca',
            'type' => 'text',
            'default'          => self::$default_settings['Jitheme_top_login_desc'],
        ));
        $Jitheme_top_login->add_field(array(
            'name'    => __( '登录框显示二维码', 'b2' ),
            'id'=>'Jitheme_top_login_ewmimg',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片','b2'),
            'default'=>self::$default_settings['index_wuzu_img'],
        ));
        $Jitheme_top_login->add_field(array(
            'name' => __('扫码方式','b2'),
            'id'   => 'Jitheme_top_login_saoma',
            'type' => 'text',
            'default'          => self::$default_settings['Jitheme_top_login_desc'],
        ));
        $Jitheme_top_login->add_field(array(
            'name'    => __( '文字介绍', 'b2' ),
            'id'=>'Jitheme_top_login_btn',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<a href="链接" class="css-1bdtll5">按钮名称</a>').'</code>'),
        )); 
    }
}
$list_Head = new Head();
$list_Head->init();