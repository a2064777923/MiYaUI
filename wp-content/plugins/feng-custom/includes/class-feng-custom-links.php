<?php
// +----------------------------------------------------------------------
// | 晨风自定义 [ 用最简单的代码，实现最简单的事情。 ]
// +----------------------------------------------------------------------
// | Home Page: https://feng.pub/feng-custom
// +----------------------------------------------------------------------
// | Gitee: https://gitee.com/ouros/feng-custom
// +----------------------------------------------------------------------
// | WordPress: https://cn.wordpress.org/plugins/feng-custom
// +----------------------------------------------------------------------
// | Author: 阿锋 <mypen@163.com>
// +----------------------------------------------------------------------
/**
 * 链接类
 *
 * @author 阿锋
 *
 */
class Feng_Custom_Links extends Feng_Custom_Base {
    
    /**
     * 链接&RSS配置数据
     * @var array
     */
    private  $config;
    
    /**
     * SimplePie数据缓存key
     * @var string
     */
    private $feed_data_cache_key = 'feed_data';
    
    /**
     * 排序后的RSS聚合列表缓存key
     * @var string
     */
    private $feed_items_cache_key = 'feed_items';
    
    /**
     * SimplePie缓存目录
     * @var string
     */
    private $simplepie_cache_path = WP_CONTENT_DIR . '/cache/feng-custom/SimplePie';
    
    /**
     * RSS聚合页面显示数量
     * @var integer
     */
    private $rss_page_limit = 50;
    
    /**
     * 单例
     * @var Feng_Custom_Links
     */
    static private $instance;
    
    /**
     * 单例
     * @return Feng_Custom_Links
     */
    static public function instance($config = []) {
        if(!self::$instance) self::$instance = new self($config);
        return self::$instance;
    }
    
    /**
     * 初始化
     */
    public function __construct($config = []) {
        // 配置数据
        if (empty($config)) {
            // 获取配置数据
            require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
            $this->config = Feng_Custom_Options::instance()->get_options_data('links');
        }else {
            $this->set_config($config);
        }
    }
    
    /**
     * 初始化
     */
    public function init() {
        // 激活WP链接功能
        if(has_filter('pre_option_link_manager_enabled') === false) add_filter('pre_option_link_manager_enabled', '__return_true');
        
        $this->public_init();
        
        if(is_admin()){
            $this->admin_init();
        }
        
        if ($this->config['rss_cron_open']) {
            $this->rss_cron();
        }else {
            $this->rss_cron_unset();
        }
    }
    
    /**
     *  舒适化
     */
    private function public_init() {
        // 页面添加内容
        add_filter('the_content', function($content){
            if (is_page()) {
                $config = $this->config;
                global $post;
                if ($config['open'] && (int)$post->ID === (int)$config['page_id']) {
                    // 内容后添加链接列表
                    $content .= $this->links_page_content();
                }else if ($config['rss_open'] && (int)$post->ID === (int)$config['rss_page_id']) {
                    // 内容后添加RSS聚合列表
                    $content .= $this->rss_page_content();
                }
            }
            return $content;
        });
    }
    
    /**
     * 初始化后台管理
     */
    private function admin_init() {
        // 页面管理页面添加页面状态
        add_filter( 'display_post_states', function($states){
            $config = $this->config;
            global $post;
            if (!empty($post->ID)) {
                if ($config['open'] && (int)$config['page_id'] === (int)$post->ID) {
                    $states['fct_links_page'] = esc_html__('链接页面', 'feng-custom');
                }
                if ($config['rss_open'] && (int)$config['rss_page_id'] === (int)$post->ID) {
                    $states['fct_links_rss_page'] = esc_html__('RSS聚合（链接）页面', 'feng-custom');
                }
            }
            return $states;
        });
    }
    
