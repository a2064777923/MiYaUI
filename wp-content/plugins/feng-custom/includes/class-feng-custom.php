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
 * 晨风自定义入口类
 *
 * @link       http://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 * @access   protected
	 * @var      Feng_Custom_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		if ( defined( 'FENG_CUSTOM_VERSION' ) ) {
		    $this->version = FENG_CUSTOM_VERSION;
		} else {
			$this->version = '1.2.4';
		}
		$this->plugin_name = 'feng-custom';

		add_filter( 'cron_schedules', array(&$this, 'add_cron_interval') );
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Feng_Custom_Loader. Orchestrates the hooks of the plugin.
	 * - Feng_Custom_i18n. Defines internationalization functionality.
	 * - Feng_Custom_Admin. Defines all hooks for the admin area.
	 * - Feng_Custom_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 * 
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feng-custom-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feng-custom-i18n.php';

		/**
		 * Feng Custom Base Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feng-custom-base.php';
		
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feng-custom-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-feng-custom-public.php';

		$this->loader = new Feng_Custom_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Feng_Custom_i18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Feng_Custom_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 * 
	 * @access   private
	 */
	private function define_admin_hooks() {
	    
	    if (is_admin()) {
	        $plugin_admin = new Feng_Custom_Admin( $this->get_plugin_name(), $this->get_version() );
	        
	        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	    }
	    
	}

	/**
	 * Register all of the hooks related to the public-facing functionalityof the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Feng_Custom_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_custom_file' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
		$version = maybe_unserialize(get_option('feng_custom_version'));
		if (!isset($version['plugin_version']) || (isset($version['plugin_version']) && $version['plugin_version'] !== FENG_CUSTOM_VERSION)) {
		    require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-activator.php';
		    Feng_Custom_Activator::activate();
		}
		
		require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
		$OptionsClass = Feng_Custom_Options::instance();
		if ($OptionsClass->get_switch('links')) {
		    // 开启了友情链接功能
		    require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-links.php';
		    Feng_Custom_Links::instance()->init();
		}
		
		return ;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 * 
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Feng_Custom_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * 为WP-Cron添加间隔时间
	 * @return number[]|NULL[]
	 */
	public function add_cron_interval($schedules) {
	    if (!isset($schedules['one_minute'])) {
	        $schedules['one_minute'] = array(
	            'interval' => 60,
	            'display' => __( '每分钟一次', 'feng-custom' )
	        );
	    }
	    if (!isset($schedules['two_minutes'])) {
	        $schedules['two_minutes'] = array(
	            'interval' => 120,
	            'display' => __( '每2分钟一次', 'feng-custom' )
	        );
	    }
	    if (!isset($schedules['three_minutes'])) {
	        $schedules['three_minutes'] = array(
	            'interval' => 180,
	            'display' => __( '每3分钟一次', 'feng-custom' )
	        );
	    }
	    if (!isset($schedules['five_minutes'])) {
	        $schedules['five_minutes'] = array(
	            'interval' => 300,
	            'display' => __( '每5分钟一次', 'feng-custom' )
	        );
	    }
	    return $schedules;
	}
	
	
}
