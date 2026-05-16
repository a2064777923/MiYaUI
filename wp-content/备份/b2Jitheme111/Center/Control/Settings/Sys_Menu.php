<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Sys
{
    //默认设置项
    public static $default_settings = [
        //基本设置
        'one_fangdao'=>1,
        'one_header_image_mr'=>B2_CHILD_URI.'/Center/Assets/images/one_header.png',
        'default-img'=>B2_CHILD_URI.'/Center/Assets/images/default-img.jpg',
        'one_logo_saog'=>0,
        'index_huise'=>0,
        'mokuai_padding'=>'',
        'index_descmk_off'=>0,
        'index_jiaobiao_color1'=>'#03bbff',
        'index_jiaobiao_color2'=>'#ca26ff',
        'index_jiaobiao_color3'=>'#7bdcb5',
        'index_jiaobiao_color4'=>'#ff2a8e',
        'index_jiaobiao_color5'=>'#ff8f21',
        'index_jiaobiao_color6'=>'#10e396',
        'jitheme_padding'=>'10px',
        'one_video_mp4'=>'https://www.chuangkit.com/distweb/media/index_bg_video.6d1136d1.mp4',
        'one_about_image'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'jitheme_padding'=>'16px',
        'jitheme_radius'=>'0px',
        'onecad_Archive_list_color'=>'',
        'Archive_image'=>B2_CHILD_URI.'/Center/Assets/images/Archive_image.png',
        'index_circles_top_off'=>0,
        'index_circles_top_img'=>'',
        'index_circles_ct_img'=>'',
        'jitheme_line'=>'#fff',
    ];
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_main_page']);
    }
    //构造页面功能参数
    public function Jitheme_main_page(){
        $Jitheme_main = new_cmb2_box( array(
            'id'           => 'Jitheme_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_main_tab1',
            'tab_group'    => 'b2_Jitheme_main_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('基本设置','b2'),
            'menu_title'   => __('综合设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        // $Jitheme_main->add_field(array(
        //     'name'    => __( '是否压缩代码', 'b2' ),
        //     'desc'    => __( '压缩WordPress前端html代码。', 'b2' ),
        //     'id'      =>'jitheme_web',
        //     'type'             => 'select',
        //     'default'          => self::$default_settings['one_fangdao'],
        //     'options'          => array(
        //         1   => __( '开启', 'b2' ),
        //         0   => __( '关闭', 'b2' ),
        //     ),
        // ));
        $Jitheme_main->add_field(array(
            'name'    => __( '是否开启防盗模式', 'b2' ),
            'desc'    => __( '防止客户端浏览网页F12和右键。', 'b2' ),
            'id'      =>'one_fangdao',
            'type'             => 'select',
            'default'          => self::$default_settings['one_fangdao'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_main->add_field(array(
            'name'    => __( '是否开启暗夜模式', 'b2' ),
            'desc'    => __( '网页会有个深色模式，方便在夜间浏览网页。', 'b2' ),
            'id'      =>'ji_dark',
            'type'             => 'select',
            'default'          => self::$default_settings['one_fangdao'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_main->add_field(array(
            'name'    => __( '导航栏背景渐变色', 'b2' ),
            'id'=>'one_header_image',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片<br><span class="red">'.self::$default_settings['one_header_image_mr'].'</span>','b2'),
            'default'    =>''
        ));
        // $Jitheme_main->add_field(array(
        //     'name'    => __( '自定义缩略图', 'b2' ),
        //     'id'=>'default-img',
        //     'type'=>'file',
        //     'options' => array(
        //         'url' => true, 
        //     ),
        //     'desc'=> __('不显示请留空,可以直接复制下面的地址增加默认的图片<br><span class="red">'.self::$default_settings['default-img'].'</span>','b2'),
        //     'default'    =>''
        // ));
        $Jitheme_main->add_field(array(
            'name'    => __( '是否开启logo闪光效果', 'b2' ),
            'desc'    => __( 'LOGO上会有一束白光闪过，很多人都喜欢这个功能。', 'b2' ),
            'id'      =>'one_logo_saog',
            'type'             => 'select',
            'default'          => self::$default_settings['one_logo_saog'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_main->add_field(array(
            'name'    => __( '重大事件哀悼', 'b2' ),
            'desc'    => __( '纪念哀悼事件，使网站变灰色。', 'b2' ),
            'id'      =>  'index_huise',
            'type'             => 'select',
            'default'          => self::$default_settings['index_huise'],
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Jitheme_main->add_field(array(
            'name' => __('模块圆角','b2'),
            'id'   => 'jitheme_radius',
            'type' => 'text',
            'default'          => self::$default_settings['jitheme_radius'],
            'desc'    => __( '统一各个模块的外框圆角。', 'b2' ),
        ) ); 
        $Jitheme_main->add_field(array(
            'name' => __('文章列表间距','b2'),
            'id'   => 'jitheme_padding',
            'type' => 'text',
            'desc'    => __( '控制网站的所有模块间距默认的为16px。', 'b2' ),
            'default'          => self::$default_settings['jitheme_padding'],
        ) ); 
        $Jitheme_main->add_field(array(
            'name' => __('文章列表边框','b2'),
            'id'   => 'mokuai_padding',
            'type' => 'text',
            'default'          => self::$default_settings['mokuai_padding'],
            'desc'    => sprintf(__( '需要开启上方渐变背景，否则为白色边框，默认%s，不要边框则不填写', 'b2' ),'<code>16px</code>','<code>px</code>'),
        )); 
        $Jitheme_main->add_field(array(
            'name' => __('统一模块边线','b2'),
            'id'   => 'jitheme_line',
            'type'             => 'select',
            'default'          => self::$default_settings['index_huise'],
            'options'          => array(
                3 => __( '显示边线和阴影', 'b2' ),
                2 => __( '显示阴影', 'b2' ),
                1 => __( '显示边线', 'b2' ),
                0   => __( '都不显示', 'b2' ),
            ),
            'default'          => 2,
        )); 
        self::Jitheme_main_tab2();
    }
    public function Jitheme_main_tab2(){
        $Jitheme_main_color= new_cmb2_box( array(
            'id'           => 'b2_Jitheme_main_tab2_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_main_tab2', // The option key and admin menu page slug.            
            'tab_title'    => __('颜色设置','b2'), // Falls back to 'title' (above).
            'parent_slug'  => 'b2_Jitheme_main_tab2',
            'tab_group'    => 'b2_Jitheme_main_options',
        ) );
        $Jitheme_main_color->add_field(array(
            'before_row'=>'<h2>角标颜色设置</h2>',
            'name'    => __( '颜色/角标1', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color1</span>', 'b2' ),
            'id'=>'index_jiaobiao_color1',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color1'],
        ));
        $Jitheme_main_color->add_field(array(
            'name'    => __( '颜色/角标2', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color2</span>', 'b2' ),
            'id'=>'index_jiaobiao_color2',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color2'],
        ));
        $Jitheme_main_color->add_field(array(
            'name'    => __( '颜色/角标3', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color3</span>', 'b2' ),
            'id'=>'index_jiaobiao_color3',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color3'],
        ));
        $Jitheme_main_color->add_field(array(
            'name'    => __( '颜色/角标4', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color4</span>', 'b2' ),
            'id'=>'index_jiaobiao_color4',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color4'],
        ));
        $Jitheme_main_color->add_field(array(
            'name'    => __( '颜色/角标5', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color5</span>', 'b2' ),
            'id'=>'index_jiaobiao_color5',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color5'],
        ));
        $Jitheme_main_color->add_field(array(
            'name'    => __( '颜色/角标6', 'b2' ),
            'desc'    => __( '为您的区块后缀背景色设置一个舒适的颜色 <span class="red">index_jiaobiao_color6</span>', 'b2' ),
            'id'=>'index_jiaobiao_color6',
            'type'=>'colorpicker',
            'default' => self::$default_settings['index_jiaobiao_color6'],
        ));
    }
}
$list_Sys = new Sys();
$list_Sys->init();
