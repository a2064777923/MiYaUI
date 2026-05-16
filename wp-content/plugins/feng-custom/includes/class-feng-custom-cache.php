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
 * 缓存类
 * @author 阿锋
 * 
 */
class Feng_Custom_Cache {
    
    /**
     * 缓存目录
     * @var string
     */
    private $cache_path = WP_CONTENT_DIR . '/cache/feng-custom';
    
    /**
     * 缓存组
     * @var string
     */
    private $group;
    
    /**
     * 过期时间
     * @var int
     */
    private $expire;
    
    /**
     * 错误信息
     * @var string|array
     */
    private $error;
    
    /**
     *
     * @var Feng_Custom_Cache
     */
    static private $instance;
    
    /**
     * 返回实例（单例）
     * @return Feng_Custom_Cache
     */
    static public function instance($group = null, $expire = null) {
        if(!self::$instance) {
            self::$instance = new self();
        }
        if (!empty($group)) {
            self::$instance->set_group($group);
        }
        if (!empty($expire)) {
            self::$instance->set_expire($expire);
        }
        return self::$instance;
    }
    
    /**
     * 初始化
     */
    public function __construct($group = 'default', $expire = 3600) {
        // 分组
        $this->group = $group;
        
        // 缓存时间，0标识不过期（秒）
        $this->expire = $expire;
        
    }
    
    /**
     * 设置缓存数据
     * @param string $key 缓存关键字
     * @param string|array data 缓存的数据
     * @param string $group 缓存前缀
     * @param int $expire 过期时间（秒）0表示永不过期
     */
    public function set($key, $data, $group = null, $expire = null) {
        $cache_params = [];
        $cache_params['key'] = $key;
        $cache_params['data'] = $data;
        // 分组
        $group = $this->get_group($group);
        $cache_params['group'] = $group;
        // 有效期
        $expire = $this->get_expire($expire);
        $cache_params['expire'] = $expire;
        // 当前时间戳
        $current_time = time();
        $cache_params['build_time'] = $current_time;
        if ($expire === 0) {
            // 永不过期
            $expire_time = 0;
        }else {
            // 过期时间
            $expire_time = (int)$current_time + (int)$expire;
        }
        $cache_params['expire_time'] = $expire_time;
        
        // 初始化目录
        if (!$this->init_cache_path()) {
            return false;
        }
        
        // 缓存文件名
        $filename = $this->get_cache_filename($key, $group);
        
        if (is_file($filename)) {
            // 读写方式打开文件
            $file = fopen($filename, "w+");
            $file_content = fread($file, filesize($filename));
            $cache_params_origin = maybe_unserialize($file_content);
            if (is_array($cache_params_origin)) {
                $cache_params = wp_parse_args($cache_params, $cache_params_origin);
            }
        }else {
            // 写的方式打开文件
            $file = fopen($filename, "w");
        }
        
        // 序列化数据
        $filetext = maybe_serialize($cache_params);
        
        // 写入文件
        fwrite($file, $filetext);
        // 关闭文件
        fclose($file);
    }
    
    /**
     * 获取缓存数据
     * @param string $key 缓存关键字
     * @param string $group 缓存前缀
     * @return null|array
     */
    public function get($key, $group = null) {
        $group = $this->get_group($group);
        $cache_data = $this->get_cache_params($key, $group);
        if (empty($cache_data)) {
            // 没有缓存数据
            return null;
        }
        if ($cache_data['expire_time'] === 0) {
            return $cache_data['data'];
        }
        // 当前时间
        $current_time = time();
        if ($cache_data['expire_time'] >= $current_time) {
            return $cache_data['data'];
        }
        // 删除缓存
        $this->delete($key, $group);
        return null;
    }
    
    /**
     * 获取缓存详细数据
     * @param string $key
     * @param string $group
     * @return array|null
     */
    private function get_cache_params($key, $group = null) {
        $group = $this->get_group($group);
        // 获取缓存文件
        $filename = $this->get_cache_filename($key, $group);
        if (file_exists($filename) === false) {
            // 文件不存在
            return null;
        }
        try {
            $file = fopen($filename, "r");
            $cache_file_content = fread($file, filesize($filename));
            fclose($file);
            $cache_params = maybe_unserialize($cache_file_content);
            if (!is_array($cache_params)) {
                // 非缓存文件，直接删除
                wp_delete_file($filename);
                return null;
            }
            return $cache_params;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * 删除缓存
     * 
     * @param string $key 
     * @param string $group
     */
    public function delete($key, $group = null) {
        $group = empty($group) ? $this->group : $group;
        $filename = $this->get_cache_filename($key, $group);
        if (file_exists($filename)) {
            // 删除文件
            wp_delete_file($filename);
        }
    }
    
    /**
     * 清空缓存
     * @param string $group
     */
    public function clear($group = null) {
        if ($group) {
            $this->delete_folder($this->cache_path . '/' . $group);
        }else {
            $this->delete_folder($this->cache_path);
        }
    }
    
    /**
     * 获取缓存文件路径名称
     * @param string $key
     * @param string $group 
     * @return string 缓存文件名
     */
    private function get_cache_filename($key, $group = null) {
        $group = empty($group) ? $this->group : $group;
        $filename = $this->cache_path . '/' . $group . '/' . md5($group . '_' . $key) . '.cache';
        return $filename;
    }
    
    /**
     * 设置缓存分组，也可用于切换缓存分组
     * @param string $group
     */
    public function set_group($group) {
        $this->group = $group;
    }
    
    /**
     * 获取缓存分组
     * @param string $group
     * @return string
     */
    public function get_group($group = null) {
        if (empty($group)) {
            return $this->group;
        }else {
            $this->set_group($group);
            return $group;
        }
    }
    
    /**
     * 设置缓存过期时间，秒
     * @param int $expire
     */
    public function set_expire($expire) {
        $this->expire = $expire;
    }
    
    /**
     * 获取缓存过期时间，秒
     * @param int $expire
     * @return int 
     */
    public function get_expire($expire = null) {
        if ($expire === 0) {
            return 0;
        }
        if (empty($expire)) {
            return $this->expire;
        }else {
            $this->set_expire($expire);
            return $expire;
        }
    }
    
    /**
     * 初始化缓存目录
     * 
     * @param string $group 缓存前缀
     * @return boolean
     */
    private function init_cache_path() {
        $group = $this->get_group();
        // 获取缓存目录
        $cache_path = $this->cache_path . '/' . $group;
        // 判断目录是否存在
        if (is_dir($cache_path)) {
            return true;
        }else {
            // 递归创建目录
            if (wp_mkdir_p($cache_path) != true) {
                $this->error = __('缓存目录创建失败', 'feng-custom');
                return false;
            }
        }
        return true;
    }
    
    /**
     * 递归删除文件夹
     * @param string $folder_path 文件夹路径
     */
    private function delete_folder($folder_path) {
        if (is_dir($folder_path)) {
            $files = array_diff(scandir($folder_path), array('.', '..'));
            foreach ($files as $file) {
                $file_path = $folder_path . '/' . $file;
                is_dir($file_path) ? $this->delete_folder($file_path) : unlink($file_path);
            }
            rmdir($folder_path);
        }
    }
    
    /**
     * 获取错误信息
     * @return string|array
     */
    public function get_error() {
        return $this->error;
    }
    
}



