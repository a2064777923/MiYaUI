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
 * 管理类
 *
 * @link       http://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/admin
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_Admin extends Feng_Custom_Base{

	/**
	 * The ID of this plugin.
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	private $screens;
	
	/**
	 * Initialize the class and set its properties.
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
	    $this->plugin_name = $plugin_name;
	    $this->version = $version;
	    
	    // 添加后台管理菜单
	    add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	    
	    // 添加更新RSS的ajax请求
	    add_action('wp_ajax_feng_custom_refresh_rss', array(&$this, 'ajax_handler_refresh_rss'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Feng_Custom_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Feng_Custom_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	    wp_enqueue_style( $this->plugin_name.'-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), FENG_CUSTOM_VERSION, 'all' );
	    
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Feng_Custom_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	    // 引入灯箱 Fancybox JS
	    wp_enqueue_script( $this->plugin_name.'-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), FENG_CUSTOM_VERSION, false );
	    
	    if ($hook === 'links_page_feng-custom-links-rss') {
	        $title_nonce = wp_create_nonce('feng-custom-links-rss');
	        wp_localize_script($this->plugin_name.'-admin', 'feng_custom_ajax_obj', array(
	            'ajax_url' => admin_url('admin-ajax.php'),
	            'nonce' => $title_nonce
	        ));
	    }
	    
	}
	
	/**
	 * 初始化仪表盘菜单
	 */
	public function admin_menu() {
	    // 晨风自定义
	    add_submenu_page('themes.php', __('晨风自定义', 'feng-custom'), __('晨风自定义', 'feng-custom'), 'edit_theme_options','feng-custom' , array(&$this,'admin_menu_option'));
	    if ($this->switch('links')) {
	        add_submenu_page('link-manager.php', __('链接RSS聚合', 'feng-custom'), __('RSS聚合', 'feng-custom'), 'manage_links', 'feng-custom-links-rss', array(&$this, 'admin_menu_links_rss'));
	    }
	}
	
	/**
	 * 显示管理页面
	 */
	public function admin_menu_option() {
	    // 请求
	    $module_name = sanitize_key(isset($_GET['module'])? $_GET['module'] : '');

	    $screens = [
	        'option' => [
	            'tab_title' => '效果配置',
	            'page_type' => 'options',
	        ],
	        'festivals' => [
	            'tab_title' => '节日氛围',
	            'page_type' => 'options',
	        ],
	        'snowflake' => [
	            'tab_title' => '雪花飘落',
	            'page_type' => 'options',
	        ],
	        'links' => [
	            'tab_title' => '链接&RSS',
	            'page_type' => 'options',
	        ],
	        'about' => [
	            'tab_title' => '关于',
	            'page_type' => 'page',
	        ]
	    ];
	    
	    $menu = [
	        'title' => __('晨风自定义', 'feng-custom'),
	    ];
	    
	    // 默认显示 option
	    $module_name = ($module_name && array_key_exists($module_name, $screens)) ? $module_name : 'option';
	    $menu['title'] = $screens[$module_name]['tab_title'];
	    switch ($screens[$module_name]['page_type']) {
	        case 'options':
	            // 引入配置模板
	            require_once FENG_CUSTOM_PATH . 'admin/partials/options.php';
	            break;
	        default:
	            // 获取模板
	            require_once FENG_CUSTOM_PATH . 'admin/partials/' . $module_name . '.php';
	        break;
	    }
	}
	
	/**
	 * 链接RSS聚合后台页面
	 */
	public function admin_menu_links_rss() {
	    // 请求
	    $module_name = sanitize_key(isset($_GET['module'])? $_GET['module'] : 'home');
	    $screens = [
	        'home' => [
	            'tab_title' => 'RSS聚合',
	            'page_type' => 'page',
	            'template' => 'links-rss',
	        ],
	        'log' => [
	            'tab_title' => '任务日志',
	            'page_type' => 'page',
	            'template' => 'links-rss-log',
	        ],
	    ];
	    $menu = [
	        'title' => __('链接RSS聚合'),
	    ];
	    if (!$this->switch('links.rss_open')) {
	        echo '<h4 style="color: red;">尚未开启RSS聚合功能</h4><p>前往 ☞ <a href="./themes.php?page=feng-custom&action=links"><b>链接&RSS</b></a> 页面开启该功能</p><hr />';;
	        return ;
	    }
	    require_once FENG_CUSTOM_PATH . 'admin/partials/' . $screens[$module_name]['template'] . '.php';
	}
	
	/**
	 * 处理Ajax请求，更新RSS
	 */
	public function ajax_handler_refresh_rss() {
	    if (!isset($_POST['do']) && !isset($_POST['_ajax_nonce']) && !wp_verify_nonce( $_POST['_ajax_nonce'], "feng-custom-links-rss")) {
	        wp_send_json_error(['error' => '提交数据有误，刷新后重试！']);
	    }
	    $do = isset($_POST['do']) ? $_POST['do'] : null;
	    $link_id = isset($_POST['link_id']) ? $_POST['link_id'] : 0;
	    
	    require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-links.php';
	    $LinkClass = Feng_Custom_Links::instance();
	    
	    if ($link_id) {
	        // 检查RSS
	        $feed_data = $LinkClass->refresh_rss($link_id);
	        $check_data = $feed_data['check_data'][$link_id];
	        $return_data = [
	            'message' => $check_data['check_feed_error'],
	            'update_time' => wp_date($this->get_date_format(), $check_data['check_feed_time'])
	        ];
	        
	        if ($do === 'order') {
	            $LinkClass->set_feed_items_cache();
	        }
	        
	        if ($check_data['check_feed_error']) {
	            wp_send_json_error($return_data);
	        }else {
	            wp_send_json_success($return_data);
	        }
	    }
	    
	    if ($do === 'order') {
	        // 更新排序
	        $result = $LinkClass->set_feed_items_cache();
	        
	        if ($result) {
	            wp_send_json_success(['message' => 'OK']);
	        }else {
	            wp_send_json_error(['message' => $LinkClass->get_error()]);
	        }
	    }
	}
}
