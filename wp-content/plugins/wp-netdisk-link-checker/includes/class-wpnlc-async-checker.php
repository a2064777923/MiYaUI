<?php
/**
 * 异步并发检测器类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 异步并发检测器类
 */
class WPNLC_Async_Checker {

    /**
     * 最大并发数
     */
    private $max_concurrent = 10;

    /**
     * 超时时间（秒）
     */
    private $timeout = 15;

    /**
     * 重试次数
     */
    private $max_retries = 3;

    /**
     * 构造函数
     */
    public function __construct() {
        $settings = wpnlc_get_settings();
        $this->max_concurrent = isset($settings['max_concurrent_checks']) ? max(5, min(20, intval($settings['max_concurrent_checks']))) : 10;
        $this->timeout = isset($settings['check_timeout']) ? max(10, min(30, intval($settings['check_timeout']))) : 15;
        $this->max_retries = isset($settings['max_retries']) ? max(1, min(5, intval($settings['max_retries']))) : 3;
    }

    /**
     * 并发检测多个链接
     *
     * @param array $links 链接数组
     * @return array 检测结果
     */
    public function concurrent_check_links($links) {
        if (empty($links)) {
            return array();
        }

        // 添加调试日志
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("WPNLC: 开始并发检测 " . count($links) . " 个链接");
        }

        // 分批处理，避免内存和连接数过多
        $batches = array_chunk($links, $this->max_concurrent);
        $all_results = array();

