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
 * Fired during plugin activation
 *
 * @link       http://feng.pub
 *
 * @package    Feng_Custom
 * @subpackage Feng_Custom/includes
 * @author     阿锋 <mypen@163.com>
 */
class Feng_Custom_Activator {
    
	/**
	 * 激活插件
	 */
	public static function activate() {
	    // 获取版本号 v1.2.0记录版本号的方式 
	    $before_version = maybe_unserialize(get_option('fct_custom_version'));
	    if (is_array($before_version)) {
	        $version = [
	            'plugin_version' => isset($before_version['v']) ? $before_version['v'] : '',
	            'build_css_version' => isset($before_version['css_version']) ? $before_version['css_version'] : '',
	            'build_js_version' => isset($before_version['js_version']) ? $before_version['js_version'] : '',
	        ];
	    }else {
	        $version = get_option('feng_custom_version');
	    }
	    
	    // 对比版本号
	    if ($version && isset($version['plugin_version']) && $version['plugin_version'] !== FENG_CUSTOM_VERSION) {
	        // 版本号不同需要重构配置数据及缓存文件
	        if ($version < '1.2.0') {
	            /**
	             * 清空缓存
	             */
	            require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
	            Feng_Custom_Cache::instance()->clear();
	            
	            /**
	             * 更新配置数据
	             */
	            require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-options.php';
	            $OptionsClass = Feng_Custom_Options::instance();
	            // 获取配置数据
	            $save_options = $OptionsClass->get_options_data();
	            // 循环更新配置数据
	            foreach ($save_options as $options_name => $options_data) {
	                switch ($options_name) {
	                    case 'option':
	                        // 获取数据库存储的配置
	                        $db_options = $OptionsClass->get_option('option');
	                        // 运行天数
	                        $options_data['run_open'] = isset($db_options['run_open']) ? $db_options['run_open'] : ( get_option('fct_xianshi_tianshu') ? 1 : $options_data['run_open'] );
	                        $options_data['run_start_time'] = isset($db_options['run_start_time']) ? $db_options['run_start_time'] : ( get_option('fct_kaitong_shijian') ? get_option('fct_kaitong_shijian') : $options_data['run_start_time'] );
	                        $options_data['run_binding_element'] = isset($db_options['run_binding_element']) ? $db_options['run_binding_element'] : ( get_option('fct_kaitong_bangding') ? get_option('fct_kaitong_bangding') : $options_data['run_binding_element'] );
	                        $options_data['run_show_text'] =  isset($db_options['run_show_text']) ? $db_options['run_show_text'] : ( get_option('fct_kaitong_neirong') ? get_option('fct_kaitong_neirong') : $options_data['run_show_text'] );
	                        
	                        $options_data['keyboard_open'] =  isset($db_options['keyboard_open']) ? $db_options['keyboard_open'] : ( get_option('fct_shurukuang') ? 1 : $options_data['keyboard_open'] );
	                        
	                        $options_data['lantern_open'] =  isset($db_options['lantern_open']) ? $db_options['lantern_open'] : ( get_option('fct_denglong') ? 1 : $options_data['lantern_open'] );
	                        $options_data['lantern_text_1'] =  isset($db_options['lantern_text_1']) ? $db_options['lantern_text_1'] : ( get_option('fct_denglong_zi_1') ? get_option('fct_denglong_zi_1') : $options_data['lantern_text_1'] );
	                        $options_data['lantern_text_2'] =  isset($db_options['lantern_text_2']) ? $db_options['lantern_text_2'] : ( get_option('fct_denglong_zi_2') ? get_option('fct_denglong_zi_2') : $options_data['lantern_text_2'] );
	                        $options_data['lantern_start_time'] =  isset($db_options['lantern_start_time']) ? $db_options['lantern_start_time'] : ( get_option('fct_denglong_kaishi') ? get_option('fct_denglong_kaishi') : $options_data['lantern_start_time'] );
	                        $options_data['lantern_end_time'] =  isset($db_options['lantern_end_time']) ? $db_options['lantern_end_time'] : ( get_option('fct_denglong_jieshu') ? get_option('fct_denglong_jieshu') : $options_data['lantern_end_time'] );
	                        
	                        $options_data['gray_page_open'] =  isset($db_options['gray_page_open']) ? $db_options['gray_page_open'] : ( get_option('fct_huise') ? 1 : $options_data['gray_page_open'] );
	                        $options_data['gray_page_start_time'] =  isset($db_options['gray_page_start_time']) ? $db_options['gray_page_start_time'] : ( get_option('fct_huise_kaishi') ? get_option('fct_huise_kaishi') : $options_data['gray_page_start_time'] );
	                        $options_data['gray_page_end_time'] =  isset($db_options['gray_page_end_time']) ? $db_options['gray_page_end_time'] : ( get_option('fct_huise_jieshu') ? get_option('fct_huise_jieshu') : $options_data['gray_page_end_time'] );
	                        $options_data['gray_page_body_bg'] =  isset($db_options['gray_page_body_bg']) ? $db_options['gray_page_body_bg'] : ( $options_data['gray_page_end_time'] );
	                        
	                        $options_data['light_box_open'] =  isset($db_options['light_box_open']) ? $db_options['light_box_open'] : ( get_option('fct_dengxiang') ? 1 : $options_data['light_box_open'] );
	                        $options_data['light_box_binding'] =  isset($db_options['light_box_binding']) ? $db_options['light_box_binding'] : ( get_option('fct_dengxiang_bangding') ? get_option('fct_dengxiang_bangding') : $options_data['light_box_binding'] );
	                        
	                        $options_data['url_open'] =  isset($db_options['url_open']) ? $db_options['url_open'] : ( get_option('fct_lianjie') ? 1 : $options_data['url_open'] );
	                        $options_data['url_source'] =  isset($db_options['url_source']) ? $db_options['url_source'] : ( get_option('fct_lianjie_weiba') ? get_option('fct_lianjie_weiba') : $options_data['url_source'] );
	                        break;
	                    case 'festivals':
	                        // 获取数据库存储的配置
	                        $db_options = $OptionsClass->get_option('festivals');
	                        // 节日氛围
	                        $options_data['open'] = isset($db_options['open']) ? $db_options['open'] : ( get_option('fct_festivals') ? 1 : $options_data['open'] );
	                        $festivals = maybe_unserialize(get_option('fct_festivals_option'));
	                        if (is_array($festivals)) {
	                            foreach ($festivals as $key => $value) {
	                                if (isset($options_data[$key])) {
	                                    $options_data[$key] = $value ? $value : $options_data[$key];
	                                }
	                            }
	                        }
	                        break;
	                    case 'snowflake':
	                        // 获取数据库存储的配置
	                        $db_options = $OptionsClass->get_option('snowflake');
	                        // 雪花飘落
	                        $options_data['open'] = isset($db_options['open']) ? $db_options['open'] : ( get_option('fct_xuehua') ? 1 : $options_data['open'] );
	                        $snowflake = maybe_unserialize(get_option('fct_xuehua_option'));
	                        if (is_array($snowflake)) {
	                            $options_data['images'] = isset($snowflake['image']) ? $snowflake['image'] : $options_data['images'];
	                            $options_data['text'] = isset($snowflake['text']) ? $snowflake['text'] : $options_data['text'];
	                            $options_data['color'] = isset($snowflake['color']) ? $snowflake['color'] : $options_data['color'];
	                            $options_data['size']['min'] = isset($snowflake['minSize']) ? $snowflake['minSize'] : $options_data['size']['min'];
	                            $options_data['size']['max'] = isset($snowflake['maxSize']) ? $snowflake['maxSize'] : $options_data['size']['max'];
	                            $options_data['speed']['min'] = isset($snowflake['minSpeed']) ? $snowflake['minSpeed'] : $options_data['speed']['min'];
	                            $options_data['speed']['max'] = isset($snowflake['maxSpeed']) ? $snowflake['maxSpeed'] : $options_data['speed']['max'];
	                            $options_data['count'] = isset($snowflake['count']) ? $snowflake['count'] : $options_data['count'];
	                            $options_data['zIndex'] = isset($snowflake['zIndex']) ? $snowflake['zIndex'] : $options_data['zIndex'];
	                        }
	                        break;
	                    case 'links':
	                        // 获取数据库存储的配置
	                        $db_options = $OptionsClass->get_option('links');
	                        // 链接&RSS
	                        $options_data['open'] = isset($db_options['open']) ? $db_options['open'] : ( get_option('fct_links') ? 1 : $options_data['open'] );
	                        $links = maybe_unserialize(get_option('fct_links_option'));
	                        if (is_array($links)) {
	                            $options_data['page_id'] = isset($links['links_page_id']) ? $links['links_page_id'] : $options_data['page_id'];
	                            $options_data['show_link_category'] = isset($links['links_category']) ? $links['links_category'] : $options_data['show_link_category'];
	                            $options_data['link_category_order'] = isset($links['links_page_cats_order']) ? $links['links_page_cats_order'] : $options_data['link_category_order'];
	                            $options_data['link_card_order'] = isset($links['links_page_card_order']) ? $links['links_page_card_order'] : $options_data['link_card_order'];
	                            $options_data['link_card_style'] = isset($links['links_page_card']) ? $links['links_page_card'] : $options_data['link_card_style'];
	                            // rss_open
	                            $options_data['rss_open'] = get_option('fct_links') ? 1 : $options_data['rss_open'];
	                            $options_data['rss_page_id'] = isset($links['links_rss_page_id']) ? $links['links_rss_page_id'] : $options_data['rss_page_id'];
	                            $options_data['show_rss_category'] = isset($links['links_rss_category']) ? $links['links_rss_category'] : $options_data['show_rss_category'];
	                            $options_data['rss_page_limit'] = isset($links['links_rss_page_limit']) ? $links['links_rss_page_limit'] : $options_data['rss_page_limit'];
	                            $options_data['rss_cache_expire'] = isset($links['links_rss_page_expire']) ? $links['links_rss_page_expire'] : $options_data['rss_cache_expire'];
	                            $options_data['rss_cron_open'] = isset($links['links_page_rss_cron']) ? $links['links_page_rss_cron'] : $options_data['rss_cron_open'];
	                            $options_data['css_style'] = isset($links['links_page_style']) ? $links['links_page_style'] : $options_data['css_style'];
	                            $options_data['show_prowered'] = isset($links['links_page_prowered']) ? $links['links_page_prowered'] : $options_data['show_prowered'];
	                        }
	                        break;
	                    default:
	                        ;
	                        break;
	                }
	                // 更新配置熟记并构建缓存文件
	                $OptionsClass->update_options($options_name, $options_data);
	            }
	        }

	        // ————————————————————————
	        // 构建js、css文件及其他缓存
	        // ————————————————————————
	        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-build.php';
	        Feng_Custom_Build::instance()->build();
	        
	    }
	}
	

}