    /**
     * 链接页面内容
     * @return string
     */
    private function links_page_content() {
        $config = $this->config;
        // 返回页面的内容
        $content = "";
        // 要显示的链接分类
        $show_link_category = $config['show_link_category'];
        // 分类排序方式
        $link_category_order = $config['link_category_order'];
        // 定义要显示分类的数据变量
        $show_category_data = [];
        // 获取要显示的分类
        if (!$show_link_category) {
            $content .= "<h5 class=\"wp-block-heading\" style=\"color:red;\">" . __('未选择要显示的连接分类') . "</h5>";
            $content .= "<p>" . sprintf(__('请先移步至「<a href="%s">仪表盘 -> 外观 -> 晨风自定义 -> 链接</a>」选择要显示的链接分类。'), $this->get_options_url('links')) . "<p>";
        }else {
            // 获取分类信息
            $show_category_data = $this->get_category([
                'include' => $show_link_category,
            ], $link_category_order);
        }
        
        // 链接页面HTML
        if ($show_category_data) {
            $content .= $this->links_page_content_html($show_category_data);
        }
        
        // 有管理权限的用户，显示分类
        if (current_user_can('manage_links')){
            $admin_show_category_data = $this->get_category([
                'exclude' => $show_link_category,
            ], $link_category_order);
            if ($admin_show_category_data) {
                $content .= '<div style="border: 3px solid red; border-left: 1px solid red; border-right: 1px solid red; border-radius: 10px; padding-top: 15px;"><h5 class="wp-block-heading" style="color: red;">该内容仅管理员可见</h5>';
                $content .= $this->links_page_content_html($admin_show_category_data);
                $content .= '</div>';
            }
        }
        
        // 支持信息HTML
        $content .= $this->get_prowered_content_html('links');
        
        return $content;
    }
    
    /**
     * 链接列表HTML
     * @param array $category
     */
    private function links_page_content_html($category) {
        $config = $this->config;
        $rand_text = $config['link_card_order'] == 'rand' ? '<span>' . __('（随机排序）' . '</span>', 'feng-custom') : '';
        
        $html_content = '<div class="fct-links">';
        foreach ($category as $key => $category) {
            $html_content .= '<div class="fct-links-category">';
            $html_content .= '<div class="title"><h2>' . $category->name . '</h2>' . $rand_text .'</div>';
            $html_content .= '</div>';
            
            $links = $this->get_links([
                'category' => $category->term_id,
            ], $config['link_card_order']);
            
            $html_content .= '<div class="fct-links-list">';
            foreach ($links as $k => $link) {
                $html_content .= '<div class="fct-link-card" id="link-'.$link->link_id.'">';
                $html_content .= '<div class="img"><a target="'.$link->link_target.'" href="'.$link->link_url.'"><img src="'.$link->link_image.'" /></a></div>';
                $html_content .= '<div class="info">';
                $html_content .= '  <div class="title"><a target="'.$link->link_target.'" href="'.$link->link_url.'">'.$link->link_name.'</a></div>';
                $html_content .= '  <div class="desc">'.$link->link_description.'</div>';
                
                $notes_start = strrpos($link->link_notes,"{show}");
                $notes_end = strrpos($link->link_notes,"{/show}");
                if ($notes_start !== false && $notes_end !== false) {
                    $notes_start = $notes_start + 6;
                    $notes_length = $notes_end - $notes_start;
                    $html_content .= '<div class="notes">'. substr($link->link_notes, $notes_start, $notes_length) . '</div>';
                }
                $html_content .= '</div>';
                $html_content .= '</div>';
            }
            $html_content .= '</div>';
        }
        $html_content .= '</div>';
        
        return $html_content;
    }
    
    /**
     * RSS聚合页面
     * @return string
     */
    private function rss_page_content() {
        $config = $this->config;
        $content = "";
        
        if (!$config['show_rss_category']) {
            $content .= "<h5 class=\"wp-block-heading\" style=\"color:red;\">" . __('未选择RSS聚合来源的链接分类') . "</h5>";
            $content .= "<p>" . sprintf(__('请先移步至「<a href="%s">仪表盘 -> 外观 -> 晨风自定义 -> 链接</a>」选择RSS聚合来源的链接分类。'), $this->get_options_url('links')) . "<p>";
        }
        
        // 读取RSS聚合的缓存数据
        $feed_items = $this->get_feed_items_cache();
        if ($feed_items){
            // 处理缓存数据
            for ($i = 0; $i < $this->rss_page_limit; $i++) {
                $item = isset($feed_items[$i]) ? $feed_items[$i] : [];
                if (empty($item)) {
                    break;
                }
                $content .= '<div class="link-rss-item">';
                $content .= '<div class="title"><a href="'. esc_attr($item['link']) .'" target="_blank">'.esc_attr($item['title']).'</a></div>';
                $content .= '<div class="meta"><span>'. esc_attr($item['publish_date']) .'</span>';
                $content .= '<span>「<a href="'.esc_attr($item['link_url']).'" target="_blank" class="">'.esc_attr($item['link_name']).'</a>';
                if ($item['author']) {
                    $content .= '<i>' . sprintf(__('（作者：%s）', 'feng-custom'), esc_attr($item['author'])) . '</i>';
                }
                $content .= '」</span></div>';
                $content .= '<div class="desc">'. wp_trim_words(sanitize_textarea_field($item['description']), 60, '...').'</div></div>';
            }
        }else {
            // 没有feed项目数据
            $content .= '<h5 class="wp-block-heading" style="color: red;">没有RSS聚合数据</h5>';
        }
        
        // 支持信息HTML
        $content .= $this->get_prowered_content_html('rss');
        
        return $content;
    }
    
