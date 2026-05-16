<?php
namespace XHTheme\AIToolbox;

final class XHAiimage
{

    private static $instance;
    private $XHCron;
    private $XHAi;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->XHAi = XHThemeAi::getInstance();
        $this->XHCron = XHCronQueue::getInstance();
    }


    private function init()
    {
        add_action('rest_after_insert_post', [$this, 'generate_aiimages'], 10, 1);
        add_filter('xhaitoolbox_cronitem_imageword', [$this, 'process_imageword'], 10, 3);
        add_filter('xhaitoolbox_cronitem_getimage', [$this, 'process_getimage'], 10, 3);
    }

    /**
     * 获取图片尺寸配置
     * 
     * @param string $pattern 平台类型: default, aliyun, cogview
     */
    private function get_imagesize($pattern = 'default',$separator = 'x')
    {
        $imageSize = xh_option('imageSize', '1280x768');
        $parts = explode('x', strtolower($imageSize));
        $width  = isset($parts[0]) ? (int) $parts[0] : 1280;
        $height = isset($parts[1]) ? (int) $parts[1] : 768;
        $imageType = 'square';
        if ($width > $height) {
            $imageType = 'landscape';
        } elseif ($width < $height) {
            $imageType = 'portrait';
        }
        $pattermArgs = [
            'default' => [
                'landscape' => [1280,720],
                'portrait'  => [720,1280],
                'square'    => [1280,1280]
            ],
            'qwenimage' => [
                'landscape' => [1664,928],
                'portrait'  => [928,1664],
                'square'    => [1328,1328]
            ]
        ];
        $patternSize = isset($pattermArgs[$pattern]) ? $pattermArgs[$pattern] : $pattermArgs['default'];
        return join($separator,$patternSize[$imageType]);
    }

    /**
     * 生成图片提示词任务
     */
    public function process_imageword($CronItem, $cronType, $postId)
    {
        if (empty($postId)) {
            $CronItem['status'] = 'error';
            $CronItem['message'] = esc_html__('Invalid parameters [code:003]', 'xhtheme-ai-toolbox');
            return $CronItem;
        }

        $config = isset($CronItem['data']['config']) && is_array($CronItem['data']['config']) ? $CronItem['data']['config'] : [];

        /**
         * 分类过滤
         */
        if ($config && !isset($config['taskStatus']) && isset($config['categories'])) {
            $filter_category = xh_loop_filter_category($postId, $config['categories']);
            if (!$filter_category) {
                $CronItem['status'] = 'skip';
                $CronItem['message'] = esc_html__('Category not match', 'xhtheme-ai-toolbox');
                // 跳过判断文章是否需要发布
                xh_loop_auto_publish($postId, $CronItem['cron_id']);
                return $CronItem;
            }
        }

        $appid = xh_option('appId', '');
        if (!$appid) {
            $this->XHAi->modelError('appId', '');
            return $CronItem;
        }
        $post = get_post($postId);
        if (!$post) {
            $CronItem['status'] = 'error';
            return $CronItem;
        }
        $aiimageword = get_post_meta($postId, '_aiimage_cueword', true);
        if (!$aiimageword) {
            $apiArgs = isset($CronItem['data']) && is_array($CronItem['data']) ? $CronItem['data'] : [];
            $respapi = wp_remote_post($this->XHAi->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $appid,
                    'Referer' => home_url()
                ],
                'body' => json_encode([
                    'type' => 'post-aiimage',
                    'content' => xh_filterContent($post->post_content, $post),
                    'posttitle' => $post->post_title,
                    'posttype' => $post->post_type,
                    'language' => xh_post_language($post->ID),
                    'stream' => false,
                    'backstage' => true,
                    'imagerec' => xh_option('imageRecognition', true),
                    'model' => xh_option('modelType', 'auto'),
                    'version' => $this->XHAi->getVersion(),
                    'timestamp' => time(),
                    'args' => $apiArgs
                ]),
                'timeout' => XHTHEME_AI_TOOLBOX_APITIMEOUT,
                'sslverify' => false
            ]);
            if (!is_wp_error($respapi)) {
                $body = wp_remote_retrieve_body($respapi);
                $response_data = json_decode($body, true);
                if (isset($response_data['code']) && $response_data['code'] == 0) {
                    $aidata = $response_data['data']['aidata'];
                    $content = $aidata['content'];
                    if ($content) {
                        $aiimageword = $content;
                        update_post_meta($postId, '_aiimage_cueword', $aiimageword);
                    }
                }
            }
        }

        if (!$aiimageword) {
            $CronItem['status'] = 'error';
            $CronItem['message'] = esc_html__('Failed to generate image prompt!', 'xhtheme-ai-toolbox');
            return $CronItem;
        }

        /**
         * 提交生图任务
         */
        $imagePattern = xh_option('imagePlatform', 'default');
        if (!in_array($imagePattern, ['default', 'aliyun', 'cogview'])) {
            $imagePattern = 'default';
        }
        $apires = $this->imageapi_create($aiimageword, $imagePattern);
        if ($apires['success']) {
            update_post_meta($postId, '_aiimage_status', -1);
            delete_post_meta($postId, '_aiimage_cueword');
            // 不触发队列
            if (isset($apires['notask']) && $apires['notask']) {
                $this->process_post($CronItem, $postId, $apires['imageUrl'], 'imageword');
            } else {
                $Cronrow = $this->XHCron->getrow('getimage' . '_' . $postId);
                if (!$Cronrow) {
                    $taskData = [
                        'task_id' => $apires['task_id'],
                        'pattern' => $imagePattern
                    ];
                    if ($config && isset($config['taskStatus'])) {
                        $taskData['config']['taskStatus'] = 1;
                    }
                    $this->XHCron->insert($postId, 'getimage', $taskData, 3);
                }
            }
            $CronItem['status'] = 'success';
        } else {
            $errorNum = (int) $CronItem['errornum'];
            $errorNum = $errorNum + 1;
            $CronItem['errornum'] = $errorNum;
            if (isset($apires['type']) && $apires['type'] == 1) {
                $CronItem['status'] = 'error';
                delete_post_meta($postId, '_aiimage_cueword');
            }
            if (isset($apires['msg']) && !empty($apires['msg'])) {
                $CronItem['message'] = $apires['msg'];
            }
        }

        return $CronItem;
    }

    /**
     * 拉取图片生成结果
     */
    public function process_getimage($CronItem, $cronType, $postId)
    {
        $task_id = $CronItem['data']['task_id'];
        $imagePattern = isset($CronItem['data']['pattern']) ? $CronItem['data']['pattern'] : 'default';
        $getImage = $this->imageapi_get($task_id, $imagePattern);
        if ($getImage['success']) {
            $CronItem['status'] = 'success';
            /**
             * 执行后续的图片更新操作
             */
            $this->process_post($CronItem, $postId, $getImage['imageUrl'], 'getimage');
        } else {
            $errorNum = (int) $CronItem['errornum'];
            $errorNum = $errorNum + 1;
            $CronItem['errornum'] = $errorNum;
            if (isset($getImage['type'])) {
                if ($getImage['type'] == 1) {
                    $CronItem['status'] = 'error';
                } elseif ($getImage['type'] == 2) {
                    $CronItem['status'] = 'hold';
                }
            }
            if (isset($getImage['msg']) && !empty($getImage['msg'])) {
                $CronItem['message'] = $getImage['msg'];
            }
        }
        return $CronItem;
    }

    /**
     * 处理文章
     */
    public function process_post($CronItem, $postId, $imageUrl, $cronType = 'getimage')
    {
        try {
            $result = $this->push_postimage($postId, $imageUrl);

            // 检查是否有待机任务进行状态变更
            if (isset($CronItem['data']['config']['taskStatus'])) {
                xh_check_hold_task($cronType, $postId);
            }

            // 自动发布检查
            xh_loop_auto_publish($postId, $CronItem['cron_id']);

            if ($result === false) {
                do_action('xhaitoolbox_log_error', 'Failed to push image for postId:' . $postId . '- Image URL: ' . $imageUrl);
                $admin_email = get_option('admin_email');
                if ($admin_email) {
                    wp_mail(
                        $admin_email,
                        __('AI Image Generation Failed Notification', 'xhtheme-ai-toolbox'),
                        '[XHAiimage] Failed to push image for postId:' . $postId . '- Image URL: ' . $imageUrl . ' Error Code:1'
                    );
                }
            }
        } catch (\Exception $e) {
            do_action('xhaitoolbox_log_error', 'Error processing image for post ' . $postId . ': ' . $e->getMessage());
            $admin_email = get_option('admin_email');
            if ($admin_email) {
                wp_mail(
                    $admin_email,
                    __('AI Image Generation Failed Notification', 'xhtheme-ai-toolbox'),
                    '[XHAiimage] Failed to push image for postId:' . $postId . '- Image URL: ' . $imageUrl . ' Error Code:2'
                );
            }
            return false;
        }
    }

    /**
     * 将图片保存到媒体库并设置为文章特色图像
     */
    public function push_postimage($post_id, $image_url)
    {
        // 检查参数有效性
        if (empty($post_id) || empty($image_url)) {
            return false;
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $imageCompress = xh_option('imageCompress', true);
        $old_image_url = $image_url;
        if ($imageCompress) {
            $image_url = XHTHEME_AI_TOOLBOX_IMAGE_COMPRESS_URL . urlencode($image_url);
        }

        // 下载图片到临时文件
        $tmp = download_url($image_url);
        if (is_wp_error($tmp)) {
            if (!$imageCompress) {
                return false;
            }
            do_action('xhaitoolbox_log_error', 'Failed to download image:' . $image_url);
            if (!get_transient('xh_ai_image_compress_error')) {
                set_transient('xh_ai_image_compress_error', 1, 600);
                $admin_email = get_option('admin_email');
                if ($admin_email) {
                    wp_mail($admin_email, __('AI Image Generation Failed Notification', 'xhtheme-ai-toolbox'), '[XHAiimage] Failed to download image:' . $image_url);
                }
            }
            $image_url = $old_image_url;
            $tmp = download_url($image_url);
            if (is_wp_error($tmp)) {
                return false;
            }
        }

        // 获取文件扩展名
        $url_parts = wp_parse_url($tmp);
        $path = $url_parts['path'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $ext = $ext ? '.' . $ext : '';
        // 使用当前时间的MD5值作为文件名
        $filename = md5(time()) . $ext;

        // 准备文件数组
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $tmp
        );

        // 将图片保存到媒体库
        $attachment_id = media_handle_sideload($file_array, $post_id);
        wp_delete_file($tmp);
        if (is_wp_error($attachment_id)) {
            return false;
        }
        return $this->handle_image_pattern($post_id, $attachment_id);
    }

    protected function handle_image_pattern($post_id, $attachment_id, $restImage = true)
    {
        $imagePattern = xh_option('imagePattern', 'thumb');
        if ($imagePattern !== 'only') {
            set_post_thumbnail($post_id, $attachment_id);
        }
        $inputImage = false;
        $updatePost = [
            'ID' => $post_id,
            'meta_input' => [
                '_aiimage_status' => -2
            ]
        ];
        if ($imagePattern == 'fill' || $imagePattern == 'only') {
            $mapnumber = (int) xh_option('imageMapnum', 1);
            if (!$restImage) {
                $mapnumber = 1;
            }
            $post = get_post($post_id);
            $content = $post->post_content;            
            if (strpos($content, '<img') === false) {
                $image_url = wp_get_attachment_url($attachment_id);
                if (has_blocks($content) || empty(trim($content))) {
                    // 区块编辑器处理
                    $figureHtml = sprintf('<figure class="wp-block-image size-full"><img src="%s" alt="%s" class="wp-image-%s"/></figure>', $image_url, $post->post_title, $attachment_id);
                    $figureBlock = array(
                        'blockName' => 'core/image',
                        'attrs' => [
                            'id' => $attachment_id,
                            'sizeSlug' => 'full',
                            'linkDestination' => 'none'
                        ],
                        'innerBlocks' => [],
                        'innerHTML' => [$figureHtml],
                        'innerContent' => [$figureHtml]
                    );
                    $new_blocks = [];
                    if (empty(trim($content))) {
                        $new_blocks[] = $figureBlock;
                        $inputImage = true;
                    } else {
                        $blocks = parse_blocks($content);
                        $paragraph_count = 0;
                        if (!empty($blocks) && is_array($blocks)) {
                            foreach ($blocks as $block) {
                                $new_blocks[] = $block;
                                if ($block['blockName'] === 'core/paragraph') {
                                    $paragraph_count++;
                                    if ($paragraph_count === $mapnumber) {
                                        $new_blocks[] = $figureBlock;
                                        $inputImage = true;
                                    }
                                }
                            }
                        }


                        if (!$inputImage) {
                            $mapnumber = $paragraph_count > 0 ? $paragraph_count - 1 : 0;
                            if (!$mapnumber) {
                                $new_blocks[] = $figureBlock;
                                $inputImage = true;
                            } else {
                                $paragraph_count = 0;
                                foreach ($blocks as $block) {
                                    $new_blocks[] = $block;
                                    if ($block['blockName'] === 'core/paragraph') {
                                        $paragraph_count++;
                                        if ($paragraph_count === $mapnumber) {
                                            $new_blocks[] = $figureBlock;
                                            $inputImage = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $content = serialize_blocks($new_blocks);
                } else {
                    // 经典编辑器处理
                    $paragraph_count = 0;
                    $new_content = '';
                    $image_html = wp_get_attachment_image($attachment_id, 'full');
                    $image_html = str_replace('alt=""', 'alt="' . $post->post_title . '"', $image_html);
                    if (strpos($content, '<p') !== false) {
                        $paragraphs = explode('<p', $content);
                        foreach ($paragraphs as $index => $paragraph) {
                            if (!empty(trim($paragraph))) {
                                $new_content .= '<p' . $paragraph;
                                $paragraph_count++;

                                if ($paragraph_count === $mapnumber) {
                                    $new_content .= $image_html;
                                    $inputImage = true;
                                }
                            }
                        }
                    } else {
                        $paragraphs = explode("\n", $content);
                        foreach ($paragraphs as $index => $line) {
                            if (!empty(trim($line))) {
                                $new_content .= $line . "\n";
                                $paragraph_count++;
                                if ($paragraph_count === $mapnumber) {
                                    $new_content .= $image_html . "\n";
                                    $inputImage = true;
                                }
                            }
                        }
                    }

                    if (!$inputImage) {
                        $new_content .= $image_html;
                        $inputImage = true;
                    }

                    $content = $new_content;
                }
                $updatePost['post_content'] = $content;
            }
        }
        if ($inputImage) {
            return wp_update_post($updatePost);
        }
        if ($restImage) {
            return $this->handle_image_pattern($post_id, $attachment_id, false);
        }
        return false;
    }

    public function imageapi_get($task_id, $imagePattern = 'default')
    {
        if (empty($task_id)) {
            return ['success' => false, 'msg' => __('Task ID is empty, cannot get image!', 'xhtheme-ai-toolbox')];
        }

        if ($imagePattern == 'aliyun') {
            $aliapikey = xh_option('imageApikey');
            if (!$aliapikey) {
                return ['success' => false, 'msg' => __('Please configure the Bailian Platform API-KEY first!', 'xhtheme-ai-toolbox')];
            }

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $aliapikey,
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            );
            $response = wp_remote_get('https://dashscope.aliyuncs.com/api/v1/tasks/' . $task_id, $args);
            if (is_wp_error($response)) {
                return ['success' => false, 'msg' => $response->get_error_message(), 'type' => 1];
            }
            $body = wp_remote_retrieve_body($response);
            $resData = json_decode($body, true);
            if (isset($resData['output']['task_status'])) {
                switch ($resData['output']['task_status']) {
                    case 'PENDING':
                    case 'RUNNING':
                        return ['success' => false, 'msg' => __('Model task in progress, waiting for next query!', 'xhtheme-ai-toolbox'), 'type' => 2];
                    case 'SUCCEEDED':
                        $imageUrl = isset($resData['output']['results'][0]['url']) ? $resData['output']['results'][0]['url'] : '';
                        if (empty($imageUrl)) {
                            return ['success' => false, 'msg' => __('Model response: Image URL is empty!', 'xhtheme-ai-toolbox')];
                        }
                        return ['success' => true, 'msg' => 'success', 'imageUrl' => $imageUrl];
                    case 'FAILED':
                        return ['success' => false, 'msg' => $resData['output']['message']];
                    case 'CANCELED':
                        return ['success' => false, 'msg' => __('Model response: Task has been canceled!', 'xhtheme-ai-toolbox')];
                    case 'UNKNOWN':
                        return ['success' => false, 'msg' => __('Model response: Task does not exist or status unknown!', 'xhtheme-ai-toolbox')];
                }
            }
            return ['success' => false, 'msg' => __('Unknown error, waiting to retry!', 'xhtheme-ai-toolbox'), 'type' => 1];
        }

        /**
         * 官方平台处理
         */
        $appid = xh_option('appId');
        if (!$appid) {
            return ['success' => false, 'msg' => __('APPID is empty, cannot continue generation!', 'xhtheme-ai-toolbox')];
        }

        $respapi = wp_remote_post($this->XHAi->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $appid,
                'Referer' => home_url()
            ],
            'body' => json_encode([
                'type' => 'get-images',
                'backstage' => true,
                'version' => '1.0',
                'timestamp' => time(),
                'args' => [
                    'task_id' => $task_id
                ]
            ]),
            'timeout' => XHTHEME_AI_TOOLBOX_APITIMEOUT,
            'sslverify' => false
        ]);
        if (is_wp_error($respapi)) {
            return ['success' => false, 'msg' => $respapi->get_error_message(), 'type' => 1];
        }
        $body = wp_remote_retrieve_body($respapi);
        $resData = json_decode($body, true);
        $resMsg = __('Unknown error, waiting to retry!', 'xhtheme-ai-toolbox');
        $restype = 1;
        if (isset($resData['code'])) {
            switch ($resData['code']) {
                case 0:
                    $aidata = $resData['data']['aidata'];
                    $imageUrl = isset($aidata['imageUrl']) ? $aidata['imageUrl'] : '';
                    if (!empty($imageUrl)) {
                        return ['success' => true, 'msg' => 'success', 'imageUrl' => $imageUrl];
                    }
                    break;
                case 10:
                    return ['success' => false, 'msg' => __('Model task in progress, waiting for next query!', 'xhtheme-ai-toolbox'), 'type' => 2];
                default:
                    if (isset($resData['message']) && !empty($resData['message'])) {
                        $resMsg = esc_html($resData['message']);
                    }
                    if ($resData['code'] == 1) {
                        $restype = 0;
                    }
                    break;
            }
        }
        return ['success' => false, 'msg' => $resMsg, 'type' => $restype];
    }

    /**
     * 智谱创建图片
     */
    public function create_pattern_cogview($prompt)
    {
        $aliapikey = xh_option('imageApikey');
        $modelType = xh_option('imageModel', 'turbo');
        $imageSize = $this->get_imagesize('cogview','x'); // 智谱图片尺寸
        $markText = xh_option('imageMark', false);
        if (!$aliapikey) {
            return ['success' => false, 'msg' => __('Please configure the Bailian Platform API-KEY first!', 'xhtheme-ai-toolbox')];
        }
        $model = 'cogview-3-flash';
        switch ($modelType) {
            case 'plus':
                $model = 'cogview-4-250304';
                break;
        }
        $argsBody = [
            'model' => $model,
            'prompt' => $prompt,
            'size' => $imageSize,
            'quality' => 'hd',
            'watermark_enabled' => (bool) $markText
        ];
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $aliapikey,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($argsBody),
            'timeout' => 40
        );

        $response = wp_remote_post('https://open.bigmodel.cn/api/paas/v4/images/generations', $args);
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'msg' => $response->get_error_message()
            ];
        }
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        $resData = json_decode($body, true);
        if (isset($resData['error']) && isset($resData['message'])) {
            $errorArr = $resData['error'];
            return [
                'success' => false,
                'msg' => '[' . $errorArr['code'] . ']' . $errorArr['message'],
                'type' => $status_code !== 429 ? 1 : 0
            ];
        }
        if ($status_code == 200 && isset($resData['data'])) {
            $imageUrl = isset($resData['data'][0]['url']) ? $resData['data'][0]['url'] : '';
            if (!empty($imageUrl)) {
                return [
                    'success' => true,
                    'notask' => true,
                    'msg' => 'success',
                    'imageUrl' => $imageUrl
                ];
            }
        }
        return [
            'success' => false,
            'msg' => __('Unknown error, waiting to retry!', 'xhtheme-ai-toolbox')
        ];
    }


    public function imageapi_create($prompt, $imagePattern)
    {
        if (empty($prompt)) {
            return ['success' => false, 'msg' => __('Image prompt is empty, cannot continue generation!', 'xhtheme-ai-toolbox')];
        }
        switch ($imagePattern) {
            case 'aliyun':
                return $this->create_pattern_aliyun($prompt);
            case 'cogview':
                return $this->create_pattern_cogview($prompt);
        }
        return $this->create_pattern_default($prompt);
    }

    private function create_pattern_default($prompt)
    {
        $imageSize = $this->get_imagesize('default','x');
        $markText = xh_option('imageMark', false);
        $imageStyle = xh_option('imageStyle', 'auto');
        $appid = xh_option('appId');
        if (!$appid) {
            return ['success' => false, 'msg' => __('APPID is empty, cannot continue generation!', 'xhtheme-ai-toolbox')];
        }

        $respapi = wp_remote_post($this->XHAi->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $appid,
                'Referer' => home_url()
            ],
            'body' => json_encode([
                'type' => 'create-images',
                'content' => $prompt,
                'backstage' => true,
                'model' => 'auto',
                'version' => '1.0',
                'timestamp' => time(),
                'args' => [
                    'size' => $imageSize,
                    'style' => $imageStyle,
                    'mark' => $markText
                ]
            ]),
            'timeout' => XHTHEME_AI_TOOLBOX_APITIMEOUT,
            'sslverify' => false
        ]);
        if (is_wp_error($respapi)) {
            return ['success' => false, 'msg' => $respapi->get_error_message()];
        }
        $body = wp_remote_retrieve_body($respapi);
        $resData = json_decode($body, true);
        $restype = 1;
        $resMsg = __('Unknown error, waiting to retry!', 'xhtheme-ai-toolbox');
        if (isset($resData['code'])) {
            switch ($resData['code']) {
                case 0:
                    $aidata = $resData['data']['aidata'];
                    if (isset($aidata['task_id'])) {
                        $task_id = isset($aidata['task_id']) ? $aidata['task_id'] : '';
                        if (!empty($task_id)) {
                            return [
                                'success' => true,
                                'msg' => 'pass',
                                'task_id' => $task_id
                            ];
                        }
                    } elseif (isset($aidata['images'])) {
                        $imageUrl = isset($aidata['images'][0]) ? $aidata['images'][0] : '';
                        if (!empty($imageUrl)) {
                            return [
                                'success' => true,
                                'notask' => true,
                                'msg' => 'pass',
                                'imageUrl' => $imageUrl
                            ];
                        }
                    }
                    break;
                default:
                    if (isset($resData['message']) && !empty($resData['message'])) {
                        $resMsg = esc_html($resData['message']);
                    }
                    if ($resData['code'] == 1) {
                        $restype = 0;
                    }
                    break;
            }
        }

        return [
            'success' => false,
            'msg' => $resMsg,
            'type' => $restype
        ];
    }

    private function create_pattern_aliyun($prompt)
    {
        $aliapikey = xh_option('imageApikey');
        $modelType = xh_option('imageModel', 'turbo');
        $markText = xh_option('imageMark', false);
        if (!$aliapikey) {
            return ['success' => false, 'msg' => __('Please configure the Bailian Platform API-KEY first!', 'xhtheme-ai-toolbox')];
        }
        $model = 'qwen-image-plus';
        switch ($modelType) {
            case 'plus':
                $model = 'qwen-image';
                break;
        }
        $imageSize = $this->get_imagesize('qwenimage','*');
        $argsBody = [
            'model' => $model,
            'input' => [
                'prompt' => $prompt
            ],
            'parameters' => [
                'size' => $imageSize,
                'n' => 1,
                'watermark' => (bool) $markText
            ]
        ];
        $args = array(
            'headers' => array(
                'X-DashScope-Async' => 'enable',
                'Authorization' => 'Bearer ' . $aliapikey,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($argsBody),
            'timeout' => 30
        );

        $response = wp_remote_post('https://dashscope.aliyuncs.com/api/v1/services/aigc/text2image/image-synthesis', $args);
        if (is_wp_error($response)) {
            return ['success' => false, 'msg' => $response->get_error_message(), 'type' => 1];
        }
        $body = wp_remote_retrieve_body($response);
        $resData = json_decode($body, true);
        if (isset($resData['code']) && isset($resData['message'])) {
            return ['success' => false, 'msg' => $resData['message']];
        }
        if (isset($resData['output']['task_status'])) {
            switch ($resData['output']['task_status']) {
                case 'PENDING':
                case 'RUNNING':
                case 'SUCCEEDED':
                    return ['success' => true, 'msg' => 'pass', 'task_id' => $resData['output']['task_id']];
                case 'FAILED':
                    return ['success' => false, 'msg' => __('Model response: Task execution failed!', 'xhtheme-ai-toolbox')];
                case 'UNKNOWN':
                    return ['success' => false, 'msg' => __('Model response: Task does not exist or status unknown!', 'xhtheme-ai-toolbox')];
            }
        }
        return ['success' => false, 'msg' => __('Unknown error, waiting to retry!', 'xhtheme-ai-toolbox'), 'type' => 1];
    }

    public function generate_aiimages($post)
    {
        if (!$this->XHAi->isMember())
            return;
        $postId = $post->ID;
        $aiimageword = get_post_meta($postId, '_aiimage_cueword', true);
        $aiimage_status = (int) get_post_meta($postId, '_aiimage_status', true);
        if ((!$aiimage_status || ($aiimage_status > 0 && $aiimage_status < 5)) && $aiimageword) {
            $imagePattern = xh_option('imagePlatform', 'default');
            $apierrMsg = '';

            // 需要使用队列生成图片
            if ($imagePattern == 'aliyun') {
                // 直接提交生成任务，同时创建查询队列
                $apires = $this->imageapi_create($aiimageword, $imagePattern);
                if ($apires['success']) {
                    update_post_meta($postId, '_aiimage_status', -1);
                    delete_post_meta($postId, '_aiimage_cueword');
                    $Cronrow = $this->XHCron->getrow('getimage' . '_' . $postId);
                    if (!$Cronrow) {
                        $this->XHCron->insert($postId, 'getimage', [
                            'task_id' => $apires['task_id'],
                            'pattern' => $imagePattern
                        ], 3);
                    }
                    return;
                } else {
                    $apierrMsg = isset($apires['msg']) ? $apires['msg'] : '';
                }
            }

            /**
             * 创建未完成的图片任务
             */
            $CronItem = $this->XHCron->getrow('imageword' . '_' . $postId);
            if (!$CronItem) {
                if (
                    $this->XHCron->insert($postId, 'imageword', [
                        'style' => xh_option('imageStyle', 'auto'),
                        'size' => xh_option('imageSize', '1280x768')
                    ], 3)
                ) {
                    $this->XHCron->update('imageword' . '_' . $postId, [
                        'status' => 'hold',
                        'message' => $apierrMsg
                    ]);
                    update_post_meta($postId, '_aiimage_status', -1);
                }
            }
        }
    }
}