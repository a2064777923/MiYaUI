<?php
class Feng_Custom_Base {
    
    /**
     * 开关
     * @var array
     */
    private $switch;
    
    /**
     * 后台配置管理链接
     * @var string
     */
    private $options_url;
    
    /**
     * 博客设置的日期时间格式
     * @var string
     */
    private $date_format;
    
    /**
     * 错误信息
     * @var int|string|array
     */
    private $error;
    
    public function __construct() {
        require_once FENG_CUSTOM_PATH . '/includes/class-feng-custom-options.php';
    }
    
    /**
     * 获取开关数据
     * @param string $name
     * @param boolean $need_refresh
     * @return array|string|boolean
     */
    public function switch($name = null, $need_refresh = false) {
        return Feng_Custom_Options::instance()->get_switch($name, $need_refresh);
    }
    
    /**
     * 获取配置管理页面的链接
     * @return string
     */
    public function get_options_url($option_name = null) {
        if (empty($this->options_url)) {
            $this->options_url = admin_url('themes.php?page=feng-custom');
        }
        if (empty($option_name)) {
            return $this->options_url;
        }else {
            return $this->options_url .  '&module=' . $option_name;
        }
    }
    
    /**
     * 获取博客设置的日期时间格式
     * @return string
     */
    public function get_date_format() {
        return $this->date_format ? $this->date_format : get_option('date_format') . ' ' . get_option('time_format');
    }
    
    /**
     * 获取错误信息
     * @return int|string|array
     */
    public function get_error() {
        return $this->error;
    }
    
    /**
     * 设置错误信息
     * @param int|string|array $error
     */
    protected function set_error($error) {
        $this->error = $error;
    }
}