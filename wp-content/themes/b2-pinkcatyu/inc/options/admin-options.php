<?php
if (!defined('ABSPATH')) {
    die;
}
 

if (class_exists('CSF')) {
    $prefix = 'senyu_admin';
    $imgs=B2_CHILD_URI.'/pic/';
     
//开始构建
    CSF::createOptions($prefix, array(
        'menu_title' => '粉色猫语',
        'menu_slug' => 'senyu_admin',
        'framework_title' => '粉色猫语',
        'show_in_customizer' => true,
        'footer_text'  => '粉色猫语B2子主题 - 平民化的价格，高端的享受！',
        'theme' => 'light',
         'menu_position' => 1,
    ));
     CSF::createSection($prefix, array(
        'id' => 'global',
        'title' => '全局&设置',
        'icon' => 'fa fa-bullseye'
    ));
    
    CSF::createSection($prefix, array(
    'parent'      => 'global',
    'title' => '基本设置',
    'icon' => 'fa fa-newspaper-o',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="fa fa-heart fa-fw"></i> 感谢您使用粉色猫语子主题</h3>
                <p>粉色猫语子主题是一款良心、厚道的好产品！创作不易，支持正版，从我做起！</p>
                <div style="margin:10px 14px;">
                <li>本页面由粉色猫语一人制作，请大家在使用的时候尊重原作者的辛苦劳动</li>
                <li>粉色猫语子主题官网：<a target="_bank" href="https://www.sluyu.com/">www.sluyu.com</a></li>
                <li>作者QQ：<a target="_bank" href="http://wpa.qq.com/msgrd?v=3&amp;uin=1035761010&amp;site=qq&amp;menu=yes">1035761010</a></li>
                </div>',
        ),
        array(
            'title' => '开关样式',
            'label' => '启用无效果，仅演示',
            'id'    => 'senyu_test',
            'default' => 'false',
            'type' => 'switcher'
            ) ,
        
    ),
));









//文章内容美化设置
CSF::createSection( $prefix, array(
	'id'    => 'admin_setting',
	'title' => '文章内容美化',
	'icon'        => 'dashicons dashicons-admin-generic',
) );
CSF::createSection( $prefix, array(
	'parent'      => 'admin_setting',
	'title'       => '基础设置',
	'icon'        => 'fa fa-newspaper-o',
	'description' => '文章内容美化设置',
	'fields'      => array(
		array(
			'id'       => 'cn_btn',
			'type'     => 'switcher',
			'title'    => '文章内页下载模块背景',
			'default' => 'false',
		),
		array(
			'id'       => 'post-list-cat',
			'type'     => 'switcher',
			'title'    => '分类页分类样式',
			'default' => 'false',
            'desc'    => '分类页变统一蓝色加强样式', // 描述信息
        ),
		array(
			'id'       => 'mao2',
			'type'     => 'switcher',
			'title'    => '可爱喵爪样式',
			'default' => 'false',
            'desc'    => '分类页变统一蓝色加强样式', // 描述信息
        ),

    )
));







   
    
 
       CSF::createSection($prefix, array(
        'id' => 'auth',
        'title' => '功能&其他',
        'icon' => 'fa fa-bullseye'
    ));
    
    
    
    
     //-----------------------------------------------------------------
    CSF::createSection($prefix, array(
        'parent'      => 'auth',
        'title'       => 'Email邮件',
        'icon'        => 'fa fa-fw fa-envelope-o',
        'description' => '',
        'fields'      => array(
     
            array(
                'title'   => '邮件SMTP',
                'id'      => 'mail_smtps',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => 'WordPress配置SMTP邮箱，解决邮件发送问题。功能和SMTP插件一致，所以！不能和其他SMTP插件一起开启！同时请注意开启服务器对应的端口！',
            ),

            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'title'      => 'SMTP配置',
                'subtitle'   => '发信人邮箱账号',
                'class'      => 'compact',
                'id'         => 'mail_name',
                'class'      => 'compact-heading',
                'default'    => '88888888@qq.com',
                'validate'   => 'csf_validate_email',
                'type'       => 'text',
            ),
array(
                'dependency' => array('mail_smtps', '!=', ''),
                'id'         => 'mail_showname',
                'class'      => 'compact',
                'title'      => '发件人显示名称',
              
                'default'    => '发件人名称',
                'type'       => 'text',
            ),
            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'id'         => 'mail_passwd',
                'class'      => 'compact',
                'title'      => 'SMTP服务邮箱密码',
                'desc'       => '此密码非邮箱密码，一般需要单独开启',
                'default'    => '',
                'type'       => 'text',
            ),

            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'id'         => 'mail_host',
                'class'      => 'compact',
                'title'      => '邮件服务器地址',
                'default'    => 'smtp.qq.com',
                'type'       => 'text',
            ),

            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'id'         => 'mail_port',
                'class'      => 'compact',
                'title'      => 'SMTP服务器端口',
                'default'    => '465',
                'type'       => 'number',
            ),

            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'title'      => 'SMTPAuth服务',
                'id'         => 'mail_smtpauth',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => true,
            ),

            array(
                'dependency' => array('mail_smtps', '!=', ''),
                'title'      => '加密方式（SMTPSecure）',
                'id'         => 'mail_smtpsecure',
                'class'      => 'compact',
                'default'    => 'ssl',
                'type'       => 'text',
            ),
            CFS_Module::email_test(),
        ),
    ));


    
}








 /**
 * @description: 后台AJAX发送测试邮件
 * @param {*}
 * @return {*}
 */
