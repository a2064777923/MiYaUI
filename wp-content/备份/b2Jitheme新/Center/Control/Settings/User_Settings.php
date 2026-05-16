<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class User
{
    //默认设置项
    public static $default_settings = [
        //顶部VIP设置
        'onecad_vips_title'=>'欢迎加入极主题VIP，开通会员尊享特权',
        'index_onecad_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad-home-vip-bg.png',
        'index_onecad_vip_title'=>'会员尊享权益圈子',
        'one_template_top_vip_img'=>B2_CHILD_URI.'/Center/Assets/images/vipiconhover.svg',
        'index_onecad_vip_desc'=>'成为我们的VIP会员，尊享无限免费下载使用，更享受全面的服务与福利',
        'index_onecad_vip_tj_title'=>'推荐购买',
        
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_user_page']);
    }
    //构造页面功能参数
    public function Jitheme_user_page(){
            $settings = get_option('b2_normal_user');
            $settings_vip = isset($settings['user_vip_group']) ? (array)$settings['user_vip_group'] : array();
            $_settings_vip = array();
            foreach($settings_vip as $k => $v){
                if(!isset($v['name'])){
                    $v['name'] = __('未设置','b2');
                }
                $_settings_vip['vip'.$k] = $v;
            }
            $settings = array_merge($_settings_vip);
            $setting_lvs = array();
            foreach($settings as $k => $v){
                $setting_lvs[$k] = $v['name'];
            }
            $Jitheme_vip = new_cmb2_box( array(
                'id'           => 'b2_Jitheme_user_options',
                'object_types' => array( 'options-page' ),
                'option_key'   => 'b2_Jitheme_user_main',
                'tab_group'    => 'b2_Jitheme_user_options',
                'parent_slug'  => 'Jitheme',
                'tab_title'    => __('基本设置','b2'),
                'menu_title'   => __('会员设置','b2'),
                'save_button'  => __( '保存配置', 'b2' )
            ) );
            $Jitheme_vip->add_field(array(
            'before_row'=>'<h2>首页VIP特权展示模块</h2>',
            'name'    => __( '是否开启VIP特权', 'b2' ),
            'desc'    => __( '显示一个漂亮VIP特权展示模块。', 'b2' ),
            'id'      =>  'index_onecad_vip_off',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
            ),
            ));
            $Jitheme_vip->add_field(array(
                'name' => __('首页VIP背景图片','b2'),
                'id'   => 'index_onecad_vip_img',
                'type' => 'file',
                'options' => array(
                    'url' => true, 
                ),
                
            ) );
            $Jitheme_vip->add_field(array(
                'name' => __('标题','b2'),
                'id'   => 'index_onecad_vip_title',
                'type' => 'text',
                'desc'    => __( '首页VIP页面标题', 'b2' ),
                'default'          => self::$default_settings['index_onecad_vip_title'],
            ) );
            $Jitheme_vip->add_field(array(
                'name' => __('描述','b2'),
                'id'   => 'index_onecad_vip_desc',
                'type' => 'text',
                'desc'    => __( '首页VIP页面副标题。', 'b2' ),
                'default'          => self::$default_settings['index_onecad_vip_desc'],
            ) );
            $Jitheme_vip->add_field(array(
                'name' => __('推荐购买','b2'),
                'id'   => 'index_onecad_vip_tj',
                'type'    => 'radio_inline',
                'options'=>$setting_lvs,
                'desc'=> __('选择一个推荐购买的级别','b2')
            ));
            $Jitheme_vip->add_field(array(
                'name' => __('角标文字','b2'),
                'id'   => 'index_onecad_vip_tj_title',
                'type' => 'text',
                'desc'    => __( '推荐购买的角标显示提醒文字。', 'b2' ),
                'default'          => self::$default_settings['index_onecad_vip_tj_title'],
            ) );
            
            
            $Jitheme_vip->add_field(array(
                'before_row'=>'<h2>VIPS会员权益页面</h2>',
                'name' => __('VIPS样式选择','b2'),
                'id'   => 'jitheme_vip_page',
                'type' => 'radio_image',
                'options'          => array(
                    'jitheme_vip0' => __('VIP原生美化','b2'), 
                    'jitheme_vip1' => __('VIP权益页面1','b2'), 
                    'jitheme_vip2' => __('VIP权益页面2','b2'), 
                    'jitheme_vip3' => __('VIP权益页面3','b2'), 
                ),
                'classes'=>array('cmb-type-radio-image'),
                'images_path'  => B2_CHILD_URI,
                'images'       => array(
                    'jitheme_vip0' => '/Center/Assets/admin/images/jitheme_vip0.png',
                    'jitheme_vip1' => '/Center/Assets/admin/images/jitheme_vip1.png',
                    'jitheme_vip2' => '/Center/Assets/admin/images/jitheme_vip2.png',
                    'jitheme_vip3' => '/Center/Assets/admin/images/jitheme_vip3.png',
                ),
            ) );   
            $Jitheme_vip->add_field(array(
                'name' => __('VIPS背景图片','b2'),
                'id'   => 'onecad_vips_img',
                'type' => 'file',
                'options' => array(
                    'url' => true, 
                ),
                
            ) );

            $Jitheme_vip->add_field(array(
                'name'    => __( '权益分类', 'b2' ),
                'id'=>'onecad_vips_list',
                'type'=>'textarea',
                'desc'=>sprintf(__( '添加代码样式为：必须<span class="red">%s</span>必须按此规范写。<a target="_blank" href="https://www.jitheme.com/demo">查看极主题图标名称代码</a>', 'b2' ),'<code>'.htmlspecialchars('图标名称|标题').'</code>'),
            ));
            $Jitheme_vips_faq_list = $Jitheme_vip->add_field( array(
                'id'          => 'onecad_vips_faq_list',
                'type'        => 'group',
                'description' => __('常见问题<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
                'repeatable'  => true, // use false if you want non-repeatable group
                'options'     => array(
                    'group_title'       => __( '添加问题-第{#}确定要删除这个问题吗', 'b2' ), // since version 1.1.4, {#} gets replaced by row number
                    'add_button'        => __( '添加问题', 'b2' ),
                    'remove_button'     => __( '删除问题', 'b2' ),
                    'sortable'          => true,
                    'closed'         => true, // true to have the groups closed by default
                    'remove_confirm' => __( '确定要删除这个问题吗？', 'b2' ), // Performs confirmation before removing group.
                ),
            ));
            $Jitheme_vip->add_group_field($Jitheme_vips_faq_list, array(
                'name' => __('问题标题','b2'),
                'id'   => 'onecad_vips_faq_list_title',
                'type' => 'text',
                'default'          => self::$default_settings['onecad_vips_title'],
            ) );
            $Jitheme_vip->add_group_field($Jitheme_vips_faq_list, array(
                'name' => __('问题答案','b2'),
                'id'   => 'onecad_vips_faq_list_daan',
                'type' => 'textarea',
                'default'          => self::$default_settings['index_onecad_vip_desc'],
            ) );
            // self::one_other_settings();
            if (!get_option('oauth')) return;}
    }
$list_User = new User();
$list_User->init();
