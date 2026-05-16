<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Head
{
    //默认设置项
    public static $default_settings = [
        'one_template_top_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg',
        'vip_title'=>'升级会员',
        'vip_top_title'=>'加入VIP即可获得',
        'vip_top_desc'=>'加入VIP即可获得',
        'vip_title'=>'优先服务',
        'vip_top_desc'=>'属于你的1对1客服，即时响应',
        'vip_img'=>B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg',
        'vip_btn_title'=>'立即开通会员',
        'vip_btn_text'=>'尊享优惠价',
        'vip_btn_link'=>'/vips',
        'Jitheme_top_login_title'=>'扫码小程序',
        'Jitheme_top_login_desc'=>'上千资源任您下载',
        'index_wuzu_img'=>B2_CHILD_URI.'/Center/Assets/images/ewm.png',
        'top_login_saoma'=>'微信扫码'
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_top_page']);
    }
    //构造页面功能参数
    public function Jitheme_top_page(){
        $Header = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_top_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_top_main',
            'tab_group'    => 'b2_Jitheme_top_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('顶部设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Header->add_field([
            'name' => __('顶部菜单是否居中', 'b2'),
            'id' => 'top_menu_conter',
            'type' => 'select',
            'options' => [
                1 => __('居中', 'b2'),
                0 => __('不居中', 'b2'),
            ],
        ]);
        $Header->add_field([
            'name' => __('顶部子菜单启用ICON/图片', 'b2'),
            'id' => 'Top_menu_icon',
            'type' => 'select',
            'options' => [
                1 => __('ICON', 'b2'),
                0 => __('图片', 'b2'),
            ],
            'default' => 0,
            'desc' => sprintf(
                __('请在外观-菜单里面设置你的ICON图标或者图片，如果是图片直接在菜单添加图片即可，<br>如果是ICON图标则使用子主题自带的ICON字体图标，格式 %s', 'b2'),
                '<code>' . esc_html('<div class="ico"><i class="Jifont Jifont-medal-1"></i></div>') . '</code><br>"Jifont-medal-1"这个名称自己根据ICON图标自行选择<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>'
            ),
        ]);
        $Header->add_field([
            'name' => __('是否开启菜单4美化', 'b2'),
            'id' => 'menu4_off',
            'type' => 'select',
            'options' => [
                1 => __('开启', 'b2'),
                0 => __('关闭', 'b2'),
            ],
            
        ]);
        $Header = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_vip_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_vip_main',
            'tab_group'    => 'b2_Jitheme_top_options',
            'tab_title'    => __('VIP图标','b2'),
            'save_button'  => __( '保存配置', 'b2' ),
            'parent_slug'  => 'b2_Jitheme_vip_main',
        ) );
        // $Header->add_field([
        //     'name' => __('是否开启菜单4美化', 'b2'),
        //     'id' => 'menu4_off',
        //     'type' => 'select',
        //     'options' => [
        //         1 => __('开启', 'b2'),
        //         0 => __('关闭', 'b2'),
        //     ],
        // ]);
        // $Header->add_field([
        //     'name' => __('是否开启VIP导视', 'b2'),
        //     'id' => 'vip_off',
        //     'type' => 'select',
        //     'options' => [
        //         1 => __('开启', 'b2'),
        //         0 => __('关闭', 'b2'),
        //     ],
        // ]);
        $Header->add_field([
            'name' => __('VIP导视风格', 'b2'),
            'id' => 'vip_fenge',
            'type' => 'select',
            'options' => [
                2 => __('VIP简约', 'b2'),
                1 => __('VIP导视1', 'b2'),
                0 => __('VIP导视2', 'b2'),
            ],
            'default'          => 1,
        ]);
        $Header->add_field(array(
            'name' => __('VIP图标','b2'),
            'id'   => 'vip_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['one_template_top_vip_img'],
        ) );
        $Header->add_field(array(
            'name' => __('VIP文字','b2'),
            'id'   => 'vip_title',
            'type' => 'text',
            'default'          => self::$default_settings['vip_title'],
        ));
        $Header->add_field(array(
            'name' => __('VIP弹窗主标题','b2'),
            'id'   => 'vip_top_title',
            'type' => 'text',
            'default'          => self::$default_settings['vip_top_title'],
        ) );
        $Header->add_field(array(
            'name' => __('VIP弹窗小标题','b2'),
            'id'   => 'vip_top_desc',
            'type' => 'text',
            'default'          => self::$default_settings['vip_top_desc'],
        ) );
        $Header->add_field(array(
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
        $Jitheme_top_vip = $Header->add_field( array(
            'id'          => 'vip_top_list',
            'type'        => 'group',
            'description' => __('VIP导航4组<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过4组</span>','b2'),
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
        $Header->add_group_field($Jitheme_top_vip, array(
            'name' => __('文字','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['vip_title'],
        ) );
        $Header->add_group_field($Jitheme_top_vip, array(
            'name' => __('描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          => self::$default_settings['vip_top_desc'],
        ) );
        $Header->add_group_field($Jitheme_top_vip, array(
            'name' => __('图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['vip_img'],
        ) );
        $Header->add_field(array(
            'name' => __('按钮文字','b2'),
            'id'   => 'vip_btn_title',
            'type' => 'text',
            'default'          => self::$default_settings['vip_btn_title'],
        ) );
        $Header->add_field(array(
            'name' => __('角标文字','b2'),
            'id'   => 'vip_btn_text',
            'type' => 'text',
            'default'          => self::$default_settings['vip_btn_text'],
        ) );
        $Header->add_field(array(
            'name' => __('按钮链接','b2'),
            'id'   => 'vip_btn_link',
            'type' => 'text',
            'default'          => self::$default_settings['vip_btn_link'],
        ) );

        $search= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_search_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_search', // The option key and admin menu page slug.            
            'tab_title'    => __('搜索弹窗','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_search',
            'tab_group'    => 'b2_Jitheme_top_options',
            'save_button'     => __( '保存设置', 'b2' )
        ) );
        $search->add_field(array(
            'name'    => __( '弹窗搜索关键词', 'b2' ),
            'id'=>'search_key',
            'type' => 'textarea_small',
            'desc'=>__('请输入关键词，每个关键词占一行，留空则不显示','b2'),
            'default'=>''
        ) );
        $search->add_field(array(
            'name'    => __( '弹窗广告图片代码', 'b2' ),
            'id'=>'search_img_link',
            'type' => 'textarea_code',
            'desc'=>__('请输HTML代码','b2'),
            'default'=>''
        ) );

        $Login= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_Login_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_Login', // The option key and admin menu page slug.            
            'tab_title'    => __('登录框','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_Login',
            'tab_group'    => 'b2_Jitheme_top_options',
            'save_button'     => __( '保存设置', 'b2' )
        ) );
        $Login->add_field([
            'name' => __('是否启用登录框测栏', 'b2'),
            'id' => 'top_login_off',
            'type' => 'select',
            'options' => [
                1 => __('开启', 'b2'),
                0 => __('关闭', 'b2'),
            ],
        ]);
        $Login->add_field(array(
            'name'    => __( '登录框背景图片', 'b2' ),
            'id'=>'Jitheme_top_login_img',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('如果不设置，请填写<span class="red">none</span>后保存或者直接留空<br>','b2'),
            'default'=>'',
        ));
        $Login->add_field(array(
            'name' => __('登录框标题','b2'),
            'id'   => 'top_login_titlea',
            'type' => 'text',
            'default'          => self::$default_settings['Jitheme_top_login_title'],
        ));
        $Login->add_field(array(
            'name' => __('登录框副标题','b2'),
            'id'   => 'top_login_desca',
            'type' => 'text',
            'default'          => self::$default_settings['Jitheme_top_login_desc'],
        ));
        $Login->add_field(array(
            'name'    => __( '登录框显示二维码', 'b2' ),
            'id'=>'top_login_ewmimg',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片','b2'),
            'default'=>self::$default_settings['index_wuzu_img'],
        ));
        $Login->add_field(array(
            'name' => __('扫码方式','b2'),
            'id'   => 'top_login_saoma',
            'type' => 'text',
            'default'          => self::$default_settings['top_login_saoma'],
        ));
        $Login->add_field(array(
            'name'    => __( '文字介绍', 'b2' ),
            'id'=>'top_login_btn',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签。', 'b2' ),'<code>'.htmlspecialchars('<a href="链接" class="css-1bdtll5">按钮名称</a>').'</code>'),
        )); 
    }
}
$list_Head = new Head();
$list_Head->init();