<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Collection
{
    //默认设置项
    public static $default_settings = [
        'dim_open' => 0,
        'collection_ls'=> 0,
        'collection_open_cover'=>0,
        'collection_title'=>'专题',
        'collection_desc'=>'实时热点，深锐观察',
        'collection_image'=>'',
        'collection_order'=>'desc',
        'collection_post_order'=>'desc',
        'collection_number'=>18,
        
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Ji_Collection_tab1']);
    }
    //构造页面功能参数
    public function Ji_Collection_tab1()
    {
        $Collection= new_cmb2_box([
            'id'           => 'ji_col_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_col_main',
            'tab_group'    => 'ji_col_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('专题设置','b2'),
            'menu_title'   => __('专题设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ]);
        $arr = array();
        $cats = get_terms('collection',array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty'      => false,
        ) );
        foreach( $cats as $cat ) {
            $arr[$cat->term_id] = $cat->name;
        } 
        $collection_name = b2_get_option('normal_custom','custom_collection_name');
        $Collection->add_field(array(
            'name'    => __( '是否开启'.$collection_name.'导航', 'b2' ),
            'desc'    => __( '专题聚合页有个漂亮的'.$collection_name.'导航。', 'b2' ),
            'id'      =>'collection_off',
            'type'             => 'select',
            'default'          => self::$default_settings['collection_ls'],
            'options'          => array(
                1   => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));
        $Collection->add_field(array(
            'name'    => __( ''.$collection_name.'列表显示', 'b2' ),
            'desc'    => __( '专题聚合页'.$collection_name.'显示方式。', 'b2' ),
            'id'      =>'collection_ls',
            'type'             => 'select',
            'default'          => self::$default_settings['collection_ls'],
            'options'          => array(
                1   => __( '文章列表', 'b2' ),
                0   => __( '专题描述', 'b2' ),
            ),
        ));
        $Collection->add_field(array(
            'name'    => '允许展示的'.$collection_name,
            'id'      => 'collection',
            'taxonomy'=>'collection',
            'desc'    => __('请选择要显示的'.$collection_name.'，可以拖动排序','b2'),
            'type'    => 'pw_multiselect',
            'options' =>$arr,
            'text'           => array(
            'no_terms_text' => sprintf(__('没有专题，请前往%s添加','b2'),'<a target="__blank" href="'.admin_url('//edit-tags.php?taxonomy=collection').'"></a>')
            ),
        ));
        $Collection->add_field(array(
            'name' => sprintf(__('%s聚合页面标题','b2'),$collection_name),
            'id'   => 'collection_title',
            'type'             => 'text',
            'default'          => self::$default_settings['collection_title'],
            'desc'=>sprintf(__('将会显示在%s聚合页面','b2'),$collection_name)
        ));

        $Collection->add_field(array(
            'name' => sprintf(__('%s聚合页面描述','b2'),$collection_name),
            'id'   => 'collection_desc',
            'type'             => 'text',
            'default'          => self::$default_settings['collection_desc'],
            'desc'=>sprintf(__('将会显示在%s聚合页面','b2'),$collection_name)
        ));

        $Collection->add_field(array(
            'name' => __('模块背景图片','b2'),
            'id'   => 'collection_image',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> sprintf(__('将会显示在%s聚合页面','b2'),$collection_name)
        ));
        self::Ji_Collection_tab2();
    }
    public function Ji_Collection_tab2(){
        $Collectio1n= new_cmb2_box( array(
            'id'           => 'Ji_Collection_tab2',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_col_tab2',
            'tab_title'    => __('专题预留（无效）','b2'),
            'parent_slug'  => 'b2_col_tab2',
            'tab_group'    => 'ji_col_options',
        ) );
        $Collectio1n ->add_field(array(
            'name'    => __( '专题预留（无效）', 'b2' ),
            'desc'    => __( '广告代码 <span class="red">纯HTML代码</span>，没有侧留空', 'b2' ),
            'id'=>'Single_topgg01111',
            'type' => 'textarea',
            'default'=>'',
        ));
    }
}
$list_Diy = new Collection();
$list_Diy->init();
