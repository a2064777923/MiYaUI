<?php
namespace B2\Modules\Common;
use B2\Modules\Common\Poster;
use B2\Modules\Common\Distribution;
use B2\Modules\Common\IntCode;
use B2\Modules\Common\Post;

class FileUpload
{

    public static $editor;
    public static $upload_dir;
    public static $allow_crop;
    public static $allow_webp;

    public function init()
    {
        //add_filter('sanitize_file_name', array($this,'rename_filename'),10);

        //本地裁剪
        self::$upload_dir = apply_filters('b2_upload_path_arg', wp_upload_dir());
        self::$allow_crop = b2_get_option('normal_write', 'write_image_crop');
        self::$allow_webp = b2_get_option('normal_write', 'write_image_webp');

    }

    public static function create_code($url)
    {
        $url = crc32($url);
        $result = sprintf("%u", $url);
        return self::code62($result);
    }

    public static function code62($x)
    {
        $show = '';
        while ($x > 0) {
            $s = $x % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x / 62);
        }
        return $show;
    }

    /**
     * 上传文件重命名
     *
     * @param string $filename 文件名
     *
     * @return string 文件名
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function rename_filename($filename, $type, $post_id)
    {
        $info = pathinfo($filename);
        $ext = empty($info['extension']) ? '' : '.' . $info['extension'];
        return $info['filename'] . '_' . $post_id . '_' . $type . '_' . self::create_code($filename) . rand(1, 9999) . $ext;
    }


    public static function url_to_base64($url)
    {
        if ($url) {
            $file_contents = wp_remote_get($url, array(
                'httpversion' => '1.0',
                'timeout' => 20,
                'redirection' => 20,
                'sslverify' => FALSE,
                'user-agent' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; MALC)'
            ));

            if (is_wp_error($file_contents)) {
                return array('error' => $file_contents->get_error_message());
            }

            $img_base64 = base64_encode($file_contents['body']);
            if ($img_base64) {
                return 'data:image/jpeg;base64,' . $img_base64;
            }
        }

        return '';
    }

    /**
     * 图片裁剪，并储存到本地
     *
     * @param array $arg 
     *
     * @return string 图片URL
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function thumb($arg)
    {
        //thumb:图片路径,
        //type:编辑形式,
        //fill:固定长宽剧中裁剪，
        //fit:等比缩放，
        //exact:固定尺寸（可能造成变形），
        //exactW:等宽缩放（宽度固定，高度自动），
        //exactH:等高缩放（高度固定，宽度自动）
        //smart:智能裁剪，
        //gif 是否移除gif动画效果
        $r = apply_filters('b2_thumb_arg', wp_parse_args($arg, array(
            'thumb' => '',
            'type' => 'fill',
            'width' => '500',
            'height' => '500',
            'gif' => 0,
            'webp' => false,
            'ratio' => B2_IMG_RATIO,
            'custom' => false //留给站长DIY的参数，为true的时候，可以使用下面 b2_thumb_custom 钩子，返回图片地址
        )));

        $r['thumb'] = Post::img_replace($r['thumb']);

        if ($r['custom']) {
            return apply_filters('b2_thumb_custom', $r['thumb'], $r);
        }

        if ($r['height'] === '100%') {
            $r['type'] = 'exactW';
            unset($r['height']);
        }

        if ($r['width'] === '100%') {
            $r['type'] = 'exactH';
            unset($r['width']);
        }

        if (defined('B2_IMG_RATIO')) {
            if (isset($r['width'])) {
                $r['width'] = ceil($r['width'] * $r['ratio']);
            }
            if (isset($r['height'])) {
                $r['height'] = ceil($r['height'] * $r['ratio']);
            }
        }

        //检查图片是为空
        if (empty($r['thumb'])) {
            return apply_filters('b2_thumb_default_image', b2_get_default_img(), $r);
        }

        //如果不是本地文件，直接返回
        if (strpos($r['thumb'], B2_HOME_URI) === false) {
            //如果使用的是相对地址
            if (strpos($r['thumb'], '//') === false) {
                $r['thumb'] = self::$upload_dir['baseurl'] . '/' . $r['thumb'];
            }
            return apply_filters('b2_thumb_no_local', $r['thumb'], $r);
        }

        if (!self::$allow_crop) {
            return $r['thumb'];
        }

        //检查是否为裁剪过的图片
        if (strpos($r['thumb'], '_mark_') !== false) {
            return $r['thumb'];
        }

        //如果不裁剪，返回原图
        if ($r['type'] == 'default') {
            return $r['thumb'];
        }

        //获取原始图片的物理地址
        $rel_file_path = str_replace(self::$upload_dir['baseurl'], '', $r['thumb']);
        $rel_file_path = str_replace(array('/', '\\'), B2_DS, $rel_file_path);

        $basedir = str_replace(array('/', '\\'), B2_DS, self::$upload_dir['basedir']);
        $rel_file_path = $basedir . $rel_file_path;

        // $is_file = wp_cache_get($rel_file_path, 'b2_thumb_is_file');

        // var_dump($is_file);

        // if (!$is_file) {
        //     $is_file = is_file($rel_file_path) ? 1 : 0;
        //     wp_cache_set($rel_file_path, $is_file, 'b2_thumb_is_file', 600);
        // }

        // // var_dump(123);

        // list($width, $height, $type, $attr) = getimagesize($rel_file_path);

        // if ((isset($r['width']) && $width < $r['width']) || (isset($r['height']) && $height < $r['height'])) {
        //     return $r['thumb'];
        // }

        $basename = basename($rel_file_path);
        $rel_file = str_replace($basedir . B2_DS, '', $rel_file_path);
        $r['height'] = isset($r['height']) ? $r['height'] : null;
        $file_path = str_replace($basename, '', $rel_file);

        $thumb_dir = $basedir . B2_DS . 'thumb' . B2_DS . $file_path . $r['type'] . '_w' . $r['width'] . '_h' . $r['height'] . '_g' . $r['gif'] . '_mark_' . $basename;

        // var_dump($thumb_dir );

        $is_thumb_file = wp_cache_get($thumb_dir, 'b2_thumb_is_file');
        if (!$is_thumb_file) {
            $is_thumb_file = is_file($thumb_dir) ? 1 : 0;
            wp_cache_set($thumb_dir, $is_thumb_file, 'b2_thumb_is_file', 500);
        }

        //如果存在直接返回
        if ($is_thumb_file) {
            $basedir = str_replace(array('/', '\\'), '/', $basedir);
            $thumb_dir = str_replace(array('/', '\\'), '/', $thumb_dir);
            return apply_filters('b2_get_thumb', str_replace($basedir, self::$upload_dir['baseurl'], $thumb_dir));
        }

        // 使用 WordPress 内置图像编辑器
        $editor = wp_get_image_editor($rel_file_path);
        if (is_wp_error($editor)) {
            return $r['thumb'];
        }

        try {
            switch ($r['type']) {
                case 'fit':
                    // 等比缩放，保持宽高比
                    $editor->resize($r['width'], $r['height'], false);
                    break;
                case 'exact':
                    // 固定尺寸，可能变形
                    $editor->resize($r['width'], $r['height'], true);
                    break;
                case 'exactW':
                    // 等宽缩放，高度自动
                    $editor->resize($r['width'], null);
                    break;
                case 'exactH':
                    // 等高缩放，宽度自动
                    $editor->resize(null, $r['height']);
                    break;
                case 'smart':
                    // 智能裁剪 - WordPress 默认裁剪方式
                    $editor->resize($r['width'], $r['height'], true);
                    break;
                default:
                    // fill - 固定长宽剧中裁剪
                    $editor->resize($r['width'], $r['height'], array('center', 'center'));
                    break;
            }

            // 处理 GIF 动画
            if ($r['gif']) {
                // WordPress 内置编辑器会自动将 GIF 转为静态图片
                // 无需额外操作
            }

            // 保存图片
            $saved = $editor->save($thumb_dir);
            if (is_wp_error($saved)) {
                return apply_filters('b2_thumb_default_image', b2_get_default_img(), $r);
            }

            // 处理 WebP 格式
            // if (self::$allow_webp) {
            //     $thumb_webp = str_replace(substr(strrchr($thumb_dir, '.'), 1), 'webp', $thumb_dir);
            //     $editor->save($thumb_webp, 'image/webp');
            // }

            $basedir = str_replace(array('/', '\\'), '/', $basedir);
            $thumb_dir = str_replace(array('/', '\\'), '/', $thumb_dir);
            return apply_filters('b2_get_thumb', str_replace($basedir, self::$upload_dir['baseurl'], $thumb_dir));
        } catch (\Exception $e) {
            return $r['thumb'];
        }

        return $r['thumb'] ?? '';
    }

    public static function auto_webp($url)
    {

        if (!self::$allow_webp) {
            return $url;
        }

        if (strpos($url, self::$upload_dir['baseurl']) === false)
            return $url;

        //如果是gif图片，直接返回 url
        if (strpos($url, '.gif') !== false) {
            return $url;
        }

        //如果是 webp 图片，直接返回 url
        if (strpos($url, '.webp') !== false) {
            return $url;
        }

        $rel_file_path = str_replace(self::$upload_dir['baseurl'], '', $url);
        $rel_file_path = str_replace(array('/', '\\'), B2_DS, $rel_file_path);

        $basedir = str_replace(array('/', '\\'), B2_DS, self::$upload_dir['basedir']);
        $rel_file_path = $basedir . $rel_file_path;

        $is_file = wp_cache_get($rel_file_path, 'b2_thumb_is_file');
        if (!$is_file) {
            $is_file = is_file($rel_file_path) ? 1 : 0;
            wp_cache_set($rel_file_path, $is_file, 'b2_thumb_is_file', 500);
        }

        if ($is_file) {

            //self::$upload_dir['basedir'] 的路径斜杠根据系统进行统一
            self::$upload_dir['basedir'] = str_replace(array('/', '\\'), B2_DS, self::$upload_dir['basedir']);

            $thumb = str_replace(substr(strrchr($rel_file_path, '.'), 1), 'webp', $rel_file_path);

            // $thumb = str_replace(self::$upload_dir['basedir'], self::$upload_dir['basedir'].B2_DS.'thumb', $thumb);

            $webp_url = str_replace(substr(strrchr($url, '.'), 1), 'webp', $url);

            // $webp_url = str_replace(self::$upload_dir['baseurl'], self::$upload_dir['baseurl'].'/thumb', $webp_url);


            $isset = wp_cache_get($thumb, 'b2_thumb_is_file');

            if (!$isset) {
                $isset = is_file($thumb) ? 1 : 0;
                wp_cache_set($thumb, $isset, 'b2_thumb_is_file', 500);
            }

            if (!$isset) {
                $editor = wp_get_image_editor($rel_file_path);
                if (is_wp_error($editor)) {
                    return $url;
                }

                try {
                    $result = $editor->save($thumb, 'image/webp');
                    if (is_wp_error($result)) {
                        return $url;
                    }

                    return apply_filters('b2_get_thumb_webp', $webp_url);

                } catch (\Throwable $th) {
                    return $url;
                }
            }

            return apply_filters('b2_get_thumb_webp', $webp_url);
        }

        return $url;
    }

    public static function get_poster_data($post_id)
    {

        if (!$post_id)
            return [];

        //默认设置项
        $default_poster_settings = get_option('b2_template_single');
        if (!isset($default_poster_settings['single_poster_group'][0])) {
            $default_poster_settings = array(
                'single_poster_default_img' => b2_get_option('template_single', 'single_poster_default_img'),
                'single_poster_default_logo' => b2_get_option('template_single', 'single_poster_default_logo'),
                'single_poster_default_text' => html_entity_decode(esc_attr(b2_get_option('template_single', 'single_poster_default_text'))),
                'single_poster_default_desc' => html_entity_decode(esc_attr(b2_get_option('template_single', 'single_poster_default_desc'))),
                'single_poster_dl' => esc_attr(b2_get_option('template_single', 'single_poster_dl')),
            );
        } else {
            $default_poster_settings = $default_poster_settings['single_poster_group'][0];
        }

        $post_year = get_the_date('Y', $post_id);
        $post_month = get_the_date('m', $post_id);
        $post_day = get_the_date('d', $post_id);

        //文章标题
        $title = esc_attr(get_the_title($post_id));

        //文章描述
        $text = b2_get_excerpt($post_id, 60);

        $thumb_url = get_post_meta($post_id, 'b2_post_poster', true);

        if (!$thumb_url) {
            //获取特色图
            $thumb_id = get_post_thumbnail_id($post_id);
            if ($thumb_id) {
                $thumb_url = wp_get_attachment_url($thumb_id);
            } else {
                $thumb_url = b2_get_first_img(get_post_field('post_content', $post_id));
            }

            if (!$thumb_url) {
                $thumb_url = $default_poster_settings['single_poster_default_img'];
            }
        }

        $thumb_url = b2_get_thumb(array('thumb' => $thumb_url, 'height' => 600, 'width' => 400));

        $ref = '';

        // $author = get_post_field('post_author',$post_id);
        $author = b2_get_current_user_id();
        if ($author) {
            if (Distribution::user_can_distribution($author)) {
                $c = new IntCode();
                $ref = '?ref=' . $c->encode($author);
            }
        }

        $logo = $default_poster_settings['single_poster_default_logo'];

        $link = get_permalink($post_id);

        if (isset($default_poster_settings['single_poster_dl']) && $default_poster_settings['single_poster_dl'] == 1) {
            $thumb_url = B2_HOME_URI . '/get-image?token=' . md5(AUTH_KEY . $thumb_url . $post_id) . '&id=' . $post_id . '&url=' . $thumb_url;
            $logo = B2_HOME_URI . '/get-image?token=' . md5(AUTH_KEY . $logo . $post_id) . '&id=' . $post_id . '&url=' . $logo;
        }

        return array(
            'title' => $title,
            'content' => $text,
            //isset($default_poster_settings['single_poster_dl']) && $default_poster_settings['single_poster_dl'] ? '//images.weserv.nl/?url='.$thumb_url.'&w=344&dpr=2' : 
            'thumb' => $thumb_url,
            'logo' => $logo,
            'desc' => $default_poster_settings['single_poster_default_desc'],
            'date' => array(
                'year' => $post_year,
                'month' => $post_month,
                'day' => $post_day
            ),
            'ref' => $ref,
            'link' => $link . ($ref ? $ref : '')
        );
    }

    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return array($r, $g, $b);
    }


    public static function url_to_path($url, $dir)
    {

        //获取远程图片到本地
        $url = self::url_file_upload($url);

        //如果保存失败，返回错误
        if (isset($url['error']) && $url['error']) {
            return $url;
        }

        return $url;
    }

    /**
     * 图片上传
     *
     * @param object $request restapi object
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function file_upload($request)
    {

        $user_id = b2_get_current_user_id();

        if (!$user_id) {
            return array('error' => __('请先登录', 'b2'));
        }

        wp_set_current_user($user_id);

        if (!isset($request['post_id']))
            return array('error' => __('缺少参数', 'b2'));

        if (!$request['post_id'] || !is_numeric($request['post_id'])) {
            return array('error' => __('缺少文章ID', 'b2'));
        }

        if (!isset($request['type']))
            return array('error' => __('请设置一个type', 'b2'));

        if (!in_array($request['type'], b2_file_type()))
            return array('error' => __('不支持这个type', 'b2'));

        //文件体积检查
        if (!isset($_FILES['file']['size'])) {
            return array('error' => sprintf(__('文件损坏，请重新选择（%s）', 'b2'), $_FILES['file']['name']));
        }

        $size = 0;
        $mime = '';
        if (strpos($_FILES['file']['type'], 'image') !== false) {
            $mime = 'image';
            $size = b2_get_option('normal_write', 'write_image_size');
            $text = __('图片', 'b2');
        } elseif (strpos($_FILES['file']['type'], 'video') !== false) {
            $mime = 'video';
            $size = b2_get_option('normal_write', 'write_video_size');
            $text = __('视频', 'b2');
        } else {
            $mime = 'file';
            $size = b2_get_option('normal_write', 'write_file_size');
            $text = __('文件', 'b2');
        }

        if ($_FILES['file']['size'] > $size * 1048576) {
            return array('error' => sprintf(__('%s必须小于%sM，请重新选择', 'b2'), $text, $size));
        }

        // if(!$user_id) return ['error'=>__('无权上传','b2')];
        //检查上传权限
        $role = User::check_user_media_role($user_id, $mime);
        if (!$role)
            return array('error' => sprintf(__('您无权上传%s', 'b2'), $text));

        //检查图片上传数量
        $count = b2_get_option('normal_safe', 'upload_count');

        $has_upload_count = (int) wp_cache_get('b2_upload_limit_' . $user_id, 'b2_upload_limit');

        if ($has_upload_count >= $count && !user_can($user_id, 'manage_options')) {

            return array('error' => __('非法操作', 'b2'));
        }

        $count = 0;

        //如果是认证，检查上传的数量
        if (($request['type'] == 'verify' || $request['type'] == 'avatar') && !$role) {
            $mimetype = b2isImg($_FILES['file']['tmp_name']);
            if ($mimetype) {
                if (!$role) {
                    $count = (int) get_user_meta($user_id, 'b2_verify_upload_count', true);

                    if ($count >= 10)
                        return array('error' => __('非法操作', 'b2'));
                }
            } else {
                return array('error' => __('非法操作', 'b2'));
            }
        }

        wp_cache_set('b2_upload_limit_' . $user_id, ($has_upload_count + 1), 'b2_upload_limit', 600);
        // $mimes = array('gif' => 'image/gif','jpeg'=>'image/jpeg','jpg'=>'image/jpeg','png'=>'image/png');

        // if($request['filetype'] === 'video'){
        //     $mimes = array('mp4'=>'video/mp4', 'asf'=>'video/x-ms-asf','wmv'=>'video/x-ms-wmv', 'avi'=>'video/avi','flv'=>'video/x-flv','mpeg'=>'video/mpeg','ogg'=>'video/ogg','webm'=>'video/webm','3gpp'=>'video/3gpp','3gpp2'=>'video/3gpp2');
        // }

        // if($request['filetype'] === 'file'){
        //     $mimes = array('mp4'=>'video/mp4', 'asf'=>'video/x-ms-asf','wmv'=>'video/x-ms-wmv', 'avi'=>'video/avi','flv'=>'video/x-flv','mpeg'=>'video/mpeg','ogg'=>'video/ogg','webm'=>'video/webm','3gpp'=>'video/3gpp','3gpp2'=>'video/3gpp2');
        // }

        require_once ABSPATH . B2_DS . 'wp-admin' . B2_DS . 'includes' . B2_DS . 'image.php';
        require_once ABSPATH . 'wp-admin' . B2_DS . 'includes' . B2_DS . 'file.php';
        require_once ABSPATH . 'wp-admin' . B2_DS . 'includes' . B2_DS . 'media.php';

        if (!isset($request['file_name'])) {
            $_FILES['file']['name'] = self::rename_filename($_FILES['file']['name'], $request['type'], $request['post_id']);
        } else {
            $_FILES['file']['name'] = $request['file_name'];
        }

        $id = media_handle_upload('file', $request['post_id']);

        if (is_wp_error($id)) {
            return array('error' => sprintf(__('上传失败(%s)：', 'b2'), $_FILES['file']['name']) . $id->get_error_message());
        } else {

            if (!$role) {
                update_user_meta($user_id, 'b2_verify_upload_count', ($count + 1));
            }

            if (isset($request['set_poster']) && get_post_field('post_author', absint($request['set_poster'])) == $user_id) {
                set_post_thumbnail(absint($request['set_poster']), $id);
            }
            return array('id' => $id, 'url' => wp_get_attachment_url($id));
        }

    }
}