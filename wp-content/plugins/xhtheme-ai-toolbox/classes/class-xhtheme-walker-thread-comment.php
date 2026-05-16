<?php
/**
 * Custom comment walker for Thread posts
 *
 * @package XHTheme_AI_Toolbox
 * @since 1.0.0
 */

if (!class_exists('XHTheme_Walker_Thread_Comment')) {
    class XHTheme_Walker_Thread_Comment extends Walker_Comment
    {

        /**
         * Outputs a comment in the HTML5 format.
         */
        protected function html5_comment($comment, $depth, $args)
        {
            $tag = ('div' === $args['style']) ? 'div' : 'li';
            $post_author_email = get_the_author_meta('user_email');
            ?>
            <<?php echo esc_attr($tag); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class($this->has_children ? 'parent comment' : 'comment', $comment); ?>>
                <div style="display:flex;gap:16px;width:100%;max-width:100%;">
                    <div class="xhai-avatarbox" style="flex-shrink:0;">
                        <?php echo get_avatar($comment, $args['avatar_size'], '', '', array('class' => 'xhaicomm-avatar', 'style' => 'border-radius:50%;')); ?>
                    </div>
                    <div style="flex-grow:1;min-width:0;">
                        <div class="xhaicomm-meta"
                            style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:15px;">
                            <span class="xhaicomm-author" style="font-weight:700;font-size:14px;color:#111827;">
                                <?php echo esc_html(get_comment_author($comment)); ?>
                                <?php if ($comment->comment_author_email === $post_author_email): ?>
                                    <span
                                        style="font-size:12px;color:#4F46E5;margin-left:4px;">(<?php esc_html_e('Author', 'xhtheme-ai-toolbox'); ?>)</span>
                                <?php endif; ?>
                            </span>
                            <span class="xhaicomm-metadata" style="font-size:12px;color:#9CA3AF;font-family:monospace;">
                                <?php echo esc_html(human_time_diff(get_comment_time('U'), strtotime(current_time('mysql'))) . ' ' . esc_html__('ago', 'xhtheme-ai-toolbox')); ?>
                            </span>
                        </div>

                        <div class="xhaicomm-text" style="color:#4B5563;font-size:16px;line-height:1.625;margin-bottom:12px;">
                            <?php
                            comment_text();
                            if ('0' === $comment->comment_approved) {
                                ?>
                                <p class="xhaicomm-awaiting-moderation" style="color:#F59E0B;font-size:14px;margin-top:8px;">
                                    <?php esc_html_e('Your comment is waiting for moderation.', 'xhtheme-ai-toolbox'); ?>
                                </p>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="xhaicomm-actions"
                            style="display:flex;gap:20px;align-items:center;opacity:0.4;transition:opacity 0.3s;">
                            <?php
                            $likes = get_comment_meta($comment->comment_ID, '_comment_likes', true);
                            $like_count = is_array($likes) ? count($likes) : 0;
                            $user_id = get_current_user_id();
                            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Fallback provided
                            $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
                            $user_identifier = $user_id ? 'user_' . $user_id : 'ip_' . md5($user_ip);
                            $is_liked = is_array($likes) && in_array($user_identifier, $likes);

                            $liked_class = $is_liked ? ' liked' : '';
                            ?>
                            <button class="xhaitool-xhaicomm-like xhaicomm-like<?php echo esc_attr($liked_class); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                    </path>
                                </svg>
                                <span class="like-count"><?php echo esc_html($like_count); ?></span>
                            </button>

                            <?php
                            $reply_link = get_comment_reply_link(
                                array_merge(
                                    $args,
                                    array(
                                        'add_below' => 'div-comment',
                                        'depth' => $depth,
                                        'max_depth' => $args['max_depth'],
                                        'before' => '<button class="xhaicomm-reply-btn" data-comment-id="' . get_comment_ID() . '" data-post-id="' . get_the_ID() . '" style="font-size:12px;font-weight:700;color:#6B7280;background:none;border:none;padding:0;cursor:pointer;text-decoration:none;transition:color 0.3s;">',
                                        'after' => '</button>',
                                        'reply_text' => esc_html__('Reply', 'xhtheme-ai-toolbox')
                                    )
                                )
                            );

                            if ($reply_link) {
                                echo wp_kses_post($reply_link);
                            }
                            ?>
                        </div>
                        <div id="reply-form-<?php comment_ID(); ?>" class="reply-form-container"
                            style="margin-top:16px;display:none;"></div>
                    </div>
                </div>
                <?php
        }

        /**
         * 渲染单个评论的 HTML（静态方法，用于 AJAX 响应）
         */
        public static function render_single_comment($comment, $post_id = 0)
        {
            if (!$post_id) {
                $post_id = $comment->comment_post_ID;
            }

            $post_author_email = get_the_author_meta('user_email', $post_id);
            $likes = get_comment_meta($comment->comment_ID, '_comment_likes', true);
            $like_count = is_array($likes) ? count($likes) : 0;

            $user_id = get_current_user_id();
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Fallback provided
            $user_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
            $user_identifier = $user_id ? 'user_' . $user_id : 'ip_' . md5($user_ip);
            $is_liked = is_array($likes) && in_array($user_identifier, $likes);
            $liked_class = $is_liked ? ' liked' : '';
            $avatar = get_avatar($comment, 40, '', '', array('class' => 'xhaicomm-avatar', 'style' => 'border-radius:50%;'));

            $time_diff = human_time_diff(strtotime($comment->comment_date), strtotime(current_time('mysql'))) . ' ' . esc_html__('ago', 'xhtheme-ai-toolbox');
            ob_start();
            ?>
                <li id="comment-<?php echo esc_attr($comment->comment_ID); ?>" class="comment">
                    <div style="display:flex;gap:16px;width:100%;max-width:100%;">
                        <div class="xhai-avatarbox" style="flex-shrink:0;">
                            <?php echo wp_kses_post($avatar); ?>
                        </div>
                        <div style="flex-grow:1;min-width:0;">
                            <div class="xhaicomm-meta"
                                style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:15px;">
                                <span class="xhaicomm-author" style="font-weight:700;font-size:14px;color:#111827;">
                                    <?php echo esc_html($comment->comment_author); ?>
                                    <?php if ($comment->comment_author_email === $post_author_email): ?>
                                        <span
                                            style="font-size:12px;color:#4F46E5;margin-left:4px;">(<?php esc_html_e('Author', 'xhtheme-ai-toolbox'); ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <span class="xhaicomm-metadata" style="font-size:12px;color:#9CA3AF;font-family:monospace;">
                                    <?php echo esc_html($time_diff); ?>
                                </span>
                            </div>

                            <div class="xhaicomm-text" style="color:#4B5563;font-size:16px;line-height:1.625;margin-bottom:12px;">
                                <?php echo wp_kses_post(wpautop($comment->comment_content)); ?>
                            </div>

                            <div class="xhaicomm-actions"
                                style="display:flex;gap:20px;align-items:center;opacity:0.4;transition:opacity 0.3s;">
                                <button class="xhaitool-xhaicomm-like xhaicomm-like<?php echo esc_attr($liked_class); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                        </path>
                                    </svg>
                                    <span class="like-count"><?php echo esc_html($like_count); ?></span>
                                </button>
                                <button class="xhaicomm-reply-btn" data-comment-id="<?php echo esc_attr($comment->comment_ID); ?>"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    style="font-size:12px;font-weight:700;color:#6B7280;background:none;border:none;padding:0;cursor:pointer;text-decoration:none;transition:color 0.3s;box-shadow: none;"><?php esc_html_e('Reply', 'xhtheme-ai-toolbox'); ?></button>
                            </div>
                            <div id="reply-form-<?php echo esc_attr($comment->comment_ID); ?>" class="reply-form-container"
                                style="margin-top:16px;display:none;"></div>
                        </div>
                    </div>
                </li>
                <?php

                return ob_get_clean();
        }
    }
}
