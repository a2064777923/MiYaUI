
/**
 * Thread Page JavaScript
 * 话题聚合页面交互脚本 - Vanilla JS
 */

/**
 * Toast 通知系统
 * 用于替代 alert()，提供更好的用户体验
 */
const XHThreadToast = {
    // 配置
    config: {
        duration: 4000, // 默认显示时长（毫秒）
        position: 'bottom-right', // 可选: 'top-center', 'bottom-right'
        containerId: 'xhaitool-toast-container'
    },

    // 初始化容器
    init: function () {
        if (!document.getElementById(this.config.containerId)) {
            const container = document.createElement('div');
            container.id = this.config.containerId;
            container.className = 'xhaitool-toast-container';
            container.setAttribute('data-position', this.config.position);
            document.body.appendChild(container);
        }
    },

    // 显示通知
    show: function (message, type = 'info', duration = this.config.duration) {
        this.init();

        const container = document.getElementById(this.config.containerId);
        const toast = document.createElement('div');
        const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

        toast.id = id;
        toast.className = `xhaitool-toast xhaitool-toast-${type}`;

        // 图标 SVG
        const icons = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };

        toast.innerHTML = `
            <div class="xhaitool-toast-icon">${icons[type] || icons.info}</div>
            <div class="xhaitool-toast-message">${this.escapeHtml(message)}</div>
            <button class="xhaitool-toast-close" aria-label="关闭">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;

        container.appendChild(toast);

        // 绑定关闭按钮
        const closeBtn = toast.querySelector('.xhaitool-toast-close');
        closeBtn.addEventListener('click', () => this.remove(id));

        // 触发进入动画
        setTimeout(() => {
            toast.classList.add('xhaitool-toast-show');
        }, 10);

        // 自动移除
        if (duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, duration);
        }

        return id;
    },

    // 移除通知
    remove: function (id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.classList.remove('xhaitool-toast-show');
            toast.classList.add('xhaitool-toast-hide');

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    },

    // HTML 转义
    escapeHtml: function (text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // 快捷方法
    success: function (message, duration) {
        return this.show(message, 'success', duration);
    },

    error: function (message, duration) {
        return this.show(message, 'error', duration);
    },

    info: function (message, duration) {
        return this.show(message, 'info', duration);
    }
};


/**
 * 悬浮导航检测模块
 * 检测 .xhaitool-thread-container 容器
 * 当距离页面顶部小于 30px 时，添加顶部内边距
 */
const XHStickyHeaderDetector = {
    containers: [],

    init: function () {
        // 查找所有需要检测的容器
        this.containers = document.querySelectorAll('.xhaitool-thread-container');

        if (this.containers.length === 0) return;

        // 页面加载时检测
        this.updateAll();

        // 窗口大小改变时重新检测
        window.addEventListener('resize', () => {
            window.requestAnimationFrame(() => this.updateAll());
        }, { passive: true });
    },

    getElementOffsetTop: function (element) {
        // 获取元素相对于文档顶部的位置
        let offsetTop = 0;
        while (element) {
            offsetTop += element.offsetTop;
            element = element.offsetParent;
        }
        return offsetTop;
    },

    updateAll: function () {
        this.containers.forEach(container => {
            const offsetTop = this.getElementOffsetTop(container);
            if (offsetTop < 35) {
                container.classList.add('has-sticky-header');
            } else {
                container.classList.remove('has-sticky-header');
            }
        });
    }
};

// DOM 加载完成后初始化悬浮导航检测
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => XHStickyHeaderDetector.init());
} else {
    XHStickyHeaderDetector.init();
}


/**
 * 区块宽度检测模块
 * 检测 .xhai-xhrelated-section 或 .xhaitool-thread-title-section 元素
 * 当宽度小于 body 宽度时，添加 xhai-xhrelated-narrow 类
 */
const XHRelatedSectionDetector = {
    section: null,

    init: function () {
        this.section = document.querySelector('.xhai-xhrelated-section')
            || document.querySelector('.xhaitool-thread-title-section');
        if (!this.section) return;

        // 页面加载时检测
        this.update();

        // 窗口大小改变时重新检测
        window.addEventListener('resize', () => {
            window.requestAnimationFrame(() => this.update());
        }, { passive: true });
    },

    update: function () {
        if (!this.section) return;

        const sectionWidth = this.section.offsetWidth;
        const bodyWidth = document.body.clientWidth;
        if (sectionWidth < bodyWidth) {
            this.section.classList.add('xhai-xhrelated-narrow');
        } else {
            this.section.classList.remove('xhai-xhrelated-narrow');
        }
    }
};

// DOM 加载完成后初始化相关话题区块检测
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => XHRelatedSectionDetector.init());
} else {
    XHRelatedSectionDetector.init();
}


(function () {
    'use strict';

    // Thread Page Handler
    const XHThreadPage = {
        // 配置
        config: {
            containerSelector: '.xhaitool-threads-grid',
            loadMoreBtnSelector: '.xhaitool-loadmore-btn',
            currentPage: 1,
            postsPerPage: 6,
            maxPages: 1,
            loading: false
        },

        // 初始化
        init: function () {
            // 等待DOM加载完成
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.start());
            } else {
                this.start();
            }
        },

        start: function () {
            this.setMaxPages();
            this.bindEvents();
        },

        // 绑定事件
        bindEvents: function () {
            const btn = document.querySelector(this.config.loadMoreBtnSelector);
            if (!btn) return;

            btn.addEventListener('click', (e) => {
                e.preventDefault();

                if (this.config.loading) return;

                if (btn.classList.contains('no-more') || btn.classList.contains('loading')) {
                    return;
                }

                this.loadMore(btn);
            });
        },

        // 设置最大页数
        setMaxPages: function () {
            const btn = document.querySelector(this.config.loadMoreBtnSelector);
            if (btn) {
                this.config.maxPages = parseInt(btn.getAttribute('data-max-pages')) || 1;
                this.config.currentPage = parseInt(btn.getAttribute('data-current-page')) || 1;
                this.config.postsPerPage = parseInt(btn.getAttribute('data-posts-per-page')) || 6;
            }
        },

        // 加载更多
        loadMore: function (btn) {
            const nextPage = this.config.currentPage + 1;

            if (nextPage > this.config.maxPages) {
                this.showNoMore(btn);
                return;
            }

            // 设置加载状态
            this.config.loading = true;
            btn.classList.add('loading');
            const originalText = btn.textContent;
            btn.textContent = '';

            // 使用REST API
            fetch(xhThreadPageData.restUrl + '/thread/load-more', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': xhThreadPageData.nonce
                },
                body: JSON.stringify({
                    page: nextPage,
                    posts_per_page: this.config.postsPerPage
                })
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success && response.html) {
                        // 添加新内容
                        const container = document.querySelector(this.config.containerSelector);
                        if (container) {
                            // 创建临时容器来解析HTML字符串
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = response.html;

                            // 将新元素移动到容器中
                            while (tempDiv.firstChild) {
                                container.appendChild(tempDiv.firstChild);
                            }
                        }

                        // 更新当前页码
                        this.config.currentPage = nextPage;
                        btn.setAttribute('data-current-page', nextPage);

                        // 检查是否还有更多
                        if (nextPage >= this.config.maxPages) {
                            this.showNoMore(btn);
                        } else {
                            btn.classList.remove('loading');
                            btn.textContent = originalText;
                        }

                        // 触发自定义事件
                        const event = new CustomEvent('xhtheme:threadsLoaded', { detail: response });
                        document.dispatchEvent(event);
                    } else {
                        this.showNoMore(btn);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showError(btn, originalText);
                })
                .finally(() => {
                    this.config.loading = false;
                });
        },

        // 显示没有更多
        showNoMore: function (btn) {
            btn.classList.remove('loading');
            btn.classList.add('no-more');
            btn.textContent = '没有更多内容了';
        },

        // 显示错误
        showError: function (btn, originalText) {
            btn.classList.remove('loading');
            btn.textContent = '加载失败，请重试';

            setTimeout(() => {
                btn.textContent = originalText;
            }, 2000);
        }
    };

    // 初始化
    XHThreadPage.init();

})();


/**
 * Thread Page JavaScript
 * Handles interactions for the thread single page template
 */

(function () {
    'use strict';

    // 确保仅在包含 .xhaitool-thread-c1 容器的页面运行
    const threadContainer = document.querySelector('.xhaitool-thread-c1');
    if (!threadContainer) {
        return;
    }

    // 获取本地化数据
    const pageData = window.xhThreadPageData || {};

    /**
     * 阅读进度条
     */
    function initProgressBar() {
        const progressBar = document.getElementById('xh-progress-bar');
        if (!progressBar) return;

        let ticking = false;

        function updateProgressBar() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + '%';
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(updateProgressBar);
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * 平滑滚动到评论区
     */
    function initSmoothScroll() {
        // 参与讨论按钮
        const joinBtns = threadContainer.querySelectorAll('.xhbtn-discussion');
        joinBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.getElementById('comments');
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // 聚焦输入框
                    const input = document.getElementById('xhaicomm-input');
                    if (input) setTimeout(() => input.focus(), 500);
                }
            });
        });
    }



    /**
     * 评论提交功能
     */
    function initCommentSubmission() {
        const submitBtn = document.getElementById('xh-submit-comment');
        const inputArea = document.getElementById('xhaicomm-input');
        const commentsList = document.getElementById('xhaicomm-list');

        if (!submitBtn || !inputArea) return;

        submitBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const content = inputArea.value.trim();
            if (!content) {
                XHThreadToast.error('请输入评论内容');
                inputArea.focus();
                return;
            }

            if (!pageData.postId) {
                console.error('Post ID not found');
                return;
            }

            // Loading state
            const originalText = submitBtn.innerText;
            submitBtn.innerText = '发布中...';
            submitBtn.disabled = true;

            const data = {
                post_id: pageData.postId,
                content: content
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
                        inputArea.value = '';

                        // 插入新评论
                        if (commentsList && data.html) {
                            // 移除可能的"暂无评论"提示
                            const noComments = commentsList.querySelector('.no-comments');
                            if (noComments) noComments.remove();

                            // 插入到列表顶部 (after comments title)
                            const title = commentsList.querySelector('.xhaicomm-s-title'); // hidden title
                            if (title) {
                                title.insertAdjacentHTML('afterend', data.html);
                            } else {
                                commentsList.insertAdjacentHTML('afterbegin', data.html);
                            }
                        }

                        XHThreadToast.success('评论发布成功！');
                    } else {
                        XHThreadToast.error(data.message || '发布失败，请重试');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    XHThreadToast.error('网络错误，请稍后重试');
                })
                .finally(() => {
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                });
        });
    }

    /**
     * 图片懒加载优化
     */
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) return;
        // Polyfill logic if needed
    }

    /**
     * 错误处理
     */
    function handleError(error, context) {
        console.error('[Thread Page] Error in ' + context + ':', error);
    }

    /**
     * 初始化所有功能
     */
    function init() {
        try {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeFeatures);
            } else {
                initializeFeatures();
            }
        } catch (error) {
            handleError(error, 'initialization');
        }
    }

    /**
     * 初始化各个功能模块
     */
    function initializeFeatures() {
        try { initProgressBar(); } catch (e) { handleError(e, 'progress bar'); }
        try { initSmoothScroll(); } catch (e) { handleError(e, 'smooth scroll'); }
        try { initCommentSubmission(); } catch (e) { handleError(e, 'comment submission'); }
        try { initCommentLikes(); } catch (e) { handleError(e, 'comment likes'); }
        try { initLazyLoading(); } catch (e) { handleError(e, 'lazy loading'); }
    }

    /**
     * 评论点赞功能
     */
    function initCommentLikes() {
        // 使用事件委托处理所有点赞按钮点击
        threadContainer.addEventListener('click', function (e) {
            const likeBtn = e.target.closest('.xhaitool-xhaicomm-like');
            if (!likeBtn) return;

            e.preventDefault();

            // 防止重复点击
            if (likeBtn.classList.contains('processing')) return;

            const commentItem = likeBtn.closest('.xhaicomm-item, .comment');
            if (!commentItem) return;

            const commentId = commentItem.id.replace('comment-', '');
            if (!commentId) return;

            // 设置处理中状态
            likeBtn.classList.add('processing');

            fetch(pageData.restUrl + '/comment/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': pageData.nonce
                },
                body: JSON.stringify({
                    comment_id: commentId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 更新点赞状态
                        if (data.liked) {
                            likeBtn.classList.add('liked');
                        } else {
                            likeBtn.classList.remove('liked');
                        }

                        // 更新点赞数
                        const countEl = likeBtn.querySelector('.like-count');
                        if (countEl) {
                            countEl.textContent = data.like_count;
                        } else {
                            // 如果不存在，创建点赞数显示
                            const likeCount = document.createElement('span');
                            likeCount.className = 'like-count';
                            likeCount.textContent = data.like_count;
                            likeBtn.appendChild(likeCount);
                        }

                        // 如果点赞数为0，隐藏数字
                        if (data.like_count === 0 && countEl) {
                            countEl.textContent = '';
                        }
                    } else {
                        console.error('Like failed:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    likeBtn.classList.remove('processing');
                });
        });

        // 初始化现有评论的点赞状态
        initExistingCommentLikes();
    }

    /**
     * 初始化已存在评论的点赞数据
     */
    function initExistingCommentLikes() {
        const comments = threadContainer.querySelectorAll('.xhaicomm-item, .comment');
        comments.forEach(comment => {
            const commentId = comment.id.replace('xhaicomm-', '');
            if (!commentId) return;
        });
    }


    // 启动应用
    init();

})();
