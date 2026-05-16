<?php
use B2\Modules\Common\User;
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Index
{
    //默认设置项
    public static $default_settings = [
        //基本设置
        'open'=>1,
        'close'=>0,
        'one_header_image_mr'=>B2_CHILD_URI.'/Center/Assets/images/one_header.png',
        'index_mp4_hight'=>'800px',
        'ico_name'=>'Jifont',
        'ico_font_family'=>'logo',
        //网站动态
        'color1'=> '#f78da7',
        'color2'=> '#ff6900',
        'color3'=> '#fcb900',
        'color4'=> '#f7c32e',
        'color5'=> '#00d084',
        'color6'=> '#8ed1fc',
        'color7'=> '#0693e3',
        'color8'=> '#9b51e0',
        'one_video_mp4'=>'https://www.chuangkit.com/distweb/media/index_bg_video.6d1136d1.mp4',
        'one_about_image'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'onecad_padding'=>'0px',
        'onecad_Archive_list_color'=>'',
        'home_search_img'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'Archive_image'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'index_circles_top_img'=>'',
        'index_circles_ct_img'=>'',
        //顶部VIP设置
        'onecad_vips_title'=>'欢迎加入极主题VIP，开通会员尊享特权',
        'index_onecad_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad-home-vip-bg.png',
        'index_onecad_vip_title'=>'会员尊享权益圈子',
        
        'index_onecad_vip_desc'=>'成为我们的VIP会员，尊享无限免费下载使用，更享受全面的服务与福利',
        'index_onecad_vip_tj_title'=>'推荐购买',
        'home_search_desc'=>'开通会员尊享特权',
        'home_search_text'=>'海量精选模板素材供你挑选，立即输入关键字搜索',
        'home_search_btn'=>'提交问答',
        'home_search_btn_link'=>'/ask',
        //登录框优化
        'onecad_diy_dl_img'=>B2_CHILD_URI.'/Center/Assets/images/denglu_img.png',
        //首页自定义搜索模块
        'index_onecad_search_title'=>'海量优质素材欢迎下载',
        'index_onecad_search_desc'=>'开通VIP免费下载全站内容',
        'index_onecad_search_kuangtext'=>'输入关键词 按回车搜索',
        'index_diy_search_list_link'=>'<a href="链接" class="fl" target="_blank">标题</a>',
        'index_diy_search_list_img'=>B2_CHILD_URI.'/Center/Assets/images/icon.svg',
        'index_onecad_search_xtimg'=>B2_CHILD_URI.'/Center/Assets/images/hot.svg',
        'index_onecad_search_color'=>'#fff',
        'index_onecad_search_hot'=>'热门搜索',
        'home_search_1_title'=>'极主题-美化最好看的B2子主题',
        'home_search_2_title'=>'完美售后，集成后台，不断更新',
        'home_search_3_title'=>'子主题不断更新中......',
        //区块
        'index_wuzu_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
        'index_wuzu_desc'=>'https://www.jitheme.com/',
        'qukuai_hd2_img'=>B2_CHILD_URI.'/Render/img/20221129105017635.png',
        'index_wuzu_title'=>'极主题',
        //推荐作者模块
        'one_index_tj_user_img' =>B2_CHILD_URI.'/Center/Assets/images/tj.svg',
        'one_index_tj_user_title'=>'推荐作者',
        'one_index_tj_user_desc'=>'推荐作者描述',
        'one_index_tj_user_id'=>'1,1,1,1',
        'one_index_tj_user_hang'=>'4',
        'one_index_tj_user_toub_title'=>'极主题',
        'one_index_tj_user_toub_desc'=>'利用本网站庞大的曝光矩阵，全方位提升你的个人影响力，获得更多职场机会和收入。',
        'one_index_tj_user_toub_user'=>'8786',
        'one_index_user_img'=>B2_CHILD_URI.'/Center/Assets/images/hy.svg',
        'one_index_user_title'=>'活跃用户',
        'one_index_user_desc'=>'每天精神抖擞的精神气用户',
        'one_index_user_id'=>'1,1,1,1',
        //区块模块
        'index_qukuai_title_hz'=>'',
        'index_qukuai_title_hz_color'=>'#ff6000',
        'index_qukuai_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
        //圈子问答
        'one_index_circle_tags_title'=>'圈子分类标题',
        'one_index_document_img' =>B2_CHILD_URI.'/Center/Assets/images/wd.svg',
        'one_index_circle_tags_desc'=>'圈子分类描述',
        'one_index_document_title'=>'问答社区',
        'one_index_document_desc'=>'问答社区描述',
        'one_index_document_shuliang'=>10,
        //圈子设置
        'index_circles_title'=>'链接标题',
        'index_circles_title_hz'=>'https://www.jitheme.com/',
        'index_circles_desc'=>'链接描述',
        //分类集合
        'one_index_fenlei_xfl'=>'1,1,1,1,1,1',
        'one_index_fenlei_fldh'=>'1',
        'one_index_fenlei_fldh_id'=>'1,1,1,1,1,1,1,1,1,1,1,1',
        'one_index_fenlei_title'=>'大分类展示',
        'one_index_fenlei_desc'=>'大分类描述',
        'one_index_fenlei_fl'=>'1,1,1,1',
        'ico_title'=>'jitheme',
        //快捷导航
        'jitheme_ico'=>B2_CHILD_URI.'/Center/Assets/images/kjdh/202205250906256.png|小程序|http://www.jitheme.com/',
        'jitheme_img'=>B2_CHILD_URI.'/Center/Assets/images/kjdh/a01.png',
        //5组图片切换
        'i7zimg_zs'=>5,
        //首页模块
        'move_hight'=>'0px',
    ];
	
    public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_index_page']);
    }
    //构造页面功能参数

    public function Jitheme_index_page(){
        $index = new_cmb2_box( array(
            'id'           => 'Jitheme_index_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_main',
            'tab_group'    => 'b2_Jitheme_index_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('首页设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $index->add_field(array(
            'name'    => __( '显示首页模块描述', 'b2' ),
            'desc'    => __( '各个模块标题下方的描述,排除B2父主题首页模块的描述,可在首页模块中关闭。', 'b2' ),
            'id'      =>  'index_descmk_off',
            'type'             => 'select',
            'default'          => self::$default_settings['close'],
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $index->add_field(array(
            'name'    => __( '首页是否弹出公告', 'b2' ),
            'desc'    => __( '首页会有一个好看的弹窗公告，每次打开首页就弹出最新一条的公告内容。', 'b2' ),
            'id'      =>  'index_gg_off',
            'type'             => 'select',
            'default'          => self::$default_settings['close'],
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $index->add_field(array(
            'name'    => __( '是否开启首页导航透明', 'b2' ),
            'desc'    => __( '首页导航透明状态，非常漂亮的。<span class="red">本功能只针对顶部布局形式01 02 03有作用，其他的会出现错位！</span">。', 'b2' ),
            'id'      =>'ji_top_tm',
            'type'             => 'select',
            'default'          => self::$default_settings['open'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $index->add_field(array(
            'name'    => __( '文章导航样式切换', 'b2' ),
            'desc'    => __( '文章标题及分类样式。', 'b2' ),
            'id'      =>  'index_post_tabs',
            'type'             => 'select',
            'default'          => self::$default_settings['close'],
            'options'          => array(
                1 => __( '样式A', 'b2' ),
                0   => __( '样式B', 'b2' ),
            ),
        ));
        $index->add_field(array(
            'name'    => '倒计时结束日期',
            'id'      => 'time_data',
            'type'    => 'text_date',
            'date_format' => 'Y/m/d',
            'default'          => '2022/10/10',
            'desc'    => __('<br>设置一个活动截止日期<br>区块的倒计时也改在这里设置','b2'),
        ));
        $index->add_field(array(
            'name'    => '倒计时结束时间',
            'id'      => 'text_date_h',
            'desc'    => __('添加一个准确结束时间','b2'),
            'type'    => 'text',
            'default'          => '00:00:00',
        ));
        self::Jitheme_index_home();
        self::Jitheme_index_tab2();
        self::Jitheme_index_sous();
        self::Jitheme_index_tab10();
        self::Jitheme_index_tab3();
        self::Jitheme_index_tab12();
        self::Jitheme_index_tab4();
        self::Jitheme_index_tab5();
        self::Jitheme_index_tab6();
        self::Jitheme_index_tab7();
        self::Jitheme_index_tab9();
        self::Jitheme_index_tab8();
        // self::Jitheme_index_tab11();
    }
    public function Jitheme_index_sous(){
        $home_search = new_cmb2_box(array(
            'id' => 'b2_jitheme_home_search',
            'tab_title' => __('IMG搜索', 'b2'), // 设置新的tab标题
            'object_types' => ['options-page'],
            'option_key' => 'b2_jitheme_home_search',
            'parent_slug'     => '/admin.php?page=b2_jitheme_home_search',
            'tab_group' => 'b2_Jitheme_index_options',
            'save_button'     => __( '保存设置', 'b2' )
        ));
        $home_search->add_field(array(
            'name' =>__('搜索模块背景图片','b2'),
            'id'   => 'home_search_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['home_search_img'],
            'desc'=>'必须选择采用图片方式才可以显示，否则失效',
        ) );
        $home_search->add_field(array(
            'name' => __('模块轮播标题【1】','b2'),
            'id'   => 'home_search_1_title',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_1_title'],
            'desc'=> '搜索框上方轮播标题',
        ) );
        $home_search->add_field(array(
            'name' => __('模块轮播标题【2】','b2'),
            'id'   => 'home_search_2_title',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_2_title'],
            'desc'=> '搜索框上方轮播标题',
        ) );
        $home_search->add_field(array(
            'name' => __('模块轮播标题【3】','b2'),
            'id'   => 'home_search_3_title',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_3_title'],
            'desc'=> '搜索框上方轮播标题',
        ) );
        $home_search->add_field(array(
            'name' => __('副标题','b2'),
            'id'   => 'home_search_desc',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_desc'],
            'desc'=> '轮播标题下副标题',
        ) );
        $home_search->add_field(array(
            'name' => __('搜索框默认语','b2'),
            'id'   => 'home_search_text',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_text'],
        ) );
        $home_search->add_field(array(
            'name' => __('右侧按钮标题','b2'),
            'id'   => 'home_search_btn',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_btn'],
        ) );
        $home_search->add_field(array(
            'name' => __('右侧按钮链接','b2'),
            'id'   => 'home_search_btn_link',
            'type' => 'text',
            'default'          => self::$default_settings['home_search_btn_link'],
        ) );
        $home_search->add_field(array(
            'name'    => __( '搜索关键词', 'b2' ),
            'id'=>'home_search_key',
            'type' => 'textarea_small',
            'desc'=>__('请输入关键词，每个关键词占一行，留空则不显示','b2'),
            'default'=>''
        ) );
    }
    public function Jitheme_index_home(){
        $lvs = User::get_user_roles();
        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        if(b2_get_option('verify_main','verify_allow')){
            $setting_lvs['verify'] = __('认证用户','b2');
        }
        $index_home= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_index_home_options_page',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_index_home', // The option key and admin menu page slug.            
        'tab_title'    => __('首页模块','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_index_home',
        'tab_group'    => 'b2_Jitheme_index_options',
        ) );
        $index_group = $index_home->add_field( array(
            'id'          => 'index_group',
            'type'        => 'group',
            'description' => '极主题-首页内容模块布局（点击小箭头可展开设置）<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span><br>使用本子主题必须使用此首页模块，B2的原有首页即将失去作用，B2所添加的模块都必须使用短代码调用方式引入。<br>具体看下教程<a  target=“_blank” style="color: #ff0000;" href="https://www.bilibili.com/video/BV1E24y1S7cJ/">极主题2.3.0版本更新内容设置方法</a>',
            // 'repeatable'  => false, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '模块{#}', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加新模块', 'b2' ),
                'remove_button'     => __( '删除模块', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个模块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_home->add_group_field( $index_group, array(
            'name' => sprintf(__('模块标题%s','b2'),'<span class="red">（必填）</span>'),
            'id'   => 'title',
            'type' => 'text',
            'attributes' => array(
                'required' => 'required',
              ),
            'desc'=> __('给这个模块起个名字，某些模块下会显示这个标题','b2')
            // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ) );

        $index_home->add_group_field( $index_group, array(
            'name' => sprintf(__('模块key%s','b2'),'<span class="red">（必填）</span>'),
            'id'   => 'key',
            'type' => 'text',
            'attributes' => array(
                'required' => 'required',
              ),
            'desc'=> sprintf(__('%s如果你调用原B2的首页模块，请将这个Key的值与您对应的B2模块的值一致%s，一般情况下不需要随意改动，这个key将和它对应的小工具挂钩，模块顺序变了以后不影响小工具的显示，如果改动这个值，你将无法调用B2模块所设置的小工具，如果没有小工具，就可随意填写','b2'),'<b class="red">','</b>')
            // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ) );
        // $index_home->add_group_field( $index_group, array(
        //     'name' => __('模块前置图标','b2'),
        //     'id'   => 'module_title_img',
        //     'type' => 'file',
        //     'options' => array(
        //         'url' => true, 
        //     ),
        //     'desc'=> __('标题可设置一个漂亮的图标','b2'),
        //     // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        // ) );
        $index_home->add_group_field( $index_group, array(
            'name' => __('模块可见性','b2'),
            'id'   => 'module_mobile_show',
            'type' => 'select',
            'options' => array(
                1 => __('桌面和移动端都显示','b2'), 
                0=>__('仅桌面可见','b2'),
                2=>__('仅移动端可见','b2'),
                3=>__('都不显示','b2'),
            ),
        ) );
        $index_home->add_group_field( $index_group, array(
            'name' => __('幻灯宽度','b2'),
            'id'   => 'slider_width',
            'type'             => 'select',
            'default'          => 1,
            'options'          => array(
                1 => __( '与页面同宽', 'b2' ),
                0   => __( '铺满窗口', 'b2' ),
            ),
        ));
        // $index_home->add_group_field($index_group,array(
        //     'name' => __('允许此模块查看的级别','b2'),
        //     'id'   => 'jitheme_role',
        //     'type' => 'multicheck_inline',
        //     'options'=>$setting_lvs,
        //     'desc'=> __('选择之后，这些等级的用户发布文章之后不用审核','b2')
        // ));
        $index_home->add_group_field($index_group,array(
            'name'=>__('上下浮动','b2'),
            'id'=>'move_hight_px',
            'type'    => 'text',
            'desc'=>__('此模块上下浮动数，正数向下负数向上,不设置即为不上浮需要带单位px','b2'),
            'default'=>'0px',
        ));
        
        $index_home->add_group_field( $index_group, array(
            'name' => __('是否启用间距/背景图片','b2'),
            'id'      => 'padding_off',
            'type'    => 'select',
            'options' => array(
                0 => __( '开启', 'b2' ),
                1 => __( '关闭', 'b2' ),
            ),
            'default'          => 1,
            'desc'=> __('模块上下会有一个16PX的间距','b2')
        ) );
        
        $index_home->add_group_field($index_group,array(
            'before_row'=>'<div id="index_onecad_search_ss" class="jitheme-module cmb-row">',
            'name'=>__('设置间距','b2'),
            'id'=>'padding_px',
            'type'    => 'text',
            'desc'=>__('注意顺序(上 右 下 左)间距为一个空格','b2'),
            'default'=>'16px 0px 16px 0px',
        ));
        $index_home->add_group_field( $index_group, array(
            'name' => __('模块背景颜色','b2'),
            'id'   => 'module_bg_color',
            'type' => 'colorpicker',
            'default'=>'',
            'desc'=> __('某些模块设置背景颜色会生效，如果不设置，显示默认背景颜色，请点击清空','b2')
            // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ) );

        $index_home->add_group_field( $index_group, array(
            'name' => __('模块背景图片','b2'),
            'id'   => 'module_bg_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('一些配置低的电脑或手机可能造成卡顿，请尽量上传提交较小的图片','b2'),
            // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
            'after_row'=>'</div>',
        ) );
        $index_home->add_group_field( $index_group, array(
            'name' => __('调用内容','b2'),
            'id'      => 'module_type',
            'type'    => 'radio_inline',
            'classes' => 'model-picked',
            'options' => array(
                0 => __( '极主题模块', 'b2' ),
                1 => __( 'B2短代码', 'b2' ),
                2 => __( 'B2首页调用', 'b2' ),
            ),
            'default'          => 0,
        ) );
        $index_home->add_group_field( $index_group, array(
            'before_row'=>'<div class="sliders-module cmb-row  0-module set-hidden">',
            'name'    => __( '模块样式', 'b2' ),
            'id'=>'jitheme_Module',
            'type' => 'radio_image',
            'options'          => array(
                'index_Search_A' => __('MP4/幻灯背景-搜索','b2'), 
                'index_Search_B' => __('图片背景-搜索','b2'), 
                'index_qk' => __('区块A','b2'), 
                'index_qub' => __('区块B','b2'), 
                'index_fl' => __('大分类表','b2'), 
                'index_user' => __('首推作者','b2'),
                'hotUser' => __('活跃会员','b2'), 
                'index_document' => __('问答列表','b2'),
                'index_diy_archive' => __('分类','b2'),
                'index_gg_5z' => __('图片切换','b2'), 
                'index_kjdh' => __('快捷导航','b2'), 
                'index_sjdh' => __('手机菜单','b2'), 
                'index_vip' => __('VIP模块','b2'),
                'index_new' => __('网站动态','b2'),
                'index_gg' => __('自助广告','b2'),
                // 'index_rili' => __('网站日历','b2'),
            ),
            'classes'=>array('cmb-type-radio-image'),
            'images_path'  => B2_CHILD_URI,
            'images'       => array(
                'index_Search_A' => '/Center/Assets/admin/images/jitheme-1.png',
                'index_Search_B' => '/Center/Assets/admin/images/jitheme-1.png',
                'index_qk' => '/Center/Assets/admin/images/jitheme-2.png',
                'index_qub' => '/Center/Assets/admin/images/jitheme-2.png',
                'index_fl' => '/Center/Assets/admin/images/jitheme-3.png',
                'index_user' => '/Center/Assets/admin/images/jitheme-4.png',
                'hotUser' => '/Center/Assets/admin/images/jitheme-5.png',
                'index_document' => '/Center/Assets/admin/images/jitheme-6.png',
                'index_diy_archive' => '/Center/Assets/admin/images/jitheme-7.png',
                'index_gg_5z' => '/Center/Assets/admin/images/jitheme-8.png',
                'index_kjdh' => '/Center/Assets/admin/images/jitheme-9.png',
                'index_sjdh' => '/Center/Assets/admin/images/jitheme-10.png',
                'index_vip' => '/Center/Assets/admin/images/jitheme-11.png',
                'index_new' => '/Center/Assets/admin/images/jitheme-12.png',
                'index_gg' => '/Center/Assets/admin/images/jitheme-12.png',
            ),
        ));
        $index_home->add_group_field( $index_group, array(
            'before_row'=>'</div><div class="html-module cmb-row 1-module set-hidden">',
            'name' => __('B2短代码','b2'),
            'id'      => 'jitheme_b2',
            'type'    => 'text',
            'desc'    => __( '请输入B2的短代码<br>短代码必须为B2所支持的<span class="red">onecad_slider</span>子主题会自己调用,注意名字不能改变,幻灯的高度建议为<span class="red">600px</span>', 'b2' ),
            'desc'    =>sprintf(__( '请输入B2的短代码<br>短代码必须为B2所支持的。<br>比如：<span class="red">%s</span>等', 'b2' ),'<code>'.htmlspecialchars('[b2_index_module key=onecad_slider]').'</code>'),
        ) );
        $index_home->add_group_field( $index_group, array(
            'before_row'=>'</div><div class="posts-module  cmb-row 2-module set-hidden">',
            'name' => __('B2首页配置','b2'),
            'id'      => 'jitheme_b2home',
            'type'    => 'text',
            'attributes' => array(
                'readonly' => 'readonly'
            ),
            'desc'    =>sprintf(__( '如果选择这个就会调用B2原有设置的首页全部配置，如果不懂就不要改这个！<br>比如：<span class="red">%s</span>等', 'b2' ),'<code>'.htmlspecialchars('b2_index').'</code>'),
            'default'=>'b2_index',
            'after_row'=>'</div>',
        ) );
    }
    public function Jitheme_index_tab2(){
        $index_search= new_cmb2_box( array(
        'id'           => 'b2_Jitheme_index_tab2_options_page',
        'object_types' => array( 'options-page' ),
        'option_key'   => 'b2_Jitheme_index_tab2', // The option key and admin menu page slug.            
        'tab_title'    => __('MP4/幻灯搜索','b2'), // Falls back to 'title' (above).
        'parent_slug'  => 'b2_Jitheme_index_tab2',
        'tab_group'    => 'b2_Jitheme_index_options',
        ) );
        $index_search->add_field(array(
            'name'    => __( '幻灯片/视频切换', 'b2' ),
            'desc'    => __( '选择取消视频背景改为幻灯片.视频/幻灯片二选一<br>使用视频,则需要填写下面的视频地址<br>选择幻灯片,则在首页增加的幻灯片模块key名为<span class="red">onecad_slider</span>子主题会自己调用,注意名字不能改变,幻灯的高度建议为<span class="red">600px</span>', 'b2' ),
            'id'      =>  'index_onecad_search_slider',
            'type'             => 'select',
            'options'          => array(
                1 => __( '使用视屏背景', 'b2' ),
                0   => __( '使用幻灯片背景', 'b2' ),
            ),
        ));
        $index_search->add_field(array(
            'name'    => __( '搜索模块背景视频', 'b2' ),
            'id'=>'index_onecad_search_mp4',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('背景图片开启才有作用，默认为主题内部提供的视频<span class="red">注意：视频高度建议≤600px，宽度≥1920px</span>','b2'),
            'default'=>self::$default_settings['one_video_mp4'],
        ));
        $index_search->add_field(array(
            'name' => __('设置视频高度','b2'),
            'id'   => 'index_mp4_hight',
            'type' => 'text',
            'default'=>self::$default_settings['index_mp4_hight'],
            'desc'=> __('设置搜索视频的高度,注意：默认高度<code class="jitheme_code red">700px</code>,请带上单位，也可以设置<code class="jitheme_code red">100vh</code>','b2'),
        ) );
        $index_search->add_field(array(
            'name'    => __( '搜索框/侧菜单框切换', 'b2' ),
            'desc'    => __( '可选择关闭搜索/侧边菜单模块2种模式切换，自由搭配', 'b2' ),
            'id'      =>  'index_onecad_searchoff',
            'type'             => 'select',
            'options'          => array(
                0 => __( '搜索框', 'b2' ),
                1   => __( '菜单框', 'b2' ),
                2  => __( '都不显示', 'b2' ),
            ),
        ));
        $index_search->add_field(array(
            'before_row'=>'<div id="index_onecad_search_ss" class="jitheme-module cmb-row">',
            'name'    => __( '是否关闭站外搜索', 'b2' ),
            'desc'    => __( '集合了关于百度/360/知乎等搜索引擎工具', 'b2' ),
            'id'      =>  'index_onecad_search_wloff',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $index_search->add_field(array(
            'name'    => __( '搜索区块文字颜色', 'b2' ),
            'desc'    => __( '更改搜索区块文字颜色。', 'b2' ),
            'id'=>'index_onecad_search_color',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_onecad_search_color'],
        ));
        $index_search->add_field(array(
            'name' => __('搜索标题','b2'),
            'id'   => 'index_onecad_search_title',
            'type' => 'text',
            'default'          => self::$default_settings['index_onecad_search_title'],
        ) );
        $index_search->add_field(array(
            'name' => __('搜索标题右侧图标','b2'),
            'id'   => 'index_onecad_search_xtimg',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_onecad_search_xtimg'],
        ) );
        $index_search->add_field(array(
            'name' => __('搜索描述','b2'),
            'id'   => 'index_onecad_search_desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_onecad_search_desc'],
        ) );
        $index_search->add_field(array(
            'name' => __('搜索框内文字','b2'),
            'id'   => 'index_onecad_search_kuangtext',
            'type' => 'text',
            'default'          => self::$default_settings['index_onecad_search_kuangtext'],
        ) );
        $index_search->add_field(array(
            'name'    => __( '热门搜索关键词', 'b2' ),
            'id'=>'index_onecad_search_key',
            'type' => 'textarea_small',
            'desc'=>__('请输入关键词，每个关键词用,号区隔开，留空则不显示','b2'),
            'default'=>''
        ) );




        $index_search->add_field(array(
            'name'    => __( '是否显示分类图标', 'b2' ),
            'desc'    => __( '侧边栏快捷菜单显示在幻灯/视频的网页布局的左边/右边', 'b2' ),
            'id'      =>  'index_search_soft_cat',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
       $index_search_cat= $index_search->add_field( array(
            'id'          => 'index_search_soft_cat_list',
            'type'        => 'group',
            'description' => __('搜索框下方5组广告<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过5组</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加分类组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加分类组', 'b2' ),
                'remove_button'     => __( '删除分类组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接导航吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_search->add_group_field($index_search_cat, array(
            'name' => __('分类图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_wuzu_img'],
        ) );
        $index_search->add_group_field($index_search_cat, array(
            'name' => __('分类描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $index_search->add_group_field($index_search_cat, array(
            'name' => __('分类链接','b2'),
            'id'   => 'link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) );



























        $index_search->add_field(array(
            'before_row'=>'</div><div id="index_onecad_search_cd" class="jitheme-module cmb-row">',
            'name'    => __( '侧栏菜单上下距离', 'b2' ),
            'desc'    => __( '侧边栏快捷菜单距离幻灯片顶部/底部<code>0px</code>。', 'b2' ),
            'id'=>'index_search_mag',
            'type' => 'text',
            'default'=>'0px'
        ));
        // $index_search->add_field(array(
        //     'name'    => __( '侧栏菜单显示位置左/右侧', 'b2' ),
        //     'desc'    => __( '侧边栏快捷菜单显示在幻灯/视频的网页布局的左边/右边', 'b2' ),
        //     'id'      =>  'index_search_weizhi',
        //     'type'             => 'select',
        //     'options'          => array(
        //         1 => __( '左侧', 'b2' ),
        //         0   => __( '右侧', 'b2' ),
        //     ),
        // ));
       $index_search_cd = $index_search->add_field( array(
            'id'          => 'index_search_cd_list',
            'type'        => 'group',
            'description' => __('幻灯片侧栏菜单组<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过7组</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加侧栏菜单组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加侧栏菜单组', 'b2' ),
                'remove_button'     => __( '删除侧栏菜单组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个链接导航吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_search->add_group_field($index_search_cd, array(
            'name' => __('ICO图标名称','b2'),
            'id'   => 'ico_title',
            'type' => 'text',
            'desc'    =>sprintf(__( '如果您想用自己编辑的图标库，请填相应的位置代码即可，注意要先引入。<a target="_blank" href="https://www.iconfont.cn/">阿里巴巴矢量图标库</a> <a target="_blank" href="https://www.iconfont.cn/help/detail?spm=a313x.7781069.1998910419.d8cf4382a&helptype=code">查看帮助</a><br><a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a><br>比如：<span class="red">%s</span>等标签。比如前面的<code>jitheme</code>为项目名称<code>ji-mail-send-line</code>为图标名称', 'b2' ),'<code>'.htmlspecialchars('<i class="jitheme ji-mail-send-line"></i>').'</code>'),
            'default'=> self::$default_settings['ico_title'],
        ) );
        $index_search->add_group_field($index_search_cd, array(
            'name' => __('图标','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_diy_search_list_img'],
        ) );
        $index_search->add_group_field($index_search_cd, array(
            'name' => __('菜单标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'=> self::$default_settings['index_wuzu_title'],
        ) );
        $index_search->add_group_field($index_search_cd, array(
            'name'    => __( '是否关闭弹出框', 'b2' ),
            'desc'    => __( '可以直接设置一个连接', 'b2' ),
            'id'      =>  'index_celan_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'          => 0,
        ));
        $index_search->add_group_field($index_search_cd, array(
            'name' => __('菜单链接','b2'),
            'id'   => 'index_celan_link',
            'type' => 'text',
            'default'=> self::$default_settings['index_wuzu_desc'],
        ) );
        $index_search->add_group_field($index_search_cd, array(
            'name'    => __( '分类导航组<span class="red">最多4个</span>', 'b2' ),
            'id'=>'fenlei',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>', 'b2' ),'<code>'.htmlspecialchars('图标名|标题|描述|小图标|链接').'</code>'),
            'default'=>'ji-mail-send-line|标题名称1|我是一个标题描述|ji-k-line|#|#fcb900
ji-mail-send-line|标题名称2|我是一个标题描述|ji-k-line|#|#3385ff
ji-mail-send-line|标题名称3|我是一个标题描述|ji-k-line|#|#abb8c3
ji-mail-send-line|标题名称4|我是一个标题描述|ji-k-line|#|#00d084',
        ));
        $index_search->add_group_field($index_search_cd, array(
            'name'    => __( '推荐按钮组<span class="red">最多6个</span>', 'b2' ),
            'id'=>'hot',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>', 'b2' ),'<code>'.htmlspecialchars('图标名|标题|链接').'</code>'),
            'default'=>'ji-mail-send-line|标题名称|#
ji-mail-send-line|标题名称|#
ji-mail-send-line|标题名称|#
ji-mail-send-line|标题名称|#',
        ));
        
        
        
        
        
        
        
        
        $index_search->add_field(array(
            'before_row'=>'</div>',
            'name'    => __( '是否开启头部快速链接导航', 'b2' ),
            'desc'    => __( '关闭一个半透明的链接导航,可以启用下方的<span class="red">区块模块</span>这样组合效果也不错哦', 'b2' ),
            'id'      =>  'index_onecad_search_header_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
        ));
        $index_search->add_field(array(
            'before_row'=>'<div id="index_onecad_search_daoh"  class="jitheme-module cmb-row">',
            'name' => __('搜索描述','b2'),
            'id'   => 'index_onecad_search_descs',
            'type' => 'text',
            'default'  =>'暂无内容',
        ) );
       $index_diy_search_A = $index_search->add_field( array(
            'id'          => 'index_diy_search_list',
            'type'        => 'group',
            'description' => __('头部快速链接导航5组<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过5组</span>','b2'),
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
        $index_search->add_group_field($index_diy_search_A, array(
            'name' => __('图标','b2'),
            'id'   => 'index_diy_search_list_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_diy_search_list_img'],
        ) );
        $index_search->add_group_field($index_diy_search_A, array(
            'name' => __('标题','b2'),
            'id'   => 'index_diy_search_list_title',
            'type' => 'text',
            'default'=> self::$default_settings['index_wuzu_title'],
        ) );
        $index_search->add_group_field($index_diy_search_A, array(
            'name' => __('此标题链接','b2'),
            'id'   => 'index_diy_search_list_title_link',
            'type' => 'text',
            'default'=> self::$default_settings['index_wuzu_desc'],
        ) );
        $index_search->add_group_field($index_diy_search_A, array(
            'name' => __('连接组内容','b2'),
            'id'   => 'index_diy_search_list_link',
            'type' => 'textarea',
            'default' => self::$default_settings['index_diy_search_list_link'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签注:不要超过2个。', 'b2' ),'<code>'.htmlspecialchars('<a href="链接" class="fl" target="_blank">标题</a>').'</code>'),
        ) );
        //$index_search->add_field(array(
            //'name' => __('文字链接组内容','b2'),
            //'id'   => 'index_diy_search_text_link',
            //'type' => 'textarea',
            //'default' => self::$default_settings['index_diy_search_list_link'],
            //'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>等标签注:不要超过4个。', 'b2' ),'<code>'.htmlspecialchars('<a href="链接" class="fl" target="_blank">标题</a>').'</code>'),
        //) );
       $index_diy_search_B = $index_search->add_field( array(
            'id'          => 'index_diy_searchB_list',
            'type'        => 'group',
            'description' => __('头部快速链接导航右侧2组<span class="red">注：每个模块的必填项必须填写，否则无法保存,建议不超过2组</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加右侧2组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加右侧2组', 'b2' ),
                'remove_button'     => __( '删除右侧2组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个区块吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_search->add_group_field($index_diy_search_B, array(
            'name' => __('图标','b2'),
            'id'   => 'index_diy_searchB_list_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_diy_search_list_img'],
        ) );
        $index_search->add_group_field($index_diy_search_B, array(
            'name' => __('标题','b2'),
            'id'   => 'index_diy_searchB_list_title',
            'type' => 'text',
            'default' => self::$default_settings['index_wuzu_title'],
        ) );
        $index_search->add_group_field($index_diy_search_B, array(
            'name' => __('标题链接','b2'),
            'id'   => 'index_diy_searchB_list_link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
            // $index_search->add_field(array(
            //     'before_row'=>'<h2>幻灯片/视频背景切换</h2>',
            //     'name'    => __( '<span class="red">总开关:</span>整个搜索组是否关闭', 'b2' ),
            //     'desc'    => __( '关闭本组全部功能(视频/幻灯片背景及搜索框)。<br>如果你开启了<span class="red">首页自定义搜索模块</span>就关闭此组,会给你一个全新的搜索界面', 'b2' ),
            //     'id'      =>  'index_onecad_search_all',
            //     'type'             => 'select',
            //     'options'          => array(
            //         1 => __( '开启', 'b2' ),
            //         0   => __( '关闭', 'b2' ),
            //     ),
            // ));
            // $index_search->add_field(array(
            //     'name'    => __( '是否开启首页搜索模块', 'b2' ),
            //     'desc'    => __( '首页幻灯片/视频上会有一个漂亮的搜索框，方便快捷搜索内容。<br>建议开启，则模块key名为<span class="red">onecad_search</span>此名称不可更改', 'b2' ),
            //     'id'      =>  'index_onecad_search',
            //     'type'             => 'select',
            //     'options'          => array(
            //         1 => __( '开启', 'b2' ),
            //         0   => __( '关闭', 'b2' ),
            //     ),
            // ));
            // $index_search->add_field(array(
            //     'name'    => __( '背景幻灯片/视频切换', 'b2' ),
            //     'desc'    => __( '图片幻灯片与视频背景切换，幻灯片与视频背景二选一，默认为视频背景。<br>如果关闭，则幻灯片的模块key名为<span class="red">onecad_slider</span>此名称不可更改', 'b2' ),
            //     'id'      =>  'index_video',
            //     'type'             => 'select',
            //     'options'          => array(
            //         1 => __( '开启', 'b2' ),
            //         0   => __( '关闭', 'b2' ),
            //     ),
            // ));
            // $index_search->add_field(array(
            //     'name'    => __( '背景视频', 'b2' ),
            //     'id'=>'one_video_mp4',
            //     'type'=>'file',
            //     'options' => array(
            //         'url' => true, 
            //     ),
            //     'desc'=> __('背景图片开启才有作用，默认为主题内部提供的视频<span class="red">注意：视频高度建议≤600px，宽度≥1920px</span>','b2'),
            //     'default'=>self::$default_settings['one_video_mp4'],
            // ));
            // $index_search->add_field(array(
            //     'name' => __('为背景视频设置一灰度','b2'),
            //     'id'   => 'one_video_huidu',
            //     'type' => 'text',
            //     'desc'=> __('开启控制背景图片灰度<span class="red">注意：100为透明无灰度，0为最高灰度即黑色无透明</span>','b2'),
            //     'default'          => self::$default_settings['one_video_huidu'],
            // ) );
    }
    public function Jitheme_index_tab3() {
        $index_qk= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab3_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab3', // The option key and admin menu page slug.            
            'tab_title'    => __('区块A','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab3',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) );
        $index_qk->add_field(array(
            'name'    => __( '首页背景齐边', 'b2' ),
            'desc'    => __( '如果首页开启了背景，则模块随两端对齐。<span class="red">注意首页要开启背景，不然就错位</span">。', 'b2' ),
            'id'      =>'home_qk_off',
            'type'             => 'select',
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $index_qk->add_field(array(
            'name'    => __( '区块图标样式', 'b2' ),
            'desc'    => __( '图标及主副标题的显示方式<br><span class="red">默认为左右结构</span>', 'b2' ),
            'id'      =>  'index_qukuai_jg',
            'type'             => 'select',
            'options'          => array(
                1 => __( '左右结构', 'b2' ),
                0   => __( '上下结构', 'b2' ),
            ),
            'default'          => self::$default_settings['open'],
        )); 
       $index_qukuai_list = $index_qk->add_field( array(
            'id'          => 'index_qukuai_list',
            'type'        => 'group',
            'description' => __('首页区块<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
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
        $index_qk->add_group_field($index_qukuai_list, array(
            'name'    => __( '选择采用ICO图标/图片', 'b2' ),
            'desc'    => __( '用户可以选择用图片或者还是用ICO代码图标<br><span class="red">建议使用ICO图标,很漂亮的</span>', 'b2' ),
            'id'      =>  'qukuai_img',
            'type'             => 'radio_inline',
            'classes' => 'model-picked',
            'options'          => array(
                1 => __( '图片', 'b2' ),
                0   => __( 'ICO图标', 'b2' ),
            ),
            'default'          => 1,
        )); 
        $index_qk->add_group_field($index_qukuai_list, array(
            'before_row'=>'<div class="sliders-module cmb-row  1-module set-hidden">',
            'name' => __('区块图片','b2'),
            'id'   => 'index_qukuai_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_qukuai_img'],
            'desc'=>'必须选择采用图片方式才可以显示，否则失效',
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'before_row'=>'</div><div class="html-module cmb-row 0-module set-hidden">',
            'name' => __('图标前缀','b2'),
            'id'   => 'qukuai_img_xmmc',
            'type' => 'text',
            'default'=> self::$default_settings['ico_name'],
            'desc'=>sprintf(__( '必须<span class="red">%s</span>，<code class="red">jitheme</code>为此行内容，必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a><br><span class="red">上方必须选择ICO图标否则失效</span>', 'b2' ),'<code>'.htmlspecialchars('<i class="jitheme ji-mail-send-line"></i>').'</code>'),
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'name' => __('图标名称','b2'),
            'id'   => 'qukuai_img_ico',
            'type' => 'text',
            'default'=>self::$default_settings['ico_font_family'],
            'desc'=>sprintf(__( '必须<span class="red">%s</span>，<code class="red">ji-mail-send-line</code>为此行内容，必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a><br><span class="red">上方必须选择ICO图标否则失效</span>', 'b2' ),'<code>'.htmlspecialchars('<i class="jitheme ji-mail-send-line"></i>').'</code>'),
            'after_row'=>'</div>',
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'name' => __('区块标题','b2'),
            'id'   => 'index_qukuai_title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'name' => __('区块连接','b2'),
            'id'   => 'index_qukuai_links',
            'type' => 'text',
            'default'          => self::$default_settings['index_circles_title_hz'],
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'name' => __('区块标题后缀','b2'),
            'id'   => 'index_qukuai_title_hz',
            'desc'    => __( '标题后方的角标,为空则不显示。', 'b2' ),
            'type' => 'text',
            'default'          => self::$default_settings['index_qukuai_title_hz'],
        ) );
        $index_qk->add_group_field($index_qukuai_list, array(
            'name'    => __( '区块后缀背景色', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色。', 'b2' ),
            'id'=>'index_qukuai_title_hz_color',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_qukuai_title_hz_color'],
        ));
        $index_qk->add_group_field($index_qukuai_list, array(
            'name' => __('区块描述','b2'),
            'id'   => 'index_qukuai_desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $index_qk->add_field(array(
            'name'    => __( '是否开启区块广告及VIP组', 'b2' ),
            'desc'    => __( '一个规矩漂亮的区块,带会员属性显示的<br><span class="red">建议开启,很漂亮的</span>', 'b2' ),
            'id'      =>  'index_qukuai_gg_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'          => self::$default_settings['open'],
        )); 
        $index_qk->add_field(array(
            'name'    => __( '广告/置顶文章切换', 'b2' ),
            'desc'    => __( '区块广告/置顶文章自由切换<br><span class="red">建议开启,很漂亮的</span>', 'b2' ),
            'id'      =>  'index_qukuai_top',
            'type'             => 'select',
            'options'          => array(
                1 => __( '广告区块', 'b2' ),
                0   => __( '置顶文章', 'b2' ),
            ),
        )); 
        $index_qk->add_field(array(
            'name' => __('显示数量','b2'),
            'id'   => 'index_qukuai_top_sl',
            'type' => 'text',
             'desc'    => __( '默认显示数量为<span class="red">5个</span>', 'b2' ),
        ) );
        $index_qk->add_field(array(
            'before_row'=>'<div id="qukuai_zu" class="jitheme-module cmb-row">',
            'name' => __('VIP组及倒计时组背景图片','b2'),
            'id'   => 'qukuai_color',
            'type' => 'text',
            'desc'    => sprintf(__( '您可以到此网站去查看渐变色代码,复制代码粘贴此处即可<a target="_blank" href="http://color.oulu.me/">渐变色代码</a>', 'b2' )),
            'default'  =>'background: linear-gradient(90deg,#fdf8eb,#feeec3);',
        ));
        $index_wuzu_list = $index_qk->add_field( array(
            'id'          => 'index_wuzu_list',
            'type'        => 'group',
            'description' => __('广告展示<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加图片-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加图片', 'b2' ),
                'remove_button'     => __( '删除图片', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个图片吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_qk->add_group_field($index_wuzu_list, array(
            'name' => __('图片','b2'),
            'id'   => 'index_wuzu_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_wuzu_img'],
        ) );
        $index_qk->add_group_field($index_wuzu_list, array(
            'name' => __('标题','b2'),
            'id'   => 'index_wuzu_title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $index_qk->add_group_field($index_wuzu_list, array(
            'name' => __('链接','b2'),
            'id'   => 'index_wuzu_desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) ); 
        $index_qk->add_group_field($index_wuzu_list, array(
            'name' => __('角标','b2'),
            'id'   => 'index_wuzu_jiaob',
            'type'             => 'select',
            'options'          => array(
                0 => __( '推荐', 'b2' ),
                1   => __( '热门', 'b2' ),
                2   => __( '活动', 'b2' ),
                3   => __( '精品', 'b2' ),
                4   => __( '广告', 'b2' ),
            ),
        ) ); 
        $index_qk->add_field(array(
            'name'    => __( 'VIP组/倒计时组切换', 'b2' ),
            'desc'    => __( '用户组与活动倒计时切换功能', 'b2' ),
            'id'      =>  'qukuai_hd_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( 'VIP用户组', 'b2' ),
                0   => __( '活动倒计时1', 'b2' ),
                2 => __( '活动倒计时2', 'b2' ),
            ),
            'default'          => self::$default_settings['open'],
        ));
        $index_qk->add_field(array(
            'before_row'=>'<div id="qukuai_vip" class="jitheme-module cmb-row">',
            'name'    => 'VIP组标题',
            'id'      => 'qukuai_vip_title',
            'desc'    => __('给VIP组标题增加一个标题','b2'),
            'type'    => 'text',
            'default'          =>'开通会员全站素材无限制下载',
        )); 
        $index_qk->add_field(array(
            'before_row'=>'</div><div id="qukuai_hd" class="jitheme-module cmb-row">',
            'name'    => '活动标题',
            'id'      => 'qukuai_hd_title',
            'desc'    => __('给本次活动增加一个标题','b2'),
            'type'    => 'text',
            'default'          => '请为您的倒计时设置一个标题',
        )); 
        $index_qk->add_field(array(
            'name'    => '按钮文字',
            'id'      => 'qukuai_btn_title',
            'desc'    => __('给本次活动增加一个标题','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ));
        $index_qk->add_field(array(
            'name'    => '按钮连接',
            'id'      => 'qukuai_btn_link',
            'desc'    => __('按钮的连接','b2'),
            'type'    => 'text_url',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ));
        $index_qk->add_field(array(
            'name'    => '按钮角标',
            'id'      => 'qukuai_btn_jb',
            'desc'    => __('给本次活动增加一个标题','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['index_onecad_vip_tj_title'],
        ));
        $index_qk->add_field(array(
            'name' => __('倒计时2底部图片','b2'),
            'id'   => 'qukuai_hd2_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片<br><span class="red">'.self::$default_settings['qukuai_hd2_img'].'</span>','b2'),
            'default'    =>'',
            'after_row'=>'</div>',
        ) ); 
        
    }
    public function Jitheme_index_tab4() {
        $index_fl= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab4_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab4', // The option key and admin menu page slug.            
            'tab_title'    => __('分类展示','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab4',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) );
        $index_fl->add_field(array(
            'before_row'=>'<h2>大列表分类</h2>',
            'name'    => '分类标题',
            'id'      => 'one_index_fenlei_title',
            'desc'    => __('给分类增加一个醒目的标题','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['one_index_fenlei_title'],
        ));
        $index_fl->add_field(array(
            'name'    => '分类描述',
            'id'      => 'one_index_fenlei_desc',
            'desc'    => __('给分类增加一个小描述','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['one_index_fenlei_desc'],
        ));
        $index_fl->add_field(array(
            'name'    => '选择大分类(最多4个)',
            'id'      => 'one_index_fenlei_fl',
            'desc'    => __('请选择要显示的文章分类，输入方式为ID,ID……    DI之间使用英文逗号隔，最多选择4个','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['one_index_fenlei_fl'],
        ));
        $index_fl->add_field(array(
            'name'    => '选择小分类(最多6个)',
            'id'      => 'one_index_fenlei_xfl',
            'desc'    => __('请选择要显示的文章分类，输入方式为ID,ID……    DI之间使用英文逗号隔，最多选择6个','b2'),
            'type'    => 'text',
            'default'          => self::$default_settings['one_index_fenlei_xfl'],
        ));
        $index_fl->add_field(array(
            'before_row'=>'<h2>小列表左右切换分类</h2>',
            'name'    => __( '自定义分类选择', 'b2' ),
            'desc'    => __( '可以选择自定义或者是指定的分类', 'b2' ),
            'id'      =>  'jitheme_diy_flqh',
            'type'             => 'select',
            'options'          => array(
                1 => __( '指定分类ID', 'b2' ),
                0   => __( '自定义分类', 'b2' ),
            ),
        'default'=>self::$default_settings['open'],
        ));
        $index_fl->add_field(array(
            'name' => __('每一个分类间距','b2'),
            'id'   => 'diy_fl_jj',
            'type' => 'text',
            'desc'=> __('设置每一个分类的间距尺寸,默认为10,不能带px,否则会报错','b2'),
            'default'          =>'',
        ) ); 
        $index_fl->add_field(array(
            'name' => __('显示数量','b2'),
            'id'   => 'diy_fl_zs',
            'type' => 'text',
            'desc'=> __('默认显示6个','b2'),
            'default'          =>'6',
        ) ); 
        $index_fl->add_field(array(
            'name'    => __( '选择分类ID', 'b2' ),
            'desc'    => __( '输入方式为ID,ID……    DI之间使用英文逗号隔开,<span class="red">注意：默认为12个分类，可增减</span>', 'b2' ),
            'id'=>'jitheme_diy_flqh_id',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_fenlei_fldh_id'],

        ) );
        $index_fl_list = $index_fl->add_field( array(
            'id'          => 'diy_fl_zs_list',
            'type'        => 'group',
            'description' => __('自定义分类切换<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加自定义分类-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加广告', 'b2' ),
                'remove_button'     => __( '删除广告', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个自定义分类吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_fl->add_group_field($index_fl_list, array(
            'name' => __('分类标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) ); 
        $index_fl->add_group_field($index_fl_list, array(
            'name' => __('分类图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_qukuai_img'],
        ) );
        $index_fl->add_group_field($index_fl_list, array(
            'name' => __('分类连接','b2'),
            'id'   => 'link',
            'type' => 'text_url',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) ); 
        $index_fl->add_group_field($index_fl_list, array(
            'name' => __('分类描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) ); 
    }
    public function Jitheme_index_tab5(){
        $index_user= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab5_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab5', // The option key and admin menu page slug.            
            'tab_title'    => __('首推作者','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab5',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) );
        $index_user->add_field(array(
            'before_row'=>'<h2>推荐作者模块</h2>',
            'name'    => __( '列表标题', 'b2' ),
            'desc'    => __( '给模块取一个好听的名字', 'b2' ),
            'id'=>'one_index_tj_user_title',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_title'],
        ));
        $index_user->add_field(array(
            'name'    => __( '列表描述', 'b2' ),
            'desc'    => __( '给模块取一个好听的名字', 'b2' ),
            'id'=>'one_index_tj_user_desc',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_desc'],
        ));
        $index_user->add_field(array(
            'name' => __('标题前置图片','b2'),
            'id'   => 'one_index_tj_user_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['one_index_tj_user_img'],
        ) );
        $index_user->add_field(array(
            'name'    => __( '选择推荐作者ID', 'b2' ),
            'desc'    => __( '输入方式为ID,ID……    DI之间使用英文逗号隔开<span class="red">建议采用4/9/14……等个数</span>', 'b2' ),
            'id'=>'one_index_tj_user_id',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_id'],
        ));
        $index_user->add_field(array(
            'name'    => __( '每行显示个数', 'b2' ),
            'desc'    => __( '请输入每行显示个数宽度默认为25%<span class="red">注意：1行=4个作者  2行=9个作者  3行=14个作者  4行=19个作者</span>', 'b2' ),
            'id'=>'one_index_tj_user_hang',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_hang'],
        ));
        $index_user->add_field(array(
            'name'    => __( '投稿框标题', 'b2' ),
            'desc'    => __( '设置一个醒目的投稿框标题', 'b2' ),
            'id'=>'one_index_tj_user_toub_title',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_toub_title'],
        ));
        $index_user->add_field(array(
            'name'    => __( '投稿框描述', 'b2' ),
            'desc'    => __( '设置一个投稿框的说明描述', 'b2' ),
            'id'=>'one_index_tj_user_toub_desc',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_toub_desc'],
        ));
        $index_user->add_field(array(
            'name'    => __( '投稿框人数', 'b2' ),
            'desc'    => __( '自定义一个投稿人数', 'b2' ),
            'id'=>'one_index_tj_user_toub_user',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_tj_user_toub_user'],
        ));
        $index_user->add_field(array(
            'before_row'=>'<h2>活跃用户模块</h2>',
            'name'    => __( '模块标题', 'b2' ),
            'desc'    => __( '给模块取一个名字吧', 'b2' ),
            'id'=>'one_index_user_title',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_user_title'],
        ));
        $index_user->add_field(array(
            'name'    => __( '模块描述', 'b2' ),
            'desc'    => __( '模块描述', 'b2' ),
            'id'=>'one_index_user_desc',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_user_desc'],
        ));
        $index_user->add_field(array(
            'name' => __('标题前置图片','b2'),
            'id'   => 'one_index_user_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['one_index_user_img'],
        ) );
        $index_user->add_field(array(
            'name'    => __( '选择活跃用户ID', 'b2' ),
            'desc'    => __( '输入方式为ID,ID……    DI之间使用英文逗号隔开', 'b2' ),
            'id'=>'one_index_user_id',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_user_id'],
        ));
        $index_user->add_field(array(
            'name'    => __( '活动会员列表样式', 'b2' ),
            'desc'    => __( '必须开启活跃用户才能起作用。', 'b2' ),
            'id'      =>  'one_index_user_ys',
            'type'             => 'select',
            'options'          => array(
                1   => __( '样式2', 'b2' ),
                0   => __( '样式1', 'b2' ),
            ),
        ));
    }
    public function Jitheme_index_tab6(){
        $index_qzwd= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab6_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab6', // The option key and admin menu page slug.            
            'tab_title'    => __('圈子问答','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab6',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) );   
        // $index_qzwd->add_field(array(
        //     'before_row'=>'<h2>圈子列表</h2>',
        //     'name'    => __( '圈子分类标题', 'b2' ),
        //     'desc'    => __( '自定义圈子分类标题', 'b2' ),
        //     'id'=>'one_index_circle_tags_title',
        //     'type'=>'text',
        //     'default'=>self::$default_settings['one_index_circle_tags_title'],
        // ));
        // $index_qzwd->add_field(array(
        //     'name'    => __( '圈子分类描述', 'b2' ),
        //     'desc'    => __( '自定义圈子分类描述', 'b2' ),
        //     'id'=>'one_index_circle_tags_desc',
        //     'type'=>'text',
        //     'default'=>self::$default_settings['one_index_circle_tags_desc'],
        // ));
        $index_qzwd->add_field(array(
            'before_row'=>'<h2>问答列表</h2>',
            'name'    => __( '问答社区标题', 'b2' ),
            'desc'    => __( '自定义问答社区标题', 'b2' ),
            'id'=>'one_index_document_title',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_document_title'],
        ));
        $index_qzwd->add_field(array(
            'name'    => __( '问答社区描述', 'b2' ),
            'desc'    => __( '自定义问答社区描述', 'b2' ),
            'id'=>'one_index_document_desc',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_document_desc'],
        ));
        $index_qzwd->add_field(array(
            'name' => __('标题前置图片','b2'),
            'id'   => 'one_index_document_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['one_index_document_img'],
        ) );
        $index_qzwd->add_field(array(
            'name'    => __( '显示文章数量', 'b2' ),
            'desc'    => __( '输入要显示的文章数量   <span class="red">默认显示10个</span>', 'b2' ),
            'id'=>'one_index_document_shuliang',
            'type'=>'text',
            'default'=>self::$default_settings['one_index_document_shuliang'],
        ));
    }
    public function Jitheme_index_tab7(){
            $index_Mobile= new_cmb2_box( array(
                'id'           => 'b2_Jitheme_index_tab7_options_page',
                'object_types' => array( 'options-page' ),
                'option_key'   => 'b2_Jitheme_index_tab7', // The option key and admin menu page slug.            
                'tab_title'    => __('手机端模块','b2'), // Falls back to 'title' (above).
                'parent_slug'  => 'b2_Jitheme_index_tab7',
                'tab_group'    => 'b2_Jitheme_index_options',
            ) );  
            $index_Mobile->add_field(array(
                'before_row'=>'<h2>手机端区块</h2>',
                'name'    => __( '是否显示此栏目', 'b2' ),
                'desc'    => __( '手机端显示一个上下各5组图标。', 'b2' ),
                'id'      =>  'Onecad_index_5zuimg_off',
                'type'             => 'select',
                'options'          => array(
                    1 => __( '显示', 'b2' ),
                    0   => __( '隐藏', 'b2' ),
                ),
                'default'          => 0,
            ));
           $index_Mobile_5zuimg = $index_Mobile->add_field( array(
                'id'          => 'Onecad_index_5zuimg_list',
                'type'        => 'group',
                'description' => __('第一组 : 添加推荐链接<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
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
            $index_Mobile->add_group_field($index_Mobile_5zuimg, array(
                'name' => __('链接图片','b2'),
                'id'   => 'Onecad_index_5zuimg_img',
                'type' => 'file',
                'options' => array(
                    'url' => true, 
                ),
                'default'          => self::$default_settings['index_wuzu_img'],
            ) );
            $index_Mobile->add_group_field($index_Mobile_5zuimg, array(
                'name' => __('链接标题','b2'),
                'id'   => 'Onecad_index_5zuimg_title',
                'type' => 'text',
                'default'          => self::$default_settings['index_wuzu_title'],
            ) );
            $index_Mobile->add_group_field($index_Mobile_5zuimg, array(
                'name' => __('超级链接','b2'),
                'id'   => 'Onecad_index_5zuimg_title_hz',
                'type' => 'text',
                'default'          => self::$default_settings['index_wuzu_title'],
            ) );
    }
    public function Jitheme_index_tab8(){
        $index_gg= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab8_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab8', // The option key and admin menu page slug.            
            'tab_title'    => __('5组图片切换','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab8',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) ); 
        $index_gg->add_field(array(
            'before_row'=>'<div id="i7zimg_zs" class="jitheme-module cmb-row">',
            'name' => __('显示数量','b2'),
            'id'   => 'i7zimg_zs',
            'type' => 'text',
            'desc'=> __('默认显示4张图切换','b2'),
            'default'=>'',
            'default'          => self::$default_settings['i7zimg_zs'],
        ) ); 
        $index_gg_list = $index_gg->add_field( array(
            'id'          => 'index_7zimg_list',
            'type'        => 'group',
            'description' => __('首页5组图片切换<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加广告区块-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加广告', 'b2' ),
                'remove_button'     => __( '删除广告', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个广告吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_gg->add_group_field($index_gg_list, array(
            'name' => __('图片','b2'),
            'id'   => 'index_7zimg_img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_qukuai_img'],
        ) );
        $index_gg->add_group_field($index_gg_list, array(
            'name' => __('广告链接','b2'),
            'id'   => 'index_7zimg_link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) ); 
    }
    public function Jitheme_index_tab9(){
        $index_kjdh= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab9_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab9', // The option key and admin menu page slug.            
            'tab_title'    => __('快捷导航','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab9',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) ); 
        $index_kjdh->add_field(array(
            'name'    => __( '是否显示网站统计', 'b2' ),
            'desc'    => __( '一个漂亮的快捷导航。', 'b2' ),
            'id'      =>  'jitheme_index_tongji_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
            'default'=>self::$default_settings['open'],
        ));
        $index_kjdh->add_field(array(
            'name'    => '统计-网站开机日期',
            'id'      => 'kjdh_data',
            'type'    => 'text_date',
            'date_format' => 'Y-m-d',
            'default'          => '2022/10/10',
            'desc'    => __('设置网站的启动日期，可以统计运行多少天','b2'),
        ));
        $index_kjdh->add_field(array(
            'name' => __('此模块浮动位置','b2'),
            'id'   => 'jitheme_index_kjdh_top',
            'type' => 'text',
            'default'          => self::$default_settings['onecad_padding'],
        ) ); 
        $index_kjdh->add_field(array(
            'name'    => __( '快捷导航链接组1', 'b2' ),
            'id'=>'index_kjdh_links1',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>', 'b2' ),'<code>'.htmlspecialchars('极主题图标名称|标题|链接').'</code>'),
        ));
        $index_kjdh->add_field(array(
            'name'    => __( '快捷导航链接组2', 'b2' ),
            'id'=>'index_kjdh_links2',
            'type'=>'textarea',
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>', 'b2' ),'<code>'.htmlspecialchars('极主题图标名称|标题|链接').'</code>'),
        ));
        $index_kjdh_list = $index_kjdh->add_field( array(
            'id'          => 'index_kjdh_list',
            'type'        => 'group',
            'description' => __('首页快捷导航组(只能为4组)<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加导航组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加导航组', 'b2' ),
                'remove_button'     => __( '删除导航组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个广告吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_kjdh->add_group_field($index_kjdh_list, array(
            'name' => __('背景图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['jitheme_img'],
        ) );
        $index_kjdh->add_group_field($index_kjdh_list, array(
            'name' => __('小标题','b2'),
            'id'   => 'xbt',
            'type' => 'text',
            'default'          => '极主题',
        ) ); 
        $index_kjdh->add_group_field($index_kjdh_list, array(
            'name' => __('大标题','b2'),
            'id'   => 'dbt',
            'type' => 'text',
            'default'          =>'7B2主题美化最好看的子主题',
        ) ); 
        $index_kjdh->add_group_field($index_kjdh_list, array(
            'name'    => __( '图标导航链接组', 'b2' ),
            'id'=>'ico',
            'type'=>'textarea',
            'default'          => self::$default_settings['jitheme_ico'],
            'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。', 'b2' ),'<code>'.htmlspecialchars('图片链接|标题|链接').'</code>'),
        )); 
        $index_kjdh->add_field(array(
            'name'    => __( '是否显示文字导航组', 'b2' ),
            'desc'    => __( '关闭下方8组文字导航组。', 'b2' ),
            'id'      =>  'index_kjdh_wzlist_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
            'default'=>1,
        ));
       $index_kjdh_wzlist = $index_kjdh->add_field( array(
            'id'          => 'index_kjdh_wzlist',
            'type'        => 'group',
            'description' => __('首页彩色文字导航组(建议8组)<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加文字导航组-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加文字导航组', 'b2' ),
                'remove_button'     => __( '删除文字导航组', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个广告吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $index_kjdh->add_group_field($index_kjdh_wzlist, array(
            'name' => __('文字标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) ); 
        $index_kjdh->add_group_field($index_kjdh_wzlist, array(
            'name' => __('链接','b2'),
            'id'   => 'link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) ); 
        $index_kjdh->add_group_field($index_kjdh_wzlist, array(
            'name'    => __( '链接颜色', 'b2' ),
            'desc'    => __( '为您的链接设置一个舒适的颜色', 'b2' ),
            'id'=>'color',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color1'],
        ));
    }
    public function Jitheme_index_tab10(){
        $massages= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab10_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab10', // The option key and admin menu page slug.            
            'tab_title'    => __('网站动态','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab10',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) ); 
        $massages->add_field(array(
            'name'    => __( '网站动态样式', 'b2' ),
            'desc'    => __( '一个好看的网站动态提示框。<span class="red">本功能只在首页有作用，如果是全站滚动弹幕，开启下方<code class="jitheme_code">全站右下角动态</code>！</span">。', 'b2' ),
            'id'      =>'msg_off',
            'type'             => 'select',
            'options'          => array(
                0   => __( '单行滚动统计', 'b2' ),
                1   => __( '三栏分类样式', 'b2' ),
            ),
        ));
        $massages->add_field(array(
            'name'    => __( '首页背景齐边', 'b2' ),
            'desc'    => __( '如果首页开启了背景，则模块随两端对齐。<span class="red">注意首页要开启背景，不然就错位</span">。', 'b2' ),
            'id'      =>'home_new_off',
            'type'             => 'select',
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $massages->add_field(array(
            'name'    => __( '全站右下角动态', 'b2' ),
            'desc'    => __( '一个好看的网站动态提示框。<span class="red">右下角弹幕！</span">。', 'b2' ),
            'id'      =>'msg_yxj',
            'type'             => 'select',
            'options'          => array(
                1   => __( '启用', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $massages->add_field(array(
            'name' => __('弹幕内容','b2'),
            'id'   => 'msg_nr',
            'type' => 'multicheck_inline',
            'options'=>[
                'vote_up'=>__('点赞','b2'),
                'register'=>__('注册用户','b2'),
                'user_mission'=>__('签到','b2'),
                'user_comment'=>__('评论','b2'),
                'user_shop'=>__('购买商品','b2'),
                'user_vip'=>__('开通VIP','b2'),
                'user_po_ask'=>__('回复问答','b2'),
                'user_best_answer'=>__('最佳答案','b2'),
                'user_po_answer'=>__('参与回答','b2'),
                'user_po_circle'=>__('发布圈子','b2'),
                'user_buy_download'=>__('支付下载','b2'),
                'user_buy_hidden'=>__('购买隐藏','b2'),
                'user_ds'=>__('打赏','b2'),
            ],
            'desc'=> __('选择一个要显示的弹幕内容,注意启动插件之后需要等待一段时间，只要有上述操作都会被写入数据库，所以需要等待网站动态内容<br>另外本弹幕是采用Swiper组件写的，当某一个条件内只有一条记录的时候会显示3条，且不会滚动，请让网站有足够的动态信息','b2')
        ));
        $massages->add_field(array(
            'before_row'=>'<h2>滚动背景颜色</h2><p class="red">只作用于全站右下角动态</p>',
            'name'    => __( '颜色1/注册签到', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color1</span>', 'b2' ),
            'id'=>'color1',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color1'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色2/评论回答', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color2</span>', 'b2' ),
            'id'=>'color2',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color2'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色3/点赞', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color3</span>', 'b2' ),
            'id'=>'color3',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color3'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色4/付费类', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color4</span>', 'b2' ),
            'id'=>'color4',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color4'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色5/开通VIP', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color5</span>', 'b2' ),
            'id'=>'color5',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color5'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色6/最佳答案', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color6</span>', 'b2' ),
            'id'=>'color6',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color6'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色7/发布内容', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color7</span>', 'b2' ),
            'id'=>'color7',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color7'],
        ));
        $massages->add_field(array(
            'name'    => __( '颜色8/打赏', 'b2' ),
            'desc'    => __( '为您的弹幕设置一个舒适的颜色 <span class="red">color8</span>', 'b2' ),
            'id'=>'color8',
            'type'=>'colorpicker',
            'default' => self::$default_settings['color8'],
        ));
    }
    public function Jitheme_index_tab11(){
        $rili= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab11_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab11', // The option key and admin menu page slug.            
            'tab_title'    => __('日历','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab11',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) ); 
        $index_rl_list = $rili->add_field( array(
            'id'          => 'diy_rl_list',
            'type'        => 'group',
            'description' => __('自定义分类切换<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
            'repeatable'  => true, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'       => __( '添加自定义分类-第{#}组', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'        => __( '添加广告', 'b2' ),
                'remove_button'     => __( '删除广告', 'b2' ),
                'sortable'          => true,
                'closed'         => true, // true to have the groups closed by default
                'remove_confirm' => __( '确定要删除这个自定义分类吗？', 'b2' ), // Performs confirmation before removing group.
            ),
        ));
        $rili->add_group_field($index_rl_list, array(
            'name' => __('分类标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) ); 
        $rili->add_group_field($index_rl_list, array(
            'name' => __('分类图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_qukuai_img'],
        ) );
        $rili->add_group_field($index_rl_list, array(
            'name' => __('分类连接','b2'),
            'id'   => 'link',
            'type' => 'text_url',
            'default'          => self::$default_settings['index_wuzu_desc'],
        ) ); 
        $rili->add_group_field($index_rl_list, array(
            'name' => __('分类描述','b2'),
            'id'   => 'desc',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) ); 
    }
    public function Jitheme_index_tab12() {
        $Qukuaib= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_index_tab12_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_index_tab12', // The option key and admin menu page slug.            
            'tab_title'    => __('区块B','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_index_tab12',
            'tab_group'    => 'b2_Jitheme_index_options',
        ) );
       $index_qukuaib_list = $Qukuaib->add_field( array(
            'id'          => 'index_qukb_list',
            'type'        => 'group',
            'description' => __('首页区块<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
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
        $Qukuaib->add_group_field($index_qukuaib_list, array(
            'name' => __('区块标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Qukuaib->add_group_field($index_qukuaib_list, array(
            'name' => __('区块链接','b2'),
            'id'   => 'link',
            'type' => 'text',
            'default'          => self::$default_settings['index_wuzu_title'],
        ) );
        $Qukuaib->add_group_field($index_qukuaib_list, array(
            'name' => __('区块图片','b2'),
            'id'   => 'img',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'default'          => self::$default_settings['index_qukuai_img'],
            'desc'=>'必须选择采用图片方式才可以显示，否则失效',
        ) );
        $Qukuaib->add_group_field($index_qukuaib_list, array(
            'name'    => __( '搜索关键词', 'b2' ),
            'id'=>'qukb_key',
            'type' => 'textarea_small',
            'desc'=>__('请输入关键词，每个关键词占一行，留空则不显示,<span class="red">注意规范：连接|名称</span>','b2'),
            'default'=>''
        ) );       
    }
}
$list_Index = new Index();
$list_Index->init();
