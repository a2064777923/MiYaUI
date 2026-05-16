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
 * The public-facing functionality of the plugin.
 *
 * @link       https://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/public
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_Public {

	/**
	 * The ID of this plugin.
	 * 
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		
		require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-build.php';
		require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
		
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
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
	    
	    
        
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 */
	public function enqueue_scripts() {

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
	    
	    
	}
	
	/**
	 * 加载自定义CSS、JS文件
	 */
	public function enqueue_custom_file() {
	    if (Feng_Custom_Options::instance()->get_switch('festivals')) {
	        // 引入日历工具
	        wp_enqueue_script( $this->plugin_name.'-lunar', FENG_CUSTOM_URL . 'static/js/lunar.js', array(), 'v1.6.7', true );
	        wp_enqueue_style( $this->plugin_name.'-festivals', FENG_CUSTOM_URL . 'static/css/festivals.css', array(), 'v0.0.2', 'all' );
	        wp_enqueue_script( $this->plugin_name.'-festivals', FENG_CUSTOM_URL . 'static/js/festivals.js', array($this->plugin_name.'-lunar'), 'v0.0.2', false );
	    }
	    
	    if (Feng_Custom_Options::instance()->get_switch('option.keyboard_open')) {
	        wp_enqueue_script( $this->plugin_name.'-activate-power', plugin_dir_url( __FILE__ ) . 'dist/activate-power-mode.js', array( 'jquery' ), 'v1', true );
	    }
	    
	    if (Feng_Custom_Options::instance()->get_switch('option.light_box_open')) {
	        // 引入灯箱 Fancybox CSS
	        wp_enqueue_style( $this->plugin_name.'-fancybox', plugin_dir_url( __FILE__ ) . 'dist/fancybox/fancybox.css', array(), 'v4.0.22', 'all' );
	        // 引入灯箱 Fancybox JS
	        wp_enqueue_script( $this->plugin_name.'-fancybox', plugin_dir_url( __FILE__ ) . 'dist/fancybox/fancybox.umd.js', array( 'jquery' ), 'v4.0.22', false );
	    }
	    
	    $build_files = Feng_Custom_Build::instance()->get_static_files();
	    wp_enqueue_style( 'feng_custom_build_css', $build_files['css'], array(), $this->version, 'all' );
	    wp_enqueue_script( 'feng_custom_build_js', $build_files['js'], array('jquery'), $this->version, false );
	    
	}

}
