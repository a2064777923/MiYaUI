<?php
namespace XHTheme\AIToolbox;
use XHTheme\AIToolbox\XH_AvatarLocal;

class XHTool
{

    /**
     * 获取评论
     * @param int $postId 文章ID
     * @return array 评论数据
     */
    public function get_postComments($postId)
    {
        $comments_query = get_comments(array(
            'post_id' => $postId,
            'status' => array('approve', 'hold'),
            'hierarchical' => 'threaded',
            'order' => 'ASC'
        ));

        $processed_comments = [];

        // 首先收集所有子评论信息
        $comment_children = [];
        foreach ($comments_query as $comment) {
            if ($comment->comment_parent > 0) {
                if (!isset($comment_children[$comment->comment_parent])) {
                    $comment_children[$comment->comment_parent] = 1;
                }
            }
        }

        // 处理每条评论
        foreach ($comments_query as $comment) {
            // 获取头像信息
            $avatar = get_comment_meta($comment->comment_ID, '_ai_avatar', true) ?: '';
            if ($comment->comment_parent > 0) {
                continue;
            }
            // 构建评论数据
            $processed_comment = array(
                'id' => $comment->comment_ID,
                'username' => $comment->comment_author,
                'content' => $comment->comment_content,
                'date' => $comment->comment_date,
                'avatar' => $avatar,
                'reply' => isset($comment_children[$comment->comment_ID]) ? 0 : 1
            );
            $processed_comments[] = $processed_comment;
        }

        return $processed_comments;
    }

    public function get_ai_avatar($comment_ID)
    {
        $avatar = get_comment_meta($comment_ID, '_ai_avatar', true);
        if (empty($avatar)) {
            return '';
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        $avatar_data = json_decode($avatar, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($avatar_data)) {

            if (isset($avatar_data['url']) && filter_var($avatar_data['url'], FILTER_VALIDATE_URL)) {
                return $avatar_data['url'];
            }

            if (isset($avatar_data['type']) && $avatar_data['type'] === 'local') {
                if (!class_exists(__NAMESPACE__ . '\XH_AvatarLocal')) {
                    require_once plugin_dir_path(__FILE__) . 'class-xhtheme-avatarlocal.php';
                }
                $local_avatar = XH_AvatarLocal::download_avatar($avatar_data['hash']);
                if ($local_avatar) {
                    $avatar_url = esc_url($local_avatar);
                    update_comment_meta($comment_ID, '_ai_avatar', $avatar_url);
                    return $avatar_url;
                } else {
                    delete_comment_meta($comment_ID, '_ai_avatar');
                }
            }
        }
        return '';
    }

    /**
     * 根据用户id获取头像
     */
    public function get_avatar_by_user($user_id)
    {
        if (empty($user_id)) {
            return '';
        }
        $user_avatar = get_user_meta($user_id, '_ai_avatar', true);
        if ($user_avatar) {
            return $user_avatar;
        }
        return '';
    }

    /**
     * 处理头像
     */
    public function handleAvatar($args, $id_or_email)
    {
        if (is_string($id_or_email) && is_email($id_or_email)) {
            $email = $id_or_email;
            $email_suffix = '@xmail.com';
            if (substr($email, -strlen($email_suffix)) === $email_suffix) {
                $comments = get_comments(array(
                    'author_email' => $email,
                    'number' => 1
                ));
                if (!empty($comments)) {
                    $id_or_email = $comments[0];
                }
            }
        }

        if (!is_object($id_or_email) || !isset($id_or_email->comment_ID)) {
            if ((is_numeric($id_or_email) && $id_or_email > 0) || !in_the_loop()) {
                $userAvatar = $this->get_avatar_by_user($id_or_email);
                if ($userAvatar) {
                    $args['url'] = esc_url($userAvatar);
                    $args['aiavatarurl'] = $args['url'];
                }
                return $args;
            }
            global $comment;
            $id_or_email = $comment;
        }

        if (!is_object($id_or_email) || !isset($id_or_email->comment_ID)) {
            return $args;
        }

        if ($id_or_email->comment_type !== 'ai_comment') {
            return $args;
        }

        if ($id_or_email->user_id) {
            $userAvatar = $this->get_avatar_by_user($id_or_email->user_id);
            if ($userAvatar) {
                $args['url'] = esc_url($userAvatar);
                $args['aiavatarurl'] = $args['url'];
            }
            return $args;
        }

        $comment_ID = $id_or_email->comment_ID;
        $ai_avatar = $this->get_ai_avatar($comment_ID);
        if ($ai_avatar) {
            $args['url'] = esc_url($ai_avatar);
            $args['aiavatarurl'] = $args['url'];
        }
        return $args;
    }

    public function filter_avatar_data($args, $id_or_email)
    {
        if (isset($args['aiavatarurl'])) {
            $args['url'] = $args['aiavatarurl'];
        }
        return $args;
    }

    /**
     * 修改原生评论类型下拉框
     */
    function add_comment_types_dropdown($comment_types)
    {
        $comment_types['ai_comment'] = __('AI Comments', 'xhtheme-ai-toolbox');
        return $comment_types;
    }

    /**
     * 添加自定义列到评论列表
     */
    function add_comment_columns($columns)
    {
        $new_columns = array();

        // 在评论内容列后添加来源列
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'comment') {
                $new_columns['comment_source'] = __('Source', 'xhtheme-ai-toolbox');
                $new_columns['comment_status'] = __('Status', 'xhtheme-ai-toolbox');
            }
        }

