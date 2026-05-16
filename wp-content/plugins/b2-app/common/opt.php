<?php
namespace B2APP\common;
use B2\Modules\Templates\Modules\Sliders;

class opt
{

    public function init()
    {

        add_action('cmb2_admin_init', array($this, 'main_options_page'));
        // add_action('cmb2_admin_init',array($this,'setting_action'),99999);
    }

    public function b2_sanitize_value($value, $field_args, $field)
    {
        foreach ($value as $key => $_value) {
            $type = $_value['module_type'];
            foreach ($_value as $k => $v) {
                if (strpos($k, $type) === false) {
                    unset($value[$key]);
                }
            }
        }

        return $value;
    }

    public static function setting_action()
    {

    }

    public static function opt($where, $key)
    {

        $where = 'b2_app_' . $where;

        $opt = get_option($where);

        if ($opt === false)
            return self::get_default_settings($where, $key);

        if (isset($opt[$key])) {
            return $opt[$key];
        } else {
            return self::get_default_settings($where, $key);
        }

        return '';
    }

    public static function get_default_settings($where, $key)
    {

        $arr = apply_filters('b2_app_default_settings', array(
            'b2_app_options' => array(
                'logo' => plugin_dir_url(dirname(__FILE__)) . 'static/logo.png',
                'logo_light' => plugin_dir_url(dirname(__FILE__)) . 'static/logo_light.png',
                'name' => get_bloginfo('name'),
                'open_simple' => 0,
                'allow_update' => 0,
                'must_update' => 0,
                'app_verison' => '1.0.3',
                'update_info' => '',
                'app_android' => '',
                'app_ios' => '',
                'fb_title' => '',
                'fb_logo' => '',
                'fb_desc' => '',
                'fb_info' => '',
                'fb_android' => '',
                'fb_ios' => '',
                'fb_miniapp_weixin' => '',
                'fb_miniapp_alipay' => '',
                'fb_miniapp_baidu' => '',
                'fb_miniapp_toutiao' => '',
                'fb_miniapp_qq' => '',
                'fb_miniapp_weixin_name' => '',
                'fb_miniapp_alipay_name' => '',
                'fb_miniapp_baidu_name' => '',
                'fb_miniapp_toutiao_name' => '',
                'fb_miniapp_qq' => '',
                'fb_h5' => '',
                'fb_kuai' => '',
                'fb_thumbs' => array()
            ),
            'b2_app_mp_normal' => array(
                'post_cat' => array(),
                'shop_cat' => array(),
                'circle_cat' => array()
            ),
            'b2_app_shop_normal' => array(
                'open_wechat_push' => 0,
                'open_wechat_push_pohne' => '',
                'slider_list' => '',
                'shoptype' => array(),
                'shop_ex' => array(),
                'shop_show_type' => array(
                    'normal',
                    'exchange',
                    'lottery'
                ),
                'shop_type_count' => 4,
                'shop_list_type' => 'grid'
            ),
            'b2_app_pay_options_page' => array(
                'pay_allow_mpweixin' => 0,
                'pay_appid_mpopen' => '',
                'pay_baidu_dealId' => '',
                'pay_baidu_appkey' => ''
            ),
            'b2_app_message_normal' => [
                'msg_weixin' => 0
            ]
        ));

        if (isset($arr[$where][$key])) {
            return $arr[$where][$key];
        }

        return '';
    }

    public function main_option_page_cb()
    {
        ?>
        <h1>请先激活主题，再使用APP插件</h1>
        <p><a href="<?php echo admin_url('/admin.php?page=b2_main_options'); ?>">激活主题</a></p>
        <?php
    }

