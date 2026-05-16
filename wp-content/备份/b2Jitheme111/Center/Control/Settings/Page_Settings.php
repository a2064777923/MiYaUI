<?php
/**
 * 极主题-Jitheme.com
 * 7B2主题美化最好看的子主题QQ:8600376600
 */
class Pages
{
    //默认设置项
    public static $default_settings = [
        //文章内页设置
        // 'index_Single_title'=>'免费,VIP,推荐,原创,热门,精品',
        'ranks_title'=>'极主题排行榜',
        'title'=> '全部问答',
        'cat_db'=>'连接|图标|名称',
        'ranks_fl_title'=>'标题',
        'ranks_fl_desc'=>'副标题',
        'ranks_desc'=>'榜单刷新时间：实时刷新',
        'ranks_arc'=>'1,1,1',
        'ranks_user'=>'1,1,1,1,1,1',
        'ranks_rules'=>'为设计师发声，替好作品说话。媒体矩阵曝光渠道，为设计赋能！',
        'ranks_select'=>'new',
        'ranks_img'=>B2_CHILD_URI.'/Center/Assets/images/links_search_img.jpg',
    ];
	
   public function init()
    {
        //创建设置页面
        add_action('cmb2_admin_init', [$this, 'Jitheme_page_ranks']);
    }
    //构造页面功能参数
    public function Jitheme_page_ranks(){
        $Ranks = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_page_options',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_page_ranks',
            'tab_group'    => 'b2_Jitheme_page_options',
            'parent_slug'  => 'Jitheme',
            'tab_title'    => __('排行榜','b2'),
            'menu_title'   => __('模板页面设置','b2'),
            'save_button'  => __( '保存配置', 'b2' )
        ) );
        $Ranks->add_field(array(
            'name' => '榜单标题',
            'id'   => 'ranks_title',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_title'],
            'desc'=>'排行榜标题'
        ));

        $Ranks->add_field(array(
            'name' => '副标题描述',
            'id'   => 'ranks_desc',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_desc'],
            'desc'=>'排行榜副标题描述'
        ));
        $Ranks->add_field(array(
            'name' => __('模块背景图片','b2'),
            'id'   => 'ranks_image',
            'type' => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=> '给排行榜加一个好看的背景图片',
            'default'          => self::$default_settings['ranks_img'],
        ));

        $Ranks->add_field(array(
            'before_row'=>'<h3>分类榜单设置项</h3>',
            'name' => '分类榜单',
            'id'   => 'ranks_arc',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_arc'],
            'desc'=>'分类榜单填写分类ID即可，用英文,隔开，最多3个'
        ));
        $Ranks->add_field(array(
            'name' => '分类榜单标题',
            'id'   => 'ranks_fl_title',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_fl_title'],
            'desc'=>'分类榜单标题'
        ));
        $Ranks->add_field(array(
            'name' => '分类榜单标题',
            'id'   => 'ranks_fl_desc',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_fl_desc'],
            'desc'=>'分类榜单描述',
        ));
        
        $Ranks->add_field(array(
            'before_row'=>'<h3>人气榜单设置项</h3>',
            'name' => '人气榜单',
            'id'   => 'ranks_user',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_user'],
            'desc'=>'人气榜单填写用户ID即可，用英文,隔开，最多6个'
        ));
        $Ranks->add_field(array(
            'name' => '人气榜单标题',
            'id'   => 'ranks_rq_title',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_fl_title'],
            'desc'=>'分类榜单标题'
        ));
        $Ranks->add_field(array(
            'name' => '人气榜单标题',
            'id'   => 'ranks_rq_desc',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_fl_desc'],
            'desc'=>'分类榜单描述'
        ));
        $Ranks->add_field(array(
            'before_row'=>'<h3>上榜规则设置项</h3>',
            'name' => '上榜规则',
            'id'   => 'ranks_rules',
            'type'             => 'text',
            'default'          => self::$default_settings['ranks_rules'],
            'desc'=>'上榜规则要求描述，简洁一些，文字不宜过多。'
        ));  
        
        $Ranks->add_field(array(
            'name' => '排行榜默认列表方式',
            'id'   => 'ranks_select',
            'type'             => 'select',
            'options'          => array(
                'new'   => __( '最新', 'b2' ),
                'random'  => __( '随机', 'b2' ),
                'views'   => __( '最多浏览', 'b2' ),
                'like'   => __( '最多喜欢', 'b2' ),
                'comments'   => __( '最多评论', 'b2' ),
            ),
            'default'          => self::$default_settings['ranks_select'],
            'desc'=>'更改排行榜TAB1页面文章默认的排列方式。'
        ));  
        self::Jitheme_page_ask();
        self::Jitheme_page_tabb();
    }
    public function Jitheme_page_ask(){
        $Ask = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_pages_options_ask',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_pages_ask',
            'tab_group'    => 'b2_Jitheme_page_options',
            'tab_title'    => __('问答页面','b2'),
            'parent_slug'  => 'b2_Jitheme_pages_ask',
        ) );
        $Ask->add_field(array(
            'name'    => __( '是否开启分类栏', 'b2' ),
            'desc'    => __( '问答聚合页面右侧会有一个分类侧栏。', 'b2' ),
            'id'      =>  'cat',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
            'default'          => 0,
        ));
        $Ask->add_field(array(
            'name' => __('侧栏头部连接','b2'),
            'id'   => 'title',
            'type' => 'text',
            'desc'    => __( '侧栏上方的全部问答连接名称,他是直接连接/ask这个地址的', 'b2' ),
            'default'          => self::$default_settings['title'],
        ) );
        $Ask->add_field(array(
            'name' => __('侧栏底部连接','b2'),
            'id'   => 'cat_db',
            'type' => 'textarea',
            'default' => self::$default_settings['cat_db'],
            'desc'    => __( '代码实例 <span class="red">https://www.jitheme.com/shop/1306.html|jitheme jitheme-switch-line|买子主题</span>，没有侧留空,注意填写标准,多个连接请换行<br>注意：中间有个“|”作为分隔符的，书写规范： <span class="red">连接  |  图标  |  连接文字</span>，注意图标的用法，可以自己定义iconfont的样式', 'b2' ),
        ) );
    }
    public function Jitheme_page_tabb(){
        $Pages = new_cmb2_box( array(
            'id'           => 'b2_Jitheme_pages_options_gg',
            'object_types' => array( 'options-page' ),
            'option_key'   => 'b2_Jitheme_pages_tab99',
            'tab_group'    => 'b2_Jitheme_page_options',
            'tab_title'    => __('待添加','b2'),
            'parent_slug'  => 'b2_Jitheme_pages_tab99',
        ) );
        $Pages->add_field(array(
            'name'    => __( '待添加', 'b2' ),
            'desc'    => __( '广告代码 <span class="red">纯HTML代码</span>，没有侧留空', 'b2' ),
            'id'=>'page_log',
            'type' => 'textarea',
            'default'=>'',
        ));
    }
}
$list_Post = new Pages();
$list_Post->init();