    /**
     * 页面显示支持信息
     * @param string $type links|rss
     * @return string
     */
    private function get_prowered_content_html($type = 'links') {
        $config = $this->config;
        if ($config['show_prowered']) {
            if ($type == 'links') {
                return '<div class="fct-prowered">Links Prowered by <a target="_blank" href="https://feng.pub/feng-custom">Feng Custom</a></div>';
            }
            if ($type == 'rss') {
                return '<div class="fct-prowered">RSS Prowered by <a target="_blank" href="https://feng.pub/feng-custom">Feng Custom</a></div>';
            }
        }else {
            return '';
        }
    }
    
    /**
     * 设置配置数据
     * @param array $config
     */
    public function set_config($config) {
        $this->config = $config;
        $this->rss_page_limit = $config['rss_page_limit'] ? (int)$config['rss_page_limit'] : $this->rss_page_limit;
    }
    
    /**
     * 获取配置数据
     * @return array|mixed|string
     */
    public function get_config() {
        return $this->config;
    }
    
    /**
     * 获取链接分类数据
     *
     * @param array $param 分类参数
     *              name__like  => 链接名称
     *              include     => 包含分类ID
     *              exclude     => 排除分类ID
     *              orderby     => 排序方式
     *              order       => 排序
     * @param string $order_type 排序方式：rand|name-asc|name-desc|id-asc|id-desc
     *
     * @return array
     */
    private function get_category($param = [], $order_type = null) {
        switch ($order_type) {
            case 'rand':
                $param['orderby'] = 'rand';
                break;
            case 'name-asc':
                $param['orderby'] = 'name';
                $param['order'] = 'ASC';
                break;
            case 'name-desc':
                $param['orderby'] = 'name';
                $param['order'] = 'DESC';
                break;
            case 'id-asc':
                $param['orderby'] = 'id';
                $param['order'] = 'ASC';
                break;
            case 'id-desc':
                $param['orderby'] = 'id';
                $param['order'] = 'DESC';
                break;
            default:
                ;
            break;
        }
        
        $param = wp_parse_args($param, ['taxonomy' => 'link_category']);
        $terms = get_terms( $param );
        return $terms;
    }
    
    /**
     * 获取链接列表数据
     * @param array $param 获取链接的参数
     *                  category    => 分类
     *                  orderby     => 排序方式
     *                  order       => 排序
     * @param string $order_type 排序方式：rand|name-asc|name-desc|id-asc|id-desc|rating-asc
     * @return array
     */
    public function get_links($param = [], $order_type = null) {
        switch ($order_type) {
            case 'rand':
                $param['orderby'] = 'rand';
                break;
            case 'name-asc':
                $param['orderby'] = 'name';
                $param['order'] = 'ASC';
                break;
            case 'name-desc':
                $param['orderby'] = 'name';
                $param['order'] = 'DESC';
                break;
            case 'id-asc':
                $param['orderby'] = 'id';
                $param['order'] = 'ASC';
                break;
            case 'id-desc':
                $param['orderby'] = 'id';
                $param['order'] = 'DESC';
                break;
            case 'rating-asc':
                $param['orderby'] = 'rating';
                $param['order'] = 'ASC';
                break;
            default:
                ;
            break;
        }
        $bookmarks = get_bookmarks($param);
        $bookmark_data = [];
        foreach ($bookmarks as $bookmark) {
            $bookmark_data[$bookmark->link_id] = $bookmark;
        }
        return $bookmark_data;
    }
    
