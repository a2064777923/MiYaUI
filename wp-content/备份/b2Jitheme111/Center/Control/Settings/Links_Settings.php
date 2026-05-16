<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Link
{
    //默认设置项
    public static $default_settings = [
        //网址导航
        'index_links_desc'=>'网站描述',
        'index_links_title_hz'=>'https://www.jitheme.com/',
        'index_links_title'=>'网站标题',
        'index_links_tis_title'=>'胡哥提示：Ctrl+D 快速收藏我们哦 ~',
        'index_links_tj_title'=>'链接推荐标题',
        'index_links_tj'=>1,
        'links_search_title'=>'以最快的速度获取想要的资源',
        'links_search_image'=>B2_CHILD_URI.'/Center/Assets/images/links_search_img.jpg',
        'index_wuzu_img'=>B2_CHILD_URI.'/Center/Assets/images/onecad.zzewm.jpg',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_link_page']);
    }
    //构造页面功能参数
    public function Jitheme_link_page(){
            $Jitheme_links = new_cmb2_box( array(
                'id'           => 'b2_Jitheme_link_options',
                'object_types' => array( 'options-page' ),
                'option_key'   => 'b2_Jitheme_link_main',
                'tab_group'    => 'b2_Jitheme_link_options',
                'parent_slug'  => 'Jitheme',
                'tab_title'    => __('基本设置','b2'),
                'menu_title'   => __('导航设置','b2'),
                'save_button'  => __( '保存配置', 'b2' )
            ) );
            $Jitheme_links->add_field(array(
                'name'    => __( '是否开启搜索框', 'b2' ),
                'desc'    => __( '导航页显示一个漂亮的搜索。', 'b2' ),
                'id'      =>  'links_search_off',
                'type'             => 'select',
                'options'          => array(
                    1 => __( '显示', 'b2' ),
                    0   => __( '隐藏', 'b2' ),
                ),
            ));
            $Jitheme_links->add_field(array(
                'before_row'=>'<div id="links_search" class="cmb-row">',
                'name' => __('搜索栏目标题','b2'),
                'id'   => 'links_search_title',
                'type' => 'text',
                'default'          => self::$default_settings['links_search_title'],
            ) );
            $Jitheme_links->add_field(array(
                'name'    => __( '搜索框背景图片', 'b2' ),
                'id'=>'links_search_image',
                'type'=>'file',
                'options' => array(
                    'url' => true, 
                ),
                'desc'=> __('不显示请留空','b2'),
                'default'=>self::$default_settings['links_search_image'],
            ));
            $Jitheme_links->add_field(array(
                'before_row'=>'</div>',
                'name'    => __( '是否开启导航推荐', 'b2' ),
                'desc'    => __( '导航页显示一个漂亮的推荐栏目。', 'b2' ),
                'id'      =>  'index_links_tj',
                'type'             => 'select',
                'options'          => array(
                    1 => __( '显示', 'b2' ),
                    0   => __( '隐藏', 'b2' ),
                ),
            ));
            $Jitheme_links->add_field(array(
                'before_row'=>'<div id="index_links_tj_title" class="cmb-row">',
                'name' => __('推荐标题','b2'),
                'id'   => 'index_links_tj_title',
                'type' => 'text',
                'default'          => self::$default_settings['index_links_tj_title'],
            ) );
            $Jitheme_links->add_field(array(
                'name' => __('提示语','b2'),
                'id'   => 'index_links_tis_title',
                'type' => 'text',
                'default'          => self::$default_settings['index_links_tis_title'],
            ) );
           $Jitheme_links_list = $Jitheme_links->add_field( array(
                'id'          => 'index_links_list',
                'type'        => 'group',
                'description' => __('添加推荐链接<span class="red">注意：每个模块的必填项必须填写，否则无法保存</span>','b2'),
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
            $Jitheme_links->add_group_field($Jitheme_links_list, array(
                'name' => __('链接图片','b2'),
                'id'   => 'index_links_img',
                'type' => 'file',
                'options' => array(
                    'url' => true, 
                ),
                'default'          => self::$default_settings['index_wuzu_img'],
            ) );
            $Jitheme_links->add_group_field($Jitheme_links_list, array(
                'name' => __('链接标题','b2'),
                'id'   => 'index_links_title',
                'type' => 'text',
                'default'          => self::$default_settings['index_links_title'],
            ) );
            $Jitheme_links->add_group_field($Jitheme_links_list, array(
                'name' => __('超级链接','b2'),
                'id'   => 'index_links_title_hz',
                'type' => 'text',
                'default'          => self::$default_settings['index_links_title_hz'],
            ) );
            $Jitheme_links->add_group_field($Jitheme_links_list, array(
                'name' => __('链接描述','b2'),
                'id'   => 'index_links_desc',
                'type' => 'text',
                'default'          => self::$default_settings['index_links_desc'],
            ) );
            // self::one_other_settings();
            if (!get_option('oauth')) return;}
    }
$list_Link = new Link();
$list_Link->init();
