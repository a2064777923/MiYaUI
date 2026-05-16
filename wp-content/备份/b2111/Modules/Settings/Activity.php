<?php namespace B2\Modules\Settings;

class Activity{

    public static $default_settings = array(
        'activity_open'=>1,
    );

     
    public function init(){
        add_action('cmb2_admin_init',array($this,'activity_settings'));

    }

    public static function get_default_settings($key){
       
        $arr = array(
            'activity_open'=>1,
            'activity_cats'=>[],
            'activity_title'=>__('活动','b2'),
            'activity_name'=>__('活动','b2'),
            'activity_tdk_desc'=>'',
            'activity_tdk_keywords'=>'',

        );

        if($key == 'all'){
            return $arr;
        }
        
        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function activity_settings(){

        $activity_name = b2_get_option('normal_custom','custom_activity_name');

        //常规设置
        $activity = new_cmb2_box( array(
            'id'           => 'b2_activity_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_activity_main',
            'tab_group'    => 'b2_activity_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'),$activity_name),
            'menu_title'   => sprintf(__('%s设置','b2'),$activity_name),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $activity_cats = array();

        $activity_data = get_terms( 'activity_cat', array(
            'hierarchical' => true,
            'hide_empty' => true,
            'cache_domain'=>'b2_activity_cat'
        ) );

        foreach( $activity_data as $v ) {
            $activity_cats[$v->term_id] = $v->name;
        };

        $activity->add_field(array(
            'name'    => sprintf(__( '是否启用%s', 'b2' ),$activity_name),
            'id'=>'activity_open',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用','b2'),
                0=>__('关闭','b2')
            )
        ));

        $activity->add_field(array(
            'name'    => sprintf(__( '%s首页标题', 'b2' ),$activity_name),
            'id'=>'activity_title',
            'type'    => 'text',
            'default' =>__('活动','b2'),
            'desc'=>sprintf(__('显示在%s首页的顶部'),$activity_name)
        ));

        $activity->add_field(array(
            'name'    => sprintf(__( '%s首页SEO名称', 'b2' ),$activity_name),
            'id'=>'activity_name',
            'type'=>'text',
            'default'=>self::get_default_settings('activity_name')
        ));

        $activity->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ),$activity_name),
            'id'=>'activity_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('activity_tdk_desc')
        ));

        $activity->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ),$activity_name),
            'id'=>'activity_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('activity_tdk_keywords')
        ));

        $activity->add_field(array(
            'name'    => sprintf(__( '要显示的%s分类', 'b2' ),$activity_name),
            'id'=>'activity_cats',
            'type'    => 'pw_multiselect',
            'options' =>$activity_cats,
            'desc'=>sprintf(__('请确保%s中有链接，否则此处不显示','b2'),$activity_name)
        ));

        $activity_submit = new_cmb2_box( array(
            'id'           => 'b2_activity_submit_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_activity_submit',
            'tab_group'    => 'b2_activity_options',
            'parent_slug'     => '/admin.php?page=b2_activity_main',
            'tab_title'    => __('发布设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $activity_submit->add_field(array(
            'name'    => sprintf(__( '允许入驻的%s分类', 'b2' ),$activity_name),
            'id'=>'activity_submit_cats',
            'type'    => 'pw_multiselect',
            'options' =>$activity_cats,
        ));

        $activity_submit->add_field(array(
            'name'    => sprintf(__( '网站描述允许的最小字数', 'b2' ),$activity_name),
            'id'=>'activity_submit_content_count',
            'type'    => 'text',
            'default' =>'100',
            'desc'=>__('网站描述需要最少输入多少个字，才允许发布','b2')
        ));

        $activity_single = new_cmb2_box( array(
            'id'           => 'b2_activity_single_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_activity_single',
            'tab_group'    => 'b2_activity_options',
            'parent_slug'     => '/admin.php?page=b2_activity_main',
            'tab_title'    => __('连接内页设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $activity_single->add_field(array(
            'name'    => __( '活动内页顶部广告位', 'b2' ),
            'id'=>'activity_single_top',
            'type'    => 'textarea_code',
        ));

        $activity_single->add_field(array(
            'name'    => __( '活动内页底部广告位', 'b2' ),
            'id'=>'activity_single_bottom',
            'type'    => 'textarea_code',
        ));
    }
}