    /**
     * 获取链接数据
     * @param int $link_id
     * @return object
     */
    public function get_link($link_id) {
        return get_bookmark($link_id);
    }
    
    /**
     * 检查RSS源
     * @param int $link_id 链接ID
     * @return array 返回feed_data缓存数据
     */
    public function refresh_rss($link_id) {
        $feed_data = $this->get_feed_data_cache();
        // 获取链接信息
        $link_obj = $this->get_link($link_id);
        $link_rss = $link_obj->link_rss;
        if (!$link_rss) {
            return $feed_data;
        }
        $feed = $this->fetch_feed($link_rss);
        // 获取到的数量
        $maxitems = $feed->get_item_quantity();
        $feed_items = $feed->get_items();
        $feed_error = $feed->error();
        $feed_items_data = [];
        if ($maxitems > 0) {
            foreach ($feed_items as $item) {
                $author = $item->get_author();
                if ($author) {
                    $author_name = $author->name;
                }else {
                    $author_name = '';
                }
                $update_time = $item->get_date('U');
                $feed_items_data[] = [
                    'title' => $item->get_title(),
                    'description' => $item->get_description(),
                    'content' => $item->get_content(),
                    'thumbnail' => $item->get_thumbnail(),
                    'author' => $author_name,
                    'copyright' => $item->get_copyright(),
                    'update_time' => $update_time,
                    'publish_date' => wp_date($this->get_date_format(), $update_time),
                    'updated_date' => wp_date($this->get_date_format(), $item->get_updated_date('U')),
                    'permalink' => esc_attr($item->get_permalink()),
                    'link' => esc_attr($item->get_link()),
                    'link_id' => $link_id,
                    'link_name' => $link_obj->link_name,
                    'link_image' => $link_obj->link_image,
                    'link_url' => $link_obj->link_url,
                ];
            }
        }
        
        
        $feed_data['links_data'][$link_id] = [
            'id' => $link_obj->link_id,
            'name' => $link_obj->link_name,
            'image' => $link_obj->link_image,
            'url' => $link_obj->link_url,
            'rss' => $link_rss,
        ];
        $feed_data['check_data'][$link_id] = [
            'check_feed_error' => $feed_error,
            'check_feed_time' => time(),
        ];
        $feed_data['items_data'][$link_id] = $feed_items_data;
        
        $this->set_feed_data_cache($feed_data);
        
        // 返回数据
        return $feed_data;
    }
    
    /**
     * 设置检查RSS的列表缓存
     * @param array $feed_data
     */
    private function set_feed_data_cache($feed_data) {
        require_once FENG_CUSTOM_PATH . '/includes/class-feng-custom-cache.php';
        // 写入缓存数据
        Feng_Custom_Cache::instance()->set(
            $this->feed_data_cache_key,
            $feed_data,
            'links', 0);
    }
    
    /**
     * 获取检查RSS的列表缓存
     * @return array|NULL
     */
    public function get_feed_data_cache() {
        require_once FENG_CUSTOM_PATH . '/includes/class-feng-custom-cache.php';
        // 获取缓存数据
        $feed_data =  Feng_Custom_Cache::instance()->get($this->feed_data_cache_key, 'links');
        return $feed_data ? $feed_data : [];
    }
    
    /**
     * 设置缓存，依据时间重新排序
     * @return boolean
     */
    public function set_feed_items_cache() {
        // 获取检查RSS的缓存数据
        $feed_data = $this->get_feed_data_cache();
        if (empty($feed_data)) {
            $this->set_error('没有RSS缓存数据，请前往后台检查更新RSS源。');
            return false;
        }
        $items = [];
        foreach ($feed_data['items_data'] as $key => $value) {
            $items = array_merge($items, $value);
        }
        // 排序
        $items = wp_list_sort($items, 'update_time', 'DESC');
        Feng_Custom_Cache::instance()->set($this->feed_items_cache_key, $items, 'links', 0);
        
        $feed_data['items_update_time'] = time();
        $this->set_feed_data_cache($feed_data);
        return true;
    }
    
