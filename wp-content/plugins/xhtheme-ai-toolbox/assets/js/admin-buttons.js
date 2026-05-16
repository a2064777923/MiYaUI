class AdminButtons {
  constructor() {
    this.initEvents();
    this.buttonTypes = {
      'add': this.handleAddaitasks.bind(this),
      // 可以添加更多按钮类型
    };
    this.initNotices();
  }

  /**
   * 初始化事件监听
   */
  initEvents() {
    document.addEventListener('click', (e) => {
      const button = e.target.closest('[data-taskclick]');
      if (button) {
        const buttonType = button.dataset.taskclick;
        if (this.buttonTypes[buttonType]) {            
          this.buttonTypes[buttonType](button);
        }
      }
      
      // 关闭通知弹窗的点击事件
      if (e.target.closest('.xhtheme-notice-close')) {
        const closeButton = e.target.closest('.xhtheme-notice-close');
        const noticeId = closeButton.dataset.noticeId || '';
        const days = closeButton.dataset.days || 0;
        this.closeNotice(noticeId, days);
      }
    });
  }

  /**
   * 初始化所有通知弹窗
   */
  initNotices() {
    // 检查是否在thread列表页
    if (xhaitoolbox_vars.pagehook === 'edit.php' && new URLSearchParams(window.location.search).get('post_type') === 'thread') {
      // 检查本地存储中是否已经关闭了通知
      const noticeHidden = localStorage.getItem('xhtheme_thread_notice_hidden');
      const currentTime = new Date().getTime();
      
      // 如果没有关闭通知或者关闭时间已过期
      if (!noticeHidden || currentTime > parseInt(noticeHidden)) {
        this.showNotice('thread', {
          title: '话题功能使用说明 「重要!!! 」',
          content: `
            <p><strong>话题功能使用说明：</strong></p>
            <ol>
              <li>话题生成时只有标题没有内容，状态为待审核，请勿手动操作，等待系统后续生成内容后自动切换状态，并更新引用文章的缓存；</li>
              <li>前端话题页面不显示报404或者显示非话题内容页，您需要到「后台->设置->固定链接」点击一下保存按钮来更新静态规则；</li>
              <li>话题内容生成之后可以手动对内容进行编辑修改，包括修改话题的标题、别名以及内容；</li>
              <li>前端UI适配说明：插件添加的页面属于通用样式，可能与部分主题存在兼容问题。我们后续计划通过增加页面样式选择及动态加载优化体验。如您的网站出现显示异常，请邮件反馈至 admin@xhtheme.com，我们将针对性适配调整。</li>
              <li>更多常见的使用问题可查看官方文档：<a href="https://www.xhtheme.com/docs/aitoolbox/problem" target="_blank">常见问题(FAQ)</a></li>
            </ol>
          `,
          days: 14
        });
      }
    }

    // 检查是否在评论列表页
    if (xhaitoolbox_vars.pagehook === 'edit-comments.php' && xhaitoolbox_vars.userMember && xhaitoolbox_vars.aiComment) {
      // 检查本地存储中是否已经关闭了通知
      const noticeHidden = localStorage.getItem('xhtheme_comment_notice_hidden');
      const currentTime = new Date().getTime();
      
      // 如果没有关闭通知或者关闭时间已过期
      if (!noticeHidden || currentTime > parseInt(noticeHidden)) {
        this.showNotice('comment', {
          title: 'AI评论功能使用说明 「重要!!! 」',
          content: `
            <p><strong>AI评论功能使用说明：</strong></p>
            <ol>
              <li>AI生成的评论会自动标记为AI类型，用于跟正常评论区分；</li>
              <li>AI评论初步生成时状态为待审核并设置预设发布时间，到预设时间会自动发布；</li>
              <li>评论的提交时间就是自动发布的预设时间，系统将自动处理，<strong>无需您手动批准</strong>；</li>
              <li>手动批准时一定要修改发布时间小于当前时间，否则前端显示会存在问题；</li>
              <li>AI评论您可以同正常评论一样进行编辑及删除。</li>
              <li>更多常见的使用问题可查看官方文档：<a href="https://www.xhtheme.com/docs/aitoolbox/problem" target="_blank">常见问题(FAQ)</a></li>
            </ol>
          `,
          days: 14
        });
      }
    }

    if( xhaitoolbox_vars.pagehook === 'edit.php' && !new URLSearchParams(window.location.search).get('post_type') ){
      // 判断id为title的元素宽度，如果宽度低于20px则显示弹窗，关闭后10分钟内不再弹出
      const titleElement = document.getElementById('title');
      if (titleElement && titleElement.offsetWidth < 20) {
        // 检查本地存储中是否已经关闭了通知
        const noticeHidden = localStorage.getItem('xhtheme_title_notice_hidden');
        const currentTime = new Date().getTime();
        
        // 如果没有关闭通知或者关闭时间已过期（10分钟）
        if (!noticeHidden || currentTime > parseInt(noticeHidden)) {
          this.showNotice('title', {
            title: '列表错乱处理方法',
            content: '<p><strong>提示：</strong>程序检测到您的列表显示有异常，请按下面的方法进行处理。</p><p>点击右上角的显示选项，把不重要的列勾选去掉，即可恢复列表的正常显示！</p>',
            days: 0
          });
        }
      }
    }
  }

  /**
   * 显示通知弹窗
   * @param {string} noticeType - 通知类型，用于区分不同的通知
   * @param {object} options - 通知配置选项
   * @param {string} options.title - 通知标题
   * @param {string} options.content - 通知内容HTML
   * @param {number} options.days - 不再提醒的天数，默认为14天，如果设置为0则不显示长期关闭按钮
   */
  showNotice(noticeType, options) {
    const noticeId = `xhtheme-${noticeType}-notice`;
    // 设置默认值
    const days = options.days !== undefined ? options.days : 14;
    const daysText = days > 0 ? `${days}天内不再提醒` : '';
    
    // 构建长期关闭按钮HTML
    let longTermButtonHtml = '';
    if (days > 1) {
      longTermButtonHtml = `<button class="xhtheme-notice-close button button-primary" data-notice-id="${noticeId}" data-days="${days}">我知道了，${daysText}</button>`;
    }
    
    const noticeHtml = `
      <div class="xhtheme-notice-overlay">
        <div class="xhtheme-notice-modal">
          <div class="xhtheme-notice-header">
            <h2><span class="dashicons dashicons-warning"></span>${options.title}</h2>
            <button class="xhtheme-notice-close" data-notice-id="${noticeId}" data-days="1">&times;</button>
          </div>
          <div class="xhtheme-notice-content">
            ${options.content}
          </div>
          <div class="xhtheme-notice-footer">
            ${longTermButtonHtml}
            <button class="xhtheme-notice-close button" data-notice-id="${noticeId}" data-days="1">关闭</button>
          </div>
        </div>
      </div>
    `;
    
    // 创建样式（如果不存在）
    if (!document.querySelector('style[data-xhtheme-notice]')) {
      this.createNoticeStyles();
    }
    
    // 添加到DOM
    const noticeElement = document.createElement('div');
    noticeElement.id = noticeId;
    noticeElement.innerHTML = noticeHtml;
    document.body.appendChild(noticeElement);
  }

  /**
   * 创建通知弹窗样式
   */
  createNoticeStyles() {
    const style = document.createElement('style');
    style.setAttribute('data-xhtheme-notice', 'true');
    style.textContent = `
      .xhtheme-notice-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
      }
      .xhtheme-notice-modal {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.25);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        animation: xhthemeNoticeSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid #ddd;
      }
      @keyframes xhthemeNoticeSlideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
      }
      .xhtheme-notice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(90deg, #e53935, #ff5252);
        color: white;
      }
      .xhtheme-notice-header h2 {
        margin: 0;
        font-size: 18px;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .xhtheme-notice-header .dashicons {
        font-size: 22px;
        width: 22px;
        height: 22px;
      }
      .xhtheme-notice-header button {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: white;
        opacity: 0.8;
        transition: opacity 0.2s;
      }
      .xhtheme-notice-header button:hover {
        opacity: 1;
      }
      .xhtheme-notice-content {
        padding: 20px;
        line-height: 1.6;
      }
      .xhtheme-notice-highlight {
        background-color: #fff9c4;
        border-left: 4px solid #fbc02d;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
      }
      .xhtheme-notice-important {
        background-color: #ffebee;
        border-left: 4px solid #e53935;
        padding: 10px 15px;
        margin-top: 20px;
        border-radius: 4px;
      }
      .xhtheme-notice-content ul, .xhtheme-notice-content ol {
        margin-left: 20px;
      }
      .xhtheme-notice-content li {
        margin-bottom: 10px;
      }
      .xhtheme-notice-footer {
        padding: 15px 20px;
        text-align: right;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f5f5f5;
      }
      .xhtheme-notice-footer .button-primary {
        background: #e53935;
        border-color: #c62828;
        color: white;
      }
      .xhtheme-notice-footer .button-primary:hover {
        background: #d32f2f;
        border-color: #b71c1c;
      }
    `;
    document.head.appendChild(style);
  }

  /**
   * 关闭通知弹窗并设置本地存储
   * @param {string} noticeId - 通知ID
   * @param {number} days - 关闭的天数
   */
  closeNotice(noticeId, days) {
    const notice = document.getElementById(noticeId);
    if (notice) {
      notice.remove();
      if (days > 0) {
        // 设置本地存储，记录关闭时间
        const expiryTime = new Date().getTime() + (parseInt(days) * 24 * 60 * 60 * 1000);
        
        // 根据通知ID设置不同的本地存储字段
        const key = noticeId.replace(/-/g, '_') + '_hidden';
        localStorage.setItem(key, expiryTime);
      }
    }
  }

  /**
   * 处理AI任务按钮点击
   * @param {HTMLElement} button - 点击的按钮元素
   */
  handleAddaitasks(button) {
    const postId        = button.dataset.postId;
    const taskType      = button.dataset.type;
    const originalText  = button.innerHTML;
    
    this.setButtonState(button, 'loading');
    
    fetch(ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'handle_xhaitool_aitasks',
        post_id: postId,
        type: taskType,
        _ajax_nonce: xhaitoolbox_vars.nonce
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        this.setButtonState(button, 'success', data.data.message);
      } else {
        this.setButtonState(button, 'error', data.data.message || '操作失败，请重试！');
      }
    })
    .catch(error => {
      this.setButtonState(button, 'error', '请求错误');
      console.error('Error:', error);
    });
  }

  /**
   * 设置按钮状态
   * @param {HTMLElement} button - 按钮元素
   * @param {string} state - 状态（loading, success, error）
   * @param {string|null} message - 显示的消息
   */
  setButtonState(button, state, message = null) {
    const states = {
      loading: {
        html: 'Loading ...',
        disabled: true
      },
      success: {
        html: message || '<span style="color: #14c2b2">Success</span>',
      },
      error: {
        html: message || xhaitoolbox_vars.errorMsg,
        disabled: false
      }
    };
    if (state == 'success') {
        const parent = button.closest('p');
        if (parent) {
          parent.innerHTML = states[state].html;
          return;
        }
    }
    button.innerHTML = states[state].html;
    button.disabled = states[state].disabled;
    button.dataset.buttonState = state;
  }
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
  window.adminButtons = new AdminButtons();
});