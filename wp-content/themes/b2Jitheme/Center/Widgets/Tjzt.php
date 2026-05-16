<?php
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}
class jitheme_widget_tjzt extends WP_Widget{

    //小工具slug
	protected $widget_slug = 'jitheme-widget-tjzt';

    //短代码名
	protected static $shortcode = 'jitheme_widget_tjzt';

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
			__( 'A-极主题-推荐专题', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“推荐专题侧边栏”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'team_title'=>__('极主题-推荐专题','b2'),
			'team_arg'  => '',
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'team_title',
				'id'     => 'team_title',
				'type'   => 'text'
            ),
			array(
				'name'   => __('专题ID','b2'),
				'id_key' => 'team_arg',
                'id'     => 'team_arg',
                'desc' => __('请输入专题ID，并用英文逗号隔开','b2'),
				'type'   => 'textarea'
			),
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
        $html='';
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
    	$list = explode(',',$instance['team_arg']);
        if(!empty($widget)) return $widget;
            $html .='<ul class="b2-widget-list-ul">';
            foreach ($list as $k => $v) {
                $k++;
                $t = get_term_by('id', $v, 'collection');
                if(is_wp_error( $t ) || !isset($t->name)) continue;
                $img = get_term_meta($v,'b2_tax_img',true);
                $qishu = get_term_meta($v, 'b2_tax_index', true);
                $thumb = b2_get_img(array('class'=>array('sort-config-icon b2-radius'),'src'=>$img,));
                $html.=' <li class="b2-widget-box widget-post widget-post-small">
                        <div class="b2-widget-post-thumb b2-radius">
                            <div class="b2-widget-post-title">
                                <h2>'.$t->name.'</h2>
                                <div>
                                    <span class="item-meta">第'.$qishu.'期</span>
                                    <span class="item-meta">'.$t->count.'篇文章</span>
                                    <h4 class="item-btn b2-radius">查看专题</h4>
                                </div>
                            </div>
                            <div class="b2-widget-post-thumb-img">'.$thumb.'</div>
                            
                        </div>
                        <a ref="nofollow" class="link-overlay" href="'.get_term_link((int)$v).'"></a>
                    </li>';
            }
            $html .='</ul> ';
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div id="ji-widget-zt" class="jitheme_widget_padding"><div class=" b2-widget-title  jitheme-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['team_title'] ) .$atts['after_title'];
			$widget .= '</div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .='<div class="jitheme-jb-btn"><a href="/collection" target="_blank">全部专题</a></div></div>';
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
/**
 * Register this widget with WordPress.
 */
function register_wds_widget_boilerplate() {
	register_widget( 'jitheme_widget_tjzt' );
}
add_action( 'widgets_init', 'register_wds_widget_boilerplate' );