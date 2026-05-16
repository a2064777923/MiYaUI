<?php

namespace XHTheme\AIToolbox;

use XHTheme\AIToolbox\XHCronQueue;
use XHTheme\AIToolbox\XHThemeAi;

final class XHComment extends XHTool
{

    private static $instance;
    private $XHCron;
    private $XHAi;
    private $maxComments = 100;

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
        $this->XHCron = XHCronQueue::getInstance();
        $this->XHAi = XHThemeAi::getInstance();
    }

    function pushComment($postId, $comments, $allnumber = 0, $maxdays = 1)
    {
        // 添加评论
        if (!$comments || !is_array($comments) || !$postId) {
            return;
        }
        $numbers = $allnumber > count($comments) ? $allnumber : count($comments);
        $inserted_comments = array();

        $comentCount = count($comments);
        $times = xh_randTimeList('', $maxdays, $numbers + $comentCount);
        foreach ($comments as $key => $comment) {
            $comments[$key]['date'] = $times[$key];
            unset($times[$key]);
        }
        $times = array_values($times);

        $this->analysisComment($postId, $comments, []);

        if ($numbers > 0) {
            /**
             *预留定时任务
             */
            $Cronrow = $this->XHCron->getrow('comment' . '_' . $postId);
            if (!$Cronrow) {
                $this->XHCron->insert($postId, 'comment', [
                    'maxday' => $maxdays,
                    'allnumber' => $numbers,
                    'times' => $times
                ]);
            }
        }
        return $inserted_comments;
    }


    public function init()
    {
        add_filter('admin_comment_types_dropdown', array($this, 'add_comment_types_dropdown'));
        add_filter('manage_edit-comments_columns', array($this, 'add_comment_columns'));
        add_action('manage_comments_custom_column', array($this, 'manage_comment_columns'), 10, 2);
        add_filter('get_avatar_comment_types', array($this, 'add_avatar_comment_types'));
        add_filter('pre_get_avatar_data', array($this, 'handleAvatar'), 99, 2);
        add_filter('get_avatar_data', array($this, 'filter_avatar_data'), 99, 2);
        add_filter('pre_get_avatar', array($this, 'pre_get_avatar'), 99, 2);
        add_filter('get_user_metadata', array($this, 'user_metadata_filter'), 10, 4);
        add_action('xhaitoolbox_minute_cron', array($this, 'process_comment_status'), 5);
        add_filter('xhaitoolbox_cronitem_comment', array($this, 'process_ai_comments'), 10, 1);
        add_action('xhtheme_ai_toolbox_avatarloacal_error', array($this, 'avatarloacal_error'), 10, 1);
        add_filter('wp_list_comments_args', array($this, 'add_comment_type'));
        add_filter('comment_class', array($this, 'add_comment_class'), 10, 5);
        add_action('init', array($this, 'add_aiuser_role'));

        /**
         * 屏蔽邮件通知
         */
        add_filter('comment_notification_recipients', array($this, 'comment_notification'), 10, 2);
        add_filter('comment_moderation_recipients', array($this, 'comment_notification'), 10, 2);
        /**
         * 为评论绑定用户
         */
        add_action('wp_set_comment_status', array($this, 'bind_comment_user'), 10, 2);
    }

    function user_metadata_filter($value, $object_id, $meta_key, $single)
    {
        if ($meta_key === 'zib_other_data') {
            $user = get_user_by('ID', $object_id);
            if ($user && in_array('xhaiuser', (array) $user->roles)) {
                $userAvatar = $this->get_avatar_by_user($object_id);
                if (!empty($userAvatar)) {
                    $valueArr = [
                        'custom_avatar' => esc_url($userAvatar)
                    ];
                    return [$valueArr];
                }
            }
        }
        return $value;
    }

    public function pre_get_avatar($avatar, $id_or_email)
    {
        if (is_null($avatar) || xh_themeName() !== 'Zibll')
            return $avatar;
        $reststart = false;
        if (is_numeric($id_or_email)) {
            if ($id_or_email > 0) {
                $user = get_user_by('ID', $id_or_email);
                if ($user && in_array('xhaiuser', (array) $user->roles)) {
                    $reststart = true;
                }
            }
        } elseif (is_object($id_or_email)) {
            if ($id_or_email instanceof \WP_Comment) {
                if ($id_or_email->comment_type == 'ai_comment') {
                    $reststart = true;
                }
            } elseif ($id_or_email instanceof \WP_User) {
                if (in_array('xhaiuser', (array) $id_or_email->roles)) {
                    $id_or_email = $id_or_email->ID;
                    $reststart = true;
                }
            }
        } elseif (is_string($id_or_email) && is_email($id_or_email)) {
            $reststart = true;
        }

        if ($reststart) {
            $avatarArr = $this->handleAvatar([], $id_or_email);
            if ($avatarArr && isset($avatarArr['aiavatarurl'])) {
                $new_avatar_url = $avatarArr['aiavatarurl'];
                if (strpos($avatar, '-src=') !== false) {
                    $avatar = preg_replace(
                        '/-src=[\'"]([^\'"]*)[\'"]/',
                        '-src="' . $new_avatar_url . '"',
                        $avatar,
                        1
                    );
                } else {
                    $avatar = preg_replace(
                        '/src=[\'"]([^\'"]*)[\'"]/',
                        'src="' . $new_avatar_url . '"',
                        $avatar,
                        1
                    );
                }
            }
        }
        return $avatar;
    }

    public function add_aiuser_role()
    {
        if (!get_role('xhaiuser')) {
            add_role(
                'xhaiuser',
                __('AI User', 'xhtheme-ai-toolbox'),
                array(
                    'read' => true
                )
            );
        }
    }

    public function bind_comment_user($comment_id, $comment_status)
    {
        if ($comment_status == 'approve' || $comment_status == '1') {
            $comment = get_comment($comment_id);
            if ($comment->comment_type == 'ai_comment') {
                $this->update_comment_user($comment);
            }
        }
    }

    public function update_comment_user($comment)
    {
        $commentuser = xh_option('commentuser', 'guest');

        if ($commentuser == 'guest') {
            return;
        }

        if ($commentuser === 'mixed') {
            $commentMixedRatio = xh_option('commentMixedRatio', 20);
            $random = wp_rand(1, 100);
            if ($random < $commentMixedRatio) {
                return;
            }
        }

        $user_id = $comment->user_id;
        if ($user_id) {
            return;
        }

        $commentId = $comment->comment_ID;
        $userName = $comment->comment_author;
        $userEmail = $comment->comment_author_email;

        if (empty($userName) || empty($userEmail)) {
            return;
        }

        if (!is_email($userEmail)) {
            return;
        }

        $existing_user = email_exists($userEmail);

        if ($existing_user) {
            wp_update_comment(array(
                'comment_ID' => $commentId,
                'user_id' => $existing_user
            ));
            delete_comment_meta($commentId, '_ai_avatar');
            wp_cache_delete($user_id, 'user_avatar');
            return;
        }

        // 检查是否存在相同昵称
        global $wpdb;
        $email_like = '%' . $wpdb->esc_like('@xmail.com');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time query for unique nickname check
        $Nickname = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ID,user_email FROM {$wpdb->users} 
                 WHERE user_nicename = %s 
                 AND user_email LIKE %s",
                $userName,
                $email_like
            )
        );
        if ($Nickname) {
            $userId = $Nickname->ID;
            wp_update_comment(array(
                'comment_ID' => $commentId,
                'user_id' => $userId,
                'comment_author_email' => $Nickname->user_email
            ));
            delete_comment_meta($commentId, '_ai_avatar');
            wp_cache_delete($userId, 'user_avatar');
            return;
        }

        // 根据邮箱前缀生成用户名
        $user_login = substr($userEmail, 0, strpos($userEmail, '@'));
        $userAvatar = $this->get_ai_avatar($commentId);
        if ($user_login) {
            $userdata = array(
                'user_login' => 'xhai_' . $user_login,
                'user_email' => $userEmail,
                'user_pass' => wp_generate_password(12, false),
                'display_name' => $userName,
                'nickname' => $userName,
                'role' => 'xhaiuser'
            );
            $user_id = wp_insert_user($userdata);

            if (!is_wp_error($user_id)) {
                if (!empty($userAvatar)) {
                    update_user_meta($user_id, '_ai_avatar', $userAvatar);
                }
                wp_update_comment(array(
                    'comment_ID' => $commentId,
                    'user_id' => $user_id
                ));

                delete_comment_meta($commentId, '_ai_avatar');
                wp_cache_delete($user_id, 'user_avatar');
            }
        }
    }

    public function add_avatar_comment_types($comment_types)
    {
        $comment_types[] = 'ai_comment';
        return $comment_types;
    }

    public function add_comment_class($classes, $css_class, $comment_id, $comment, $post)
    {
        if ($comment && $comment->comment_type === 'ai_comment') {
            $classes[] = 'comment';
        }
        return $classes;
    }

    function add_comment_type($args)
    {
        if (isset($args['type']) && $args['type'] == 'comment') {
            $args['type'] = 'all';
        }
        return $args;
    }

    public function comment_notification($emails, $comment_id)
    {
        $commentNotice = xh_option('commentNotice', false);
        if (!$commentNotice) {
            return $emails;
        }
        $comment = get_comment($comment_id);
        if ($comment && $comment->comment_type === 'ai_comment') {
            return array();
        }
        return $emails;
    }

    /**
     * 处理头像本地化错误
     */
    public function avatarloacal_error($avatar_url)
    {
        $avatar_localerror = get_option('xhtheme_ai_toolbox_avatarlocal_error');
        $avatar_localerror = $avatar_localerror ? json_decode($avatar_localerror, true) : [];
        $daykeys = wp_date('Ymd');
        $avatar_localerror[$daykeys][] = $avatar_url;
        $sendEmail = false;
        if (count($avatar_localerror[$daykeys]) > 10) {
            $sendEmail = $avatar_localerror[$daykeys];
            unset($avatar_localerror[$daykeys]);
        }
        update_option('xhtheme_ai_toolbox_avatarlocal_error', json_encode($avatar_localerror));
        /**
         * 头像本地化失败过多，给管理员发送邮件
         */
        if ($sendEmail) {
            $admin_email = get_option('admin_email');
            if ($admin_email) {
                $notify_message = __('Dear Website Administrator:', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('The system has detected multiple failures in avatar localization (more than 10 times today). This may cause some comments to fail to display custom avatars.', 'xhtheme-ai-toolbox') . "\r\n\r\n";
                $notify_message .= __('Possible reasons:', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('1. Insufficient upload directory permissions', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('2. Remote image server downtime', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('3. Network connection issues', 'xhtheme-ai-toolbox') . "\r\n\r\n";
                $notify_message .= __('Recommended checks:', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('1. Ensure /wp-content/uploads/avatars directory is writable', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('2. Check error logs for details', 'xhtheme-ai-toolbox') . "\r\n";
                $notify_message .= __('3. Contact "Xinghe AI Toolbox" author for help. Website: https://www.xhtheme.com', 'xhtheme-ai-toolbox') . "\r\n\r\n\r\n";
                $notify_message .= __('This email was automatically sent by "Xinghe AI Toolbox" plugin!', 'xhtheme-ai-toolbox');
                wp_mail(
                    $admin_email,
                    __('Avatar Localization Failure Notification', 'xhtheme-ai-toolbox'),
                    $notify_message
                );
            }
        }
    }

    /**
     * 处理定时发布的评论
     */
    public function process_comment_status()
    {
        // 获取当前时间
        $current_time = current_time('mysql');
        $comments = get_comments(array(
            'status' => 'hold',
            'type' => 'ai_comment',
            'date_query' => array(
                'before' => $current_time
            ),
            'number' => 0
        ));
        if ($comments) {
            foreach ($comments as $comment) {
                wp_set_comment_status($comment->comment_ID, 'approve');
            }
        }
    }

    public function process_ai_comments($cronArr)
    {
        if (!isset($cronArr['post_id']) || !isset($cronArr['data']['allnumber'])) {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('Parameter missing!', 'xhtheme-ai-toolbox');
            return $cronArr;
        }

        if (!isset($cronArr['data']['maxday']) || !isset($cronArr['data']['times'])) {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('Function changed, please delete the task!', 'xhtheme-ai-toolbox');
            return $cronArr;
        }

        $appid = xh_option('appId', '');
        if (!$appid) {
            $this->XHAi->modelError('appId', '');
            return $cronArr;
        }
        $postId = $cronArr['post_id'];
        $post = get_post($postId);
        if (!$post) {
            $cronArr['status'] = 'error';
            $cronArr['message'] = esc_html__('No article found.', 'xhtheme-ai-toolbox');
            return $cronArr;
        }
        $comments = $this->get_postComments($postId);

        // 判断评论数量
        $existing_comments_count = count($comments);
        if ($existing_comments_count >= $this->maxComments) {
            $cronArr['status'] = 'success';
            return $cronArr;
        }

        /**
         * 组装请求参数
         */
        $allnumber = $cronArr['data']['allnumber'];
        $maxday = $cronArr['data']['maxday'];
        $times = (array) $cronArr['data']['times'];

        $postContent = $post->post_content;

        $apiArgs = [
            'number' => $allnumber > 15 ? 10 : $allnumber,
            'exist' => [],
            'reply' => 0
        ];
        if ($existing_comments_count > 4) {
            $apiArgs['reply'] = 1;
        }

        if ($existing_comments_count > 0) {
            foreach ($comments as $comment) {
                $apiArgs['exist'][] = [
                    'content' => $comment['content'],
                    'id' => $comment['id'],
                    'reply' => $comment['reply']
                ];
            }
        }

        $postContent = xh_filterContent($postContent, $post);
        $respapi = wp_remote_post($this->XHAi->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $appid,
                'Referer' => home_url()
            ],
            'body' => json_encode([
                'apivar' => XHTHEME_AI_TOOLBOX_APIVERSION,
                'type' => 'post-comment',
                'content' => $postContent,
                'imagevl' => xh_get_imagevl($postContent),
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
                $newComments = $response_data['data']['aidata'];
                if (!$newComments || !is_array($newComments))
                    return $cronArr;

                foreach ($newComments as $key => $comment) {
                    if (isset($times[$key])) {
                        $newComments[$key]['date'] = $times[$key];
                        unset($times[$key]);
                    } else {
                        $newComments[$key]['date'] = current_time('mysql');
                    }
                }
                $times = array_values($times);

                $passNumber = $this->analysisComment($postId, $newComments, $comments);
                if ($allnumber - $passNumber > 2) {
                    $cronArr['status'] = 'hold';
                    $cronArr['data']['allnumber'] = $allnumber - $passNumber;
                    $cronArr['data']['times'] = $times;
                } else {
                    $cronArr['status'] = 'success';
                }
            } elseif (isset($response_data['message'])) {
                $cronArr['message'] = $response_data['message'];
                $errorNum = (int) $cronArr['errornum'];
                $errorNum = $errorNum + 1;
                $cronArr['errornum'] = $errorNum;
                if ($errorNum >= 5) {
                    $cronArr['status'] = 'error';
                }
            }
        }
        return $cronArr;
    }

    public function analysisComment($postId, $newComments, $comments = [])
    {

        $number = 0;

        if (apply_filters('xhtheme_ai_toolbox_pre_comments_publish', false)) {
            return apply_filters('xhtheme_ai_toolbox_comments_publish', $number, $newComments, $comments);
        }

        $existingContents = [];
        // 提取已存在评论的内容与时间戳
        foreach ($comments as $comment) {
            $existingContents[] = trim($comment['content']);
        }

        $validComments = [];

        foreach ($newComments as $comment) {
            if (empty($comment['content'])) {
                continue;
            }

            $content = trim($comment['content']);
            if (in_array($content, $existingContents)) {
                continue;
            }

            $existingContents[] = $content;
            $validComments[] = $comment;
        }

        foreach ($validComments as $comment) {
            $comment['date_gmt'] = get_gmt_from_date($comment['date']);
            if ($this->insertComment($postId, $comment)) {
                $number++;
            }
        }

        return $number;
    }
}