    /**
     * 获取排序后的RSS项目列表数据
     * @return array|NULL
     */
    public function get_feed_items_cache() {
        require_once FENG_CUSTOM_PATH . '/includes/class-feng-custom-cache.php';
        // 获取缓存数据
        $feed_items_data =  Feng_Custom_Cache::instance()->get($this->feed_items_cache_key, 'links');
        return $feed_items_data ? $feed_items_data : [];
    }
    
    /**
     * 更新检查RSS
     */
    public function do_rss_refresh() {
        $config = $this->config;
        $do_rss_refresh_time = time();
        $feed_data = $this->get_feed_data_cache();
        // 自动更新的状态
        $auto_check_status = isset($feed_data['auto_check_status']) ? $feed_data['auto_check_status'] : 0;
        // 上次自动更新的时间
        $auto_refresh_time = isset($feed_data['auto_refresh_time']) ? $feed_data['auto_refresh_time'] : time();
        
        if ($auto_check_status && (time() - $auto_refresh_time) < 600 ) {
            // 任务进行中，未超过10分钟
            return [
                'status' => 0,
                'message' => __('任务进行中，未超过10分钟', 'feng-custom'),
            ];
        }else {
            $feed_data['auto_check_status'] = 1;
            $this->set_feed_data_cache($feed_data);
        }
        
        // 自动更新序号（链接ID）
        $auto_refresh_index = isset($feed_data['auto_refresh_index']) ? $feed_data['auto_refresh_index'] : 0;
        
        $need_do_refresh = false;
        $refresh_next_rss = false;
        
        if (!$config['show_rss_category']) {
            return [
                'status' => 0,
                'message' => __('未设置RSS聚合来源的链接分类', 'feng-custom'),
            ];
        }
        
        // 获取链接
        $links_data = $this->get_links([
            'category' => $config['show_rss_category'],
            'orderby' => 'id',
            'order' => 'ASC'
        ]);
        
        if (!$links_data) {
            return [
                'status' => 0,
                'message' => __('未获取到链接数据', 'feng-custom'),
            ];
        }
        $i = 0;
        $message_str = '';
        foreach ($links_data as $index => $link) {
            // $index 为链接ID
            $i++; // 循环次数
            if ($i >= count($links_data)) {
                $auto_refresh_index = 0;
            }
            
            if (!$link->link_rss) {
                // 未设置RSS地址，跳出本次循环
                continue;
            }
            
            if ($auto_refresh_index) {
                if ($index === $auto_refresh_index) {
                    $refresh_next_rss = true;
                    // 跳出本次循环
                    continue;
                }
            }else {
                // 需要刷新
                $need_do_refresh = true;
            }
            
            if ($need_do_refresh || $refresh_next_rss) {
                if ($i >= count($links_data)) {
                    $auto_refresh_index = 0;
                }else {
                    $auto_refresh_index = $index;
                }
                
                $check_data = isset($feed_data['check_data']) ? $feed_data['check_data'] : [];
                if ($check_data) {
                    $check = isset($check_data[$index]) ? $check_data[$index] : [];
                    if (isset($check['check_feed_time']) && (time() - $check['check_feed_time']) < $config['rss_cache_expire'] ) {
                        // RSS缓存未过期，终止循环
                        $message_str = 'RSS缓存未过期，暂时不需要检查。';
                        break;
                    }
                }
                
                // 检查RSS源
                $feed_data = $this->refresh_rss($index);
                if (isset($feed_data['items_data'][$index])) {
                    $message_str = '检查RSS成功。';
                    // 需要排序
                    $this->set_feed_items_cache();
                }else {
                    if (isset($feed_data['check_data'][$index]['check_feed_error']) && $feed_data['check_data'][$index]['check_feed_error']) {
                        $message_str = "检查RSS出错：\r\n" . $feed_data['check_data'][$index]['check_feed_error'];
                    }else {
                        $message_str = "检查RSS完成，但未获取到条目。";
                    }
                }
                // 终止循环
                break;
            }
        }
        
        $feed_data['auto_check_status'] = 0;
        $feed_data['auto_refresh_index'] = $auto_refresh_index;
        $feed_data['auto_refresh_time'] = $do_rss_refresh_time;
        
        $this->set_feed_data_cache($feed_data);
        
        return [
            'status' => 1,
            'message' => "<p>" . sprintf(__('（Link ID: %s） %s ［ %s ］', 'feng-custom'), $link->link_id, $link->link_name, $link->link_rss) . "</p>\r\n<p>" . $message_str . "</p>\r\n\r\n",
            'data' => [
                'link' => [
                    'id' => $link->link_id,
                    'name' => $link->link_name,
                    'url' => $link->link_url,
                    'rss' => $link->link_rss,
                ],
                'auto_refresh_index' => $feed_data['auto_refresh_index'], 
                'auto_refresh_time' => $feed_data['auto_refresh_time'],
                'need_do_refresh' => $need_do_refresh,
                'refresh_next_rss' => $refresh_next_rss
            ]
        ];
    }
    
