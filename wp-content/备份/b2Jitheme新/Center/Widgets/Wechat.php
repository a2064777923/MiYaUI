<?php 
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class mini_wechat extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'jitheme-widget-wechat';

    //短代码名
	protected static $shortcode = 'mini_widget_wechat';

    //CMB2
	protected $cmb2 = null;

    //默认设置
	protected static $defaults = array();
    
    //实例
	protected $_instance = array();
    
    //cmb2项目
	protected $cmb2_fields = array();

    
	public function __construct() {
		parent::__construct(
			$this->widget_slug,
			__( 'A-极主题-微信联系群', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“自定义HTML”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
            'ads_title'=>'',
			'ads_content'=>'',
			'wechat_logo'=>'',
			'ads_ewmname'=>'',
			'ads_img'=>'',
			'show_mobile'=>0
		);
		//设置项
		$this->cmb2_fields = array(
            array(
				'name'   => __('标题','b2'),
				'id_key' => 'ads_title',
				'id'     => 'ads_title',
				'type'   => 'text'
            ),
			array(
				'name'   => __('群介绍','b2'),
				'id_key' => 'ads_content',
				'id'     => 'ads_content',
				'type'=>'textarea_code',
            ),
			array(
				'name'   => __('二维码用户名','b2'),
				'id_key' => 'ads_ewmname',
				'id'     => 'ads_ewmname',
				'type'=>'text',
            ),
			array(
				'name'   => __('二维码地址','b2'),
				'id_key' => 'ads_img',
				'id'     => 'ads_img',
				'type'=>'text',
            ),
// 			array(
// 				'name'    => __( '二维码', 'b2' ),
//                 'desc'    => __( '如果未设置，此处将显示您设置的浅色LOGO', 'b2' ),
//                 'id_key'      => 'wechat_logo',
//                 'id'      => 'wechat_logo',
//                 'type'    => 'file',
//                 'text'    => array(
//                     'add_upload_file_text' => __( '选择LOGO图片', 'b2' ),
//                 ),
// 				'options' => array(
// 					'url' => true, // Hide the text input for the url
// 				),
//                 'query_args' => array(
//                     'type' => array(
//                         'image/svg+xml',
//                         'image/gif',
//                         'image/jpeg',
//                         'image/png',
//                     ),
//                 ),
// 				'preview_size' => 'large',
// 			),
			array(
				'name'=>__('移动端是否显示','b2'),
				'id_key'=>'show_mobile',
				'id'=>'show_mobile',
				'type'=>'radio_inline',
				'options'=>array(
					1=>__('显示','b2'),
					0=>__('隐藏','b2')
				)
			)
        );
        
        //关于我们短代码
		//add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_about_us' ) );
	}
	
	/**
     * 刷新缓存
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function flush_widget_cache() {
		wp_cache_delete( $this->id, 'widget' );
	}

    /**
     * 显示小工具
     *
     * @param [type] $args
     * @param [type] $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function widget( $args, $instance ) {
	
		echo self::get_widget( array(
			'args'     => $args,
			'instance' => $instance,
			'cache_id' => $this->id,
		) );
	}
    
    /**
     * 显示小工具短代码内容
     *
     * @param [type] $atts
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public static function get_widget( $atts ) {

        $atts['args']['cache_id'] = $atts['cache_id'];

		//获取设置项
		$instance = shortcode_atts(
			self::$defaults,
			! empty( $atts['instance'] ) ? (array) $atts['instance'] : array(),
			self::$shortcode
		);

		$atts = shortcode_atts(
			array(
				'instance'      => array(),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'cache_id'      => '',
				'flush_cache'   => isset( $_GET['delete-trans'] ), 
			),
			isset( $atts['args'] ) ? (array) $atts['args'] : array(),
			self::$shortcode
        );

		// if ( empty( $atts['cache_id'] ) ) {
		// 	$atts['cache_id'] = md5( serialize( $atts ) );
        // }

		// if(B2_OPEN_CACHE){
		// 	$widget = ! $atts['flush_cache']
		// 	? wp_cache_get( $atts['cache_id'], 'widget' )
        //     : '';
		// }else{
		// 	$widget = '';
		// }

		// if(!empty($widget)) return $widget;
// 		$logo = $instance['wechat_logo'];
// 		$logo_ = $logo;
// 		$_logo = str_replace(array('https://','http://'),'',$logo);
// 		if(is_numeric($_logo)){
// 			$logo = wp_get_attachment_url($_logo);
// 		}else{
// 			$logo = $logo_;
// 		}
		$html = '
			<div class="html-widget">
				<div class="html-widget-content">
					'.$instance['ads_content'].'
				</div>
			</div>
		';
		$groups = explode("\n", $instance['ads_content']);
        $output = '<ul>' . "\n";
        
        for ($i = 0; $i < count($groups); $i++) {
            if ($i % 3 == 0 && $i != 0) {
                $output .= '</ul>' . "\n" . '<ul>' . "\n";
            }
            $line = '<li class="wg"><i class="count"><i class="txt">' . ($i + 1) . '</i></i>' . $groups[$i] . '</li>' . "\n";
            $output .= $line;
        }
        
        $output .= '</ul>' . "\n";
		
		
		
        $html = '<div class="widget-wrap">
    <div class="widget-content">
        <div class="wechat-group">
            '.$output.'
        </div>
        <div class="add-admin">
            <div class="add-wrap">
                <div class="add-avatar">
                    <i class="avatar"><i class="thumb " style="background-image:url('.$instance['ads_img'].')"></i></i></div>
                <div class="add-thumb">
                    <i class="thumb " style="background-image:url('.$instance['ads_img'].')"></i></div>
                <h3 class="add-title">添加管理员 '.$instance['ads_ewmname'].'</h3>
                <h5>微信号: 扫码添加</h5>
                <h5>专业好学的经验交流群</h5>
            </div>
        </div>
    </div>
</div>';
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="jitheme_widget_padding"><div class="b2-widget-title  jitheme-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['ads_title'] ) .$atts['after_title'];
			$widget .= '</div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			
			// if(B2_OPEN_CACHE){
			// 	wp_cache_set( $atts['cache_id'], $widget, 'widget', WEEK_IN_SECONDS );
			// }
			
		}

		return $widget;
	}
    
    /**
     * 更新小工具
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();
		$sanitized = $this->cmb2( true )->get_sanitized_values( $new_instance );
		return $sanitized;
	}
    
    /**
     * 小工具表单
     *
     * @param array $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function form( $instance ) {
		// 如果没有设置项使用默认
		$this->_instance = wp_parse_args( (array) $instance, self::$defaults );
		$cmb2 = $this->cmb2();
		$cmb2->object_id( $this->option_name );
		\CMB2_hookup::enqueue_cmb_css();
		\CMB2_hookup::enqueue_cmb_js();
		$cmb2->show_form();
	}
    
    /**
     * 创建实例
     *
     * @param bool $saving
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function cmb2( $saving = false ) {

		$cmb2 = new \CMB2( array(
			'id'      => $this->option_name .'_box', 
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->option_name )
			),
		), $this->option_name );
		foreach ( $this->cmb2_fields as $field ) {
			if ( ! $saving ) {
				$field['id'] = $this->get_field_name( $field['id'] );
			}
			$field['default_cb'] = array( $this, 'default_cb' );
			$cmb2->add_field( $field );
		}
		return $cmb2;
	}
	/**
	 * 设置默认项
	 *
	 * @param  array      $field_args CMB2的设置项
	 * @param  CMB2_Field $field CMB2 选项对象
	 *
	 * @return mixed      Field value.
	 */
	public function default_cb( $field_args, $field ) {
		return isset( $this->_instance[ $field->args( 'id_key' ) ] )
			? $this->_instance[ $field->args( 'id_key' ) ]
			: null;
	}
}

function mini_widget_wechat_add() {
	register_widget( 'mini_wechat' );
}
add_action( 'widgets_init', 'mini_widget_wechat_add' );