        foreach ($batches as $batch_index => $batch) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 处理第 " . ($batch_index + 1) . " 批，共 " . count($batch) . " 个链接");
            }

            $batch_results = $this->process_batch($batch);
            $all_results = array_merge($all_results, $batch_results);

            // 批次间添加短暂延迟，避免过于频繁的请求
            if ($batch_index < count($batches) - 1) {
                usleep(500000); // 0.5秒延迟
            }
        }

        return $all_results;
    }

    /**
     * 处理单个批次
     *
     * @param array $links 链接数组
     * @return array 检测结果
     */
    private function process_batch($links) {
        $multi_handle = curl_multi_init();
        $curl_handles = array();
        $results = array();

        // 初始化所有curl句柄
        foreach ($links as $index => $link) {
            $curl_handles[$index] = $this->create_curl_handle($link['url']);
            curl_multi_add_handle($multi_handle, $curl_handles[$index]);
        }

        // 执行并发请求
        $running = null;
        do {
            curl_multi_exec($multi_handle, $running);
            curl_multi_select($multi_handle);
        } while ($running > 0);

        // 收集结果
        foreach ($links as $index => $link) {
            $curl_handle = $curl_handles[$index];
            $response_body = curl_multi_getcontent($curl_handle);
            $http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
            $response_time = curl_getinfo($curl_handle, CURLINFO_TOTAL_TIME);
            $error = curl_error($curl_handle);

            // 处理单个链接结果
            $result = $this->process_single_result($link, $response_body, $http_code, $response_time, $error);
            $results[] = $result;

            // 清理curl句柄
            curl_multi_remove_handle($multi_handle, $curl_handle);
            curl_close($curl_handle);
        }

        curl_multi_close($multi_handle);

        return $results;
    }

    /**
     * 创建curl句柄
     *
     * @param string $url 链接URL
     * @return resource curl句柄
     */
    private function create_curl_handle($url) {
        // 随机选择User-Agent
        $user_agents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        );
        $random_ua = $user_agents[array_rand($user_agents)];

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => $random_ua,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                'Cache-Control: max-age=0',
                'Upgrade-Insecure-Requests: 1'
            ),
            // 设置更大的缓冲区，提高性能
            CURLOPT_BUFFERSIZE => 16384,
            // 只获取头部和部分内容，节省时间和带宽
            CURLOPT_NOBODY => false,
            CURLOPT_RANGE => '0-16384' // 只下载前16KB内容
        ));

        return $ch;
    }

    /**
     * 处理单个链接的检测结果
     *
     * @param array $link 链接信息
     * @param string $response_body 响应内容
     * @param int $http_code HTTP状态码
     * @param float $response_time 响应时间
     * @param string $error curl错误信息
     * @return array 处理后的结果
     */
    private function process_single_result($link, $response_body, $http_code, $response_time, $error) {
        $url = $link['url'];
        $type = $link['type'];

        // 如果有curl错误，尝试重试
        if (!empty($error)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 链接检测出错: $url, 错误: $error");
            }
            
            return $this->retry_check_link($link);
        }

        // 根据网盘类型检测
        switch ($type) {
            case 'baidu':
                return $this->check_baidu_response($url, $response_body, $http_code, $response_time);
            case 'lanzou':
                return $this->check_lanzou_response($url, $response_body, $http_code, $response_time);
            case 'ty':
                return $this->check_ty_response($url, $response_body, $http_code, $response_time);
            case 'weiyun':
                return $this->check_weiyun_response($url, $response_body, $http_code, $response_time);
            case 'aliyun':
                return $this->check_aliyun_response($url, $response_body, $http_code, $response_time);
            case 'quark':
                return $this->check_quark_response($url, $response_body, $http_code, $response_time);
            case 'ctfile':
                return $this->check_ctfile_response($url, $response_body, $http_code, $response_time);
            case '123pan':
                return $this->check_123pan_response($url, $response_body, $http_code, $response_time);
            default:
                return $this->check_generic_response($url, $response_body, $http_code, $response_time);
        }
    }

    /**
     * 重试检测链接（带指数退避）
     *
     * @param array $link 链接信息
     * @return array 检测结果
     */
    private function retry_check_link($link) {
        $url = $link['url'];
        
        for ($i = 1; $i <= $this->max_retries; $i++) {
            // 指数退避：1秒、2秒、4秒
            $delay = pow(2, $i - 1);
            sleep($delay);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("WPNLC: 重试检测链接 $url (第 $i 次)");
            }

            $ch = $this->create_curl_handle($url);
            $response_body = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            $error = curl_error($ch);
            curl_close($ch);

            if (empty($error)) {
                // 重试成功
                return $this->process_single_result($link, $response_body, $http_code, $response_time, '');
            }
        }

        // 所有重试都失败
        return array(
            'url' => $url,
            'type' => $link['type'],
            'status' => 'error',
            'message' => '网络连接失败，已重试 ' . $this->max_retries . ' 次',
            'response_time' => 0,
            'retry_count' => $this->max_retries
        );
    }

    /**
     * 检测百度网盘响应
     */
    private function check_baidu_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'baidu',
                'status' => 'invalid',
                'message' => '无法访问链接（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        // 检测失效关键词
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

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'baidu',
                    'status' => 'invalid',
                    'message' => '链接已失效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'baidu',
            'status' => 'valid',
            'message' => '链接有效',
            'response_time' => $response_time
        );
    }

    /**
     * 检测蓝奏云响应
     */
    private function check_lanzou_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'lanzou',
                'status' => 'invalid',
                'message' => '无法访问文件（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '文件不存在', '文件已删除', '取消了分享', '分享已取消',
            '文件取消分享', '该文件已删除', '链接不存在',
            '分享链接无效', '文件已过期', '分享已过期'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'lanzou',
                    'status' => 'invalid',
                    'message' => '文件已失效',
                    'response_time' => $response_time
                );
            }
        }

        // 检测有效标识
        $valid_indicators = array('点击下载', '立即下载', 'file-info', 'fileinfo', '文件大小', 'down_file');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'lanzou',
                    'status' => 'valid',
                    'message' => '文件有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'lanzou',
            'status' => 'invalid',
            'message' => '无法确认文件有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测天翼云响应
     */
    private function check_ty_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'ty',
                'status' => 'invalid',
                'message' => '无法访问分享（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '分享链接不存在', '分享已失效', '文件不存在', '分享已过期',
            '分享的文件已经被删除', '链接不存在', '文件已删除',
            '该分享不存在', '分享已取消', '链接已失效'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'ty',
                    'status' => 'invalid',
                    'message' => '分享已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array('share-box', 'file-list', 'download-btn', '文件列表', 'cloud189');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'ty',
                    'status' => 'valid',
                    'message' => '分享有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'ty',
            'status' => 'invalid',
            'message' => '无法确认分享有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测微云响应
     */
    private function check_weiyun_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'weiyun',
                'status' => 'invalid',
                'message' => '无法访问分享（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '分享不存在', '已失效', '文件已删除', '分享已过期',
            '链接不存在', '文件不存在', '分享已取消',
            '该分享不存在', '分享链接失效', '分享失效'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'weiyun',
                    'status' => 'invalid',
                    'message' => '分享已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array('mod-body', 'file-item', 'down-btn', '文件大小', 'weiyun.com');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'weiyun',
                    'status' => 'valid',
                    'message' => '分享有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'weiyun',
            'status' => 'invalid',
            'message' => '无法确认分享有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测阿里云盘响应
     */
    private function check_aliyun_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'aliyun',
                'status' => 'invalid',
                'message' => '无法访问分享（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '分享不存在', '已失效', '文件已删除', '分享已取消',
            '分享已过期', '链接不存在', '文件不存在',
            '该分享已失效', '分享链接失效', '资源不存在',
            '啊哦，你访问的页面不存在', '分享的文件已经被删除'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'aliyun',
                    'status' => 'invalid',
                    'message' => '分享已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array(
            'file-list', 'share-file-list', 'download-btn', '文件列表', 'aliyundrive',
            'alipan', 'share-info', 'file-item', 'file-name', 'file-size',
            '保存到网盘', '下载到本地', 'share-detail', 'folder-list', 'preview-content'
        );

        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'aliyun',
                    'status' => 'valid',
                    'message' => '分享有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'aliyun',
            'status' => 'invalid',
            'message' => '无法确认分享有效性（可能已失效或需要验证）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测夸克网盘响应
     */
    private function check_quark_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'quark',
                'status' => 'invalid',
                'message' => '无法访问分享（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '分享不存在', '已失效', '文件已删除', '分享已过期',
            '链接不存在', '文件不存在', '分享已取消',
            '该分享不存在', '分享链接失效', '资源已失效',
            '页面不存在', '分享的资源已删除'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'quark',
                    'status' => 'invalid',
                    'message' => '分享已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array('file-list', 'share-info', 'download', '保存到夸克', 'quark');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'quark',
                    'status' => 'valid',
                    'message' => '分享有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'quark',
            'status' => 'invalid',
            'message' => '无法确认分享有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测城通网盘响应
     */
    private function check_ctfile_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => 'ctfile',
                'status' => 'invalid',
                'message' => '无法访问文件（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '文件不存在', '已删除', '已失效', '文件已过期',
            '链接不存在', '文件已被删除', '下载链接失效',
            '该文件不存在', '文件链接失效', '资源不存在',
            '404 Not Found', '页面不存在'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'ctfile',
                    'status' => 'invalid',
                    'message' => '文件已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array('下载地址', 'download', 'file-info', '文件大小', 'ctfile');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => 'ctfile',
                    'status' => 'valid',
                    'message' => '文件有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => 'ctfile',
            'status' => 'invalid',
            'message' => '无法确认文件有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测123云盘响应
     */
    private function check_123pan_response($url, $body, $http_code, $response_time) {
        if ($http_code !== 200) {
            return array(
                'url' => $url,
                'type' => '123pan',
                'status' => 'invalid',
                'message' => '无法访问分享（HTTP ' . $http_code . '）',
                'response_time' => $response_time
            );
        }

        $invalid_keywords = array(
            '分享不存在', '已失效', '文件已删除', '分享已过期',
            '链接不存在', '文件不存在', '分享已取消',
            '该分享不存在', '分享链接失效', '资源不存在',
            '页面不存在', '分享的文件已经被删除'
        );

        foreach ($invalid_keywords as $keyword) {
            if (strpos($body, $keyword) !== false) {
                return array(
                    'url' => $url,
                    'type' => '123pan',
                    'status' => 'invalid',
                    'message' => '分享已失效',
                    'response_time' => $response_time
                );
            }
        }

        $valid_indicators = array('file-list', 'share-info', 'download', '保存到123云盘', '123pan');
        foreach ($valid_indicators as $indicator) {
            if (strpos($body, $indicator) !== false) {
                return array(
                    'url' => $url,
                    'type' => '123pan',
                    'status' => 'valid',
                    'message' => '分享有效',
                    'response_time' => $response_time
                );
            }
        }

        return array(
            'url' => $url,
            'type' => '123pan',
            'status' => 'invalid',
            'message' => '无法确认分享有效性（可能已失效）',
            'response_time' => $response_time
        );
    }

    /**
     * 检测通用链接响应
     */
    private function check_generic_response($url, $body, $http_code, $response_time) {
        if ($http_code === 200) {
            return array(
                'url' => $url,
                'type' => 'unknown',
                'status' => 'valid',
                'message' => '链接可访问',
                'response_time' => $response_time
            );
        } else {
            return array(
                'url' => $url,
                'type' => 'unknown',
                'status' => 'invalid',
                'message' => '链接无法访问',
                'response_time' => $response_time
            );
        }
    }

    /**
     * 获取并发检测统计信息
     *
     * @param array $results 检测结果
     * @return array 统计信息
     */
    public function get_check_statistics($results) {
        $stats = array(
            'total' => count($results),
            'valid' => 0,
            'invalid' => 0,
            'error' => 0,
            'avg_response_time' => 0,
            'max_response_time' => 0,
            'min_response_time' => PHP_INT_MAX,
            'total_response_time' => 0,
            'retry_count' => 0
        );

        foreach ($results as $result) {
            switch ($result['status']) {
                case 'valid':
                    $stats['valid']++;
                    break;
                case 'invalid':
                    $stats['invalid']++;
                    break;
                case 'error':
                    $stats['error']++;
                    break;
            }

            if (isset($result['response_time'])) {
                $response_time = $result['response_time'];
                $stats['total_response_time'] += $response_time;
                $stats['max_response_time'] = max($stats['max_response_time'], $response_time);
                $stats['min_response_time'] = min($stats['min_response_time'], $response_time);
            }

            if (isset($result['retry_count'])) {
                $stats['retry_count'] += $result['retry_count'];
            }
        }

        if ($stats['total'] > 0) {
            $stats['avg_response_time'] = $stats['total_response_time'] / $stats['total'];
        }

        if ($stats['min_response_time'] === PHP_INT_MAX) {
            $stats['min_response_time'] = 0;
        }

        return $stats;
    }
}