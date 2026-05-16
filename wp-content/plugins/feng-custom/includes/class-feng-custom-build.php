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
 * 构建插件生成的js、css及其他数据文件或缓存
 * 
 * @author 阿锋
 *
 */
class Feng_Custom_Build
{

    private $static_path_name = '/cache/feng-custom/static';

    private $static_path;
    /**
     * 配置数据
     * @var array
     */
    private $options_data;

    /**
     * 版本控制的配置名称
     * @var string
     */
    private $version_option_name = 'feng_custom_version';

    /**
     * 数据库中记录插件的版本号
     * @var string
     */
    private $db_plugin_version;

    /**
     * 自定义CSS文件的版本
     * @var string
     */
    private $build_css_version;

    /**
     * 自定义JS文件的版本
     * @var string
     */
    private $build_js_version;

    /**
     * 构建CSS样式
     * @var array
     */
    private $build_css_style = [];

    /**
     * 构建JS脚本
     * @var array
     */
    private $build_js_script = [];

    /**
     * 缓存分组
     * @var string
     */
    private $build_cache_group = 'build_cache';

    /**
     * 错误信息
     * @var string|array
     */
    private $error;

    /**
     *
     * @var Feng_Custom_Build
     */
    static private $instance;

    /**
     * 返回实例（单例）
     * @return Feng_Custom_Build
     */
    static public function instance($options_data = [])
    {
        if (!self::$instance) {
            self::$instance = new self($options_data);
        } else {
            if ($options_data) {
                self::$instance->options_data = $options_data;
            }
        }
        return self::$instance;
    }

    /**
     * 初始化
     */
    public function __construct($options_data = [])
    {
        if ($options_data) {
            $this->options_data = $options_data;
        } else {
            $this->options_data = Feng_Custom_Options::instance()->get_options_data();
        }

        // 初始化版本号
        $build_version = $this->get_build_version();
        $this->db_plugin_version = isset($build_version['plugin_version']) ? $build_version['plugin_version'] : '';
        $this->build_css_version = isset($build_version['build_css_version']) ? $build_version['build_css_version'] : '';
        $this->build_js_version = isset($build_version['build_js_version']) ? $build_version['build_js_version'] : '';

        // 初始化目录
        $this->init_path();
    }