        return $new_columns;
    }

    /**
     * 显示自定义列内容
     */
    function manage_comment_columns($column, $comment_id)
    {
        $comment = get_comment($comment_id);
        if (!$comment || !is_a($comment, 'WP_Comment')) {
            return;
        }
        if ($column === 'comment_source') {
            $comment_type = $comment->comment_type;
            $is_ai = $comment_type === 'ai_comment';
            echo '<p style="padding-top:5px;padding-bottom:5px">';
            if ($is_ai) {
                if ($comment->user_id > 0) {
                    $profile_url = get_edit_user_link($comment->user_id);
                    echo '<a href="' . esc_url($profile_url) . '" class="ai-user-badge" style="margin-top:5px;display:inline-block;background-color: #e5f7ff; color: #049be0; padding: 3px 8px; border-radius: 3px; font-size: 12px; text-decoration: none;" title="' . esc_attr__('User Profile', 'xhtheme-ai-toolbox') . '">' . esc_html__('AI User', 'xhtheme-ai-toolbox') . '</a>';
                } elseif ($comment->comment_approved == 0 && xh_option('commentuser', 'guest') !== 'guest') {
                    if (xh_option('commentuser', 'guest') === 'mixed') {
                        echo '<span class="ai-user-badge" style="margin-top:5px;background-color:rgb(255, 229, 254); color:rgb(224, 4, 154); padding: 3px 8px; border-radius: 3px; font-size: 12px;">' . esc_html__('AI Comments - Mixed Mode', 'xhtheme-ai-toolbox') . '</span>';
                        echo '<span style="display:flex;margin-top:4px;font-size:11px;line-height:1.4;color:#8c8f94;align-items: center;">';
                        echo '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:4px;" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
                        echo esc_html__('Comment type is determined after publication', 'xhtheme-ai-toolbox');
                        echo '</span>';
                    } else {
                        echo '<span class="ai-user-badge" style="margin-top:5px;background-color: #ebffe5; color:rgb(4, 224, 173); padding: 3px 8px; border-radius: 3px; font-size: 12px;">' . esc_html__('AI User', 'xhtheme-ai-toolbox') . '</span>';
                        echo '<span style="display:flex;margin-top:4px;font-size:11px;line-height:1.4;color:#8c8f94;align-items: center;">';
                        echo '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:4px;" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
                        echo esc_html__('Automatically generate users when publishing', 'xhtheme-ai-toolbox');
                        echo '</span>';
                    }

                } else {
                    echo '<span class="ai-guest-badge" style="margin-top:5px;background-color: #e5f7e5; color: #00a32a; padding: 3px 8px; border-radius: 3px; font-size: 12px;">' . esc_html__('AI Guest', 'xhtheme-ai-toolbox') . '</span>';
                }
            } else {
                echo '<span class="user-badge" style="margin-top:5px;background-color: #f0f0f1; color: #50575e; padding: 3px 8px; border-radius: 3px; font-size: 12px;">' . esc_html__('User', 'xhtheme-ai-toolbox') . '</span>';
            }
            echo '</p>';
        }
        if ($column === 'comment_status') {
            echo '<p style="padding-top:5px;padding-bottom:5px">';
            if ($comment->comment_approved == 0 && $comment->comment_type === 'ai_comment') {
                echo '<span style="color: #ff9800;">';
                /* translators: %s: Scheduled date and time in Y-m-d H:i format */
                echo sprintf(esc_html__('Schedule [%s]', 'xhtheme-ai-toolbox'), esc_html(mysql2date('Y-m-d H:i', $comment->comment_date)));
                echo '</span>';
            } else {
                $comment_type = get_comment_type($comment_id);
                $status = wp_get_comment_status($comment_id);
                $statuses = get_comment_statuses();
                switch ($status) {
                    case 'approved':
                        $status = 'approve';
                        break;
                    case 'unapproved':
                        $status = 'hold';
                        break;
                }
                if (isset($statuses[$status])) {
                    echo esc_html($statuses[$status]);
                } else {
                    echo esc_html($status);
                }
            }
            echo '</p>';
        }
    }

    /**
     * 随机邮箱生成
     */
    function generateRandomEmail()
    {
        $domains = apply_filters('xhaitoolbox_randomemail_domains', ['xmail.com']);
        $username = substr(md5(uniqid(wp_rand(), true)), 0, 8);
        $domain = $domains[array_rand($domains)];
        return $username . '@' . $domain;
    }

    function getAgents()
    {
        $user_agents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Edg/116.0.1938.69',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/117.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 OPR/102.0.0.0',
            'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/116.0 Firefox/116.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/116.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Vivaldi/6.1.3035.111',
            'Mozilla/5.0 (Linux; Android 13; SM-S908B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari Line/13.5.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Edg/116.0.1938.62',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/116.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36 Whale/3.21.192.18',
            'Mozilla/5.0 (Linux; Android 13; SM-A536B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Mobile Safari/537.36'
        );

        // 随机返回一个浏览器 User-Agent
        return $user_agents[array_rand($user_agents)];
    }

    /**
     * 输出一条随机评论的信息
     */
    function get_random_comment_optimized($current_post_id)
    {
        global $wpdb;
        static $commentallCount = null;
        static $commentallId = null;
        if ($commentallId === null || $commentallId != $current_post_id) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cached in static variable
            $commentallCount = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(comment_ID) FROM $wpdb->comments
                 WHERE comment_type = %s AND comment_post_ID != %d",
                'ai_comment',
                $current_post_id
            ));
            $commentallId = $current_post_id;
        }

        if (empty($commentallCount)) {
            return null;
        }

        // 生成一个随机偏移量
        $random_offset = wp_rand(0, $commentallCount - 1);

        // 根据随机偏移量获取一条评论
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Random query not cacheable
        $random_comment = $wpdb->get_row($wpdb->prepare(
            "SELECT comment_ID, comment_author_email, comment_agent, comment_author
             FROM $wpdb->comments
             WHERE comment_type = %s AND comment_post_ID != %d
             LIMIT %d, 1",
            'ai_comment',
            $current_post_id,
            $random_offset
        ), ARRAY_A);

        return $random_comment;
    }

    /**
     * 预设昵称
     */
    function get_rand_nicknames()
    {
        $nicknames = [
            __('Starlight', 'xhtheme-ai-toolbox'),
            __('Windwalker', 'xhtheme-ai-toolbox'),
            __('Shadow', 'xhtheme-ai-toolbox'),
            __('Dreamer', 'xhtheme-ai-toolbox'),
            __('Aurora', 'xhtheme-ai-toolbox'),
            __('Fawn', 'xhtheme-ai-toolbox'),
            __('Nightwhisper', 'xhtheme-ai-toolbox'),
            __('Phantom', 'xhtheme-ai-toolbox'),
            __('Breeze', 'xhtheme-ai-toolbox'),
            __('Future', 'xhtheme-ai-toolbox'),
            __('Smile', 'xhtheme-ai-toolbox'),
            __('Passion', 'xhtheme-ai-toolbox'),
            __('Silence', 'xhtheme-ai-toolbox'),
            __('Lightyear', 'xhtheme-ai-toolbox'),
            __('Trend', 'xhtheme-ai-toolbox'),
            __('Lone Wolf', 'xhtheme-ai-toolbox'),
            __('Cloud', 'xhtheme-ai-toolbox'),
            __('Hope', 'xhtheme-ai-toolbox'),
            __('Spark', 'xhtheme-ai-toolbox'),
            __('Dawn', 'xhtheme-ai-toolbox'),
        ];
        return $nicknames[array_rand($nicknames)];
    }

    /**
     * 评论昵称处理
     */
    function commentNameFilter($comment)
    {
        global $wpdb;
        $username = $comment['comment_author'];
        $current_post_id = $comment['comment_post_ID'];

        $use_random_comment = true;

        if (!empty($username)) {
            $use_random_comment = false;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time lookup for comment author
            $existing_comments = $wpdb->get_results($wpdb->prepare(
                "SELECT comment_ID, comment_author_email, comment_agent, comment_post_ID, comment_author
                FROM $wpdb->comments
                WHERE comment_type = %s AND comment_author = %s
                ORDER BY comment_ID DESC
                LIMIT 30",
                'ai_comment',
                $username
            ), ARRAY_A);

            if (!empty($existing_comments)) {
                foreach ($existing_comments as $exist_comment) {
                    if ((int) $exist_comment['comment_post_ID'] === (int) $current_post_id) {
                        $use_random_comment = true;
                        break;
                    }
                }

                if (!$use_random_comment) {
                    $first_exist_comment = $existing_comments[0];
                    $comment['comment_author'] = $first_exist_comment['comment_author'];
                    $comment['comment_author_email'] = $first_exist_comment['comment_author_email'];
                    $comment['comment_agent'] = $first_exist_comment['comment_agent'];
                    $comment['avatar'] = get_comment_meta($first_exist_comment['comment_ID'], '_ai_avatar', true);
                }
            }
        }

        if ($use_random_comment) {
            // 使用优化后的随机评论获取函数
            $random_comment = $this->get_random_comment_optimized($current_post_id);
            if ($random_comment) {
                $comment['comment_author'] = $random_comment['comment_author'];
                $comment['comment_author_email'] = $random_comment['comment_author_email'];
                $comment['comment_agent'] = $random_comment['comment_agent'];
                if (isset($random_comment['comment_ID'])) {
                    $comment['avatar'] = get_comment_meta($random_comment['comment_ID'], '_ai_avatar', true);
                }
            } else {
                $max_attempts = 5; // 设置最大尝试次数
                $attempt = 0;
                $nickname_used = true;

                while ($nickname_used && $attempt < $max_attempts) {
                    $random_nickname = $this->get_rand_nicknames();
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Loop check for nickname uniqueness
                    $existing_nickname = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT comment_ID FROM $wpdb->comments 
                            WHERE comment_post_ID = %d 
                            AND comment_author = %s 
                            LIMIT 1",
                            $current_post_id,
                            $random_nickname
                        )
                    );

                    if (!$existing_nickname) {
                        $nickname_used = false;
                        $comment['comment_author'] = $random_nickname;
                    }

                    $attempt++;
                }

                if ($nickname_used) {
                    $comment['comment_author'] = __('Guest', 'xhtheme-ai-toolbox') . wp_rand(100, 999);
                }
            }
        }
        return $comment;
    }

    /**
     * 创建单个AI评论
     * @param int $postId 文章ID
     * @param array $comment 评论数据
     * @return int|false 成功返回评论ID，失败返回false
     */
    function insertComment($postId, $comment)
    {
        // 准备评论数据
        $commentdata = array(
            'comment_post_ID' => $postId,
            'comment_author' => isset($comment['username']) ? $comment['username'] : '',
            'comment_author_email' => $this->generateRandomEmail(),
            'comment_content' => $comment['content'],
            'comment_type' => 'ai_comment',
            'comment_approved' => 0,
            'comment_author_IP' => isset($comment['ip']) ? $comment['ip'] : '127.0.0.1',
            'comment_agent' => $this->getAgents(),
            'comment_date' => isset($comment['date']) ? $comment['date'] : current_time('mysql'),
            'comment_parent' => isset($comment['parent']) ? (int) $comment['parent'] : 0
        );
        if (isset($comment['date_gmt'])) {
            $commentdata['comment_date_gmt'] = $comment['date_gmt'];
        } else {
            $commentdata['comment_date_gmt'] = get_gmt_from_date($commentdata['comment_date']);
        }
        /**
         * 处理昵称重复问题
         */
        $commentdata = $this->commentNameFilter($commentdata);

        // 插入评论
        $comment_id = wp_insert_comment($commentdata);

        // 添加评论元数据，标记为AI生成
        if ($comment_id) {
            add_comment_meta($comment_id, '_ai_avatar', wp_json_encode($comment['avatar']), true);
            // 根据文章评论数量来决定评论发布时间
            $comment_count = wp_count_comments($postId);
            if ($comment_count->approved == 0) {
                if (
                    wp_update_comment([
                        'comment_ID' => $comment_id,
                        'comment_date' => current_time('mysql')
                    ])
                ) {
                    wp_set_comment_status($comment_id, 'approve');
                }
            }
            do_action('xhaitoolbox_comment_created', $comment_id);
            return $comment_id;
        }

        return false;
    }

}