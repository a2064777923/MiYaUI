<?php
/**
 * 后台管理类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 后台管理类
 */
class WPNLC_Admin {

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
        // 添加文章列表列
        add_filter('manage_posts_columns', array($this, 'add_posts_columns'));
        add_action('manage_posts_custom_column', array($this, 'display_posts_column'), 10, 2);
        
        // 添加页面列表列
        add_filter('manage_pages_columns', array($this, 'add_posts_columns'));
        add_action('manage_pages_custom_column', array($this, 'display_posts_column'), 10, 2);
        
        // 添加文章编辑页面的元框
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // 添加仪表盘小工具
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // 添加批量操作
        add_filter('bulk_actions-edit-post', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-edit-post', array($this, 'handle_bulk_actions'), 10, 3);

        // 添加行操作
        add_filter('post_row_actions', array($this, 'add_row_actions'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_row_actions'), 10, 2);

        // 添加文章列表页面的工具栏
        add_action('restrict_manage_posts', array($this, 'add_posts_toolbar'));
        add_action('admin_notices', array($this, 'display_admin_notices'));

        // 添加文章列表过滤功能
        add_action('restrict_manage_posts', array($this, 'add_posts_filter'));
        add_filter('parse_query', array($this, 'filter_posts_by_status'));
    }

    /**
     * 添加文章列表列
     *
     * @param array $columns 现有列
     * @return array 修改后的列
     */
    public function add_posts_columns($columns) {
        $columns['wpnlc_status'] = __('网盘链接状态', 'wp-netdisk-link-checker');
        return $columns;
    }

    /**
     * 显示文章列表列内容
     *
     * @param string $column_name 列名
     * @param int $post_id 文章ID
     */
    public function display_posts_column($column_name, $post_id) {
        if ($column_name !== 'wpnlc_status') {
            return;
        }
        
        // 获取文章状态信息（优先从新表读取）
        $status_info = $this->get_post_status_info($post_id);
        
        if (empty($status_info['status'])) {
            echo '<div class="wpnlc-column-content">';
            echo '<span class="wpnlc-column-status wpnlc-status-unknown">未检测</span>';
            echo '</div>';
            return;
        }

        echo '<div class="wpnlc-column-content">';
        
        // 显示状态标签
        if ($status_info['status'] === 'no_links') {
            echo '<span class="wpnlc-column-status wpnlc-status-no-links">无网盘链接</span>';
        } else {
            $status_text = wpnlc_get_status_text($status_info['status']);
            $status_class = 'wpnlc-column-status netdisk-status-' . $status_info['status'];
            echo '<span class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>';
        }
        
        // 显示网盘类型
        if (!empty($status_info['types'])) {
            $type_text = wpnlc_get_netdisk_type_text($status_info['types']);
            if (!empty($type_text)) {
                echo '<span class="wpnlc-column-types">' . esc_html(trim($type_text)) . '</span>';
            }
        }
        
        // 显示最后检测时间
        if ($status_info['last_check']) {
            $check_time = date_i18n('m-d H:i', $status_info['last_check']);
            echo '<span class="wpnlc-column-time">检测: ' . esc_html($check_time) . '</span>';
        }
        
        echo '</div>';
    }

    /**
     * 添加元框
     */
    public function add_meta_boxes() {
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wpnlc_meta_box',
                __('网盘链接状态', 'wp-netdisk-link-checker'),
                array($this, 'display_meta_box'),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * 显示元框内容
     *
     * @param WP_Post $post 文章对象
     */
    public function display_meta_box($post) {
        $post_id = $post->ID;
        
        // 获取文章状态信息（优先从新表读取）
        $status_info = $this->get_post_status_info($post_id);
        
        echo '<div class="wpnlc-meta-box">';
        
        if (empty($status_info['status'])) {
            echo '<p>尚未检测网盘链接状态</p>';
        } elseif ($status_info['status'] === 'no_links') {
            echo '<p>此文章中没有发现网盘链接</p>';
        } else {
            $status_text = wpnlc_get_status_text($status_info['status']);
            $status_class = 'netdisk-status-' . $status_info['status'];

            echo '<p>状态: <span class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></p>';
            
            if ($status_info['last_check']) {
                $check_time = date_i18n('Y-m-d H:i:s', $status_info['last_check']);
                echo '<p>最后检测: ' . esc_html($check_time) . '</p>';
            }
            
            // 显示链接详情
            if (!empty($status_info['links_data'])) {
                echo '<h4>链接详情:</h4>';
                echo '<ul>';
                foreach ($status_info['links_data'] as $link) {
                    $type_name = wpnlc_get_netdisk_type_text(array($link['type']));
                    $link_status_text = wpnlc_get_status_text($link['status']);
                    $link_status_class = 'wpnlc-status-' . $link['status'];

                    echo '<li>';
                    echo '<strong>' . esc_html(trim($type_name)) . '</strong>: ';
                    echo '<span class="' . esc_attr($link_status_class) . '">' . esc_html($link_status_text) . '</span>';

                    // 显示链接来源
                    if (isset($link['source'])) {
                        $source_text = '';
                        switch ($link['source']) {
                            case 'b2_download':
                                $source_text = ' (B2下载区域)';
                                break;
                            case 'b2_download_old':
                                $source_text = ' (B2下载区域-旧版)';
                                break;
                            default:
                                $source_text = ' (文章内容)';
                                break;
                        }
                        echo '<small style="color: #666;">' . $source_text . '</small>';
                    }

                    echo '<br><small>' . esc_html($link['url']) . '</small>';

                    // 显示资源名称和密码（如果有）
                    if (isset($link['name']) && !empty($link['name'])) {
                        echo '<br><small>资源名称: ' . esc_html($link['name']) . '</small>';
                    }
                    if (isset($link['password']) && !empty($link['password'])) {
                        echo '<br><small>提取信息: ' . esc_html($link['password']) . '</small>';
                    }

                    echo '</li>';
                }
                echo '</ul>';
            }
        }
        
        // 检测按钮已禁用，避免JavaScript交互
        echo '<p><em>检测功能已禁用，避免弹窗干扰</em></p>';

        // 调试信息容器
        echo '<div id="wpnlc-debug-info-' . esc_attr($post_id) . '" style="display: none; margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; font-size: 12px;"></div>';
        
        echo '</div>';
        
        // JavaScript已完全移除，避免任何弹窗




    }

    /**
     * 添加仪表盘小工具
     */
    public function add_dashboard_widget() {
        $settings = wpnlc_get_settings();
        
        if ($settings['show_dashboard_widget'] === 'yes') {
            wp_add_dashboard_widget(
                'wpnlc_dashboard_widget',
                __('网盘链接状态统计', 'wp-netdisk-link-checker'),
                array($this, 'display_dashboard_widget')
            );
        }
    }

    /**
     * 显示仪表盘小工具内容 - 现代化美化版本
     */
    public function display_dashboard_widget() {
        // 获取统计数据
        $post_stats = wpnlc_get_post_check_statistics();
        $link_stats = wpnlc_get_link_statistics();
        
        // 计算进度百分比
        $progress = $post_stats['total'] > 0 ? round(($post_stats['checked'] / $post_stats['total']) * 100, 1) : 0;
        
        echo '<div class="wpnlc-dashboard-widget">';
        
        // 插件状态概览 - 卡片式设计
        echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; color: white; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">';
        echo '<div style="display: flex; align-items: center; justify-content: space-between;">';
        echo '<div>';
        echo '<h3 style="margin: 0; font-size: 18px; font-weight: 600;">🔗 链接检测状态</h3>';
        echo '<p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 14px;">实时监控网盘链接健康状态</p>';
        echo '</div>';
        echo '<div style="text-align: right;">';
        echo '<div style="font-size: 28px; font-weight: bold; line-height: 1;">' . number_format($post_stats['total']) . '</div>';
        echo '<div style="font-size: 12px; opacity: 0.8; margin-top: 2px;">总文章数</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // 检测进度区域
        echo '<div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #28a745;">';
        echo '<div style="display: flex; align-items: center; margin-bottom: 10px;">';
        echo '<span class="dashicons dashicons-analytics" style="color: #28a745; margin-right: 8px;"></span>';
        echo '<strong style="color: #495057;">检测进度</strong>';
        echo '<span style="margin-left: auto; font-weight: bold; color: #28a745;">' . $progress . '%</span>';
        echo '</div>';
        
        // 精美的进度条
        echo '<div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 8px;">';
        echo '<div style="background: linear-gradient(90deg, #28a745, #20c997); height: 100%; width: ' . $progress . '%; transition: width 0.6s ease; border-radius: 4px;"></div>';
        echo '</div>';
        
        echo '<div style="font-size: 12px; color: #6c757d;">';
        echo '已检测: <strong>' . number_format($post_stats['checked']) . '</strong> / ' . number_format($post_stats['total']) . ' 篇文章';
        echo '</div>';
        echo '</div>';
        
        // 链接状态统计 - 卡片风格
        echo '<div style="margin-bottom: 16px;">';
        echo '<div style="display: flex; align-items: center; margin-bottom: 12px;">';
        echo '<span class="dashicons dashicons-admin-links" style="color: #6f42c1; margin-right: 8px;"></span>';
        echo '<strong style="color: #495057;">链接状态分布</strong>';
        echo '</div>';
        
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">';
        
        // 有效链接卡片
        echo '<div style="background: linear-gradient(135deg, #d4edda, #c3e6cb); padding: 12px; border-radius: 6px; text-align: center; border: 1px solid #b8daff;">';
        echo '<div style="font-size: 18px; font-weight: bold; color: #155724; margin-bottom: 4px;">' . number_format($link_stats['valid']) . '</div>';
        echo '<div style="font-size: 11px; color: #155724; font-weight: 500;">🟢 有效</div>';
        echo '</div>';
        
        // 失效链接卡片
        echo '<div style="background: linear-gradient(135deg, #f8d7da, #f5c6cb); padding: 12px; border-radius: 6px; text-align: center; border: 1px solid #f5c6cb;">';
        echo '<div style="font-size: 18px; font-weight: bold; color: #721c24; margin-bottom: 4px;">' . number_format($link_stats['invalid']) . '</div>';
        echo '<div style="font-size: 11px; color: #721c24; font-weight: 500;">🔴 失效</div>';
        echo '</div>';
        
        // 部分有效卡片
        echo '<div style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); padding: 12px; border-radius: 6px; text-align: center; border: 1px solid #ffeaa7;">';
        echo '<div style="font-size: 18px; font-weight: bold; color: #856404; margin-bottom: 4px;">' . number_format($link_stats['mixed']) . '</div>';
        echo '<div style="font-size: 11px; color: #856404; font-weight: 500;">🟡 混合</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        // 网盘类型统计
        $this->display_dashboard_netdisk_types();
        
        // 最近失效的链接
        $recent_invalid = wpnlc_get_recent_invalid_links(3);
        if (!empty($recent_invalid)) {
            echo '<div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 16px; margin-bottom: 16px;">';
            echo '<div style="display: flex; align-items: center; margin-bottom: 12px;">';
            echo '<span class="dashicons dashicons-warning" style="color: #e53e3e; margin-right: 8px;"></span>';
            echo '<strong style="color: #742a2a;">最近失效的链接</strong>';
            echo '</div>';
            
            echo '<div style="display: flex; flex-direction: column; gap: 8px;">';
            foreach ($recent_invalid as $invalid) {
                echo '<div style="background: white; padding: 10px; border-radius: 4px; border-left: 3px solid #e53e3e; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                echo '<a href="' . get_edit_post_link($invalid['post_id']) . '" style="text-decoration: none; color: #2b6cb0; font-weight: 500; font-size: 13px;">' . esc_html($invalid['title']) . '</a>';
                if ($invalid['check_time']) {
                    $check_time = date_i18n('m-d H:i', strtotime($invalid['check_time']));
                    echo '<div style="font-size: 11px; color: #a0aec0; margin-top: 4px;">🕰️ ' . $check_time . '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // 现代化操作按钮
        echo '<div style="background: #f7fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">';
        echo '<div style="display: flex; gap: 8px;">';
        echo '<a href="' . admin_url('tools.php?page=wpnlc-statistics') . '" class="button button-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 6px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; flex: 1; justify-content: center;">';
        echo '<span class="dashicons dashicons-chart-area" style="font-size: 16px;"></span> 统计详情';
        echo '</a>';
        echo '<a href="' . admin_url('options-general.php?page=wp-netdisk-link-checker') . '" class="button" style="border-radius: 6px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; flex: 1; justify-content: center;">';
        echo '<span class="dashicons dashicons-admin-settings" style="font-size: 16px;"></span> 设置';
        echo '</a>';
        echo '</div>';
        echo '</div>';

        // 添加动画效果和响应式样式
        echo '<style>';
        echo '.wpnlc-dashboard-widget {';
        echo '    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;';
        echo '    line-height: 1.5;';
        echo '}';
        
        // 悬停动画效果
        echo '.wpnlc-dashboard-widget [style*="background:"][style*="padding:"][style*="border-radius:"]:hover {';
        echo '    transform: translateY(-2px);';
        echo '    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;';
        echo '    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);';
        echo '}';
        
        // 链接悬停效果
        echo '.wpnlc-dashboard-widget a:hover {';
        echo '    transform: scale(1.02);';
        echo '    transition: all 0.2s ease;';
        echo '}';
        
        // 按钮悬停效果
        echo '.wpnlc-dashboard-widget .button:hover {';
        echo '    transform: translateY(-1px) scale(1.02);';
        echo '    box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        echo '    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);';
        echo '}';
        
        // 状态卡片动画
        echo '.wpnlc-dashboard-widget [style*="grid-template-columns"] > div:hover {';
        echo '    transform: scale(1.05);';
        echo '    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);';
        echo '}';
        
        // 网盘类型项目悬停效果
        echo '.wpnlc-dashboard-widget [style*="background: rgba(255,255,255,0.8)"] {';
        echo '    cursor: pointer;';
        echo '}';
        
        echo '.wpnlc-dashboard-widget [style*="background: rgba(255,255,255,0.8)"]:hover {';
        echo '    background: rgba(255,255,255,1) !important;';
        echo '    transform: translateX(4px);';
        echo '    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;';
        echo '}';
        
        // 进度条动画
        echo '.wpnlc-dashboard-widget [style*="transition: width"] {';
        echo '    animation: progressLoad 1.5s ease-in-out;';
        echo '}';
        
        echo '@keyframes progressLoad {';
        echo '    from { width: 0 !important; }';
        echo '}';
        
        // 响应式设计
        echo '@media (max-width: 768px) {';
        echo '    .wpnlc-dashboard-widget [style*="grid-template-columns"] {';
        echo '        grid-template-columns: 1fr !important;';
        echo '        gap: 6px !important;';
        echo '    }';
        echo '    .wpnlc-dashboard-widget [style*="display: flex"][style*="gap: 8px"] {';
        echo '        flex-direction: column !important;';
        echo '        gap: 6px !important;';
        echo '    }';
        echo '    .wpnlc-dashboard-widget [style*="justify-content: space-between"] {';
        echo '        flex-direction: column !important;';
        echo '        text-align: center !important;';
        echo '    }';
        echo '}';
        
        // 深色模式支持
        echo '@media (prefers-color-scheme: dark) {';
        echo '    .wpnlc-dashboard-widget [style*="background: #f8f9fa"] {';
        echo '        background: #2d3748 !important;';
        echo '        color: #e2e8f0 !important;';
        echo '    }';
        echo '    .wpnlc-dashboard-widget [style*="background: #f7fafc"] {';
        echo '        background: #2d3748 !important;';
        echo '        border-color: #4a5568 !important;';
        echo '    }';
        echo '}';
        
        // 数字跳动动画
        echo '.wpnlc-dashboard-widget [style*="font-size: 28px"],';
        echo '.wpnlc-dashboard-widget [style*="font-size: 18px"] {';
        echo '    animation: numberPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);';
        echo '}';
        
        echo '@keyframes numberPop {';
        echo '    0% { transform: scale(0.5); opacity: 0; }';
        echo '    100% { transform: scale(1); opacity: 1; }';
        echo '}';
        
        echo '</style>';
        
        // 添加交互JavaScript
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        
        // 网盘类型项目点击效果
        echo '    $(".wpnlc-dashboard-widget [style*=\"background: rgba(255,255,255,0.8)\"]").on("click", function() {';
        echo '        $(this).css({"transform": "scale(0.95)", "transition": "all 0.1s ease"});';
        echo '        setTimeout(() => {';
        echo '            $(this).css({"transform": "translateX(4px)", "transition": "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)"});';
        echo '        }, 100);';
        echo '    });';
        
        // 数字增长动画效果
        echo '    function animateNumber(element, targetNumber) {';
        echo '        let current = 0;';
        echo '        const increment = targetNumber / 30;';
        echo '        const timer = setInterval(() => {';
        echo '            current += increment;';
        echo '            if (current >= targetNumber) {';
        echo '                current = targetNumber;';
        echo '                clearInterval(timer);';
        echo '            }';
        echo '            $(element).text(Math.floor(current).toLocaleString());';
        echo '        }, 50);';
        echo '    }';
        
        // 初始化动画
        echo '    setTimeout(() => {';
        echo '        $(".wpnlc-dashboard-widget [style*=\"font-size: 28px\"]").each(function() {';
        echo '            const targetNum = parseInt($(this).text().replace(/,/g, ""));';
        echo '            if (!isNaN(targetNum)) {';
        echo '                $(this).text("0");';
        echo '                animateNumber(this, targetNum);';
        echo '            }';
        echo '        });';
        echo '    }, 300);';
        
        echo '});';
        echo '</script>';

        echo '</div>';
    }
    
    /**
     * 仪表盘显示网盘类型统计 - 现代化美化版本
     */
    private function display_dashboard_netdisk_types() {
        global $wpdb;
        $database = WPNLC_Core::get_instance()->get_database();
        $type_counts = array();

        // 检查是否使用新表
        if ($database->table_exists()) {
            // 从新表获取数据
            $table_name = $database->get_links_table();
            $links_data = $wpdb->get_results(
                "SELECT link_type, COUNT(*) as count 
                 FROM $table_name 
                 WHERE link_type != '' AND link_type != 'none' AND link_type IS NOT NULL
                 GROUP BY link_type
                 ORDER BY count DESC
                 LIMIT 5",
                ARRAY_A
            );
            
            foreach ($links_data as $row) {
                $type = $row['link_type'];
                $count = intval($row['count']);
                if ($type && $count > 0) {
                    $type_counts[$type] = $count;
                }
            }
        } else {
            // 从旧meta表获取数据
            $links_data = $wpdb->get_results(
                "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_links_data' AND meta_value != '' LIMIT 50",
                ARRAY_A
            );

            foreach ($links_data as $row) {
                $links = maybe_unserialize($row['meta_value']);
                if (is_array($links)) {
                    foreach ($links as $link) {
                        if (isset($link['type']) && $link['type'] && $link['type'] !== 'unknown') {
                            $type = $link['type'];
                            if (!isset($type_counts[$type])) {
                                $type_counts[$type] = 0;
                            }
                            $type_counts[$type]++;
                        }
                    }
                }
            }
            
            // 按数量排序并只取前5个
            if (!empty($type_counts)) {
                arsort($type_counts);
                $type_counts = array_slice($type_counts, 0, 5, true);
            }
        }

        // 网盘类型统计区域
        echo '<div style="background: linear-gradient(135deg, #e8f4fd, #c3e8ff); border: 1px solid #bee5eb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">';
        echo '<div style="display: flex; align-items: center; margin-bottom: 12px;">';
        echo '<span class="dashicons dashicons-cloud" style="color: #0c5aa6; margin-right: 8px; font-size: 18px;"></span>';
        echo '<strong style="color: #495057; font-size: 15px;">📁 网盘类型分布</strong>';
        echo '</div>';
        
        if (empty($type_counts)) {
            echo '<div style="text-align: center; padding: 20px; background: rgba(255,255,255,0.7); border-radius: 6px; border: 2px dashed #b8daff;">';
            echo '<div style="font-size: 48px; margin-bottom: 8px;">📂</div>';
            echo '<p style="color: #6c757d; margin: 0; font-weight: 500;">暂无网盘链接数据</p>';
            echo '<p style="color: #adb5bd; margin: 8px 0 0 0; font-size: 12px;">请先执行链接检测，然后再查看统计数据</p>';
            echo '</div>';
            echo '</div>';
            return;
        }

        // 网盘类型列表
        echo '<div style="display: flex; flex-direction: column; gap: 6px;">';
        
        // 获取最大值用于计算进度条
        $max_count = max($type_counts);
        
        // 网盘类型颜色映射
        $type_colors = array(
            'baidu' => '#4285f4',
            'lanzou' => '#00bcd4', 
            'ty' => '#ff9800',
            'weiyun' => '#4caf50',
            'aliyun' => '#ff5722',
            'quark' => '#9c27b0',
            'ctfile' => '#795548',
            '123pan' => '#607d8b'
        );
        
        foreach ($type_counts as $type => $count) {
            $type_name = wpnlc_get_netdisk_type_text($type);
            if (empty(trim($type_name))) {
                $type_name = ucfirst($type);
            }
            
            // 获取预定义颜色或使用默认颜色
            $color = isset($type_colors[$type]) ? $type_colors[$type] : '#6c757d';
            
            // 计算进度条宽度
            $percentage = $max_count > 0 ? ($count / $max_count) * 100 : 0;
            
            echo '<div style="background: rgba(255,255,255,0.8); padding: 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s ease;">';
            
            // 顶部：类型名和数量
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
            echo '<div style="display: flex; align-items: center; gap: 8px;">';
            echo '<div style="width: 12px; height: 12px; background: ' . $color . '; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>';
            echo '<span style="font-size: 13px; font-weight: 500; color: #495057;">' . esc_html(trim($type_name)) . '</span>';
            echo '</div>';
            echo '<span style="background: linear-gradient(135deg, ' . $color . ', ' . $color . '99); color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">' . number_format($count) . '</span>';
            echo '</div>';
            
            // 底部：进度条
            echo '<div style="background: #e9ecef; height: 4px; border-radius: 2px; overflow: hidden;">';
            echo '<div style="background: linear-gradient(90deg, ' . $color . ', ' . $color . '99); height: 100%; width: ' . $percentage . '%; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 2px;"></div>';
            echo '</div>';
            
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    /**
     * 添加批量操作
     *
     * @param array $bulk_actions 现有批量操作
     * @return array 修改后的批量操作
     */
    public function add_bulk_actions($bulk_actions) {
        $bulk_actions['wpnlc_check_links'] = __('检测网盘链接', 'wp-netdisk-link-checker');
        return $bulk_actions;
    }

    /**
     * 处理批量操作
     *
     * @param string $redirect_to 重定向URL
     * @param string $doaction 操作名称
     * @param array $post_ids 文章ID数组
     * @return string 重定向URL
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'wpnlc_check_links') {
            return $redirect_to;
        }

        if (empty($post_ids)) {
            return $redirect_to;
        }

        // 检查权限
        if (!current_user_can('edit_posts')) {
            return $redirect_to;
        }

        // 执行批量检测
        $checker = WPNLC_Core::get_instance()->get_checker();
        $results = $checker->batch_check_posts($post_ids, true);

        $checked_count = count($results);

        $redirect_to = add_query_arg(array(
            'wpnlc_checked' => $checked_count
        ), $redirect_to);

        return $redirect_to;
    }

    /**
     * 添加行操作
     *
     * @param array $actions 现有操作
     * @param WP_Post $post 文章对象
     * @return array 修改后的操作
     */
    public function add_row_actions($actions, $post) {
        if (current_user_can('edit_post', $post->ID)) {
            $check_url = wp_nonce_url(
                admin_url('admin-ajax.php?action=wpnlc_check_single_post&post_id=' . $post->ID),
                'wpnlc_check_single'
            );

            // 移除快速检测链接，避免弹窗
            // $actions['wpnlc_check'] = '<a href="#" class="wpnlc-quick-check" data-post-id="' . $post->ID . '">检测链接</a>';
        }

        return $actions;
    }

    /**
     * 显示管理通知
     */
    public function display_admin_notices() {
        // 显示批量检测结果
        if (isset($_GET['wpnlc_checked'])) {
            $checked_count = intval($_GET['wpnlc_checked']);
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf(__('已检测 %d 篇文章的网盘链接。', 'wp-netdisk-link-checker'), $checked_count) . '</p>';
            echo '</div>';
        }
    }

    /**
     * 获取文章的链接状态摘要
     *
     * @param int $post_id 文章ID
     * @return array 状态摘要
     */
    public function get_post_status_summary($post_id) {
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);

        $summary = array(
            'status' => $link_status,
            'status_text' => wpnlc_get_status_text($link_status),
            'link_count' => 0,
            'types' => array(),
            'last_check' => $last_check,
            'last_check_formatted' => ''
        );

        if (!empty($links_data)) {
            $summary['link_count'] = count($links_data);

            foreach ($links_data as $link) {
                if (!in_array($link['type'], $summary['types'])) {
                    $summary['types'][] = $link['type'];
                }
            }
        }

        if ($last_check) {
            $summary['last_check_formatted'] = date_i18n('Y-m-d H:i:s', $last_check);
        }

        return $summary;
    }

    /**
     * 生成状态图标HTML
     *
     * @param string $status 状态
     * @return string HTML代码
     */
    public function generate_status_icon($status) {
        $icons = array(
            'valid' => 'dashicons-yes-alt',
            'invalid' => 'dashicons-dismiss',
            'mixed' => 'dashicons-warning',
            'no_links' => 'dashicons-minus',
            'unknown' => 'dashicons-editor-help'
        );

        $icon_class = isset($icons[$status]) ? $icons[$status] : $icons['unknown'];
        $status_class = 'wpnlc-status-' . $status;

        return '<span class="dashicons ' . esc_attr($icon_class) . ' ' . esc_attr($status_class) . '"></span>';
    }

    /**
     * 检查是否需要显示升级通知
     */
    public function check_upgrade_notice() {
        // 这里可以添加版本升级相关的通知逻辑
        $current_version = get_option('wpnlc_version', '0.0.0');

        if (version_compare($current_version, WPNLC_VERSION, '<')) {
            // 更新版本号
            update_option('wpnlc_version', WPNLC_VERSION);

            // 可以在这里添加升级后的处理逻辑
        }
    }

    /**
     * 添加文章列表页面工具栏
     */
    public function add_posts_toolbar() {
        // 工具栏已移除，功能已迁移到设置页面
        return;
    }

    /**
     * 添加文章列表过滤器
     */
    public function add_posts_filter() {
        global $typenow;

        // 支持所有公开的文章类型
        $supported_post_types = get_post_types(array('public' => true));
        
        if (in_array($typenow, $supported_post_types)) {
            $current_filter = isset($_GET['wpnlc_filter']) ? $_GET['wpnlc_filter'] : '';
            
            // 获取当前筛选状态的统计数据
            $stats = $this->get_filter_statistics($typenow);

            echo '<select name="wpnlc_filter" class="wpnlc-filter-select">';
            echo '<option value=""' . selected($current_filter, '', false) . '>所有链接状态</option>';
            echo '<option value="checked"' . selected($current_filter, 'checked', false) . '>已检测 (' . $stats['checked'] . ')</option>';
            echo '<option value="unchecked"' . selected($current_filter, 'unchecked', false) . '>未检测 (' . $stats['unchecked'] . ')</option>';
            echo '<option value="valid"' . selected($current_filter, 'valid', false) . '>有效链接 (' . $stats['valid'] . ')</option>';
            echo '<option value="invalid"' . selected($current_filter, 'invalid', false) . '>失效链接 (' . $stats['invalid'] . ')</option>';
            echo '<option value="mixed"' . selected($current_filter, 'mixed', false) . '>部分有效 (' . $stats['mixed'] . ')</option>';
            echo '<option value="no_links"' . selected($current_filter, 'no_links', false) . '>无网盘链接 (' . $stats['no_links'] . ')</option>';
            echo '</select>';
            
            // 添加快速筛选链接
            if (!empty($current_filter)) {
                echo '<span class="wpnlc-filter-status">当前筛选: <strong>' . $this->get_filter_label($current_filter) . '</strong></span>';
                echo '<a href="' . admin_url('edit.php?post_type=' . $typenow) . '" class="wpnlc-filter-clear">清除筛选</a>';
            }
            
            // 添加快速筛选按钮组
            $this->add_quick_filter_buttons($typenow, $current_filter);
        }
    }

    /**
     * 根据链接状态过滤文章
     */
    public function filter_posts_by_status($query) {
        global $pagenow, $typenow;

        // 支持所有公开的文章类型
        $supported_post_types = get_post_types(array('public' => true));
        
        if ($pagenow === 'edit.php' && in_array($typenow, $supported_post_types) && isset($_GET['wpnlc_filter']) && !empty($_GET['wpnlc_filter'])) {
            $filter = sanitize_text_field($_GET['wpnlc_filter']);
            $database = new WPNLC_Database();

            if ($database->table_exists()) {
                // 使用新表查询（优化版）
                $this->filter_posts_by_status_new_table_optimized($query, $filter, $database, $typenow);
            } else {
                // 使用旧的meta表查询（优化版）
                $this->filter_posts_by_status_meta_table_optimized($query, $filter, $typenow);
            }
        }
    }




    /**
     * 获取筛选统计数据
     */
    private function get_filter_statistics($post_type = 'post') {
        global $wpdb;
        $database = new WPNLC_Database();
        
        // 缓存统计数据，避免重复查询
        $cache_key = 'wpnlc_filter_stats_' . $post_type;
        $stats = wp_cache_get($cache_key);
        
        if (false === $stats) {
            // 获取该文章类型的总数
            $total_posts = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = %s",
                $post_type
            ));
            
            if ($database->table_exists()) {
                $table_name = $database->get_links_table();
                
                // 使用优化的查询获取各种状态的统计
                $status_stats = $wpdb->get_results($wpdb->prepare(
                    "SELECT 
                        SUM(CASE WHEN link_status = 'valid' AND no_invalid = 1 THEN 1 ELSE 0 END) as valid,
                        SUM(CASE WHEN link_status = 'invalid' AND no_valid = 1 THEN 1 ELSE 0 END) as invalid,
                        SUM(CASE WHEN has_both = 1 THEN 1 ELSE 0 END) as mixed,
                        SUM(CASE WHEN link_status = 'no_links' THEN 1 ELSE 0 END) as no_links
                     FROM (
                         SELECT p.ID,
                                MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) as has_valid,
                                MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) as has_invalid,
                                MAX(CASE WHEN l.link_status = 'no_links' THEN 1 ELSE 0 END) as no_links,
                                CASE WHEN MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) = 1 
                                     AND MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) = 0 
                                     THEN 'valid' 
                                     WHEN MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) = 1 
                                     AND MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) = 0 
                                     THEN 'invalid'
                                     WHEN MAX(CASE WHEN l.link_status = 'no_links' THEN 1 ELSE 0 END) = 1 
                                     THEN 'no_links'
                                     ELSE 'mixed' END as link_status,
                                CASE WHEN MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) = 1 
                                     AND MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) = 0 
                                     THEN 1 ELSE 0 END as no_invalid,
                                CASE WHEN MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) = 1 
                                     AND MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) = 0 
                                     THEN 1 ELSE 0 END as no_valid,
                                CASE WHEN MAX(CASE WHEN l.link_status = 'valid' THEN 1 ELSE 0 END) = 1 
                                     AND MAX(CASE WHEN l.link_status = 'invalid' THEN 1 ELSE 0 END) = 1 
                                     THEN 1 ELSE 0 END as has_both
                         FROM $wpdb->posts p
                         LEFT JOIN $table_name l ON p.ID = l.post_id
                         WHERE p.post_status = 'publish' AND p.post_type = %s
                         GROUP BY p.ID
                     ) as post_stats",
                    $post_type
                ), ARRAY_A);
                
                $checked_count = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT p.ID) FROM $wpdb->posts p 
                     INNER JOIN $table_name l ON p.ID = l.post_id 
                     WHERE p.post_status = 'publish' AND p.post_type = %s",
                    $post_type
                ));
                
                $stats = array(
                    'checked' => $checked_count,
                    'unchecked' => $total_posts - $checked_count,
                    'valid' => !empty($status_stats) ? (int) $status_stats[0]['valid'] : 0,
                    'invalid' => !empty($status_stats) ? (int) $status_stats[0]['invalid'] : 0,
                    'mixed' => !empty($status_stats) ? (int) $status_stats[0]['mixed'] : 0,
                    'no_links' => !empty($status_stats) ? (int) $status_stats[0]['no_links'] : 0
                );
            } else {
                // 使用meta表的查询
                $checked_count = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT p.ID) FROM $wpdb->posts p 
                     INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
                     WHERE p.post_status = 'publish' AND p.post_type = %s AND pm.meta_key = '_wpnlc_link_status'",
                    $post_type
                ));
                
                $status_counts = $wpdb->get_results($wpdb->prepare(
                    "SELECT pm.meta_value as status, COUNT(*) as count
                     FROM $wpdb->posts p
                     INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
                     WHERE p.post_status = 'publish' AND p.post_type = %s AND pm.meta_key = '_wpnlc_link_status'
                     GROUP BY pm.meta_value",
                    $post_type
                ), ARRAY_A);
                
                $stats = array(
                    'checked' => $checked_count,
                    'unchecked' => $total_posts - $checked_count,
                    'valid' => 0,
                    'invalid' => 0,
                    'mixed' => 0,
                    'no_links' => 0
                );
                
                foreach ($status_counts as $status_count) {
                    $stats[$status_count['status']] = (int) $status_count['count'];
                }
            }
            
            // 缓存5分钟
            wp_cache_set($cache_key, $stats, '', 300);
        }
        
        return $stats;
    }
    
    /**
     * 获取筛选标签
     */
    private function get_filter_label($filter) {
        $labels = array(
            'checked' => '已检测',
            'unchecked' => '未检测',
            'valid' => '有效链接',
            'invalid' => '失效链接',
            'mixed' => '部分有效',
            'no_links' => '无网盘链接'
        );
        
        return isset($labels[$filter]) ? $labels[$filter] : $filter;
    }
    
    /**
     * 添加快速筛选按钮
     */
    private function add_quick_filter_buttons($post_type, $current_filter) {
        $filters = array(
            'valid' => array('label' => '有效', 'color' => '#10b981'),
            'invalid' => array('label' => '失效', 'color' => '#ef4444'),
            'mixed' => array('label' => '部分', 'color' => '#f59e0b'),
            'no_links' => array('label' => '无链接', 'color' => '#6b7280')
        );
        
        echo '<div class="wpnlc-quick-filters">';
        echo '<span class="wpnlc-quick-filter-label">快速筛选:</span>';
        
        foreach ($filters as $filter_key => $filter_info) {
            $is_active = ($current_filter === $filter_key);
            $url = admin_url('edit.php?post_type=' . $post_type . '&wpnlc_filter=' . $filter_key);
            
            $class = 'wpnlc-quick-filter-btn ' . ($is_active ? 'active' : 'inactive');
            
            $style = sprintf(
                'border-color: %s; color: %s; background: %s;',
                $filter_info['color'],
                $is_active ? '#fff' : $filter_info['color'],
                $is_active ? $filter_info['color'] : 'transparent'
            );
            
            echo '<a href="' . esc_url($url) . '" class="' . $class . '" style="' . $style . '">';
            echo esc_html($filter_info['label']);
            echo '</a>';
        }
        
        echo '</div>';
    }
    
    /**
     * 使用新表过滤文章（优化版）
     */
    private function filter_posts_by_status_new_table_optimized($query, $filter, $database, $post_type) {
        global $wpdb;
        $table_name = $database->get_links_table();
        
        try {
            // 对于大数据量，先获取文章ID列表，然后使用post__in
            $post_ids = array();
            
            switch ($filter) {
                case 'checked':
                    // 已检测的文章
                    $post_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $table_name LIMIT 1000");
                    break;
                    
                case 'unchecked':
                    // 未检测的文章
                    $checked_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $table_name");
                    if (!empty($checked_ids)) {
                        $placeholders = implode(',', array_fill(0, count($checked_ids), '%d'));
                        $post_ids = $wpdb->get_col($wpdb->prepare(
                            "SELECT ID FROM $wpdb->posts 
                             WHERE post_status = 'publish' AND post_type = %s 
                             AND ID NOT IN ($placeholders) 
                             LIMIT 1000",
                            array_merge(array($post_type), $checked_ids)
                        ));
                    } else {
                        // 如果没有检测过的文章，返回所有文章
                        $post_ids = $wpdb->get_col($wpdb->prepare(
                            "SELECT ID FROM $wpdb->posts 
                             WHERE post_status = 'publish' AND post_type = %s 
                             LIMIT 1000",
                            $post_type
                        ));
                    }
                    break;
                    
                case 'valid':
                    // 只有有效链接的文章
                    $post_ids = $wpdb->get_col("
                        SELECT DISTINCT post_id FROM $table_name 
                        WHERE link_status = 'valid'
                        AND post_id NOT IN (
                            SELECT DISTINCT post_id FROM $table_name WHERE link_status = 'invalid'
                        ) 
                        LIMIT 1000
                    ");
                    break;
                    
                case 'invalid':
                    // 只有失效链接的文章
                    $post_ids = $wpdb->get_col("
                        SELECT DISTINCT post_id FROM $table_name 
                        WHERE link_status = 'invalid'
                        AND post_id NOT IN (
                            SELECT DISTINCT post_id FROM $table_name WHERE link_status = 'valid'
                        ) 
                        LIMIT 1000
                    ");
                    break;
                    
                case 'mixed':
                    // 既有有效又有失效的文章
                    $post_ids = $wpdb->get_col("
                        SELECT DISTINCT v.post_id FROM 
                        (SELECT DISTINCT post_id FROM $table_name WHERE link_status = 'valid') v
                        INNER JOIN 
                        (SELECT DISTINCT post_id FROM $table_name WHERE link_status = 'invalid') i 
                        ON v.post_id = i.post_id 
                        LIMIT 1000
                    ");
                    break;
                    
                case 'no_links':
                    // 无网盘链接的文章
                    $post_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $table_name WHERE link_status = 'no_links' LIMIT 1000");
                    break;
            }
            
            if (!empty($post_ids)) {
                $query->set('post__in', $post_ids);
                $query->set('orderby', 'post__in'); // 保持数据库查询的顺序
            } else {
                // 如果没有匹配的文章，设置一个不存在的ID
                $query->set('post__in', array(0));
            }
            
        } catch (Exception $e) {
            // 如果查询出错，回退到meta查询
            error_log('WPNLC Filter Error: ' . $e->getMessage());
            $this->filter_posts_by_status_meta_table_optimized($query, $filter, $post_type);
        }
    }
    
    /**
     * 使用meta表过滤文章（优化版）
     */
    private function filter_posts_by_status_meta_table_optimized($query, $filter, $post_type) {
        switch ($filter) {
            case 'checked':
                $query->set('meta_query', array(
                    array(
                        'key' => '_wpnlc_last_check',
                        'compare' => 'EXISTS'
                    )
                ));
                break;
                
            case 'unchecked':
                $query->set('meta_query', array(
                    array(
                        'key' => '_wpnlc_last_check',
                        'compare' => 'NOT EXISTS'
                    )
                ));
                break;
                
            case 'valid':
            case 'invalid':
            case 'mixed':
            case 'no_links':
                $query->set('meta_query', array(
                    array(
                        'key' => '_wpnlc_link_status',
                        'value' => $filter,
                        'compare' => '='
                    )
                ));
                break;
        }
    }
    
    /**
     * 获取文章状态信息（优先从新表读取）
     */
    private function get_post_status_info($post_id) {
        global $wpdb;
        $database = new WPNLC_Database();
        
        $status_info = array(
            'status' => '',
            'types' => array(),
            'last_check' => 0,
            'links_data' => array()
        );
        
        // 优先从新表读取
        if ($database->table_exists()) {
            $table_name = $database->get_links_table();
            
            $links = $wpdb->get_results($wpdb->prepare(
                "SELECT link_url, link_type, link_status, last_check, status_message 
                 FROM $table_name 
                 WHERE post_id = %d 
                 ORDER BY last_check DESC",
                $post_id
            ), ARRAY_A);
            
            if (!empty($links)) {
                $valid_count = 0;
                $invalid_count = 0;
                $no_links_count = 0;
                $types = array();
                $latest_check = 0;
                
                foreach ($links as $link) {
                    if ($link['link_status'] === 'valid') {
                        $valid_count++;
                    } elseif ($link['link_status'] === 'invalid') {
                        $invalid_count++;
                    } elseif ($link['link_status'] === 'no_links') {
                        $no_links_count++;
                    }
                    
                    if (!empty($link['link_type']) && !in_array($link['link_type'], $types)) {
                        $types[] = $link['link_type'];
                    }
                    
                    if (!empty($link['link_url'])) {
                        $status_info['links_data'][] = array(
                            'url' => $link['link_url'],
                            'type' => $link['link_type'],
                            'status' => $link['link_status'],
                            'message' => $link['status_message']
                        );
                    }
                    
                    $check_time = strtotime($link['last_check']);
                    if ($check_time > $latest_check) {
                        $latest_check = $check_time;
                    }
                }
                
                // 确定整体状态
                if ($no_links_count > 0) {
                    $status_info['status'] = 'no_links';
                } elseif ($valid_count > 0 && $invalid_count > 0) {
                    $status_info['status'] = 'mixed';
                } elseif ($valid_count > 0) {
                    $status_info['status'] = 'valid';
                } elseif ($invalid_count > 0) {
                    $status_info['status'] = 'invalid';
                } else {
                    $status_info['status'] = 'no_links';
                }
                
                $status_info['types'] = $types;
                $status_info['last_check'] = $latest_check;
                
                return $status_info;
            }
        }
        
        // 如果新表没有数据，回退到meta字段
        $link_status = get_post_meta($post_id, '_wpnlc_link_status', true);
        $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
        $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);
        
        if (!empty($link_status)) {
            $status_info['status'] = $link_status;
            $status_info['last_check'] = $last_check;
            $status_info['links_data'] = $links_data;
            
            // 提取网盘类型
            if (!empty($links_data) && is_array($links_data)) {
                $types = array();
                foreach ($links_data as $link) {
                    if (!empty($link['type']) && !in_array($link['type'], $types)) {
                        $types[] = $link['type'];
                    }
                }
                $status_info['types'] = $types;
            }
        }
        
        return $status_info;
    }
    
    /**
     * 快速检测脚本已禁用，避免弹窗
     */
    public function add_quick_check_script() {
        // JavaScript已完全禁用，避免任何弹窗
        return;
    }
}
