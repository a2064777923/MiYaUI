<?php
/**
 * 前台显示类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 前台显示类
 */
class WPNLC_Frontend {

    /**
     * 构造函数
     */
    public function __construct() {
        $this->init();
    }

    /**
     * 初始化
     */
    private function init() {
        $settings = wpnlc_get_settings();
        
        // 只保留B2主题下载区域的状态显示功能
        if ($settings['show_in_download_box'] === 'yes') {
            add_action('wp_footer', array($this, 'add_download_box_status'));
        }
        
        // 为管理员添加快速检测工具
        if (current_user_can('manage_options')) {
            add_action('wp_footer', array($this, 'add_admin_quick_check'));
        }
    }




    /**
     * 为B2主题下载区域添加状态显示
     */
    public function add_download_box_status() {
        if (!is_single() && !is_page()) {
            return;
        }

        $settings = wpnlc_get_settings();
        // 检查是否启用下载区域显示
        if ($settings['show_in_download_box'] !== 'yes') {
            return;
        }

        global $post;
        $post_id = $post->ID;

        // 获取链接状态
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);

        // 如果是管理员并且开启调试模式，显示调试信息
        if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- WPNLC DEBUG: Post ID: ' . $post_id . ', Status: ' . $link_status . ', Links: ' . count($links_data ?: array()) . ' -->';
        }
        
        if (empty($link_status) || $link_status === 'no_links') {
            // 如果没有检测到状态，但是管理员启用了调试，显示提示
            if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
                ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 4px;">
                    <strong>WPNLC 调试信息：</strong><br>
                    文章ID: <?php echo $post_id; ?><br>
                    链接状态: <?php echo $link_status ?: '未检测'; ?><br>
                    链接数据: <?php echo $links_data ? count($links_data) . ' 个链接' : '无数据'; ?><br>
                    <small>此信息仅在WP_DEBUG模式下对管理员显示</small>
                </div>
                <?php
            }
            return;
        }
        
        // 获取网盘类型信息
        $types = array();
        if (!empty($links_data)) {
            foreach ($links_data as $link) {
                if (!in_array($link['type'], $types)) {
                    $types[] = $link['type'];
                }
            }
        }
        $type_text = wpnlc_get_netdisk_type_text($types);
        $status_text = wpnlc_get_status_text($link_status);
        
        // 检查是否启用前台手动检测功能
        $show_manual_check = $this->should_show_manual_check();
        
        // 添加调试信息（如果启用了调试模式）
        $show_debug = defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options');
        
        // 使用JavaScript动态插入状态标签到下载区域
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($show_debug): ?>
            console.log('WPNLC: 开始查找下载区域，文章ID: <?php echo esc_js($post_id); ?>');
            console.log('WPNLC: 链接状态: <?php echo esc_js($link_status); ?>');
            console.log('WPNLC: 网盘类型: <?php echo esc_js($type_text); ?>');
            <?php endif; ?>
            
            // 查找B2主题的下载区域
            var downloadSelectors = [
                '#download-box',
                '.download-box',
                '.b2-download-box',
                '.b2-single-post-download',
                '.download-list'
            ];
            
            var statusHtml = '<div class="wpnlc-download-status wpnlc-status-<?php echo esc_js($link_status); ?>" style="' +
                'background: #f8f9fa; ' +
                'border: 1px solid #dee2e6; ' +
                'border-radius: 4px; ' +
                'padding: 8px 12px; ' +
                'margin-bottom: 10px; ' +
                'font-size: 14px; ' +
                'display: flex; ' +
                'align-items: center; ' +
                'justify-content: space-between;' +
                '">' +
                '<div class="wpnlc-status-info">' +
                '<span class="wpnlc-status-text" style="font-weight: 500;">网盘状态: <?php echo esc_js($status_text); ?></span>' +
                <?php if (!empty($type_text)): ?>
                '<span class="wpnlc-type-text" style="color: #6c757d; font-size: 12px; margin-left: 8px;"><?php echo esc_js(trim($type_text)); ?></span>' +
                <?php endif; ?>
                <?php if ($settings['show_check_time'] === 'yes'): ?>
                <?php $last_check = get_post_meta($post_id, '_wpnlc_last_check', true); ?>
                <?php if ($last_check): ?>
                '<br><small style="color: #6c757d;">检测时间: <?php echo esc_js(date_i18n('Y-m-d H:i', $last_check)); ?></small>' +
                <?php endif; ?>
                <?php endif; ?>
                '</div>' +
                <?php if ($show_manual_check): ?>
                '<button class="wpnlc-manual-check-btn" data-post-id="<?php echo esc_js($post_id); ?>" style="' +
                'background: #0073aa; ' +
                'color: white; ' +
                'border: none; ' +
                'padding: 6px 12px; ' +
                'border-radius: 4px; ' +
                'font-size: 12px; ' +
                'cursor: pointer; ' +
                'margin-left: 10px; ' +
                'transition: all 0.2s ease; ' +
                'font-weight: 500;' +
                '" ' +
                'onmouseover="this.style.background=\'#005a87\'; this.style.transform=\'translateY(-1px)\'" ' +
                'onmouseout="this.style.background=\'#0073aa\'; this.style.transform=\'translateY(0)\'" ' +
                '>检测</button>' +
                <?php endif; ?>
                '</div>';
            
            var inserted = false;
            // 尝试在多个可能的下载区域插入状态
            for (var i = 0; i < downloadSelectors.length; i++) {
                var downloadBox = document.querySelector(downloadSelectors[i]);
                <?php if ($show_debug): ?>
                console.log('WPNLC: 检查选择器 ' + downloadSelectors[i] + ':', downloadBox);
                <?php endif; ?>
                
                if (downloadBox && !downloadBox.querySelector('.wpnlc-download-status')) {
                    // 在下载区域的开头插入状态
                    downloadBox.insertAdjacentHTML('afterbegin', statusHtml);
                    inserted = true;
                    <?php if ($show_debug): ?>
                    console.log('WPNLC: 成功插入状态到:', downloadSelectors[i]);
                    <?php endif; ?>
                    break;
                }
            }
            
            <?php if ($show_debug): ?>
            if (!inserted) {
                console.log('WPNLC: 未找到合适的下载区域插入状态');
                // 显示页面中所有可能的下载相关元素
                var allDownloadElements = document.querySelectorAll('[class*="download"], [id*="download"]');
                console.log('WPNLC: 页面中的下载相关元素:', allDownloadElements);
            }
            <?php endif; ?>
            
            <?php if ($show_manual_check): ?>
            // 绑定手动检测按钮事件
            setTimeout(function() {
                var checkBtn = document.querySelector('.wpnlc-manual-check-btn');
                if (checkBtn) {
                    checkBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var postId = this.getAttribute('data-post-id');
                        wpnlcManualCheck(postId, this);
                    });
                }
            }, 1500);
            
            // 手动检测函数
            function wpnlcManualCheck(postId, button) {
                // 检查权限
                <?php if ($settings['frontend_check_permission'] === 'logged_in'): ?>
                <?php if (!is_user_logged_in()): ?>
                wpnlcShowModal('权限不足', '请先登录后再进行检测。', 'error');
                return;
                <?php endif; ?>
                <?php endif; ?>
                
                // 禁用按钮，显示加载状态
                button.disabled = true;
                button.textContent = '检测中...';
                button.style.opacity = '0.7';
                
                // 发起AJAX请求
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        // 恢复按钮状态
                        button.disabled = false;
                        button.textContent = '检测';
                        button.style.opacity = '1';
                        
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    var data = response.data;
                                    
                                    // 创建更详细的检测结果显示
                                    var statusBadgeColor = '';
                                    switch(data.status) {
                                        case 'valid':
                                            statusBadgeColor = '#28a745';
                                            break;
                                        case 'invalid':
                                            statusBadgeColor = '#dc3545';
                                            break;
                                        case 'mixed':
                                            statusBadgeColor = '#ffc107';
                                            break;
                                        default:
                                            statusBadgeColor = '#6c757d';
                                    }
                                    
                                    var checkTime = new Date().toLocaleString('zh-CN', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit'
                                    });
                                    
                                    wpnlcShowDetailedModal('检测完成', {
                                        status: data.status_text,
                                        statusColor: statusBadgeColor,
                                        linkCount: data.links_count,
                                        checkTime: checkTime,
                                        cached: data.cached
                                    });
                                    
                                    // 刷新页面以显示最新状态
                                    setTimeout(function() {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    wpnlcShowModal('检测失败', response.data || '检测过程中发生错误', 'error');
                                }
                            } catch (e) {
                                wpnlcShowModal('检测失败', '服务器响应格式错误', 'error');
                            }
                        } else {
                            wpnlcShowModal('检测失败', '网络请求失败，请稍后重试', 'error');
                        }
                    }
                };
                
                xhr.send('action=wpnlc_frontend_manual_check&post_id=' + postId + '&nonce=<?php echo wp_create_nonce('wpnlc_frontend_check'); ?>');
            }
            
            // 显示模态框函数
            function wpnlcShowModal(title, message, type) {
                // 创建模态框
                var modal = document.createElement('div');
                modal.className = 'wpnlc-modal-overlay';
                modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999; display: flex; align-items: center; justify-content: center;';
                
                var modalContent = document.createElement('div');
                modalContent.className = 'wpnlc-modal-content';
                modalContent.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);';
                
                var iconColor = type === 'success' ? '#28a745' : '#dc3545';
                var icon = type === 'success' ? '✓' : '✗';
                
                modalContent.innerHTML = 
                    '<div style="text-align: center; margin-bottom: 15px;">' +
                    '<span style="font-size: 48px; color: ' + iconColor + ';">' + icon + '</span>' +
                    '</div>' +
                    '<h3 style="text-align: center; margin: 0 0 15px 0; color: #333;">' + title + '</h3>' +
                    '<p style="text-align: center; margin: 0 0 20px 0; color: #666; white-space: pre-line; line-height: 1.5;">' + message + '</p>' +
                    '<div style="text-align: center;">' +
                    '<button onclick="this.closest(\'.wpnlc-modal-overlay\').remove()" style="background: ' + iconColor + '; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;">确定</button>' +
                    '</div>';
                
                modal.appendChild(modalContent);
                document.body.appendChild(modal);
                
                // 点击外部关闭
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
                
                // 3秒后自动关闭成功消息
                if (type === 'success') {
                    setTimeout(function() {
                        if (modal.parentNode) {
                            modal.remove();
                        }
                    }, 3000);
                }
            }
            
            // 显示详细检测结果的模态框
            function wpnlcShowDetailedModal(title, data) {
                // 创建模态框
                var modal = document.createElement('div');
                modal.className = 'wpnlc-modal-overlay';
                modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999; display: flex; align-items: center; justify-content: center;';
                
                var modalContent = document.createElement('div');
                modalContent.className = 'wpnlc-modal-content';
                modalContent.style.cssText = 'background: white; padding: 25px; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;';
                
                var cachedText = data.cached ? ' (缓存结果)' : ' (实时检测)';
                
                modalContent.innerHTML = 
                    '<div style="text-align: center; margin-bottom: 20px;">' +
                    '<div style="width: 60px; height: 60px; background: ' + data.statusColor + '; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">' +
                    '<span style="font-size: 24px; color: white;">✓</span>' +
                    '</div>' +
                    '<h3 style="margin: 0; color: #333; font-size: 20px; font-weight: 600;">' + title + '</h3>' +
                    '</div>' +
                    
                    '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">' +
                    '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">' +
                    '<span style="color: #666; font-size: 14px;">链接状态:</span>' +
                    '<span style="background: ' + data.statusColor + '; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 500;">' + data.status + '</span>' +
                    '</div>' +
                    
                    '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">' +
                    '<span style="color: #666; font-size: 14px;">检测链接数:</span>' +
                    '<span style="color: #333; font-weight: 500; font-size: 15px;">' + data.linkCount + ' 个</span>' +
                    '</div>' +
                    
                    '<div style="display: flex; justify-content: space-between; align-items: center;">' +
                    '<span style="color: #666; font-size: 14px;">检测时间:</span>' +
                    '<span style="color: #333; font-weight: 400; font-size: 13px;">' + data.checkTime + cachedText + '</span>' +
                    '</div>' +
                    '</div>' +
                    
                    '<div style="text-align: center;">' +
                    '<button onclick="this.closest(\'.wpnlc-modal-overlay\').remove()" style="background: ' + data.statusColor + '; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s;">确定</button>' +
                    '</div>' +
                    
                    '<div style="text-align: center; margin-top: 15px;">' +
                    '<small style="color: #999; font-size: 12px;">页面将在 2 秒后自动刷新</small>' +
                    '</div>';
                
                modal.appendChild(modalContent);
                document.body.appendChild(modal);
                
                // 点击外部关闭
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
                
                // 3.5秒后自动关闭
                setTimeout(function() {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                }, 3500);
            }
            <?php endif; ?>
        });
        </script>
        <?php
    }

    /**
     * 前台检测功能已禁用（避免JavaScript冲突）
     */
    public function add_frontend_check_script() {
        // 前台不再提供检测功能，避免JavaScript冲突
        return;
    }
    
    /**
     * 为管理员添加快速检测功能（已禁用）
     */
    public function add_admin_quick_check() {
        // 已禁用前台管理员工具显示
        return;
    }

    /**
     * 获取文章的链接状态信息
     *
     * @param int $post_id 文章ID
     * @return array 状态信息
     */
    public function get_post_status_info($post_id) {
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
        
        return array(
            'status' => $link_status,
            'links' => $links_data,
            'last_check' => $last_check
        );
    }

    /**
     * 生成简单的状态标签
     *
     * @param string $status 状态
     * @return string HTML代码
     */
    public function generate_simple_status_tag($status) {
        if (empty($status) || $status === 'no_links') {
            return '';
        }

        $status_text = wpnlc_get_status_text($status);
        $status_class = 'wpnlc-simple-status wpnlc-status-' . $status;

        return '<span class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>';
    }

    /**
     * 检查是否应该显示状态
     *
     * @return bool
     */
    public function should_show_status() {
        $settings = wpnlc_get_settings();
        return $settings['show_in_download_box'] === 'yes';
    }
    
    /**
     * 检查是否应该显示手动检测按钮
     *
     * @return bool
     */
    public function should_show_manual_check() {
        $settings = wpnlc_get_settings();
        
        // 检查是否启用了手动检测功能
        if ($settings['enable_frontend_manual_check'] !== 'yes') {
            return false;
        }
        
        // 检查权限
        if ($settings['frontend_check_permission'] === 'logged_in') {
            return is_user_logged_in();
        } else {
            return true; // everyone
        }
    }

    /**
     * 获取状态CSS类
     *
     * @param string $status 状态
     * @return string CSS类名
     */
    public function get_status_css_class($status) {
        $classes = array('wpnlc-status');
        
        switch ($status) {
            case 'valid':
                $classes[] = 'wpnlc-status-valid';
                break;
            case 'invalid':
                $classes[] = 'wpnlc-status-invalid';
                break;
            case 'mixed':
                $classes[] = 'wpnlc-status-mixed';
                break;
            default:
                $classes[] = 'wpnlc-status-unknown';
                break;
        }
        
        return implode(' ', $classes);
    }

    /**
     * 生成详细的链接信息HTML
     *
     * @param array $links_data 链接数据
     * @return string HTML代码
     */
    public function generate_detailed_links_html($links_data) {
        if (empty($links_data)) {
            return '';
        }
        
        $html = '<div class="wpnlc-detailed-links">';
        $html .= '<h4>链接详情:</h4>';
        $html .= '<ul>';
        
        foreach ($links_data as $link) {
            $status_class = $this->get_status_css_class($link['status']);
            $type_name = wpnlc_get_netdisk_type_text(array($link['type']));
            
            $html .= '<li class="' . esc_attr($status_class) . '">';
            $html .= '<span class="link-type">' . esc_html(trim($type_name)) . '</span>';
            $html .= '<span class="link-status">' . esc_html(wpnlc_get_status_text($link['status'])) . '</span>';
            $html .= '<span class="link-url">' . esc_html($link['url']) . '</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
}