    /**
     * 构建
     * @param string $option_name
     * @return boolean
     */
    public function build($option_name = null, $option_data = [])
    {
        if (empty($option_name)) {
            $this->build_css_style = [];
            $this->build_js_script = [];
            // 循环构建所有配置项
            foreach ($this->options_data as $name => $data) {
                if ($this->build($name, $data) === false) {
                    return false;
                }
            }
        } else {
            if (empty($option_data)) {
                $option_data = Feng_Custom_Options::instance()->get_options_data($option_name);
            }

            // 仅构建当前配置
            if ($this->setup_build_cache($option_name, $option_data) === false) {
                return false;
            }
        }

        // 生成引用的css、js文件
        try {
            foreach ($this->options_data as $name => $data) {
                if ($option_name !== $name) {
                    $cache_data = $this->get_build_cache($name);
                    if ($cache_data) {
                        // 无缓存需要构建
                        $this->build_css_style[$name] = $cache_data['__CSS__'];
                        $this->build_js_script[$name] = $cache_data['__JS__'];
                    } else {
                        if ($this->setup_build_cache($name, $data) === false) {
                            return false;
                        }
                    }
                }
            }

            $result = $this->create_static_file();
            if ($result === false) {
                return false;
            }
            $css_version = $result['css'];
            $js_version = $result['js'];

            // 更新数据库中版本号
            update_option($this->version_option_name, maybe_serialize([
                'plugin_version' => FENG_CUSTOM_VERSION,
                'build_css_version' => $css_version,
                'build_js_version' => $js_version,
            ]));

            $this->db_plugin_version = FENG_CUSTOM_VERSION;
            $this->build_css_version = $css_version;
            $this->build_js_version = $js_version;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 构建效果配置样式、脚本
     * @param array $option_data
     * @return string[]
     */
    protected function build_option_option($option_data)
    {
        $css_style = "";
        if (isset($option_data['gray_page_body_bg']) && $option_data['gray_page_body_bg']) {
            $css_style = ".fct-gray-page body { background-color: " . $option_data['gray_page_body_bg'] . "; }";
        }

        $js_script = "fengCustomEffect.run(" . json_encode($option_data) . ");";

        return [
            'css_style' => $css_style,
            'js_script' => $js_script,
        ];
    }

    /**
     * 构建节日氛围css样式及js脚本
     * @param array $option_data
     * @return string[]  
     */
    protected function build_option_festivals($option_data)
    {
        if (!(isset($option_data['open']) && $option_data['open'])) {
            return [
                'css_style' => '',
                'js_script' => '',
            ];
        }
        $js_script = "fengCustomFestivals.run(" . json_encode($option_data) . ");";

        return [
            'css_style' => '',
            'js_script' => $js_script,
        ];
    }

    /**
     * 构建雪花飘落样式、脚本文件
     * @param array $option_data
     * @return string[]
     */
    protected function build_option_snowflake($option_data)
    {
        if (!(isset($option_data['open']) && $option_data['open'])) {
            // 未开启雪花飘落返回空
            return [
                'css_style' => '',
                'js_script' => '',
            ];
        }
        $option_js_obj = "count: " . $option_data['count'] . ", ";
        $option_js_obj .= "color: \"" . $option_data['color'] . "\", ";
        $option_js_obj .= "zIndex: " . $option_data['zIndex'] . ", ";
        $option_js_obj .= "minSize: " . $option_data['size']['min'] . ", ";
        $option_js_obj .= "maxSize: " . $option_data['size']['max'] . ", ";
        $option_js_obj .= "minSpeed: " . $option_data['speed']['min'] . ", ";
        $option_js_obj .= "maxSpeed: " . $option_data['speed']['max'] . ", ";
        $option_js_obj .= "text: " . ($option_data['text'] ? "\"" . $option_data['text'] . "\"" : "false") . ", ";

        if ($option_data['images']) {
            $images = explode("\r\n", $option_data['images']);
            foreach ($images as $key => $value) {
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $images[$key] = $value;
                } else {
                    $images[$key] = FENG_CUSTOM_URL . 'public/img/' . $value;
                }
            }
            $option_js_obj .= "image: \"" . implode(',', $images) . "\",";
        }
        $js_script = "jQuery(document).ready(function($) {";
        $js_script .= "snowFall.snow(document.body, {" . $option_js_obj . "});";
        $js_script .= "});";

        return [
            'css_style' => '',
            'js_script' => $js_script,
        ];
    }

    /**
     * 构建链接&RSS的缓存文件
     * @param array $option_data 配置数据
     * @return boolean|array ['css_style', 'js_script']
     */
    protected function build_option_links($option_data)
    {
        // 链接卡片样式
        $css_style = "";
        $link_card_style = isset($option_data['link_card_style']) ? $option_data['link_card_style'] : 0;
        switch ($link_card_style) {
            case '1':
                $css_style = $this->read_custom_file('links-1.css');
                break;
            case '2':
                $css_style = $this->read_custom_file('links-2.css');
                break;
            default:
                $css_style = $this->read_custom_file('links-1.css');
                break;
        }

        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-links.php';
        // 卸载 WP-Cron 执行检查RSS的任务
        if (!$option_data['rss_cron_open']) {
            Feng_Custom_Links::instance()->rss_cron_unset();
        } else {
            Feng_Custom_Links::instance()->rss_cron();
        }

        return [
            'css_style' => $css_style,
            'js_script' => '',
        ];
    }

    /**
     * 获取构建版本数据
     * @return string[]
     */
    private function get_build_version()
    {
        $build_version = get_option($this->version_option_name);
        if ($build_version === false) {
            // 没有该配置需要添加
            $build_version = [
                'plugin_version' => FENG_CUSTOM_VERSION,
                'build_css_version' => '',
                'build_js_version' => '',
            ];
            add_option($this->version_option_name, maybe_serialize($build_version));
        } else {
            // 解构
            $build_version = maybe_unserialize($build_version);
        }
        return $build_version;
    }

    /**
     * 构建某个配置项目的缓存
     * @param string $option_name 配置项目标识
     * @param array $option_data 配置项目的数据
     */
    private function setup_build_cache($option_name, $option_data)
    {
        if (isset($option_data['open'])) {
            // 有配置开关
            $option_open = $option_data['open'];
            if (!$option_open) {
                // 未开启该功能需要移出构建
                return $this->remove_build_cache($option_name);
            }
        }

        $build_css_style = "";
        $build_js_script = "";

        // 获取自定义CSS文件
        $build_custom_css_file = $this->read_custom_file($option_name . '.css');
        if ($build_custom_css_file !== false) {
            $build_css_style .= $build_custom_css_file;
        }
        // 获取自定义JS文件
        $build_custom_js_file = $this->read_custom_file($option_name . '.js');
        if ($build_custom_js_file) {
            $build_js_script .= $build_custom_js_file;
        }

        // 检查是否有特殊构建方法
        $build_method = 'build_option_' . $option_name;
        if (method_exists($this->instance(), $build_method)) {
            $result = call_user_func(array($this->instance(), $build_method), $option_data);
            if ($result === false) {
                return false;
            }
            $build_css_style .= isset($result['css_style']) ? $result['css_style'] : "";
            $build_js_script .= isset($result['js_script']) ? $result['js_script'] : "";
        }

        // 包含自定义样式
        $build_css_style .= isset($option_data['css_style']) ? $option_data['css_style'] : "";
        // 包含自定义脚本
        $build_js_script .= isset($option_data['js_script']) ? $option_data['js_script'] : "";

        $this->build_css_style[$option_name] = $build_css_style;
        $this->build_js_script[$option_name] = $build_js_script;

        $build_cache_data = [
            '__CSS__' => $build_css_style,
            '__JS__' => $build_js_script,
        ];

        // 建立缓存
        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-cache.php';
        Feng_Custom_Cache::instance()->set($option_name, $build_cache_data, $this->build_cache_group, 0);

        return $build_cache_data;
    }

    /**
     * 移出某个配置的缓存
     * @param string $option_name
     */
    private function remove_build_cache($option_name)
    {
        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-cache.php';
        Feng_Custom_Cache::instance()->set($option_name, [
            '__CSS__' => "",
            '__JS__' => "",
        ], $this->build_cache_group, 0);
    }

    /**
     * 获取某个配置的缓存
     * @param string $option_name
     */
    private function get_build_cache($optio_name)
    {
        require_once FENG_CUSTOM_PATH . 'includes/class-feng-custom-cache.php';
        return Feng_Custom_Cache::instance()->get($optio_name, $this->build_cache_group);
    }

    /**
     * 创建静态文件
     * @param string $type CSS|JavaScript
     * @return boolean
     */
    private function create_static_file()
    {
        // 版本号
        $version = [
            'css_version' => $this->build_css_version,
            'js_version' => $this->build_js_version,
        ];

        $build_data = [];
        // CSS
        $css_line_string = "@charset \"UTF-8\"; \r\n";
        $css_line_string .= implode("\r\n", $this->build_css_style);
        $build_data['css'] = [
            'line_string' => $css_line_string,
            'old_version' => $this->build_css_version,
        ];
        // JS
        $js_line_string = "\r\n";
        $js_line_string .= implode("\r\n", $this->build_js_script);
        $build_data['js'] = [
            'line_string' => $js_line_string,
            'old_version' => $this->build_js_version,
        ];

        try {
            foreach ($build_data as $key => $value) {
                if (!function_exists('wp_generate_password')) {
                    require_once ABSPATH . WPINC . '/pluggable.php';
                }
                $build_version = md5(time() . wp_generate_password());
                $filename = $this->static_path . '/custom_' . $build_version . '.' . $key;
                // 可写方式打开文件
                $file = fopen($filename, 'w');
                if ($file === false) {
                    return $value['filename'] . __('文件打开失败！', 'feng-custom');
                }

                // 写入文件
                fwrite($file, $value['line_string']);

                // 关闭文件
                fclose($file);

                // 删除上一个版本文件
                $old_filename = $this->static_path . '/custom_' . $value['old_version'] . '.' . $key;
                wp_delete_file($old_filename);
                $version[$key] = $build_version;
            }
            return $version;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 初始化自定义文件目录
     * @return boolean
     */
    private function init_path()
    {
        $this->static_path = $folder_path = WP_CONTENT_DIR . $this->static_path_name;
        if (!is_dir($folder_path)) {
            // 递归创建目录
            if (wp_mkdir_p($folder_path) != true) {
                $this->error = '创建' . $folder_path . '目录失败，请检查文wp-content件夹权限';
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @return boolean|string[]
     */
    public function get_static_files()
    {
        // 获取构建版本
        if ($this->db_plugin_version !== FENG_CUSTOM_VERSION || !$this->build_css_version || !$this->build_js_version) {
            // 版本号不一致需要更新文件
            if ($this->build() === false) {
                return false;
            }
        }
        $build_css_file = $this->static_path . '/custom_' . $this->build_css_version . '.css';
        $build_js_file = $this->static_path . '/custom_' . $this->build_js_version . '.js';
        // 判断自定义css文件是否存在
        if (!file_exists($build_css_file) || !file_exists($build_js_file)) {
            // 构建的文件不存在，需要重新构建
            if ($this->build() === false) {
                return false;
            }
        }

        return [
            'css' => content_url($this->static_path_name) . '/custom_' . $this->build_css_version . '.css',
            'js' => content_url($this->static_path_name) . '/custom_' . $this->build_js_version . '.js',
        ];
    }


    /**
     * 读取文件内容为字符串
     * @param string $filename 文件名包含后缀 links.css
     * @return false|string
     */
    private function read_custom_file($custom_filename)
    {
        $arr = explode('.', $custom_filename);
        $name = $arr[0];
        $ext = $arr[1];

        try {
            // 未压缩的文件路径
            $filename = FENG_CUSTOM_PATH . 'includes/build_custom_files/' . $ext . '/' . $custom_filename;
            // 压缩的文件路径
            $min_filename = FENG_CUSTOM_PATH . 'includes/build_custom_files/' . $ext . '/' . $name . '.min.' . $ext;

            if (file_exists($min_filename)) {
                // 读取压缩的文件
                $file = fopen($min_filename, "r");
            } else if (file_exists($filename)) {
                // 读取未压缩的文件
                $file = fopen($filename, "r");
            } else {
                $this->error = esc_html__('文件打开失败！', 'feng-custom');
                return false;
            }

            if ($file === false) {
                $this->error = esc_html__('文件打开失败！', 'feng-custom');
                return false;
            }
            if (filesize($filename) > 0) {
                $filestring = fread($file, filesize($filename));
            }
            fclose($file);
            return $filestring;
        } catch (Exception $e) {
            $this->error = $e;
            return false;
        }
    }

    /**
     * 获取错误信息
     * @return string|[]
     */
    public function get_error()
    {
        return $this->error;
    }






}