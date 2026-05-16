<?php
/**
 * Thread Comments Template
 * 
 * Custom comments template for thread post type
 * 
 * @package XHTheme_AI_Toolbox
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables with xhaitool_ prefix

if (post_password_required()) {
    return;
}


// Load the walker class
require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-xhtheme-walker-thread-comment.php';
?>

<div id="comments" class="xhaicomm-s-section">

    <!-- 评论头部 -->
    <div class="xhaicomm-s-header"
        style="display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;">
        <h2 class="xhaicomm-s-title"><?php esc_html_e('Join Discussion', 'xhtheme-ai-toolbox'); ?></h2>
        <div class="xhaicomm-s-count" style="font-size:14px;color:#6B7280;">
            <?php
            $xhaitool_comment_count = get_comments_number();
            /* translators: %s: Number of comments */
            printf(esc_html__('%s comments', 'xhtheme-ai-toolbox'), esc_html($xhaitool_comment_count));
            ?>
        </div>
    </div>

    <!-- 评论表单 -->
    <?php if (comments_open()): ?>
        <div class="xhaicomm-form-wrapper" id="xhaicomm-form-wrapper" style="margin-bottom:48px;">
            <div class="xhaicomm-area">
                <textarea class="xhaicomm-input" id="xhaicomm-input"
                    placeholder="<?php esc_attr_e('Share your thoughts...', 'xhtheme-ai-toolbox'); ?>" rows="2" required
                    style="min-height:80px;max-height:300px;margin-bottom: 0;background: none;"></textarea>
                <div class="xhaicomm-tools">
                    <button type="button" class="xhbtn-submit"
                        id="xh-submit-comment"><?php esc_html_e('Publish', 'xhtheme-ai-toolbox'); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 评论列表 -->
    <ul class="xhaicomm-list" id="xhaicomm-list">
        <?php
        if (have_comments()):
            wp_list_comments(array(
                'walker' => new XHTheme_Walker_Thread_Comment(),
                'style' => 'ol',
                'format' => 'html5',
                'short_ping' => true,
                'avatar_size' => 40
            ));
        else:
            echo '<p style="text-align:center;color:#9CA3AF;padding:40px 0;">' . esc_html__('No comments yet, be the first to share your opinion!', 'xhtheme-ai-toolbox') . '</p>';
        endif;
        ?>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 使用事件委托处理回复按钮点击（支持动态添加的评论）
        document.addEventListener('click', function (e) {
            const replyBtn = e.target.closest('.xhaicomm-reply-btn');
            if (!replyBtn) return;

            e.preventDefault();

            const commentId = replyBtn.dataset.commentId;
            const postId = replyBtn.dataset.postId;
            const replyContainer = document.getElementById('reply-form-' + commentId);

            if (!replyContainer) return;

            // 隐藏所有其他回复表单
            document.querySelectorAll('.reply-form-container').forEach(container => {
                if (container.id !== 'reply-form-' + commentId) {
                    container.style.display = 'none';
                    container.innerHTML = '';
                }
            });

            // 切换当前回复表单
            if (replyContainer.style.display === 'none' || !replyContainer.style.display) {
                // 克隆主评论表单
                const mainFormWrapper = document.querySelector('#xhaicomm-form-wrapper .xhaicomm-area');
                if (mainFormWrapper) {
                    const clonedWrapper = mainFormWrapper.cloneNode(true);

                    // 清空并替换占位符
                    const textarea = clonedWrapper.querySelector('textarea');
                    if (textarea) {
                        textarea.value = '';
                        textarea.placeholder = '<?php echo esc_js(__('Reply to comment...', 'xhtheme-ai-toolbox')); ?>';
                        textarea.id = 'xhaicomm-input-reply-' + commentId;
                    }

                    // 找到提交按钮并更新
                    const submitBtn = clonedWrapper.querySelector('.xhbtn-submit');
                    if (submitBtn) {
                        submitBtn.id = 'xh-submit-reply-' + commentId;

                        // 添加回复提交事件
                        submitBtn.addEventListener('click', function (e) {
                            e.preventDefault();

                            const content = textarea.value.trim();
                            if (!content) {
                                XHThreadToast.error('<?php echo esc_js(__('Please enter comment content', 'xhtheme-ai-toolbox')); ?>');
                                textarea.focus();
                                return;
                            }

                            const pageData = window.xhThreadPageData || {};
                            if (!pageData.postId) {
                                console.error('Post ID not found');
                                return;
                            }

                            // Loading state
                            const originalText = submitBtn.innerText;
                            submitBtn.innerText = '<?php echo esc_js(__('Publishing...', 'xhtheme-ai-toolbox')); ?>';
                            submitBtn.disabled = true;

                            const data = {
                                post_id: pageData.postId,
                                content: content,
                                parent_id: commentId
                            };

                            fetch(pageData.restUrl + '/thread/comment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-WP-Nonce': pageData.nonce
                                },
                                body: JSON.stringify(data)
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // 清空输入框
                                        textarea.value = '';

                                        // 隐藏回复表单
                                        replyContainer.style.display = 'none';
                                        replyContainer.innerHTML = '';

                                        XHThreadToast.success('<?php echo esc_js(__('Reply published successfully!', 'xhtheme-ai-toolbox')); ?>');

                                        // 刷新页面以显示新回复
                                        location.reload();
                                    } else {
                                        XHThreadToast.error(data.message || '<?php echo esc_js(__('Publishing failed, please try again', 'xhtheme-ai-toolbox')); ?>');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    XHThreadToast.error('<?php echo esc_js(__('Network error, please try again later', 'xhtheme-ai-toolbox')); ?>');
                                })
                                .finally(() => {
                                    submitBtn.innerText = originalText;
                                    submitBtn.disabled = false;
                                });
                        });
                    }

                    // 添加取消按钮
                    const tools = clonedWrapper.querySelector('.xhaicomm-tools');
                    if (tools && submitBtn) {
                        const cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'xh-btn-cancel';
                        cancelBtn.textContent = '<?php echo esc_js(__('Cancel', 'xhtheme-ai-toolbox')); ?>';
                        cancelBtn.style.cssText = 'background:none;color:#6B7280;padding:8px 20px;border-radius:9999px;font-size:12px;font-weight:700;border:1px solid #E5E7EB;cursor:pointer;transition:all 0.3s;margin-right:8px;';
                        cancelBtn.onclick = function () {
                            replyContainer.style.display = 'none';
                            replyContainer.innerHTML = '';
                        };
                        tools.insertBefore(cancelBtn, submitBtn);
                    }

                    replyContainer.innerHTML = '';
                    replyContainer.appendChild(clonedWrapper);
                    replyContainer.style.display = 'block';

                    // 平滑滚动并聚焦
                    setTimeout(() => {
                        replyContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        if (textarea) {
                            textarea.focus();
                        }
                    }, 100);
                }
            } else {
                replyContainer.style.display = 'none';
                replyContainer.innerHTML = '';
            }
        });
    });
</script>