function zib_test_send_mail()
{
    if (empty($_POST['email'])) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入邮箱账号')));
        exit();
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo (json_encode(array('error' => 1, 'msg' => '邮箱格式错误')));
        exit();
    }
    $blog_name = get_bloginfo('name');
    $blog_url  = get_bloginfo('url');
    $title     = '[' . $blog_name . '] 测试邮件';

    $message = '您好！ <br />';
    $message .= '这是一封来自' . $blog_name . '[' . $blog_url . ']的测试邮件<br />';
    $message .= '该邮件由网站后台发出，如果非您本人操作，请忽略此邮件 <br />';
    $message .= current_time("Y-m-d H:i:s");

    try {
        $test = wp_mail($_POST['email'], $title, $message);
    } catch (\Exception $e) {
        echo array('error' => 1, 'msg' => $e->getMessage());
        exit();
    }
    if ($test) {
        echo (json_encode(array('error' => 0, 'msg' => '后台已操作')));
    } else {
        echo (json_encode(array('error' => 1, 'msg' => '发送失败')));
    }
    exit();
}
add_action('wp_ajax_test_send_mail', 'zib_test_send_mail');
 class CFS_Module
{
    public static function email_test()
    {
        $con = '<div class="options-notice">
        <div class="explain">
        <p><b>您可以在下方测试邮件发送功能是否正常，请输入您的邮箱账号：</b></p>
        <ajaxform class="ajax-form" ajax-url="' . admin_url("admin-ajax.php") . '">
        <div class="flex ac hh"><input class="mt6 mr10" type="text" style="max-width:300px;" ajax-name="email" value="' . senyu_pz('mail_name') . '" placeholder="88888888@qq.com"><a href="javascript:;" class="but jb-yellow ajax-submit mt6" style="background:linear-gradient(135deg, #f99d4d 10%, #f7631d 100%);font-size: 14px;
    padding: 5px 10px;margin-left:5px;text-align: center;border-radius: 4px;color:#fff;text-decoration: none;"><i class="fa fa-paper-plane-o"></i> 发送测试邮件</a></div>
        <div class="ajax-notice mt6"></div>
        <input type="hidden" ajax-name="action" value="test_send_mail">
        </ajaxform>
        </div></div>';
        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

}
 




//邮件smtp设置
if(senyu_pz('mail_smtps')){
function senyu_mail_smtp($phpmailer)
{
    if (senyu_pz('mail_smtps')) {
        $phpmailer->IsSMTP();
        $phpmailer->FromName   = senyu_pz('mail_showname');
        $phpmailer->Host       = senyu_pz('mail_host', 'smtp.qq.com');
        $phpmailer->Port       = senyu_pz('mail_port', '465');
        $phpmailer->Username   = senyu_pz('mail_name', '88888888@qq.com');
        $phpmailer->Passenyu   = senyu_pz('mail_passwd', '123456789');
        $phpmailer->From       = senyu_pz('mail_name', '88888888@qq.com');
        $phpmailer->SMTPAuth   = senyu_pz('mail_smtpauth', true);
        $phpmailer->SMTPSecure = senyu_pz('mail_smtpsecure', 'ssl');
    }
}
add_action('phpmailer_init', 'senyu_mail_smtp');

}








// 这里我们设置为父主题前缀，将菜单挂载至父主题后台菜单
$prefix = 'child_options';

// 设置图片目录为子主题的路径
$imagepath = get_stylesheet_directory_uri() . '/img/';

