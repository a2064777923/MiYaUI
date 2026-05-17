<?php
/**
 * 统计页面类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 统计页面类
 */
class WPNLC_Statistics {

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_management_page(
            __('网盘链接检测统计', 'wp-netdisk-link-checker'),
            __('网盘链接统计', 'wp-netdisk-link-checker'),
            'manage_options',
            'wpnlc-statistics',
            array($this, 'display_statistics_page')
        );
    }

    /**
     * 加载脚本和样式
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'tools_page_wpnlc-statistics') {
            return;
        }

        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'wpnlc-statistics-style',
            WPNLC_PLUGIN_URL . 'assets/css/admin-style.css',
            array('dashicons'),
            WPNLC_VERSION
        );

        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
    }

    /**
     * 显示统计页面
     */
    public function display_statistics_page() {
        // 获取统计数据
        $database = WPNLC_Core::get_instance()->get_database();
        $post_stats = wpnlc_get_post_check_statistics();
        $link_stats = wpnlc_get_link_statistics();
        $cron_status = WPNLC_Core::get_instance()->get_cron()->get_cron_status();
        
        ?>
        <div class="wrap">
            <h1><?php _e('网盘链接检测统计', 'wp-netdisk-link-checker'); ?></h1>
            
            <!-- 统计概览 -->
            <div class="wpnlc-stats-overview">
                <div class="wpnlc-stat-card total">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-admin-post"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $post_stats['total']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('总文章数', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>
                
                <div class="wpnlc-stat-card checked clickable" data-link="<?php echo admin_url('edit.php?post_type=post&wpnlc_filter=checked'); ?>">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $post_stats['checked']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('已检测', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>
                
                <div class="wpnlc-stat-card unchecked clickable" data-link="<?php echo admin_url('edit.php?post_type=post&wpnlc_filter=unchecked'); ?>">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $post_stats['unchecked']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('未检测', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>

                <div class="wpnlc-stat-card valid clickable" data-link="<?php echo admin_url('edit.php?post_type=post&wpnlc_filter=valid'); ?>">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-thumbs-up"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $link_stats['valid']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('有效链接', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>

                <div class="wpnlc-stat-card invalid clickable" data-link="<?php echo admin_url('edit.php?post_type=post&wpnlc_filter=invalid'); ?>">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-thumbs-down"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $link_stats['invalid']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('失效链接', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>

                <div class="wpnlc-stat-card mixed clickable" data-link="<?php echo admin_url('edit.php?post_type=post&wpnlc_filter=mixed'); ?>">
                    <div class="wpnlc-stat-icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="wpnlc-stat-content">
                        <div class="wpnlc-stat-number"><?php echo $link_stats['mixed']; ?></div>
                        <div class="wpnlc-stat-label"><?php _e('部分有效', 'wp-netdisk-link-checker'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="wpnlc-stats-container">
                <!-- 左侧主要内容 -->
                <div class="wpnlc-stats-main">
                    <!-- 图表区域 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('检测状态分布', 'wp-netdisk-link-checker'); ?></h3>
                        <div class="wpnlc-chart-container">
                            <canvas id="wpnlc-status-chart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                    <!-- 检测进度 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('检测进度', 'wp-netdisk-link-checker'); ?></h3>
                        <?php
                        $progress_percentage = $post_stats['total'] > 0 ? round(($post_stats['checked'] / $post_stats['total']) * 100, 1) : 0;
                        ?>
                        <div class="wpnlc-progress-bar">
                            <div class="wpnlc-progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                        </div>
                        <p><?php printf(__('已检测 %1$d / %2$d 篇文章 (%3$s%%)', 'wp-netdisk-link-checker'), $post_stats['checked'], $post_stats['total'], $progress_percentage); ?></p>
                    </div>
                    
                    <!-- 最近失效的链接 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('最近失效的链接', 'wp-netdisk-link-checker'); ?></h3>
                        <?php $this->display_recent_invalid_links(); ?>
                    </div>
                    
                    <!-- 网盘类型统计 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('网盘类型统计', 'wp-netdisk-link-checker'); ?></h3>
                        <?php $this->display_netdisk_type_stats(); ?>
                    </div>
                </div>
                
                <!-- 右侧边栏 -->
                <div class="wpnlc-stats-sidebar">
                    <!-- 计划任务状态 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('计划任务状态', 'wp-netdisk-link-checker'); ?></h3>
                        <div class="wpnlc-cron-status">
                            <div class="wpnlc-cron-item">
                                <strong><?php _e('主检测任务', 'wp-netdisk-link-checker'); ?></strong>
                                <div class="wpnlc-cron-info">
                                    <span class="wpnlc-status-indicator <?php echo $cron_status['main_task']['enabled'] ? 'enabled' : 'disabled'; ?>"></span>
                                    <?php if ($cron_status['main_task']['enabled']): ?>
                                        <span><?php _e('已启用', 'wp-netdisk-link-checker'); ?></span>
                                        <br><small><?php printf(__('频率: %s', 'wp-netdisk-link-checker'), $cron_status['main_task']['frequency']); ?></small>
                                        <?php if ($cron_status['main_task']['next_run']): ?>
                                            <br><small><?php printf(__('下次运行: %s', 'wp-netdisk-link-checker'), wpnlc_get_formatted_next_check_time('wpnlc_check_links_event')); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span><?php _e('已禁用', 'wp-netdisk-link-checker'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="wpnlc-cron-item">
                                <strong><?php _e('快速检测队列', 'wp-netdisk-link-checker'); ?></strong>
                                <div class="wpnlc-cron-info">
                                    <span class="wpnlc-status-indicator <?php echo $cron_status['quick_task']['enabled'] ? 'enabled' : 'disabled'; ?>"></span>
                                    <?php if ($cron_status['quick_task']['enabled']): ?>
                                        <span><?php _e('已启用', 'wp-netdisk-link-checker'); ?></span>
                                        <br><small><?php printf(__('间隔: %s', 'wp-netdisk-link-checker'), $cron_status['quick_task']['interval']); ?></small>
                                        <?php if ($cron_status['quick_task']['next_run']): ?>
                                            <br><small><?php printf(__('下次运行: %s', 'wp-netdisk-link-checker'), wpnlc_get_formatted_next_check_time('wpnlc_quick_check_event')); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span><?php _e('已禁用', 'wp-netdisk-link-checker'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 操作按钮 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('操作', 'wp-netdisk-link-checker'); ?></h3>
                        <div class="wpnlc-actions">
                            <button type="button" class="button button-primary wpnlc-batch-check" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('批量检测', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <button type="button" class="button wpnlc-refresh-stats" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('刷新统计', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <button type="button" class="button wpnlc-export-data" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('导出数据', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <a href="<?php echo admin_url('options-general.php?page=wp-netdisk-link-checker'); ?>" class="button" style="width: 100%; text-align: center; display: block; text-decoration: none;">
                                <?php _e('插件设置', 'wp-netdisk-link-checker'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- 数据库使用情况 -->
                    <div class="wpnlc-stats-widget">
                        <h3><?php _e('数据库使用情况', 'wp-netdisk-link-checker'); ?></h3>
                        <?php $this->display_database_usage(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 统计卡片点击事件
            $('.wpnlc-stat-card.clickable').on('click', function() {
                var link = $(this).data('link');
                if (link) {
                    window.location.href = link;
                }
            });

            // 为可点击的卡片添加悬停效果
            $('.wpnlc-stat-card.clickable').css({
                'cursor': 'pointer',
                'transition': 'all 0.3s ease'
            }).hover(
                function() {
                    $(this).css({
                        'transform': 'translateY(-2px)',
                        'box-shadow': '0 4px 12px rgba(0,0,0,0.15)'
                    });
                },
                function() {
                    $(this).css({
                        'transform': 'translateY(0)',
                        'box-shadow': '0 2px 4px rgba(0,0,0,0.1)'
                    });
                }
            );

            // 初始化图表
            var ctx = document.getElementById('wpnlc-status-chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['<?php _e('有效', 'wp-netdisk-link-checker'); ?>', '<?php _e('失效', 'wp-netdisk-link-checker'); ?>', '<?php _e('部分有效', 'wp-netdisk-link-checker'); ?>'],
                    datasets: [{
                        data: [<?php echo $link_stats['valid']; ?>, <?php echo $link_stats['invalid']; ?>, <?php echo $link_stats['mixed']; ?>],
                        backgroundColor: ['#46b450', '#dc3232', '#ffb900'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // 批量检测按钮
            $('.wpnlc-batch-check').on('click', function() {
                var button = $(this);
                
                if (!confirm('<?php _e('确定要开始批量检测吗？这可能需要一些时间。', 'wp-netdisk-link-checker'); ?>')) {
                    return;
                }
                
                button.prop('disabled', true).text('<?php _e('检测中...', 'wp-netdisk-link-checker'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_batch_check',
                        nonce: '<?php echo wp_create_nonce('wpnlc_batch_check'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('批量检测完成！检测了', 'wp-netdisk-link-checker'); ?> ' + response.data.checked_count + ' <?php _e('篇文章', 'wp-netdisk-link-checker'); ?>');
                            // 强制刷新页面，清除缓存
                            window.location.href = window.location.href + '&_t=' + new Date().getTime();
                        } else {
                            alert('<?php _e('检测失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('检测失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('批量检测', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });
            
            // 刷新统计按钮
            $('.wpnlc-refresh-stats').on('click', function() {
                location.reload();
            });
            
            // 导出数据按钮
            $('.wpnlc-export-data').on('click', function() {
                var button = $(this);
                
                button.prop('disabled', true).text('<?php _e('导出中...', 'wp-netdisk-link-checker'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_export_data',
                        format: 'csv',
                        nonce: '<?php echo wp_create_nonce('wpnlc_export_data'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // 创建下载链接 - 确保UTF-8编码
                            var blob = new Blob([response.data.data], {
                                type: 'text/csv;charset=utf-8'
                            });
                            var url = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = response.data.filename;
                            a.style.display = 'none';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                        } else {
                            alert('<?php _e('导出失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('导出失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('导出数据', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * 显示最近失效的链接
     */
    private function display_recent_invalid_links() {
        $recent_invalid = wpnlc_get_recent_invalid_links(10);

        if (empty($recent_invalid)) {
            echo '<p>' . __('暂无失效链接', 'wp-netdisk-link-checker') . '</p>';
            return;
        }

        echo '<div class="wpnlc-invalid-links-list">';
        foreach ($recent_invalid as $invalid) {
            echo '<div class="wpnlc-invalid-link-item">';
            echo '<div class="wpnlc-invalid-link-title">';
            echo '<a href="' . get_edit_post_link($invalid['post_id']) . '">' . esc_html($invalid['title']) . '</a>';
            echo '</div>';
            if ($invalid['check_time']) {
                $check_time = date_i18n('Y-m-d H:i', $invalid['check_time']);
                echo '<div class="wpnlc-invalid-link-time">' . sprintf(__('检测时间: %s', 'wp-netdisk-link-checker'), $check_time) . '</div>';
            }
            if (!empty($invalid['url'])) {
                echo '<div class="wpnlc-invalid-link-url"><small>' . esc_html($invalid['url']) . '</small></div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * 显示网盘类型统计 - 修复统计为空问题
     */
    private function display_netdisk_type_stats() {
        global $wpdb;
        $database = WPNLC_Core::get_instance()->get_database();
        $type_counts = array();
        $debug_info = array();

        // 检查是否使用新表
        $using_new_table = $database->table_exists();
        $debug_info[] = '数据表状态: ' . ($using_new_table ? '新表存在' : '使用旧meta表');
        
        if ($using_new_table) {
            // 从新表获取数据
            $table_name = $database->get_links_table();
            $debug_info[] = '表名: ' . $table_name;
            
            // 先检查表中总记录数
            $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $debug_info[] = '总记录数: ' . $total_records;
            
            // 检查有效的链接类型记录
            $valid_type_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE link_type != '' AND link_type != 'none' AND link_type IS NOT NULL");
            $debug_info[] = '有效类型记录数: ' . $valid_type_records;
            
            if ($total_records > 0) {
                $links_data = $wpdb->get_results(
                    "SELECT link_type, COUNT(*) as count 
                     FROM $table_name 
                     WHERE link_type != '' AND link_type != 'none' AND link_type IS NOT NULL
                     GROUP BY link_type",
                    ARRAY_A
                );
                
                $debug_info[] = '查询结果: ' . print_r($links_data, true);
                
                foreach ($links_data as $row) {
                    $type = $row['link_type'];
                    $count = intval($row['count']);
                    if ($type && $count > 0) {
                        $type_counts[$type] = $count;
                    }
                }
            }
        } else {
            // 从旧meta表获取数据
            $meta_records = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_links_data' AND meta_value != ''");
            $debug_info[] = 'Meta记录数: ' . $meta_records;
            
            if ($meta_records > 0) {
                $links_data = $wpdb->get_results(
                    "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_links_data' AND meta_value != ''",
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
            }
        }

        // 显示调试信息（仅管理员可见）
        if (current_user_can('manage_options') && (isset($_GET['debug']) || empty($type_counts))) {
            echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 12px;">';
            echo '<strong>调试信息:</strong><br>';
            foreach ($debug_info as $info) {
                echo '- ' . esc_html($info) . '<br>';
            }
            echo '</div>';
        }

        if (empty($type_counts)) {
            echo '<p>' . __('暂无网盘链接数据', 'wp-netdisk-link-checker') . '</p>';
            echo '<p><small>' . __('请先执行链接检测，然后再查看统计数据', 'wp-netdisk-link-checker') . '</small></p>';
            return;
        }

        // 按数量排序（从大到小）
        arsort($type_counts);

        echo '<div class="wpnlc-netdisk-types">';
        foreach ($type_counts as $type => $count) {
            // 修复：直接传递字符串而不是数组
            $type_name = wpnlc_get_netdisk_type_text($type);
            // 确保类型名称不为空
            if (empty(trim($type_name))) {
                $type_name = ucfirst($type); // 使用类型代码作为后备
            }
            echo '<div class="wpnlc-netdisk-type-item">';
            echo '<span class="wpnlc-netdisk-type-name">' . esc_html(trim($type_name)) . '</span>';
            echo '<span class="wpnlc-netdisk-type-count">' . number_format($count) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * 显示数据库使用情况
     */
    private function display_database_usage() {
        $database = WPNLC_Core::get_instance()->get_database();
        $usage = $database->get_database_usage();

        echo '<div class="wpnlc-database-usage">';

        // 显示记录数（兼容新表和旧表）
        echo '<div class="wpnlc-usage-item">';
        if (isset($usage['using_new_table']) && $usage['using_new_table']) {
            echo '<span class="wpnlc-usage-label">' . __('链接记录数', 'wp-netdisk-link-checker') . '</span>';
            $records_count = isset($usage['records_count']) ? $usage['records_count'] : 0;
        } else {
            echo '<span class="wpnlc-usage-label">' . __('元数据记录数', 'wp-netdisk-link-checker') . '</span>';
            $records_count = isset($usage['meta_records']) ? $usage['meta_records'] : 0;
        }
        echo '<span class="wpnlc-usage-value">' . number_format($records_count) . '</span>';
        echo '</div>';

        // 显示数据大小
        echo '<div class="wpnlc-usage-item">';
        echo '<span class="wpnlc-usage-label">' . __('数据大小', 'wp-netdisk-link-checker') . '</span>';
        $data_size = isset($usage['data_size_mb']) ? $usage['data_size_mb'] : 0;
        echo '<span class="wpnlc-usage-value">' . number_format($data_size, 2) . ' MB</span>';
        echo '</div>';

        // 如果使用新表，显示额外信息
        if (isset($usage['using_new_table']) && $usage['using_new_table']) {
            echo '<div class="wpnlc-usage-item">';
            echo '<span class="wpnlc-usage-label">' . __('表大小', 'wp-netdisk-link-checker') . '</span>';
            $table_size = isset($usage['table_size_mb']) ? $usage['table_size_mb'] : 0;
            echo '<span class="wpnlc-usage-value">' . number_format($table_size, 2) . ' MB</span>';
            echo '</div>';
        }

        echo '</div>';

        // 清理按钮
        echo '<div class="wpnlc-cleanup-actions" style="margin-top: 15px;">';
        echo '<button type="button" class="button wpnlc-cleanup-data" style="width: 100%;">';
        echo __('清理过期数据', 'wp-netdisk-link-checker');
        echo '</button>';
        echo '</div>';

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.wpnlc-cleanup-data').on('click', function() {
                var button = $(this);

                if (!confirm('<?php _e('确定要清理30天前的过期数据吗？', 'wp-netdisk-link-checker'); ?>')) {
                    return;
                }

                button.prop('disabled', true).text('<?php _e('清理中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_cleanup_data',
                        days: 30,
                        nonce: '<?php echo wp_create_nonce('wpnlc_cleanup_data'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert('<?php _e('清理失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('清理失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('清理过期数据', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * 获取检测历史趋势数据
     *
     * @param int $days 天数
     * @return array 趋势数据
     */
    public function get_check_trend_data($days = 7) {
        global $wpdb;

        $trend_data = array();
        $end_date = current_time('timestamp');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $end_date - ($i * 24 * 3600);
            $date_start = strtotime(date('Y-m-d 00:00:00', $date));
            $date_end = strtotime(date('Y-m-d 23:59:59', $date));

            $checked_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->postmeta
                 WHERE meta_key = '_wpnlc_last_check'
                 AND meta_value >= %d AND meta_value <= %d",
                $date_start, $date_end
            ));

            $trend_data[] = array(
                'date' => date('Y-m-d', $date),
                'checked' => intval($checked_count)
            );
        }

        return $trend_data;
    }

    /**
     * 获取状态变化统计
     *
     * @return array 状态变化数据
     */
    public function get_status_change_stats() {
        // 这里可以实现状态变化的统计逻辑
        // 由于当前只保存最新状态，这个功能需要额外的数据表来记录历史
        return array(
            'valid_to_invalid' => 0,
            'invalid_to_valid' => 0,
            'new_checks' => 0
        );
    }

    /**
     * 导出统计报告
     *
     * @param string $format 导出格式
     * @return array 报告数据
     */
    public function export_statistics_report($format = 'json') {
        $post_stats = wpnlc_get_post_check_statistics();
        $link_stats = wpnlc_get_link_statistics();
        $database = WPNLC_Core::get_instance()->get_database();
        $usage = $database->get_database_usage();

        $report = array(
            'generated_at' => current_time('mysql'),
            'post_statistics' => $post_stats,
            'link_statistics' => $link_stats,
            'database_usage' => $usage,
            'recent_invalid_links' => wpnlc_get_recent_invalid_links(20)
        );

        if ($format === 'json') {
            return json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $report;
    }
}