    public function main_options_page()
    {

        $circle_name = b2_get_option('normal_custom', 'custom_circle_name');
        $collection_name = b2_get_option('normal_custom', 'custom_collection_name');
        $shop_name = b2_get_option('normal_custom', 'custom_shop_name');
        $newsflashes_name = b2_get_option('normal_custom', 'custom_newsflashes_name');
        $document_name = b2_get_option('normal_custom', 'custom_document_name');
        $announcement_name = b2_get_option('normal_custom', 'custom_announcement_name');
        $infomation_name = b2_get_option('normal_custom', 'custom_infomation_name');

        // if($status){
        $options = new_cmb2_box(array(
            'id' => 'b2_app_options_page',
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'icon_url' => 'dashicons-admin-site-alt2',
            'option_key' => 'b2_app_options',
            'show_on' => array(
                'options-page' => 'b2_app_options',
            ),
            'tab_group' => 'b2_app_options',
            'object_types' => array('options-page'),
            'menu_title' => __('B2主题-移动端', 'b2'),
            'tab_title' => __('综合', 'b2'),
        ));


        $options->add_field(array(
            'before_row' => '<p>请根据自己的需求对APP进行设置</p>',
            'name' => __('移动端LOGO', 'b2'),
            'id' => 'logo',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => __('', 'b2'),
            'default' => self::get_default_settings('b2_app_options', 'logo')
        ));

        $options->add_field(array(
            'name' => __('移动端浅色LOGO', 'b2'),
            'id' => 'logo_light',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => __('', 'b2'),
            'default' => self::get_default_settings('b2_app_options', 'logo_light'),
            'desc' => __('通常情况下，如果背景颜色为大的色块，会切换显示此浅色logo', 'b2')
        ));

        $options->add_field(array(
            'name' => __('移动端站名', 'b2'),
            'id' => 'name',
            'type' => 'text',
            'desc' => __('会显示在顶部的站名', 'b2'),
            'default' => self::get_default_settings('b2_app_options', 'name')
        ));

        // $options->add_field(array(
        //     'name' => __('是否开启简洁模式','b2'),
        //     'id'   => 'open_simple',
        //     'type'             => 'select',
        //     'options' => array(
        //         0 => __('关闭','b2'),
        //         1=>__('开启','b2')
        //     ),
        //     'desc'=>__( '开启简洁模式之后，小程序、APP等无法使用支付、圈子等功能。如果您是个人用户，建议开启简洁模式，否则上架时无法审核通过。上架以后可以开启，但是需要承担被下架的风险。', 'b2' ),
        //     'default'          => self::get_default_settings('b2_app_options','open_simple')
        // ));

        $options->add_field(array(
            'before_row' => '<h2>APP端更新设置</h2><p>仅安卓和IOS的APP支持在线更新，各平台小程序无法在线更新， 只能重新提交更新。</p>',
            'name' => __('是否允许用户更新APP', 'b2'),
            'id' => 'allow_update',
            'type' => 'select',
            'options' => array(
                1 => '开启更新',
                0 => '关闭更新'
            ),
            'desc' => __('建议APP在上架审核阶段彻底关闭更新，以免出现审核不通过的情况', 'b2'),
            'default' => 0
        ));

        $options->add_field(array(
            'name' => __('是否强制用户更新APP', 'b2'),
            'id' => 'must_update',
            'type' => 'select',
            'options' => array(
                1 => '强制更新',
                0 => '选择更新'
            ),
            'desc' => __('如果有比较重要的更新，需要强制用户更新APP后才能使用，请选择强制更新，更新提示将没有关闭按钮。', 'b2'),
            'default' => 0
        ));

        $options->add_field(array(
            'name' => __('当前app版本号', 'b2'),
            'id' => 'app_verison',
            'type' => 'text',
            'desc' => '当前APP版本大于用户安装的APP版本时才会提示升级。',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('升级内容', 'b2'),
            'id' => 'update_info',
            'type' => 'textarea',
            'desc' => '请简要填写升级内容，留空则会显示：提高了效率，UI做了部分调整，修复了一些BUG',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('安卓新版APP', 'b2'),
            'id' => 'app_android',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => '您可以直接此处上传新版APP，或者直接填写新版APP的文件地址',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('IOS新版APP', 'b2'),
            'id' => 'app_ios',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => '您可以直接此处上传新版APP，或者直接填写新版APP的文件地址',
            'default' => ''
        ));

        $options->add_field(array(
            'before_row' => '<h2>统一发布页面</h2><p>我们为您生成了一个统一发布页面，请根据自己的需求，填写下面的设置项。您的统一发布页面网址是：<a href="' . home_url('app/download') . '" target="_blank">' . home_url('app/download') . '</a></p><p>各个平台的应用打包好以后，请放到服务器上，并获取连接，将连接生成二维码上传到此处</p>',
            'name' => __('统一发布页面标题', 'b2'),
            'id' => 'fb_title',
            'type' => 'text',
            'desc' => __('只会显示在统一发布页面', 'b2'),
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('统一发布页图标（通常就是应用图标，正方形）', 'b2'),
            'id' => 'fb_logo',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => __('', 'b2'),
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('统一发布页面应用描述', 'b2'),
            'id' => 'fb_desc',
            'type' => 'text',
            'desc' => __('只会显示在统一发布页面', 'b2'),
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('统一发布页面应用说明', 'b2'),
            'id' => 'fb_info',
            'type' => 'textarea_small',
            'desc' => __('只会显示在统一发布页面', 'b2'),
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('安卓APP下载连接', 'b2'),
            'id' => 'fb_android',
            'type' => 'text',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('IOS平台APP下载连接', 'b2'),
            'id' => 'fb_ios',
            'type' => 'text',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('微信小程序应用名称', 'b2'),
            'id' => 'fb_miniapp_weixin_name',
            'type' => 'text',
            'desc' => '移动端用户在应用内搜索此名称找到应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('微信小程序二维码', 'b2'),
            'id' => 'fb_miniapp_weixin',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => 'PC端用户使用手机扫此二维码进入应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('支付宝小程序应用名称', 'b2'),
            'id' => 'fb_miniapp_alipay_name',
            'type' => 'text',
            'desc' => '移动端用户在应用内搜索此名称找到应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('支付宝小程序二维码', 'b2'),
            'id' => 'fb_miniapp_alipay',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => 'PC端用户使用手机扫此二维码进入应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('百度小程序应用名称', 'b2'),
            'id' => 'fb_miniapp_baidu_name',
            'type' => 'text',
            'desc' => '移动端用户在应用内搜索此名称找到应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('百度小程序二维码', 'b2'),
            'id' => 'fb_miniapp_baidu',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => 'PC端用户使用手机扫此二维码进入应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('头条小程序应用名称', 'b2'),
            'id' => 'fb_miniapp_toutiao_name',
            'type' => 'text',
            'desc' => '移动端用户在应用内搜索此名称找到应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('头条小程序二维码', 'b2'),
            'id' => 'fb_miniapp_toutiao',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => 'PC端用户使用手机扫此二维码进入应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('QQ小程序应用名称', 'b2'),
            'id' => 'fb_miniapp_qq_name',
            'type' => 'text',
            'desc' => '移动端用户在应用内搜索此名称找到应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('QQ小程序二维码', 'b2'),
            'id' => 'fb_miniapp_qq',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'desc' => 'PC端用户使用手机扫此二维码进入应用',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('H5端网址', 'b2'),
            'id' => 'fb_h5',
            'type' => 'text',
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('快应用二维码', 'b2'),
            'id' => 'fb_kuai',
            'type' => 'file',
            'options' => array(
                'url' => true,
            ),
            'default' => ''
        ));

        $options->add_field(array(
            'name' => __('统一发布页面APP截图', 'b2'),
            'desc' => __('建议设置多个截图，方便用户查看', 'b2'),
            'id' => 'fb_thumbs',
            'type' => 'file_list',
            'text' => array(
                'add_upload_file_text' => __('选择图片', 'b2'),
            ),
            'preview_size' => array(100, 100),
            'query_args' => array(
                'type' => 'image',
            ),
            'default' => array()
        ));



        // }
        // else{
        //     $options = new_cmb2_box(array(
        //         'id'	=>	'b2_app_options_page',
        //         'title'	=>	__('B2主题-移动端专用插件','b2'),
        //         'icon_url'	=>	'dashicons-admin-site-alt2',
        //         'option_key'      => 'b2_app_options',
        //         'show_on'	=>	array(
        //             'options-page'	=>'b2_app_options',
        //         ),
        //         'tab_group'    => 'b2_app_options',
        //         'object_types' => array( 'options-page' ),
        //         'menu_title'    => __('B2主题-移动端','b2'),
        //         'display_cb'      => array($this,'main_option_page_cb'),
        //         'tab_title'   => __('综合设置','b2'),
        //     ));

        //     return;
        // }

        // $tabbar = new_cmb2_box( array(
        //     'id'           => 'b2_app_tabbar_options_page',
        //     'object_types' => array( 'options-page' ),
        //     'title'	=>	__('B2主题-移动端专用插件','b2'),
        //     'option_key'      => 'b2_app_tabbar',
        //     'parent_slug'     => '/admin.php?page=b2_app_options',
        //     'tab_group'    => 'b2_app_options',
        //     'tab_title'    => __('底部导航条设置','b2'),
        // ));

        // $custom_name = get_option('b2_normal_custom');

        // $tabbar->add_field(array(
        //     'name'    => '底部导航设置',
        //     'id'      => 'tabbar',
        //     'desc'    => __('文章页会显示此菜单，可拖动排序','b2'),
        //     'type'    => 'pw_multiselect',
        //     'options' =>[
        //         'index'=>__('首页','b2'),
        //         'circle'=>isset($custom_name['custom_circle_name']) ? $custom_name['custom_circle_name'] : __('圈子','b2'),
        //         'shop'=>isset($custom_name['custom_shop_name']) ? $custom_name['custom_shop_name'] : __('商铺','b2'),
        //         'newsflashes'=>isset($custom_name['custom_newsflashes_name']) ? $custom_name['custom_newsflashes_name'] : __('快讯','b2'),
        //         'my'=>__('我的','b2'),
        //         'announcement'=>isset($custom_name['custom_announcement_name']) ? $custom_name['custom_announcement_name'] : __('公告','b2'),
        //         'document'=>isset($custom_name['custom_document_name']) ? $custom_name['custom_document_name'] : __('文档','b2'),
        //         'collection'=>isset($custom_name['custom_collection_name']) ? $custom_name['custom_collection_name'] : __('专题','b2')
        //     ],
        //     'desc'=>__('此处可以自定义底部菜单以及排序，选择后可拖动。建议不超过5个','b2')
        // ));

        $menu = new_cmb2_box(array(
            'id' => 'b2_app_normal_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_mp_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('菜单', 'b2'),
        ));

        $cats = array();

        $categories = get_categories(array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($categories as $category) {
            $cats[$category->term_id] = $category->name;
        }

        $menu->add_field(array(
            'name' => '文章分类',
            'id' => 'category',
            'desc' => __('文章页会显示此菜单，可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $cats,
        ));

        $arr = array();

        $shops = get_terms('shoptype', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($shops as $shop) {
            $arr[$shop->term_id] = $shop->name;
        }

        $menu->add_field(array(
            'name' => '商品分类',
            'id' => 'shoptype',
            'desc' => __('商品页会显示此菜单，可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $arr = array();

        $circles = get_terms('circle_tags', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($circles as $circle) {
            $arr[$circle->term_id] = $circle->name;
        }

        $menu->add_field(array(
            'name' => $circle_name . '分类',
            'id' => 'circle_tags',
            'desc' => $circle_name . __('菜单，可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $arr = array();

        $newsflashes = get_terms('newsflashes_tags', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($newsflashes as $newsflashe) {
            $arr[$newsflashe->term_id] = $newsflashe->name;
        }

        $menu->add_field(array(
            'name' => $newsflashes_name . '分类',
            'id' => 'newsflashes_tags',
            'desc' => $newsflashes_name . __('菜单，可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $arr = array();

        $documents = get_terms('document_cat', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($documents as $document) {
            $arr[$document->term_id] = $document->name;
        }

        $menu->add_field(array(
            'name' => $document_name,
            'id' => 'document_cat',
            'desc' => sprintf(__('%s菜单，可拖动排序', 'b2'), $document_name),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $arr = array();

        $infomations = get_terms('infomation_cat', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($infomations as $infomation) {
            $arr[$infomation->term_id] = $infomation->name;
        }

        $menu->add_field(array(
            'name' => $infomation_name . '分类',
            'id' => 'infomation_cat',
            'desc' => sprintf(__('%s菜单，可拖动排序', 'b2'), $document_name),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $index = new_cmb2_box(array(
            'id' => 'b2_app_index_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_index_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('首页模块', 'b2'),
        ));

        //模块设置
        $index_group = $index->add_field(array(
            'id' => 'index_group',
            'type' => 'group',
            'description' => __('APP首页模块布局（点击小箭头展开设置）', 'b2'),
            // 'repeatable'  => false, // use false if you want non-repeatable group
            'options' => array(
                'group_title' => __('模块{#}', 'b2'), // since version 1.1.4, {#} gets replaced by row number
                'add_button' => __('添加新模块', 'b2'),
                'remove_button' => __('删除模块', 'b2'),
                'sortable' => true,
                'closed' => true, // true to have the groups closed by default
                'remove_confirm' => __('确定要删除这个模块吗？', 'b2'), // Performs confirmation before removing group.
            ),
            //'sanitization_cb'=>array($this,'b2_sanitize_value')
        ));

        $index->add_group_field($index_group, array(
            'name' => __('模块标题', 'b2'),
            'id' => 'title',
            'type' => 'text',
            'desc' => __('给这个模块起个名字，某些模块下会显示这个标题', 'b2')
            // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ));

        $temp_type = apply_filters('b2_app_temp_type', array(
            'slider' => __('幻灯', 'b2'),
            'post' => __('文章', 'b2'),
            'collection' => $collection_name,
            'shop' => $shop_name,
            'circle' => $circle_name,
            'circle_id' => sprintf(__('%s话题', 'b2'), $circle_name),
            'document' => $document_name,
            'newsflashes' => $newsflashes_name,
            'announcement' => __('公告', 'b2'),
            // 'ads'=>__('广告','b2'),
            'menu' => __('快速导航', 'b2')
        ));


        $index->add_group_field($index_group, array(
            'name' => __('调用内容', 'b2'),
            'id' => 'module_type',
            'type' => 'radio_inline',
            'options' => $temp_type,
            'default' => 'slider',
            'classes' => 'model-picked'
        ));

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="slider-module cmb-row set-hidden">',
            'name' => __('幻灯内容', 'b2'),
            'id' => 'slider_list',
            'type' => 'textarea_code',
            'description' => sprintf(
                __('支持所有文章类型（文章，活动，商品等），每组占一行，排序与此设置相同。图片可以在%s上传或选择。
            %s
            支持的格式如下：
            %s', 'b2'),
                '<a target="__blank" href="' . admin_url('/upload.php') . '">媒体中心</a>',
                '<br>', '
            <br>文章ID+幻灯图片地址：<code>123<span class="red">|</span>https://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            文章ID+文章默认的缩略图：<code>3434<span class="red">|</span>0</code><br><br>
            自定义链接+幻灯片图片地址：<code>/pages/single/page?id=10<span class="red">|</span>https://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            或：<code>https://baidu.com<span class="red">|</span>https://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            自定义链接支持本小程序链接和外部网址，根据情况建议使用本小程序内的链接，外链的网址在各个平台上存在兼容问题，本小程序的网址查看方法：在 hbuilderx 中将您的项目运行到浏览器，进入您要链接的页面，其网址中/pages/和后面部分就是当前页面的网址<br><br>
            其他小程序（格式为：其他小程序的appid:要跳转的页面）+幻灯片图片地址，比如：<code>wxe5f52902cf4de896<span class="red">:</span>/pages/single/page?id=10<span class="red">|</span>ttps://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            <br />
            幻灯图片比例为<code>16/7</code>
            '
            ),
            'options' => array('disable_codemirror' => true),
            'after_row' => '</div>'
        ));

        $post_type = apply_filters('b2_temp_post_type', array(
            'post-1' => array(
                'name' => __('网格模式', 'b2'),
                'img' => '/Assets/admin/images/post-1.svg'
            ),
            'post-3' => array(
                'name' => __('列表模式', 'b2'),
                'img' => '/Assets/admin/images/post-3.svg'
            )
        ));

        $options = array();
        $images = array();

        foreach ($post_type as $k => $v) {
            $options[$k] = isset($v['name']) ? $v['name'] : '';
            $images[$k] = isset($v['img']) ? $v['img'] : '';
        }

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="post-module set-hidden">',
            'name' => __('列表样式', 'b2'),
            'id' => 'post_type',
            'type' => 'radio_image',
            'options' => $options,
            'images_path' => B2_THEME_URI,
            'images' => $images,
            'default' => 'post-1',
        ));

        do_action('b2_temp_post_type_action', $index, $index_group);

        $index->add_group_field($index_group, array(
            'name' => __('排序方式', 'b2'),
            'id' => 'post_order',
            'type' => 'select',
            'options' => array(
                'new' => __('最新文章', 'b2'),
                'modified' => __('修改时间', 'b2'),
                'random' => __('随机文章', 'b2'),
                'sticky' => __('置顶文章', 'b2'),
                'views' => __('浏览最多文章', 'b2'),
                'comments' => __('评论最多文章', 'b2')
            ),
            'default' => 'new',
        ));

        $cats = array();

        $categories = get_categories(array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($categories as $category) {
            $cats[$category->term_id] = $category->name;
        }

        $index->add_group_field($index_group, array(
            'name' => '文章分类',
            'id' => 'post_cat',
            'desc' => __('请选择要显示的文章分类，可以拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $cats,
        ));

        // $index->add_group_field($index_group,array(
        //     'name'=>__('是否显示置顶文章','b2'),
        //     'id'=>'post_ignore_sticky_posts',
        //     'type'=>'select',
        //     'options'=>array(
        //         0=>__('显示置顶文章','b2'),
        //         1=>__('不显示置顶文章','b2')
        //     ),
        //     'default'=>1,
        // ));

        $index->add_group_field($index_group, array(
            'name' => __('缩略图比例', 'b2'),
            'id' => 'post_ratio',
            'type' => 'text',
            'default' => '4/3',
        ));

        $index->add_group_field($index_group, array(
            'name' => __('显示数量', 'b2'),
            'id' => 'post_count',
            'type' => 'text',
            'default' => 8,
            'after_row' => '</div>'
        ));

        $arr = array();

        $cats = get_terms('collection', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($cats as $cat) {
            $arr[$cat->term_id] = $cat->name;
        }

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="collection-module cmb-row set-hidden">',
            'name' => '请选择要显示的专题',
            'id' => 'collection',
            'desc' => __('可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
            'after_row' => '</div>'
        ));

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="shop-module cmb-row set-hidden">',
            'name' => '要在首页显示的商品',
            'id' => 'shopIDs',
            'desc' => sprintf(__('请填写要显示的商品ID，如果留空将显示4个最新的商品。每个ID占一行。如果需要自定义商品图片，请按照%s格式填写。%s最终的效果类似：%s%s', 'b2'), '<code>商品ID|图片地址</code>', '<br>', '<br>', '<code>12|https://baidu.com/image.jpg</code><br/><code>32</code><br/><code>89</code><br/><code>107</code>'),
            'type' => 'textarea',
        ));

        $index->add_group_field($index_group, array(
            'name' => sprintf(__('%s首页商品列表展现形式', 'b2'), $shop_name),
            'id' => 'shop_type',
            'type' => 'select',
            'options' => array(
                'grid' => __('网格', 'b2'),
                'list' => __('列表', 'b2')
            ),
            'default' => 'grid',
            'after_row' => '</div>'
        ));

        $arr = array();

        $cats = get_terms('document_cat', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($cats as $cat) {
            $arr[$cat->term_id] = $cat->name;
        }

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="document-module cmb-row set-hidden">',
            'name' => '请选择要显示的文档分类',
            'id' => 'document',
            'desc' => __('可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
            'after_row' => '</div>'
        ));

        $arr = array();

        $cats = get_terms('newsflashes_tags', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($cats as $cat) {
            $arr[$cat->term_id] = $cat->name;
        }

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="newsflashes-module cmb-row set-hidden">',
            'name' => '请选择要显示的' . $newsflashes_name . '分类',
            'id' => 'newsflashes',
            'desc' => __('可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
            'after_row' => '</div>'
        ));

        $arr = array();



        $cats = get_terms('circle_tags', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($cats as $cat) {
            $arr[$cat->term_id] = $cat->name;
        }

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="circle-module cmb-row set-hidden">',
            'name' => '请选择要显示的' . $circle_name,
            'id' => 'circle',
            'desc' => __('可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
            'after_row' => '</div>'
        ));

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="circle_id-module cmb-row set-hidden">',
            'name' => sprintf('每页要显示的%sID', $circle_name),
            'id' => 'circle_id',
            'type' => 'text',
            'desc' => __('如果要显示广场，请直接填写广场ID，如果要显示其他圈子，请直接填写ID，只能填写一个ID'),
            'after_row' => '</div>'
        ));

        $index->add_group_field($index_group, array(
            'before_row' => '<div class="announcement-module cmb-row set-hidden">',
            'name' => '要显示的公告ID',
            'id' => 'announcement',
            'type' => 'text',
            'desc' => __('如果留空将自动获取最新的一篇公告', 'b2'),
            'after_row' => '</div>'
        ));

        // $index->add_group_field($index_group,array(
        //     'before_row'=>'<div class="ads-module cmb-row set-hidden">',
        //     'name'    => '广告形式',
        //     'id'      => 'ads_type',
        //     'type'    => 'select',
        //     'options'=>[
        //         'uni'=>'uni-ad广告',
        //         'wx'=>'wx广告'
        //     ],
        //     'desc'=>__('目前支持DCloud的uni-ad广告（简称uni-ad广告）和微信小程序自带的流量主广告（简称wx广告）','b2')
        // ));

        // $index->add_group_field($index_group,array(
        //     'name'    => '广告ID',
        //     'id'      => 'ads_id',
        //     'type'    => 'text',
        //     'desc'=>__('开通广告后','b2')
        // ));


        $index->add_group_field($index_group, array(
            'before_row' => '<div class="menu-module cmb-row set-hidden">',
            'name' => '自定义导航',
            'id' => 'menu_custom',
            'type' => 'textarea_code',
            'desc' => sprintf(__('您可以使用如下格式设置添加自定义导航，每个导航占一行：%s', 'b2'), '<code>图标|名称|链接</code><br />比如：<br /><code>https://abc.com/icon.png|关于|/pages/about/my</code><br /><code>https://abc.com/icon2.png|练习|/pages/single/page?id=10</code><br />
            说明：图标建议使用png透明图片，iconfont.cn 有大量的图标，可以自行搜索下载。名称随意。网址根据情况建议使用程序内的链接，外链的网址在各个平台上存在兼容问题，网址常看方法：在 hbuilderx 中将您的项目运行到浏览器，进入您要链接的页面，其网址中/pages/和后面部分就是当前页面的网址。<br />
            设置完保存后，下面快速导航下拉框中会出现自定义的导航，您可以选择显示或者排序<br />
            <span class="red">添加完自定义导航后，请先保存，即可在下方快速导航框中选择您在此自定义的菜单</span>
            ')
        ));

        $opt = array(
            'shop' => __('商品', 'b2'),
            'circle' => $circle_name,
            'newsflashes' => $newsflashes_name,
            'collection' => $collection_name,
            'announcement' => $announcement_name,
            'document' => $document_name,
            'infomation' => $infomation_name,
        );

        $custom_menu = get_option('b2_app_index_normal');
        if (isset($custom_menu['index_group']) && !empty($custom_menu['index_group'])) {
            foreach ($custom_menu['index_group'] as $k => $v) {
                if (isset($v['menu_custom']) && $v['menu_custom']) {

                    $v['menu_custom'] = trim($v['menu_custom'], " \t\n\r\0\x0B\xC2\xA0");
                    $v['menu_custom'] = explode(PHP_EOL, $v['menu_custom']);
                    $arg = array();

                    foreach ($v['menu_custom'] as $k => $v) {
                        $_v = explode('|', $v);
                        if (isset($_v[1]) && $_v[1] && isset($_v[2]) && $_v[2]) {
                            $arg[md5($_v[2])] = $_v[1];
                        }
                    }

                    $opt = array_merge($opt, $arg);
                }
            }
        }

        $index->add_group_field($index_group, array(
            'name' => '快速导航',
            'id' => 'menu',
            'type' => 'pw_multiselect',
            'options' => $opt,
            'desc' => '<span class="red">如果修改了上面自定义导航的链接，需要重新保存，再在此重新选择排序</span>',
            'after_row' => '</div>'
        ));

        // $index->add_group_field($index_group,array(
        //     'before_row'=>'<div class="ads-module cmb-row set-hidden">',
        //     'name'    => '广告形式',
        //     'id'      => 'ads_type',
        //     'type'    => 'select',
        //     'options'=>[
        //         'uni'=>'uni-ad广告',
        //         'wx'=>'wx广告'
        //     ],
        //     'desc'=>__('目前支持DCloud的uni-ad广告（简称uni-ad广告）和微信小程序自带的流量主广告（简称wx广告）','b2')
        // ));

        // $index->add_group_field($index_group,array(
        //     'name'    => '广告ID',
        //     'id'      => 'ads_id',
        //     'type'    => 'text',
        //     'desc'=>__('开通广告后','b2')
        // ));



        $ads = new_cmb2_box(array(
            'id' => 'b2_app_ads_options_page',
            'object_types' => array('options-page'),
            'title' => __('微信小程序广告设置', 'b2'),
            'option_key' => 'b2_app_ads_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('广告', 'b2'),
        ));

        $ads->add_field(array(
            'name' => __('Adsecret', 'b2'),
            'id' => 'adsecret',
            'type' => 'text',
            'desc' => __('此为流量主广告确权使用，由系统自动生成，无需修改，请将此32位字符串复制到uniapp源码/store/index.js 文件第8行 adappid 等号后面的单引号中。如果此处您修改过，uniapp源码中也需要一同修改，保证两边一致。', 'b2')
        ));

        $ads->add_field(array(
            'before_row' => '<p>' . __('uni-ad 广告是 uniapp 平台的广告服务（开通方法：https://ask.dcloud.net.cn/article/39928），微信小程序广告，是微信官方的广告服务（开通方法：https://kf.qq.com/faq/120911VrYVrA140707qqEf6f.html）。', 'b2') . '</p><h2>首页模块广告</h2>',
            'name' => __('广告加到首页模块第几个', 'b2'),
            'id' => 'index_row',
            'type' => 'text',
            'desc' => __('从0开始计算，比如加到第二个模块之前，此处应该填写1。如果文章列表是网格形式，此处将不支持', 'b2')
        ));

        $ads->add_field(array(
            'name' => __('广告ID', 'b2'),
            'id' => 'index_id',
            'type' => 'text',
            'desc' => __('如果不使用，ID请留空', 'b2')
        ));

        $ads->add_field(array(
            'before_row' => '<h2>文章列表广告</h2>',
            'name' => __('广告加到文章列表第几行', 'b2'),
            'id' => 'post_row',
            'type' => 'text',
            'desc' => __('从0开始计算，比如加到第二个文章之前，此处应该填写1', 'b2')
        ));

        $ads->add_field(array(
            'name' => __('广告ID', 'b2'),
            'id' => 'post_id',
            'type' => 'text',
            'desc' => __('如果不使用，ID请留空', 'b2')
        ));

        $ads->add_field(array(
            'before_row' => '<h2>商品列表广告</h2>',
            'name' => __('广告加到商品列表第几行', 'b2'),
            'id' => 'shop_row',
            'type' => 'text',
            'desc' => __('从0开始计算，比如加到第二个商品之前，此处应该填写1', 'b2')
        ));

        $ads->add_field(array(
            'name' => __('广告ID', 'b2'),
            'id' => 'shop_id',
            'type' => 'text',
            'desc' => __('如果不使用，ID请留空', 'b2')
        ));

        $ads->add_field(array(
            'before_row' => '<h2>圈子话题列表广告</h2>',
            'name' => __('广告加到圈子话题第几行', 'b2'),
            'id' => 'topic_row',
            'type' => 'text',
            'desc' => __('从0开始计算，比如加到第二个话题之前，此处应该填写1', 'b2')
        ));

        $ads->add_field(array(
            'name' => __('广告ID', 'b2'),
            'id' => 'topic_id',
            'type' => 'text',
            'desc' => __('如果不使用，ID请留空', 'b2')
        ));

        // $ads->add_field(array(
        //     'before_row'=>'<h2>视频流量主广告</h2>',
        //     'name' => __('广告ID','b2'),
        //     'id'   => 'video_id',
        //     'type' => 'text',
        //     'desc'=>__('如果不使用，ID请留空','b2')
        // ));

        $ads->add_field(array(
            'before_row' => '<h2>下载流量主广告</h2>',
            'name' => __('广告ID', 'b2'),
            'id' => 'download_id',
            'type' => 'text',
            'desc' => __('如果不使用，ID请留空', 'b2')
        ));

        // $ads->add_field(array(
        //     'before_row'=>'<h2>隐藏内容流量主广告</h2>',
        //     'name' => __('广告ID','b2'),
        //     'id'   => 'hidden_id',
        //     'type' => 'text',
        //     'desc'=>__('不使用，ID请留空','b2')
        // ));

        // $ads_group = $ads->add_field( array(
        //     'id'          => 'ads_group',
        //     'type'        => 'group',
        //     'description' => __( '广告设置', 'b2' ),
        //     // 'repeatable'  => false, // use false if you want non-repeatable group
        //     'options'     => array(
        //         'group_title'       => __( '广告{#}', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
        //         'add_button'        => __( '添加新模块', 'b2' ),
        //         'remove_button'     => __( '删除模块', 'b2' ),
        //         'sortable'          => true,
        //         'closed'         => true, // true to have the groups closed by default
        //         'remove_confirm' => __( '确定要删除这个模块吗？', 'b2' ), // Performs confirmation before removing group.
        //     ),
        //     //'sanitization_cb'=>array($this,'b2_sanitize_value')
        // ));

        // $ads->add_group_field( $ads_group, array(
        //     'name' => __('广告位置','b2'),
        //     'id'   => 'where',
        //     'type' => 'select',
        //     'options'=> [
        //         'index'=>__('首页模块之间','b2'),
        //         'post'=>__('文章列表之间','b2'),
        //         'topic'=>__('圈子话题之间','b2'),
        //         'download'=>__('下载流量主广告','b2'),
        //         'video'=>__('视频流量主广告','b2'),
        //         'hidden'=>__('隐藏内容流量主广告','b2')
        //     ],
        //     'desc'=>__('首页、文章、圈子列表中的广告是信息流广告。下载、视频、隐藏内容是流量主广告','b2')
        //     // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        // ) );

        // $ads->add_group_field( $ads_group, array(
        //     'name' => __('广告加到每页的第几行','b2'),
        //     'id'   => 'row',
        //     'type' => 'text',
        //     'desc'=>__('仅在首页、文章、圈子列表中插入广告有效','b2')
        //     // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        // ) );

        // $ads->add_group_field( $ads_group, array(
        //     'name' => __('广告平台','b2'),
        //     'id'   => 'type',
        //     'type' => 'select',
        //     'options'=> [
        //         'uniapp'=>__('uni-ad 广告','b2'),
        //         'weixin'=>__('微信小程序广告','b2')
        //     ],
        //     'desc'=>__('uni-ad 广告是 uniapp 平台的广告服务（开通方法：https://ask.dcloud.net.cn/article/39928），微信小程序广告，是微信官方的广告服务（开通方法：https://kf.qq.com/faq/120911VrYVrA140707qqEf6f.html）。')
        //     // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        // ) );

        // $ads->add_group_field( $ads_group, array(
        //     'name' => __('广告ID','b2'),
        //     'id'   => 'id',
        //     'type' => 'text',
        //     'desc'=>__('要添加的广告ID','b2')
        // ) );

        $shop_page = new_cmb2_box(array(
            'id' => 'b2_app_shop_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_shop_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => $shop_name . __('设置', 'b2'),
        ));

        $shop_page->add_field(array(
            'before_row' => '<h2>微信小程序发货信息推送</h2>',
            'name' => '是否开启发货信息推送',
            'id' => 'open_wechat_push',
            'desc' => sprintf(__('如果您的小程序开通了发货信息推送功能，开启此项，当在主题订单管理中填写物流单号并且已发货时，小程序会向用户推送发货信息，方便用户在微信中查看物流进度。要使用此功能，需要确保您已经开通并使用了%s微信小程序登录%s', 'b2'), '<a href="admin.php?page=b2_app_login_normal" target="_blank">', '</a>'),
            'type' => 'select',
            'options' => [
                0 => __('关闭', 'b2'),
                1 => __('开启', 'b2')
            ],
            'default' => 0
        ));

        $shop_page->add_field(array(
            'name' => '发货人电话号码',
            'id' => 'open_wechat_push_pohne',
            'type' => 'text',
            'desc' => __('小程序中如果发货是顺丰快递，则要求必须填写发货人的联系方式，请填写发货人的联系方式'),
            'default' => ''
        ));

        $shop_page->add_field(array(
            'before_row' => '<h2>移动端商铺首页</h2>',
            'name' => $shop_name . __('首页幻灯内容', 'b2'),
            'id' => 'slider_list',
            'type' => 'textarea_code',
            'description' => sprintf(
                __('请输入商品ID，每组占一行，排序与此设置相同。图片可以在%s上传或选择。
            %s
            支持的格式如下：
            %s', 'b2'),
                '<a target="__blank" href="' . admin_url('/upload.php') . '">媒体中心</a>',
                '<br>', '
            <br>商品ID+幻灯图片地址：<code>123<span class="red">|</span>https://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            商品ID+文章默认的缩略图：<code>3434<span class="red">|</span>0</code><br>
            自定义链接+幻灯片图片地址：<code>/pages/single/page?id=10<span class="red">|</span>https://xxx.com/wp-content/uploads/xxx.jpg</code><br>
            自定义链接根据情况建议使用程序内的链接，外链的网址在各个平台上存在兼容问题，网址常看方法：在 hbuilderx 中将您的项目运行到浏览器，进入您要链接的页面，其网址中/pages/和后面部分就是当前页面的网址<br />
            幻灯图片比例为<code>16/7</code>
            '
            ),
            'options' => array('disable_codemirror' => true),
        ));

        $shops = get_terms('shoptype', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        $arr = array();

        foreach ($shops as $_shop) {
            $arr[$_shop->term_id] = $_shop->name;
        }

        $shop_page->add_field(array(
            'name' => '商品分类',
            'id' => 'shoptype',
            'desc' => __('商品首页会显示此菜单，可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $shop_page->add_field(array(
            'name' => $shop_name . '首页不显示哪些分类的商品',
            'id' => 'shop_ex',
            'desc' => $shop_name . __('首页，商品列表中将会排除这些分类的商品', 'b2'),
            'type' => 'pw_multiselect',
            'options' => $arr,
        ));

        $shop_page->add_field(array(
            'name' => '选择要显示的商品形式',
            'id' => 'shop_show_type',
            'desc' => $shop_name . __('首页要显示的商品形式', 'b2'),
            'type' => 'multicheck',
            'options' => array(
                'normal' => __('商品购买', 'b2'),
                'exchange' => __('积分兑换', 'b2'),
                'lottery' => __('积分抽奖', 'b2')
            ),
            'default' => array(
                'normal',
                'exchange',
                'lottery'
            )
        ));

        $shop_page->add_field(array(
            'name' => '每种商品在' . $shop_name . '首页显示几个',
            'id' => 'shop_type_count',
            'type' => 'text',
            'default' => 4
        ));

        $shop_page->add_field(array(
            'name' => $shop_name . '首页商品列表展现形式',
            'id' => 'shop_list_type',
            'type' => 'select',
            'options' => array(
                'grid' => __('网格', 'b2'),
                'list' => __('列表', 'b2')
            ),
            'default' => 'grid'
        ));

        $info_page = new_cmb2_box(array(
            'id' => 'b2_app_infomation_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_infomation_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => sprintf(__('%s首页', 'b2'), $infomation_name),
        ));

        $arr = array();

        $cats = get_terms('infomation_cat', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        foreach ($cats as $cat) {
            $arr[$cat->term_id] = $cat->name;
        }

        $info_page->add_field(array(
            'name' => '热门' . $infomation_name . '分类',
            'id' => 'infomatino_cats',
            'desc' => __('可拖动排序', 'b2'),
            'type' => 'pw_multiselect',
            'desc' => sprintf(__('请选择热门%s分类，最多8个', 'b2'), $infomation_name),
            'options' => $arr
        ));

        $about_page = new_cmb2_box(array(
            'id' => 'b2_app_about_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_about_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('关于页面', 'b2'),
        ));

        $about_page->add_field(array(
            'before_row' => '此设置会在个人中心，关于我们页面，和IOS端首屏协议弹框中体现，请务必保证协议页面中不要有复杂的代码，最好只保留 p 标签，h2 标签 和 纯文本，以免在弹窗中显示的时候出现样式的错误！',
            'name' => '关于我们页面',
            'id' => 'about_page',
            'type' => 'page_select_text', // This field type
            // post type also as array
            'post_type' => 'page',
            // Default is 'checkbox', used in the modal view to select the post type
            'select_type' => 'radio',
            // Will replace any selection with selection from modal. Default is 'add'
            'select_behavior' => 'replace',
            'desc' => '请直接填写关于我们页面ID，是<a href="' . admin_url('/edit.php?post_type=page') . '" target="_blank">页面的ID</a>，不是文章ID哦，如果没有，可以手动创建一个'
        ));

        $about_page->add_field(array(
            'name' => '隐私政策页面',
            'id' => 'site_privacy',
            'type' => 'page_select_text', // This field type
            // post type also as array
            'post_type' => 'page',
            // Default is 'checkbox', used in the modal view to select the post type
            'select_type' => 'radio',
            // Will replace any selection with selection from modal. Default is 'add'
            'select_behavior' => 'replace',
            'desc' => '请直接填写隐私政策页面ID，是<a href="' . admin_url('/edit.php?post_type=page') . '" target="_blank">页面的ID</a>，不是文章ID哦，如果没有，可以手动创建一个'
        ));

        $about_page->add_field(array(
            'name' => '用户协议页面',
            'id' => 'site_terms',
            'type' => 'page_select_text', // This field type
            // post type also as array
            'post_type' => 'page',
            // Default is 'checkbox', used in the modal view to select the post type
            'select_type' => 'radio',
            // Will replace any selection with selection from modal. Default is 'add'
            'select_behavior' => 'replace',
            'desc' => '请直接填写用户协议页面页面ID，是<a href="' . admin_url('/edit.php?post_type=page') . '" target="_blank">页面的ID</a>，不是文章ID哦，如果没有，可以手动创建一个'
        ));

        $pay_page = new_cmb2_box(array(
            'id' => 'b2_app_pay_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_pay_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('支付', 'b2'),
        ));

        $pay_page->add_field(array(
            'before_row' => '<h2>APP微信支付设置</h2>',
            'name' => '微信开放平台上移动应用的AppID',
            'id' => 'pay_appid_mpopen',
            'type' => 'text',
            'desc' => '<p>此处只在APP端微信支付的时候使用，请注意，开放平台的移动应用必须和您的微信商户进行了绑定。微信商户的信息在<a href="' . admin_url('/admin.php?page=b2_normal_pay') . '" target="_blank">B2主题设置->常规设置->支付设置中</a>，微信选择微信官方，下面会有需要填写的商户信息，如果没有服务号，只填写商户号和秘钥即可。</p>'
        ));

        $pay_page->add_field(array(
            'before_row' => '<h2>百度小程序支付</h2><ul>
                <li>1、百度小程序支付为聚合支付，支持微信、支付宝、百度钱包、银行卡等</li>
                <li>2、需要开通百度小程序的支付功能，请前往百度小程序后台->管理->功能管理->支付管理中申请开通，申请的时候需要生成填写公钥。私钥请填写到下面的设置项中。方法请参考：<a href="https://www.kancloud.cn/xcxxcx/bdxcx/890347" target="_blank">百度小程序公私钥生成</a></li>
                </ul>',
            'name' => '百度小程序支付的appKey',
            'id' => 'pay_baidu_appkey',
            'type' => 'text',
            'desc' => '百度小程序后台->管理->功能管理->支付管理中'
        ));

        $pay_page->add_field(array(
            'name' => '百度小程序支付的dealId',
            'id' => 'pay_baidu_dealId',
            'type' => 'text',
            'desc' => '百度小程序后台->管理->功能管理->支付管理中'
        ));

        $pay_page->add_field(array(
            'name' => '百度小程序支付的私钥',
            'id' => 'pay_baidu_priKey',
            'type' => 'textarea',
            'desc' => '上面使用工具生成的私钥，注意不要有换行，否则会报错'
        ));

        $pay_page->add_field(array(
            'name' => '百度小程序支付的平台公钥',
            'id' => 'pay_baidu_publicKey',
            'type' => 'textarea',
            'desc' => '百度小程序后台->管理->功能管理->支付管理->支付设置->平台公钥'
        ));

        $pay_page->add_field(array(
            'before_row' => '<h2>字节小程序（抖音）支付</h2><div>
            <p>1、请先前往字节小程序官网签约开通支付：<a href="https://microapp.bytedance.com/" target="_blank">https://microapp.bytedance.com/</a></p>
            <p>2、字节小程序后台->功能管理->支付->支付设置URL(服务器地址)中填写：' . home_url('/notify') . '</p>
            </div>',
            'name' => '小程序appid',
            'id' => 'pay_toutiao_appid',
            'type' => 'text',
            'desc' => '字节小程序后台->功能管理->支付->支付设置中查看 appid',
        ));

        $pay_page->add_field(array(
            'name' => 'Token(令牌)',
            'id' => 'pay_toutiao_token',
            'type' => 'text',
            'desc' => '字节小程序后台->功能管理->支付->支付设置中查看 Token(令牌)',
        ));

        $pay_page->add_field(array(
            'name' => 'SALT',
            'id' => 'pay_toutiao_salt',
            'type' => 'text',
            'desc' => '字节小程序后台->功能管理->支付->支付设置中查看 SALT',
        ));

        $pay_page->add_field(array(
            'before_row' => '<h2>合规设置</h2>',
            'name' => 'IOS端是否启允许购买虚拟物品',
            'id' => 'pay_allow_mpweixin',
            'type' => 'select',
            'options' => array(
                0 => __('IOS端关闭虚拟物品支付', 'b2'),
                1 => __('IOS端开启虚拟物品支付', 'b2')
            ),
            'desc' => '<p>根据苹果公司和微信小程序相关规定，ios端（包括各种小程序和APP）不能使用第三方支付系统购买虚拟物品，建议保持关闭状态。如果您执意要开启此功能，您的小程序和APP可能受到如下惩罚：</p>
            <p>1. 永封“iOS支付接口”功能，小程序iOS端将无法调起支付能力</p>
            <p>2. 永封“iOS用户搜索小程序”功能，iOS端将无法在搜索栏中搜索到该小程序</p>
            <p>3. 下架7天限时整改通知，到期未整改，则永封“iOS用户打开小程序”功能：届时iOS端将无法打开该小程序。</p>
            <p>4. 苹果应用商店下架等。</p>'
        ));

        $pay_page->add_field(array(
            'name' => '单个版本禁止支付',
            'id' => 'pay_disabled_var',
            'type' => 'text',
            'desc' => 'IOS平台下，如果您只需要某个版本不启用虚拟物品支付，请在此输入该版本的版本号，比如：版本号为1.1.0，请直接输入 1.1.0。'
        ));

        $login_page = new_cmb2_box(array(
            'id' => 'b2_app_login_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_login_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('登录与注册', 'b2'),
        ));

        $login_page->add_field(array(
            'before_row' => '<h2>uni 一键登录设置</h2><p>此功能为app用户登录时自动获取手机号码，直接点击登录，不需要验证短信。uni 平台提供接入，费用为2分钱每次，比短信验证便宜一倍，使用体验也非常好，推荐使用app的客户使用。</p><p>您需要登录<a href="https://dev.dcloud.net.cn/" target="_blank">DCloud开发者中心</a>，申请开通一键登录服务。</p>
            <p>详细步骤参考：<a href="https://ask.dcloud.net.cn/article/37965" target="_blank">开通一键登录服务的详细教程</a></p>
            <p>详细教程：<a href="https://7b2.com/document/53704.html" target="_blank">B2APP配置uni一键登录。</a></p>',
            'name' => 'Secret',
            'id' => 'login_secret',
            'type' => 'text',
            'desc' => '<p>说明：该 Secret 由系统自动生成，或您自己手动填写，长度为32位字母加数字。用于uniapp一键登录时鉴权所用。</p><p>1、初次使用请将此值复制到uniapp源码->uniCloud目录->getPhoneNumber目录->index.js的第七行单引号中，替换your-secret-string</p><p>2、如果您修改了此值，请按照1中方法重新复制到Uniapp源码中</p><p>3、Uniapp源码中修改完成，在编辑器左侧找到uniCloud，右键选择 运行云服务器初始化向导，如果询问是否替换，点击替换完成即可。</p>'
        ));

        $login_page->add_field(array(
            'name' => '云函数URL',
            'id' => 'login_yunurl',
            'type' => 'text',
            'desc' => '<p>1、进入您的<a href="https://unicloud.dcloud.net.cn/">unicloud</a></p><p>2、找到getPhoneNumber这个云函数，点击详情</p><p>3、找到云函数URL化，点编辑，在后面填写/getPhoneNumber并保存</p><p>4、将云函数URL前面部分复制到此处类似：https://cbd2ef24-663b-2466-93ed-0ba234527a3.bspapp.com</p>'
        ));

        $login_page->add_field(array(
            'before_row' => '<h2>微信小程序登录设置</h2>',
            'name' => '微信小程序AppID',
            'id' => 'weixin_appid',
            'type' => 'text',
            'desc' => '<p>登录微信小程序后台（<a href="https://mp.weixin.qq.com/" target="_blank">https://mp.weixin.qq.com/</a>）->开发->开发管理->开发设置->AppID</p>'
        ));

        $login_page->add_field(array(
            'name' => '微信小程序AppSecret',
            'id' => 'weixin_appsecret',
            'type' => 'text',
            'desc' => '<p>登录微信小程序后台（<a href="https://mp.weixin.qq.com/" target="_blank">https://mp.weixin.qq.com/</a>）->开发->开发管理->开发设置->AppSecret</p>'
        ));

        $login_page->add_field(array(
            'before_row' => '<h2>百度小程序登录设置</h2>',
            'name' => '百度小程序 App Key',
            'id' => 'baidu_appkey',
            'type' => 'text',
            'desc' => '<p>百度小程序后台（<a href="https://smartprogram.baidu.com/developer/applist.html" target="_blank">百度小程序管理后台</a>）->管理->基础设置->开发设置->App Key</p>'
        ));

        $login_page->add_field(array(
            'name' => '百度小程序 App Secret',
            'id' => 'baidu_appsecret',
            'type' => 'text',
            'desc' => '<p>百度小程序后台（<a href="https://smartprogram.baidu.com/developer/applist.html" target="_blank">百度小程序管理后台</a>）->管理->基础设置->开发设置->App Secret</p>'
        ));

        $login_page->add_field(array(
            'before_row' => '<h2>字节跳动（抖音）小程序登录设置</h2>',
            'name' => 'Appid',
            'id' => 'toutiao_appid',
            'type' => 'text',
            'desc' => '<p>字节小程序后台（<a href="https://microapp.bytedance.com/app" target="_blank">字节小程序管理后台</a>）->开发管理->开发设置->AppID</p>'
        ));

        $login_page->add_field(array(
            'name' => 'Secret',
            'id' => 'toutiao_secret',
            'type' => 'text',
            'desc' => '<p>字节小程序后台（<a href="https://microapp.bytedance.com/app" target="_blank">字节小程序管理后台</a>）->开发管理->开发设置->AppSecret</p>'
        ));

        $message = new_cmb2_box(array(
            'id' => 'b2_app_message_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_message_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('通知消息', 'b2'),
        ));

        $message->add_field(array(
            'before_row' => '<h2>APP消息推送设置</h2><p>此功能仅限手机APP包含安卓和IOS端，此为系统给独立用户推送消息的功能，如果需要给所有用户推送指定消息，请前往 <a href="https://dev.dcloud.net.cn/pages/app/push2/index" target="_blank">dcloud开发者后台</a> 操作，消息推送中操作。</p><p>使用前请查看<a href="https://7b2.com/document/59355.html" target="_blank" class="red">开通流程</a>。</p>',
            'name' => 'Secret',
            'id' => 'app_msg_secret',
            'type' => 'text',
            'desc' => '<p>说明：该 Secret 由系统自动生成，或您自己手动填写，长度为32位字母加数字。用于uniapp的APP中消息推送所用。</p><p>1、初次使用请将此值复制到uniapp源码->uniCloud目录->unipush目录->index.js的第七行单引号中，替换your-secret-string</p><p>2、如果您修改了此值，请按照1中方法重新复制到Uniapp源码中</p><p>3、Uniapp源码中修改完成，在编辑器左侧找到uniCloud，右键选择 运行云服务器初始化向导，如果询问是否替换，点击替换完成即可。</p>'
        ));

        $message->add_field(array(
            'name' => '云函数URL',
            'id' => 'msg_yunurl',
            'type' => 'text',
            'desc' => '<p>1、进入您的<a href="https://unicloud.dcloud.net.cn/">unicloud</a></p><p>2、找到unipush这个云函数，点击详情</p><p>3、找到云函数URL化，点编辑，在后面填写/push并保存</p><p>4、将云函数URL前面部分复制到此处类似：https://cbd2ef24-663b-2466-93ed-0ba234527a3.bspapp.com</p>'
        ));

        // $message->add_field(array(
        //     'before_row'=>'<h2>微信小程序订阅消息</h2></p>',
        //     'name'    => '是否启用微信小程序订阅消息',
        //     'id'      => 'msg_weixin',
        //     'type'    => 'select',
        //     'options'=>[
        //         1=>__('开启小程序订阅消息','b2'),
        //         2=>__('不使用小程序订阅消息')
        //     ]
        // ));

        $share_page = new_cmb2_box(array(
            'id' => 'b2_app_share_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_share_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('分享', 'b2'),
        ));

        $share_page->add_field(array(
            'name' => '微信小程序原始ID',
            'id' => 'weixin_gid',
            'type' => 'text',
            'desc' => '<p>登录微信小程序后台（<a href="https://mp.weixin.qq.com/" target="_blank">https://mp.weixin.qq.com/</a>）->设置->基本设置->最下面的原始ID（请确保您HbuilderX项目中manifest.json文件->微信小程序配置->原始ID一致）</p>'
        ));

        $share_page->add_field(array(
            'name' => 'APP端分享到微信形式',
            'id' => 'weixin_share_type',
            'type' => 'select',
            'options' => array(
                'web' => __('网页形式', 'b2'),
                'miniapp' => __('小程序形式', 'b2')
            ),
            'desc' => '<p>APP端，用户点击分享到微信后的链接是网页形式，还是小程序形式。如果是网页形式，用户点击分享后进入相应的网页。如果是小程序，用户点击之后将进入对应的小程序页面，如果选择小程序，需要您已经上传了小程序，并且已经审核通过正式上线</p>'
        ));

        $search_page = new_cmb2_box(array(
            'id' => 'b2_app_search_options_page',
            'object_types' => array('options-page'),
            'title' => __('B2主题-移动端专用插件', 'b2'),
            'option_key' => 'b2_app_search_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('搜索', 'b2'),
        ));

        $search_page->add_field(array(
            'name' => '搜索页面要显示的热门搜索词',
            'id' => 'search_keys',
            'type' => 'textarea',
            'desc' => '<p>每行一个关键词</p>'
        ));

        $upgrade_page = new_cmb2_box(array(
            'id' => 'b2_app_upgrade_options_page',
            'object_types' => array('options-page'),
            'title' => __('插件升级', 'b2'),
            'option_key' => 'b2_app_upgrade_normal',
            'parent_slug' => '/admin.php?page=b2_app_options',
            'tab_group' => 'b2_app_options',
            'tab_title' => __('插件升级', 'b2'),
            'display_cb' => array($this, 'upgrade_option_page_cb')
        ));


    }

    public static function cb_options_page_tabs($cmb_options)
    {
        $tab_group = $cmb_options->cmb->prop('tab_group');
        $tabs = array();
        foreach (\CMB2_Boxes::get_all() as $cmb_id => $cmb) {
            if ($tab_group === $cmb->prop('tab_group')) {
                $tabs[$cmb->options_page_keys()[0]] = $cmb->prop('tab_title')
                    ? $cmb->prop('tab_title')
                    : $cmb->prop('title');
            }
        }
        return $tabs;
    }

    public function upgrade_option_page_cb($cmb_options)
    {
        $tabs = self::cb_options_page_tabs($cmb_options);
        //获取当前插件的版本
        $plugin_data = get_plugin_data(B2_APP_PATH . 'b2-app.php');
        ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if (get_admin_page_title()): ?>
                <h2><?php echo wp_kses_post(get_admin_page_title()); ?></h2>
            <?php endif; ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $option_key => $tab_title): ?>
                    <a class="nav-tab<?php if (isset($_GET['page']) && $option_key === $_GET['page']): ?> nav-tab-active<?php endif; ?>"
                        href="<?php menu_page_url($option_key); ?>"><?php echo wp_kses_post($tab_title); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap" id="b2-app-upgrade-box">
                <h2 style="margin-bottom: 10px;">当前插件版本：<?php echo $plugin_data['Version']; ?></h2>

                <div id="b2-app-upgrade-box-content" v-if="data">
                    <p v-if="data?.new_version == '<?php echo $plugin_data['Version']; ?>'">
                        <span class="green">当前已是最新版本</span>
                    </p>
                    <div v-else style="display: flex;flex-direction: column;gap: 5px;" class="green">
                        <span>新版本：{{data.new_version}}</span>
                        <span>发布时间：{{data.published_at}}</span>
                        <span>更新内容：<a :href="data.info" target="_blank">查看</a></span>
                        <div style="margin-top:10px">
                            <button class="button button-primary" @click="upgrade()" :disabled="loading != ''">
                                <span v-if="loading == 'waiting'" style="display:flex;padding-right:16px">升级中<b class="dots" style="margin-left:5px"></b></span>
                                <span v-else-if="loading == 'success'">升级完成</span>
                                <span v-else>升级到最新版</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <button class="button button-primary" @click="checkUpdate()" :disabled="loading == 'waiting'">检查更新</button>
                </div>

            </div>
        </div>
        <?php
    }
}