    //开始构建
    CSF::createOptions($prefix, array(
        'menu_title'         => '子主题设置',
        'menu_slug'          => 'child_options',
        'framework_title'    => '子主题',
        'show_in_customizer' => true, //在wp-customize中也显示相同的选项
        'footer_text'        => '更优雅的wordpress主题-Zibll主题 V' . wp_get_theme()['Version'],
        'footer_credit'      => '<i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> ',
        'theme'              => 'light',
    ));


// 创建一个示例菜单项，挂在'child-theme-features'下
// 'parent'      => 挂钩到上面创建的栏目ID，使这个新的部分成为其子部分
// 'title'       => 子菜单项的标题
// 'icon'        => 子菜单项的图标
// 'description' => 菜单项描述，显示在菜单顶部
// 'fields'      => 字段数组，定义了该菜单下的所有设置选项
CSF::createSection($prefix, array(
    'id'          => 'demo', // 指定该节将作为哪个父节的子节
    'title'       => '演示字段', // 子菜单项的标题
    'icon'        => 'fa fa-cog', // 子菜单项的图标
    'description' => '这里你可以配置子主题的各种高级选项.', // 菜单顶部的描述信息
    'fields'      => array(
        // 消息字段：显示一段HTML内容
        array(
            'type'    => 'submessage', // 字段类型
            'content' => '<p><b>消息字段：</b></p><p>支持自定义HTML</p>', // 自定义HTML内容
            'style'   => 'warning', // 消息样式（如：info, success, warning, danger）
        ),
        // 文本输入框
        array(
            'id'      => 'example_textfield', // 字段ID，用于存储数据时识别
            'type'    => 'text', // 字段类型
            'title'   => '文本字段示例', // 字段标题
            'subtitle'=> '这是一个简单的文本输入框', // 字段副标题
            'default' => '默认文本', // 默认值
            'desc'    => '提供给用户的额外说明.', // 描述信息
        ),
        // 开关字段
        array(
            'title'   => '启用/禁用某功能', // 字段标题
            'id'      => 'example_switcher', // 字段ID
            'class'   => 'compact', // CSS类，可以用来改变外观
            'type'    => 'switcher', // 字段类型
            'default' => true, // 默认状态，true为开启，false为关闭
            'desc'    => '根据开关状态执行不同的操作.', // 描述信息
        ),
        // 文本区域（多行文本）
        array(
            'id'      => 'example_textarea', // 字段ID
            'type'    => 'textarea', // 字段类型
            'title'   => '多行文本区域', // 字段标题
            'subtitle'=> '允许用户输入多行文本', // 字段副标题
            'desc'    => '适合长文本输入，如描述或备注.', // 描述信息
        ),
        // 图片上传器
        array(
            'id'      => 'example_image_upload', // 字段ID
            'type'    => 'media', // 字段类型，允许选择媒体文件（图片、视频等）
            'title'   => '上传图片', // 字段标题
            'subtitle'=> '选择并上传一张图片', // 字段副标题
            'desc'    => '用于展示或作为背景图片使用.', // 描述信息
        ),
        // 选择框
        array(
            'id'      => 'example_select', // 字段ID
            'type'    => 'select', // 字段类型
            'title'   => '选择框示例', // 字段标题
            'options' => array( // 可选值列表
                'option1' => '选项 1',
                'option2' => '选项 2',
                'option3' => '选项 3',
            ),
            'default' => 'option1', // 默认选择的值
            'desc'    => '从列表中选择一个选项.', // 描述信息
        ),
        // 颜色选择器
        array(
            'id'      => 'example_color_picker', // 字段ID
            'type'    => 'color', // 字段类型
            'title'   => '颜色选择器', // 字段标题
            'default' => '#ffffff', // 默认颜色值
            'desc'    => '选择一个颜色用于网站元素.', // 描述信息
        ),
        // 字段依赖性示例：仅当开关开启时显示文本框
        array(
            'id'      => 'dependent_text',
            'type'    => 'text',
            'title'   => '依赖性文本框',
            'dependency' => array('example_switcher', '=', '1'), // 当开关开启（值为1）时显示此字段
            'desc'    => '仅在上方开关开启时可见',
        ),
    ),
));

// 功能演示
CSF::createSection($prefix, array(
    'id'      => 'base',
    'title'       => '功能演示',
    'icon'        => 'fa fa-linode',
    'description' => '',
    'fields'      => array(
        array(
            'title'   => '彩色滚动条',
            'id'      => 'child_demo_func',
            'subtitle'=> '开启后将网站滚动条改成彩色',
            'class'   => 'compact',
            'type'    => 'switcher',
            'default' => false,
            'desc'    => '菜单字段在子主题目录下的<code>/core/options/options.php</code>，功能字段在子主题目录下的<code>/core/functions/functions.php</code>',
        ),
        array(
            'title'   => '启用维护模式',
            'id'      => 'maintenance_mode_switcher',
            'class'   => 'compact',
            'type'    => 'switcher',
            'default' => false, // 默认关闭
            'desc'    => '开启后，除管理员外的所有用户将看到维护页面.',
        ),
    ),
));