    /**
     * Builds SimplePie object based on RSS or Atom feed from URL.
     * Copy and rewrite source code from wp-includes/feed.php
     * @param string|array $url
     * @return SimplePie
     */
    private function fetch_feed( $url ) {
        $config = $this->config;
        if ( ! class_exists( 'SimplePie', false ) ) {
            require_once ABSPATH . WPINC . '/class-simplepie.php';
        }
        
        require_once ABSPATH . WPINC . '/class-wp-feed-cache-transient.php';
        require_once ABSPATH . WPINC . '/class-wp-simplepie-file.php';
        require_once ABSPATH . WPINC . '/class-wp-simplepie-sanitize-kses.php';
        
        $feed = new SimplePie();
        
        $feed->set_sanitize_class( 'WP_SimplePie_Sanitize_KSES' );
        /*
         * We must manually overwrite $feed->sanitize because SimplePie's constructor
         * sets it before we have a chance to set the sanitization class.
         */
        $feed->sanitize = new WP_SimplePie_Sanitize_KSES();
        
        // 判断目录是否存在
        if (!is_dir($this->simplepie_cache_path)) {
            // 递归创建目录
            wp_mkdir_p($this->simplepie_cache_path);
        }
        
        // Register the cache handler using the recommended method for SimplePie 1.3 or later.
        SimplePie_Cache::register( 'wp_transient', 'WP_Feed_Cache_Transient' );
        $feed->set_cache_location( $this->simplepie_cache_path );
        
        $feed->set_file_class( 'WP_SimplePie_File' );
        
        $feed->set_feed_url( $url );
        
        if ($config['rss_cache_expire']) {
            /** This filter is documented in wp-includes/class-wp-feed-cache-transient.php */
            $feed->set_cache_duration( (int)$config['rss_cache_expire'] );
        }else {
            // 不启用缓存
            $feed->enable_cache(false);
        }
        
        $feed->init();
        $feed->set_output_encoding( get_option( 'blog_charset' ) );
        
        return $feed;
    }
    
    /**
     * 定时执行RSS聚合
     */
    public function rss_cron() {
        $linksClass = Feng_Custom_Links::instance();
        add_action('fct_links_rss_cron_hook', array($linksClass, 'rss_cron_callback'));
        if(!wp_next_scheduled('fct_links_rss_cron_hook')){
            wp_schedule_event(time(), 'two_minutes', 'fct_links_rss_cron_hook');
        }
    }
    
    /**
     * rss_cron_callback
     */
    public function rss_cron_callback() {
        $config = $this->config;
        if ($config['rss_cron_open']) {
            $log_date = wp_date('Y-m-d H:i:s', time());
            // 更新RSS缓存
            $result = $this->do_rss_refresh();
            $result_str = "<h5>[WP-Cron Check RSS] " . $log_date . "</h5>\r\n" . $result['message'] . "<hr />\r\n";
            
            require_once FENG_CUSTOM_PATH . '/includes/class-feng-custom-cache.php';
            $CacheClass = Feng_Custom_Cache::instance();
            $log = $CacheClass->get('rss_cron_log', 'links');
            if (strlen($log) > 10000) {
                $CacheClass->set('rss_cron_log_bak', $log, 'links', 0);
                $log = $result_str;
            }else {
                $log = $result_str . $log;
            }
            $CacheClass->set('rss_cron_log', $log, 'links', 0);
        }
    }
    
    /**
     * 删除定时执行RSS聚合任务
     */
    public function rss_cron_unset() {
        $timestamp = wp_next_scheduled( 'fct_links_rss_cron_hook' );
        if ($timestamp) {
            wp_unschedule_event( $timestamp, 'fct_links_rss_cron_hook' );
        }
    }
    
}
