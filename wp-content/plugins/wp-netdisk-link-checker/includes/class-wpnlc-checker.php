<?php
/**
 * 链接检测器类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 链接检测器类
 */
class WPNLC_Checker {

    /**
     * 构造函数
     */
    public function __construct() {
        // 这个类主要提供检测方法，不需要初始化钩子
    }

    /**
     * 检测文章中的网盘链接
     *
     * @param int $post_id 文章ID
     * @param bool $force_check 是否强制检测（忽略缓存）
     * @return array 检测结果
     */
    public function check_post_links($post_id, $force_check = false) {
        $settings = wpnlc_get_settings();
        $cache_hours = $settings['cache_hours'];
        
        // 添加调试日志
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WPNLC: 开始检测文章 $post_id, force_check=" . ($force_check ? 'true' : 'false'));
        }
        
        // 如果强制检测，先清除缓存
        if ($force_check) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 强制检测，清除文章 $post_id 的缓存");
            }
            $this->clear_post_cache($post_id);
        }
        
        // 检查缓存
        if (!$force_check) {
            $database = new WPNLC_Database();

            if ($database->table_exists()) {
                global $wpdb;
                $table_name = $database->get_links_table();
                $cache_date = date('Y-m-d H:i:s', current_time('timestamp') - ($cache_hours * 3600));

                $cached_links = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $table_name
                     WHERE post_id = %d AND last_check > %s
                     ORDER BY last_check DESC",
                    $post_id, $cache_date
                ), ARRAY_A);

                if (!empty($cached_links)) {
                    // 计算整体状态
                    $valid_count = 0;
                    $invalid_count = 0;
                    $no_links_count = 0;

                    foreach ($cached_links as $link) {
                        if ($link['link_status'] === 'valid') {
                            $valid_count++;
                        } elseif ($link['link_status'] === 'invalid') {
                            $invalid_count++;
                        } elseif ($link['link_status'] === 'no_links') {
                            $no_links_count++;
                        }
                    }

                    if ($no_links_count > 0) {
                        $overall_status = 'no_links';
                    } elseif ($valid_count > 0 && $invalid_count > 0) {
                        $overall_status = 'mixed';
                    } elseif ($valid_count > 0) {
                        $overall_status = 'valid';
                    } elseif ($invalid_count > 0) {
                        $overall_status = 'invalid';
                    } else {
                        $overall_status = 'no_links';
                    }

                    // 转换为旧格式以保持兼容性
                    $formatted_links = array();
                    foreach ($cached_links as $link) {
                        if ($link['link_url']) { // 跳过no_links记录
                            $formatted_links[] = array(
                                'url' => $link['link_url'],
                                'type' => $link['link_type'],
                                'status' => $link['link_status'],
                                'message' => $link['status_message'],
                                'source' => $link['link_source']
                            );
                        }
                    }

                    return array(
                        'status' => $overall_status,
                        'links' => $formatted_links,
                        'cached' => true
                    );
                }
            } else {
                $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
                if ($last_check && (current_time('timestamp') - $last_check) < ($cache_hours * 3600)) {
                    // 返回缓存的结果
                    return array(
                        'status' => get_post_meta($post_id, '_wpnlc_link_status', true),
                        'links' => get_post_meta($post_id, '_wpnlc_links_data', true),
                        'cached' => true
                    );
                }
            }
        }
        
        // 获取文章内容
        $post = get_post($post_id);
        if (!$post) {
            return array('status' => 'error', 'message' => '文章不存在');
        }
        
        // 提取网盘链接（从文章内容和B2主题自定义字段）
        $links = $this->extract_netdisk_links($post->post_content);

        // 检查B2主题的下载链接字段
        $b2_links = $this->extract_b2_download_links($post_id);
        if (!empty($b2_links)) {
            $links = array_merge($links, $b2_links);
        }

        if (empty($links)) {
            // 没有找到网盘链接
            $this->save_results_to_new_table($post_id, array(), 'no_links');

            return array(
                'status' => 'no_links',
                'links' => array(),
                'cached' => false
            );
        }
        
        // 检测每个链接 - 优先使用并发检测
        $settings = wpnlc_get_settings();
        $use_concurrent = isset($settings['use_concurrent_check']) ? $settings['use_concurrent_check'] === 'yes' : true;
        
        if ($use_concurrent && count($links) > 1) {
            // 使用并发检测
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 使用并发检测 " . count($links) . " 个链接");
            }
            
            // 确保异步检测器类已加载
            if (!class_exists('WPNLC_Async_Checker')) {
                require_once WPNLC_PLUGIN_DIR . 'includes/class-wpnlc-async-checker.php';
            }
            
            $async_checker = new WPNLC_Async_Checker();
            $link_results = $async_checker->concurrent_check_links($links);
        } else {
            // 使用串行检测（向后兼容）
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 使用串行检测 " . count($links) . " 个链接");
            }
            
            $link_results = array();
            foreach ($links as $index => $link) {
                // 添加延迟避免被网盘服务商限制（除了第一个链接）
                if ($index > 0) {
                    usleep(rand(500000, 1500000)); // 0.5-1.5秒随机延迟
                }
                
                $result = $this->check_single_link($link);
                $link_results[] = $result;
            }
        }
        
        // 统计结果
        $valid_count = 0;
        $invalid_count = 0;
        foreach ($link_results as $result) {
            if ($result['status'] === 'valid') {
                $valid_count++;
            } elseif ($result['status'] === 'invalid') {
                $invalid_count++;
            }
        }
        
        // 确定整体状态
        if ($invalid_count === 0) {
            $overall_status = 'valid';
        } elseif ($valid_count === 0) {
            $overall_status = 'invalid';
        } else {
            $overall_status = 'mixed';
        }
        
        // 保存结果到新表
        $this->save_results_to_new_table($post_id, $link_results, $overall_status);
        
        return array(
            'status' => $overall_status,
            'links' => $link_results,
            'cached' => false
        );
    }

    /**
     * 从文章内容中提取网盘链接
     *
     * @param string $content 文章内容
     * @return array 链接数组
     */
    public function extract_netdisk_links($content) {
        $links = array();
        
        // 网盘链接的正则表达式模式
        $patterns = array(
            // 百度网盘
            '/https?:\/\/(?:pan|yun)\.baidu\.com\/s\/[A-Za-z0-9_-]+/i',
            // 蓝奏云
            '/https?:\/\/[a-zA-Z0-9.-]*lanzou[a-zA-Z0-9.-]*\.com\/[A-Za-z0-9_\/-]+/i',
            // 天翼云盘
            '/https?:\/\/cloud\.189\.cn\/[A-Za-z0-9_\/-]+/i',
            // 微云
            '/https?:\/\/share\.weiyun\.com\/[A-Za-z0-9_-]+/i',
            // 阿里云盘
            '/https?:\/\/(?:www\.)?aliyundrive\.com\/s\/[A-Za-z0-9_-]+/i',
            // 夸克网盘
            '/https?:\/\/(?:pan\.)?(?:quark|qua)\.cn\/s\/[A-Za-z0-9_-]+/i',
            // 城通网盘
            '/https?:\/\/(?:www\.)?ctfile\.com\/[A-Za-z0-9_\/-]+/i',
            // 123云盘
            '/https?:\/\/(?:www\.)?123(?:pan|yunpan)\.com\/s\/[A-Za-z0-9_-]+/i',
            // 迅雷网盘
            '/https?:\/\/pan\.xunlei\.com\/s\/[A-Za-z0-9_-]+/i',
            // UC网盘
            '/https?:\/\/(?:drive|pan)\.uc\.cn\/s\/[A-Za-z0-9_-]+/i',
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[0] as $url) {
                    $links[] = array(
                        'url' => $url,
                        'type' => wpnlc_detect_netdisk_type($url)
                    );
                }
            }
        }
        
        // 去重
        $unique_links = array();
        $seen_urls = array();
        
        foreach ($links as $link) {
            if (!in_array($link['url'], $seen_urls)) {
                $unique_links[] = $link;
                $seen_urls[] = $link['url'];
            }
        }
        
        return $unique_links;
    }

    /**
     * 从B2主题的下载字段中提取网盘链接
     *
     * @param int $post_id 文章ID
     * @return array 链接数组
     */
    public function extract_b2_download_links($post_id) {
        $links = array();

        // 获取B2主题的下载组数据
        $download_group = get_post_meta($post_id, 'b2_single_post_download_group', true);

        if (!empty($download_group) && is_array($download_group)) {
            foreach ($download_group as $download_item) {
                if (isset($download_item['url']) && !empty($download_item['url'])) {
                    $url_content = $download_item['url'];

                    // 解析B2格式：资源名称|下载地址|提取码,解压码
                    $lines = explode("\n", $url_content);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) {
                            continue;
                        }

                        // 分割格式：资源名称|下载地址|提取码,解压码
                        $parts = explode('|', $line);

                        if (count($parts) >= 2) {
                            $url = trim($parts[1]);

                            // 检查是否为网盘链接
                            $netdisk_type = wpnlc_detect_netdisk_type($url);

                            if ($netdisk_type !== 'unknown') {
                                $links[] = array(
                                    'url' => $url,
                                    'type' => $netdisk_type,
                                    'source' => 'b2_download',
                                    'name' => isset($parts[0]) ? trim($parts[0]) : '',
                                    'password' => isset($parts[2]) ? trim($parts[2]) : ''
                                );
                            }
                        }
                    }
                }
            }
        }

        // 也检查旧版本的B2字段格式
        $old_download_links = get_post_meta($post_id, 'b2_download_info', true);
        if (!empty($old_download_links)) {
            $lines = explode("\n", $old_download_links);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $parts = explode('|', $line);

                if (count($parts) >= 2) {
                    $url = trim($parts[1]);
                    $netdisk_type = wpnlc_detect_netdisk_type($url);

                    if ($netdisk_type !== 'unknown') {
                        $links[] = array(
                            'url' => $url,
                            'type' => $netdisk_type,
                            'source' => 'b2_download_old',
                            'name' => isset($parts[0]) ? trim($parts[0]) : '',
                            'password' => isset($parts[2]) ? trim($parts[2]) : ''
                        );
                    }
                }
            }
        }

        // 去重
        $unique_links = array();
        $seen_urls = array();

        foreach ($links as $link) {
            if (!in_array($link['url'], $seen_urls)) {
                $unique_links[] = $link;
                $seen_urls[] = $link['url'];
            }
        }

        return $unique_links;
    }

    /**
     * 检测单个链接
     *
     * @param array $link 链接信息
     * @return array 检测结果
     */
    public function check_single_link($link) {
        $url = $link['url'];
        $type = $link['type'];
        
        // 根据网盘类型选择检测方法
        switch ($type) {
            case 'baidu':
                return $this->check_baidu_link($url);
            case 'lanzou':
                return $this->check_lanzou_link($url);
            case 'ty':
                return $this->check_ty_link($url);
            case 'weiyun':
                return $this->check_weiyun_link($url);
            case 'aliyun':
                return $this->check_aliyun_link($url);
            case 'quark':
                return $this->check_quark_link($url);
            case 'ctfile':
                return $this->check_ctfile_link($url);
            case '123pan':
                return $this->check_123pan_link($url);
            case 'xunlei':
                return $this->check_xunlei_link($url);
            case 'uc':
                return $this->check_uc_link($url);
            default:
                return $this->check_generic_link($url);
        }
    }

    /**
     * 检测百度网盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_baidu_link($url) {
        // 记录调试信息
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WPNLC: 开始检测百度网盘链接: $url");
        }
        
        $response = $this->make_http_request($url);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 百度网盘请求失败: $error_message");
            }
            return array(
                'url' => $url,
                'type' => 'baidu',
                'status' => 'error',
                'message' => $error_message
            );
        }
        
        $body = $response['body'];
        $status_code = $response['response']['code'];
        
        // 添加调试日志
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WPNLC: 检测百度网盘链接 $url, HTTP状态码: $status_code");
            error_log("WPNLC: 页面内容长度: " . strlen($body));
            error_log("WPNLC: 页面内容前200字符: " . mb_substr($body, 0, 200, 'UTF-8'));
        }
        
        // 检查是否为有效链接
        if ($status_code === 200) {
            // 首先检查失效关键词
            $invalid_keywords = array(
                '此链接分享内容可能因为涉及侵权、色情、反动、低俗等信息',
                '啊哦，你来晚了，分享的文件已经被删除了',
                '该分享文件已过期',
                '你所访问的页面不存在了',
                '你点击的某个链接已过期',
                '分享的文件已经被取消了',
                '分享的文件已经被删除了',
                '分享内容可能因为以下原因无法访问',
                '分享的文件不存在',
                '链接失效了，打开百度网盘',
                '此链接已失效',
                '文件不存在',
                '分享已失效',
                '链接不存在',
                '页面不存在',
                '访问错误',
                '分享链接无效',
                '链接已过期',
                '文件已被删除',
                '分享已过期',
                '资源不存在'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '链接已失效';
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("WPNLC: 检测到失效关键词，标记为无效");
                }
            } else {
                // 采用你提供的简化逻辑：没有失效提示就认为有效
                $status = 'valid';
                $message = '链接有效';
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("WPNLC: 未检测到失效关键词，标记为有效");
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问链接（HTTP ' . $status_code . '）';
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 百度网盘HTTP错误: $url, 状态码: $status_code");
            }
        }
        
        return array(
            'url' => $url,
            'type' => 'baidu',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测蓝奏云链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_lanzou_link($url) {
        $response = $this->make_http_request($url);
        
        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'lanzou',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }
        
        $body = $response['body'];
        $status_code = $response['response']['code'];
        
        if ($status_code === 200) {
            // 蓝奏云失效状态关键词检测
            $invalid_keywords = array(
                '文件不存在', '文件已删除', '取消了分享', '分享已取消',
                '文件取消分享', '该文件已删除', '链接不存在',
                '分享链接无效', '文件已过期', '分享已过期'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '文件已失效';
            } else {
                // 检测是否包含下载按钮或文件信息
                if (strpos($body, '点击下载') !== false || 
                    strpos($body, '立即下载') !== false || 
                    strpos($body, 'file-info') !== false ||
                    strpos($body, 'fileinfo') !== false ||
                    strpos($body, '文件大小') !== false ||
                    strpos($body, 'down_file') !== false) {
                    $status = 'valid';
                    $message = '文件有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认文件有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问文件（HTTP ' . $status_code . '）';
        }
        
        return array(
            'url' => $url,
            'type' => 'lanzou',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测天翼云盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_ty_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'ty',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 天翼云盘失效状态关键词检测
            $invalid_keywords = array(
                '分享链接不存在', '分享已失效', '文件不存在', '分享已过期',
                '分享的文件已经被删除', '链接不存在', '文件已删除',
                '该分享不存在', '分享已取消', '链接已失效'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                if (strpos($body, 'share-box') !== false || 
                    strpos($body, 'file-list') !== false || 
                    strpos($body, 'download-btn') !== false ||
                    strpos($body, '文件列表') !== false ||
                    strpos($body, 'cloud189') !== false) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'ty',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测微云链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_weiyun_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'weiyun',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 微云失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已过期',
                '链接不存在', '文件不存在', '分享已取消',
                '该分享不存在', '分享链接失效', '分享失效'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                if (strpos($body, 'mod-body') !== false || 
                    strpos($body, 'file-item') !== false || 
                    strpos($body, 'down-btn') !== false ||
                    strpos($body, '文件大小') !== false ||
                    strpos($body, 'weiyun.com') !== false) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'weiyun',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测阿里云盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_aliyun_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'aliyun',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 阿里云盘失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已取消',
                '分享已过期', '链接不存在', '文件不存在',
                '该分享已失效', '分享链接失效', '资源不存在',
                '啊哦，你访问的页面不存在', '分享的文件已经被删除'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 增强的积极验证：检测阿里云盘有效内容标识
                $valid_indicators = array(
                    'file-list',              // 文件列表
                    'share-file-list',        // 分享文件列表
                    'download-btn',           // 下载按钮
                    '文件列表',               // 中文文件列表
                    'aliyundrive',            // 阿里云盘标识
                    'alipan',                 // 阿里网盘标识
                    'share-info',             // 分享信息
                    'file-item',              // 文件项目
                    'file-name',              // 文件名
                    'file-size',              // 文件大小
                    '保存到网盘',             // 保存按钮
                    '下载到本地',             // 下载按钮
                    'share-detail',           // 分享详情
                    'folder-list',            // 文件夹列表
                    'preview-content'         // 预览内容
                );
                
                $has_valid_content = false;
                foreach ($valid_indicators as $indicator) {
                    if (strpos($body, $indicator) !== false) {
                        $has_valid_content = true;
                        break;
                    }
                }
                
                if ($has_valid_content) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效或需要验证）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'aliyun',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测夸克网盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_quark_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'quark',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 夸克网盘失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已过期',
                '链接不存在', '文件不存在', '分享已取消',
                '该分享不存在', '分享链接失效', '资源已失效',
                '页面不存在', '分享的资源已删除'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                if (strpos($body, 'file-list') !== false || 
                    strpos($body, 'share-info') !== false || 
                    strpos($body, 'download') !== false ||
                    strpos($body, '保存到夸克') !== false ||
                    strpos($body, 'quark') !== false) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'quark',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测城通网盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_ctfile_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'ctfile',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 城通网盘失效状态关键词检测
            $invalid_keywords = array(
                '文件不存在', '已删除', '已失效', '文件已过期',
                '链接不存在', '文件已被删除', '下载链接失效',
                '该文件不存在', '文件链接失效', '资源不存在',
                '404 Not Found', '页面不存在'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '文件已失效';
            } else {
                // 检测是否包含有效文件的标识
                if (strpos($body, '下载地址') !== false || 
                    strpos($body, 'download') !== false || 
                    strpos($body, 'file-info') !== false ||
                    strpos($body, '文件大小') !== false ||
                    strpos($body, 'ctfile') !== false) {
                    $status = 'valid';
                    $message = '文件有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认文件有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问文件（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'ctfile',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测123云盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_123pan_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => '123pan',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 123云盘失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已过期',
                '链接不存在', '文件不存在', '分享已取消',
                '该分享不存在', '分享链接失效', '资源不存在',
                '页面不存在', '分享的文件已经被删除'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                if (strpos($body, 'file-list') !== false || 
                    strpos($body, 'share-info') !== false || 
                    strpos($body, 'download') !== false ||
                    strpos($body, '保存到123云盘') !== false ||
                    strpos($body, '123pan') !== false) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => '123pan',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测迅雷网盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_xunlei_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'xunlei',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // 迅雷网盘失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已过期',
                '链接不存在', '文件不存在', '分享已取消',
                '该分享不存在', '分享链接失效', '资源不存在',
                '页面不存在', '分享的文件已经被删除', '文件已过期',
                '分享已被取消', '链接已失效', '资源已失效',
                '啊哦，你访问的页面不存在', '404 Not Found'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                $valid_indicators = array(
                    'file-list',              // 文件列表
                    'share-info',             // 分享信息
                    'download',               // 下载按钮
                    '保存到迅雷网盘',         // 保存按钮
                    '下载到本地',             // 下载按钮
                    'file-item',              // 文件项目
                    'file-name',              // 文件名
                    'file-size',              // 文件大小
                    'xunlei',                 // 迅雷标识
                    'pan.xunlei',             // 迅雷网盘标识
                    'share-detail',           // 分享详情
                    'folder-list',            // 文件夹列表
                    'share-file',             // 分享文件
                    'transfer-btn',           // 转存按钮
                    'preview-content'         // 预览内容
                );
                
                $has_valid_content = false;
                foreach ($valid_indicators as $indicator) {
                    if (strpos($body, $indicator) !== false) {
                        $has_valid_content = true;
                        break;
                    }
                }
                
                if ($has_valid_content) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效或需要验证）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'xunlei',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测UC网盘链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_uc_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'uc',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $body = $response['body'];
        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            // UC网盘失效状态关键词检测
            $invalid_keywords = array(
                '分享不存在', '已失效', '文件已删除', '分享已过期',
                '链接不存在', '文件不存在', '分享已取消',
                '该分享不存在', '分享链接失效', '资源不存在',
                '页面不存在', '分享的文件已经被删除', '文件已过期',
                '分享已被取消', '链接已失效', '资源已失效',
                '啊哦，你访问的页面不存在', '404 Not Found'
            );
            
            $is_invalid = false;
            foreach ($invalid_keywords as $keyword) {
                if (strpos($body, $keyword) !== false) {
                    $is_invalid = true;
                    break;
                }
            }
            
            if ($is_invalid) {
                $status = 'invalid';
                $message = '分享已失效';
            } else {
                // 检测是否包含有效分享的标识
                $valid_indicators = array(
                    'file-list',              // 文件列表
                    'share-info',             // 分享信息
                    'download',               // 下载按钮
                    '保存到UC网盘',           // 保存按钮
                    '下载到本地',             // 下载按钮
                    'file-item',              // 文件项目
                    'file-name',              // 文件名
                    'file-size',              // 文件大小
                    'uc.cn',                  // UC标识
                    'drive.uc',               // UC网盘标识
                    'share-detail',           // 分享详情
                    'folder-list',            // 文件夹列表
                    'share-file',             // 分享文件
                    'transfer-btn',           // 转存按钮
                    'preview-content'         // 预览内容
                );
                
                $has_valid_content = false;
                foreach ($valid_indicators as $indicator) {
                    if (strpos($body, $indicator) !== false) {
                        $has_valid_content = true;
                        break;
                    }
                }
                
                if ($has_valid_content) {
                    $status = 'valid';
                    $message = '分享有效';
                } else {
                    $status = 'invalid';
                    $message = '无法确认分享有效性（可能已失效或需要验证）';
                }
            }
        } else {
            $status = 'invalid';
            $message = '无法访问分享（HTTP ' . $status_code . '）';
        }

        return array(
            'url' => $url,
            'type' => 'uc',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 检测通用链接
     *
     * @param string $url 链接URL
     * @return array 检测结果
     */
    private function check_generic_link($url) {
        $response = $this->make_http_request($url);

        if (is_wp_error($response)) {
            return array(
                'url' => $url,
                'type' => 'unknown',
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }

        $status_code = $response['response']['code'];

        if ($status_code === 200) {
            $status = 'valid';
            $message = '链接可访问';
        } else {
            $status = 'invalid';
            $message = '链接无法访问';
        }

        return array(
            'url' => $url,
            'type' => 'unknown',
            'status' => $status,
            'message' => $message
        );
    }

    /**
     * 发起HTTP请求（使用CURL，参考你提供的有效代码）
     *
     * @param string $url 请求URL
     * @return array|WP_Error 响应结果
     */
    private function make_http_request($url) {
        // 验证URL格式和安全性
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', '无效的URL格式');
        }
        
        // 防止SSRF攻击 - 阻止内网IP访问
        $parsed_url = parse_url($url);
        if (!$parsed_url || !isset($parsed_url['host'])) {
            return new WP_Error('invalid_url', '无效的URL格式');
        }
        
        $host = $parsed_url['host'];
        
        // 检查是否为内网IP
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return new WP_Error('blocked_ip', '不允许访问内网地址');
            }
        }
        
        // 检查是否为本地主机
        if (in_array(strtolower($host), array('localhost', '127.0.0.1', '::1'))) {
            return new WP_Error('blocked_host', '不允许访问本地主机');
        }
        
        // 随机选择现代浏览器User-Agent
        $user_agents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        );
        $random_ua = $user_agents[array_rand($user_agents)];
        
        // 使用CURL，模拟你提供的有效代码
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, $random_ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1'
        ));
        
        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: CURL错误: $error");
            }
            return new WP_Error('curl_error', $error);
        }
        
        if ($http_code >= 400) {
            return new WP_Error('http_error', sprintf('HTTP错误: %d', $http_code));
        }
        
        // 构造类似wp_remote_get的返回格式
        return array(
            'response' => array('code' => $http_code),
            'body' => $body
        );
    }

    /**
     * 批量检测多篇文章
     *
     * @param array $post_ids 文章ID数组
     * @param bool $force_check 是否强制检测
     * @return array 检测结果
     */
    public function batch_check_posts($post_ids, $force_check = false) {
        $results = array();

        foreach ($post_ids as $post_id) {
            $results[$post_id] = $this->check_post_links($post_id, $force_check);

            // 避免过于频繁的请求
            usleep(200000); // 0.2秒延迟
        }

        return $results;
    }

    /**
     * 获取需要检测的文章
     *
     * @param int $limit 限制数量
     * @return array 文章ID数组
     */
    public function get_posts_to_check($limit = 10) {
        $settings = wpnlc_get_settings();
        $check_post_type = $settings['check_posts'];
        $cache_hours = $settings['cache_hours'];
        
        // 智能检测间隔
        $use_smart_intervals = isset($settings['enable_smart_intervals']) ? $settings['enable_smart_intervals'] === 'yes' : false;

        global $wpdb;
        $database = new WPNLC_Database();

        // 构建查询条件 - 修复SQL注入漏洞
        if ($check_post_type === 'all') {
            $post_types = get_post_types(array('public' => true));
            // 安全地创建占位符
            $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
            $post_type_condition = $wpdb->prepare("AND post_type IN ($placeholders)", $post_types);
        } else {
            $post_type_condition = $wpdb->prepare("AND post_type = %s", $check_post_type);
        }

        if ($database->table_exists()) {
            // 使用新表查询
            $table_name = $database->get_links_table();
            
            // 智能缓存时间计算
            if ($use_smart_intervals) {
                $cache_conditions = $this->get_smart_cache_conditions($settings);
            } else {
                $cache_date = date('Y-m-d H:i:s', current_time('timestamp') - ($cache_hours * 3600));
                $cache_conditions = "AND (l.last_check IS NULL OR l.last_check < '$cache_date')";
            }

            // 安全地构建查询 - 直接在prepare中处理所有参数
            $query_base = "SELECT p.ID
                         FROM $wpdb->posts p
                         LEFT JOIN $table_name l ON p.ID = l.post_id
                         WHERE p.post_status = 'publish'";
            
            if ($check_post_type === 'all') {
                $post_types = get_post_types(array('public' => true));
                $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
                $query = $query_base . " AND p.post_type IN ($placeholders)
                         $cache_conditions
                         GROUP BY p.ID
                         ORDER BY p.post_date DESC
                         LIMIT %d";
                $params = array_merge($post_types, array($limit));
                $post_ids = $wpdb->get_col($wpdb->prepare($query, $params));
            } else {
                $query = $query_base . " AND p.post_type = %s
                         $cache_conditions
                         GROUP BY p.ID
                         ORDER BY p.post_date DESC
                         LIMIT %d";
                $post_ids = $wpdb->get_col($wpdb->prepare($query, $check_post_type, $limit));
            }
        } else {
            // 使用旧的meta表查询
            $cache_timestamp = current_time('timestamp') - ($cache_hours * 3600);

            // 安全地构建查询 - 统一使用prepare处理所有参数
            if ($check_post_type === 'all') {
                $post_types = get_post_types(array('public' => true));
                $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
                $query = "SELECT p.ID
                         FROM $wpdb->posts p
                         LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                         WHERE p.post_status = 'publish'
                         AND p.post_type IN ($placeholders)
                         AND (pm.meta_value IS NULL OR pm.meta_value < %d)
                         ORDER BY p.post_date DESC
                         LIMIT %d";
                $params = array_merge($post_types, array($cache_timestamp, $limit));
                $post_ids = $wpdb->get_col($wpdb->prepare($query, $params));
            } else {
                $post_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT p.ID
                     FROM $wpdb->posts p
                     LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                     WHERE p.post_status = 'publish'
                     AND p.post_type = %s
                     AND (pm.meta_value IS NULL OR pm.meta_value < %d)
                     ORDER BY p.post_date DESC
                     LIMIT %d",
                    $check_post_type,
                    $cache_timestamp,
                    $limit
                ));
            }
        }

        return $post_ids;
    }

    /**
     * 保存检测结果到新的数据表
     *
     * @param int $post_id 文章ID
     * @param array $link_results 链接检测结果
     * @param string $overall_status 整体状态
     */
    private function save_results_to_new_table($post_id, $link_results, $overall_status) {
        global $wpdb;

        $database = new WPNLC_Database();
        $table_name = $database->get_links_table();

        // 检查新表是否存在
        if (!$database->table_exists()) {
            // 如果新表不存在，回退到旧的meta方式
            update_post_meta($post_id, '_wpnlc_link_status', $overall_status);
            update_post_meta($post_id, '_wpnlc_links_data', $link_results);
            update_post_meta($post_id, '_wpnlc_last_check', current_time('timestamp'));

            if (!empty($link_results)) {
                update_post_meta($post_id, '_wpnlc_link_url', $link_results[0]['url']);
            }
            return;
        }

        $post = get_post($post_id);
        $post_title = $post ? $post->post_title : '';
        $current_time = current_time('mysql');

        // 删除该文章的旧记录
        $wpdb->delete(
            $table_name,
            array('post_id' => $post_id),
            array('%d')
        );

        // 插入新的链接记录
        foreach ($link_results as $link) {
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'post_title' => $post_title,
                    'link_url' => $link['url'],
                    'link_type' => $link['type'],
                    'link_source' => isset($link['source']) ? $link['source'] : 'content',
                    'link_status' => $link['status'],
                    'status_message' => $link['message'],
                    'response_code' => isset($link['response_code']) ? $link['response_code'] : null,
                    'response_time' => isset($link['response_time']) ? $link['response_time'] : null,
                    'last_check' => $current_time,
                    'check_count' => 1
                ),
                array(
                    '%d', // post_id
                    '%s', // post_title
                    '%s', // link_url
                    '%s', // link_type
                    '%s', // link_source
                    '%s', // link_status
                    '%s', // status_message
                    '%d', // response_code
                    '%f', // response_time
                    '%s', // last_check
                    '%d'  // check_count
                )
            );
        }

        // 如果没有找到链接，插入一条no_links记录
        if (empty($link_results)) {
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'post_title' => $post_title,
                    'link_url' => '',
                    'link_type' => 'none',
                    'link_source' => 'content',
                    'link_status' => 'no_links',
                    'status_message' => '未找到网盘链接',
                    'last_check' => $current_time,
                    'check_count' => 1
                ),
                array(
                    '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
                )
            );
        }
    }

    /**
     * 清除文章的检测缓存
     *
     * @param int $post_id 文章ID
     */
    private function clear_post_cache($post_id) {
        global $wpdb;
        $database = new WPNLC_Database();
        
        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            $wpdb->delete($table_name, array('post_id' => $post_id), array('%d'));
        }
        
        // 同时清除旧的meta缓存
        delete_post_meta($post_id, '_wpnlc_link_status');
        delete_post_meta($post_id, '_wpnlc_links_data');
        delete_post_meta($post_id, '_wpnlc_last_check');
        delete_post_meta($post_id, '_wpnlc_link_url');
    }

    /**
     * 获取智能缓存条件
     *
     * @param array $settings 设置数组
     * @return string SQL条件
     */
    private function get_smart_cache_conditions($settings) {
        $valid_days = isset($settings['valid_link_recheck_days']) ? intval($settings['valid_link_recheck_days']) : 7;
        $invalid_days = isset($settings['invalid_link_recheck_days']) ? intval($settings['invalid_link_recheck_days']) : 1;
        
        $valid_cache_date = date('Y-m-d H:i:s', current_time('timestamp') - ($valid_days * 24 * 3600));
        $invalid_cache_date = date('Y-m-d H:i:s', current_time('timestamp') - ($invalid_days * 24 * 3600));
        
        return "AND (
            l.last_check IS NULL OR 
            (l.link_status = 'valid' AND l.last_check < '$valid_cache_date') OR
            (l.link_status = 'invalid' AND l.last_check < '$invalid_cache_date') OR
            (l.link_status = 'mixed' AND l.last_check < '$invalid_cache_date') OR
            (l.link_status NOT IN ('valid', 'invalid', 'mixed') AND l.last_check < '$valid_cache_date')
        )";
    }
}
