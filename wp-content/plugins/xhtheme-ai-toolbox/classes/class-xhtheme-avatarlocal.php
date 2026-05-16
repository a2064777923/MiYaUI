<?php
namespace XHTheme\AIToolbox;
class XH_AvatarLocal {
    /**
     * 下载远程图片并保存到本地
     * 
     * @param string $url 远程图片URL
     * @param string $filename 保存的文件名（不含扩展名）
     * @return string|false 成功返回本地URL，失败返回false
     */
    public static function download_avatar($url) {
        // 获取WordPress上传目录
        $upload_dir = wp_upload_dir();
        $avatar_dir = trailingslashit($upload_dir['basedir']) . 'avatars';
        
        // 确保avatars目录存在
        if (!file_exists($avatar_dir)) {
            wp_mkdir_p($avatar_dir);
        }
    
        // 从URL提取文件名
        $url_path = wp_parse_url($url, PHP_URL_PATH);
        $file_name = basename($url_path);
        if (empty($file_name)) {
            return false;
        }
    
        // 检查文件是否已存在
        $file_path = trailingslashit($avatar_dir) . $file_name;
        if (file_exists($file_path)) {
            return self::get_local_url($file_path);
        }
        
        // 获取远程图片
        $response = wp_remote_get($url,['timeout' => 5]);
        if (is_wp_error($response)) {
            return false;
        }
        
        // 获取内容类型并验证
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (!self::is_valid_image_type($content_type)) {
            return false;
        }
        
        $image_data = wp_remote_retrieve_body($response);
        $result = file_put_contents($file_path, $image_data);
        
        return $result !== false ? self::get_local_url($file_path) : false;
    }
    
    /**
     * 将本地文件路径转换为URL
     * 
     * @param string $file_path 文件绝对路径
     * @return string 本地URL
     */
    private static function get_local_url($file_path) {
        $wp_path = trailingslashit(ABSPATH);        
        if (strpos($file_path, $wp_path) === 0) {
            $relative_path = substr($file_path, strlen($wp_path));
            return trailingslashit(site_url()) . $relative_path;
        }
        
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']);
        return str_replace($base_dir, trailingslashit($upload_dir['baseurl']), $file_path);
    }
    
    /**
     * 验证是否为有效的图片类型
     * 
     * @param string $mime_type MIME类型
     * @return bool 是否为有效图片
     */
    private static function is_valid_image_type($mime_type) {
        $valid_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml'
        ];        
        return in_array($mime_type, $valid_types);
    }

}