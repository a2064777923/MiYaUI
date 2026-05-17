<?php
/**
 * 设置管理类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 设置管理类
 */
class WPNLC_Settings {

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
        add_action('admin_init', array($this, 'settings_init'));
        add_action('update_option_wpnlc_settings', array($this, 'on_settings_updated'), 10, 2);
    }

    /**
     * 添加设置菜单
     */
    public function add_admin_menu() {
        add_options_page(
            __('网盘链接检测设置', 'wp-netdisk-link-checker'),
            __('网盘链接检测', 'wp-netdisk-link-checker'),
            'manage_options',
            'wp-netdisk-link-checker',
            array($this, 'settings_page')
        );
    }

    /**
     * 初始化设置
     */
    public function settings_init() {
        register_setting('wpnlc_settings_group', 'wpnlc_settings');
        
        // 基本设置
        add_settings_section(
            'wpnlc_settings_section',
            __('基本设置', 'wp-netdisk-link-checker'),
            array($this, 'settings_section_callback'),
            'wp-netdisk-link-checker'
        );
        
        add_settings_field(
            'cache_hours',
            __('检测结果缓存时间（小时）', 'wp-netdisk-link-checker'),
            array($this, 'cache_hours_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
        
        add_settings_field(
            'check_posts',
            __('检查文章类型', 'wp-netdisk-link-checker'),
            array($this, 'check_posts_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
        
        add_settings_field(
            'check_frequency',
            __('自动检查频率', 'wp-netdisk-link-checker'),
            array($this, 'check_frequency_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
        
        // 快速检测队列设置
        add_settings_field(
            'quick_check_interval',
            __('快速检测队列', 'wp-netdisk-link-checker'),
            array($this, 'quick_check_interval_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
        
        add_settings_field(
            'quick_check_batch',
            __('每次快速检测的文章数', 'wp-netdisk-link-checker'),
            array($this, 'quick_check_batch_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
        
        // 通知设置
        add_settings_section(
            'wpnlc_notification_section',
            __('通知设置', 'wp-netdisk-link-checker'),
            array($this, 'notification_section_callback'),
            'wp-netdisk-link-checker'
        );
        
        add_settings_field(
            'enable_notifications',
            __('启用失效链接通知', 'wp-netdisk-link-checker'),
            array($this, 'enable_notifications_render'),
            'wp-netdisk-link-checker',
            'wpnlc_notification_section'
        );
        
        add_settings_field(
            'notification_email',
            __('通知邮箱', 'wp-netdisk-link-checker'),
            array($this, 'notification_email_render'),
            'wp-netdisk-link-checker',
            'wpnlc_notification_section'
        );
        
        // 显示设置
        add_settings_section(
            'wpnlc_display_section',
            __('显示设置', 'wp-netdisk-link-checker'),
            array($this, 'display_section_callback'),
            'wp-netdisk-link-checker'
        );
        
        add_settings_field(
            'show_dashboard_widget',
            __('在仪表盘显示链接状态统计', 'wp-netdisk-link-checker'),
            array($this, 'show_dashboard_widget_render'),
            'wp-netdisk-link-checker',
            'wpnlc_display_section'
        );
        
        
        add_settings_field(
            'show_in_download_box',
            __('在B2主题下载区域显示链接状态', 'wp-netdisk-link-checker'),
            array($this, 'show_in_download_box_render'),
            'wp-netdisk-link-checker',
            'wpnlc_display_section'
        );


        add_settings_field(
            'show_check_time',
            __('在前台显示检测时间', 'wp-netdisk-link-checker'),
            array($this, 'show_check_time_render'),
            'wp-netdisk-link-checker',
            'wpnlc_display_section'
        );
        
        add_settings_field(
            'enable_frontend_manual_check',
            __('前台手动检测功能', 'wp-netdisk-link-checker'),
            array($this, 'enable_frontend_manual_check_render'),
            'wp-netdisk-link-checker',
            'wpnlc_display_section'
        );
        
        add_settings_field(
            'frontend_check_permission',
            __('前台检测权限', 'wp-netdisk-link-checker'),
            array($this, 'frontend_check_permission_render'),
            'wp-netdisk-link-checker',
            'wpnlc_display_section'
        );


        // 添加快速检测间隔字段
        add_settings_field(
            'quick_check_interval',
            __('快速检测队列', 'wp-netdisk-link-checker'),
            array($this, 'quick_check_interval_render'),
            'wp-netdisk-link-checker',
            'wpnlc_settings_section'
        );
    }

    /**
     * 获取设置
     *
     * @return array
     */
    public function get_settings() {
        return wpnlc_get_settings();
    }

    /**
     * 设置更新时的处理
     *
     * @param mixed $old_value 旧值
     * @param mixed $value 新值
     */
    public function on_settings_updated($old_value, $value) {
        // 检查是否需要重新调度任务
        $need_reschedule = false;

        if (isset($old_value['check_frequency']) && isset($value['check_frequency'])) {
            if ($old_value['check_frequency'] !== $value['check_frequency']) {
                $need_reschedule = true;
            }
        }

        if (isset($old_value['quick_check_interval']) && isset($value['quick_check_interval'])) {
            if ($old_value['quick_check_interval'] !== $value['quick_check_interval']) {
                $need_reschedule = true;
            }
        }

        // 如果需要重新调度，则重新调度任务
        if ($need_reschedule) {
            $cron = WPNLC_Core::get_instance()->get_cron();
            $cron->reschedule_tasks($old_value, $value);
        }
    }

    /**
     * 设置区域说明
     */
    public function settings_section_callback() {
        echo '<p>' . __('配置网盘链接检测插件的基本行为。', 'wp-netdisk-link-checker') . '</p>';
    }

    public function notification_section_callback() {
        echo '<p>' . __('配置链接状态变化时的通知选项。', 'wp-netdisk-link-checker') . '</p>';
    }

    public function display_section_callback() {
        echo '<p>' . __('配置链接状态的显示选项。', 'wp-netdisk-link-checker') . '</p>';
        echo '<details class="wpnlc-settings-details" style="background: #f8f8f8; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ddd;">
            <summary style="cursor: pointer; font-weight: 500; margin-bottom: 8px;">' . __('点击查看显示设置说明', 'wp-netdisk-link-checker') . ' <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 16px; vertical-align: middle; opacity: 0.7;"></span></summary>
            <div class="notice notice-info inline" style="margin: 5px 0 0 0;">
                <p style="font-size: 13px; margin: 7px 0; line-height: 1.5;">' . __('
                - 仪表盘统计：在WordPress后台仪表盘显示网盘链接状态的统计信息<br>
                - B2主题下载区域：在B2主题的下载区域顶部显示网盘链接状态标签<br>
                - 显示检测时间：在状态标签中包含最后检测的时间信息<br>
                - 前台手动检测：允许用户在前台点击按钮手动检测网盘链接状态<br>
                - 检测权限控制：可设置仅登录用户或所有用户（包括游客）可使用手动检测', 'wp-netdisk-link-checker') . '</p>
            </div>
        </details>';
    }

    /**
     * 缓存时间设置字段
     */
    public function cache_hours_render() {
        $settings = $this->get_settings();
        ?>
        <input type="number" name="wpnlc_settings[cache_hours]" min="1" max="168" value="<?php echo esc_attr($settings['cache_hours']); ?>">
        <p class="description"><?php _e('设置检测结果的缓存时间（小时）。较短的时间可以更快反映链接状态变化，但会增加服务器负载。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 文章类型设置字段
     */
    public function check_posts_render() {
        $settings = $this->get_settings();
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <select name="wpnlc_settings[check_posts]">
            <option value="all" <?php selected($settings['check_posts'], 'all'); ?>><?php _e('所有文章类型', 'wp-netdisk-link-checker'); ?></option>
            <?php foreach ($post_types as $post_type): ?>
            <option value="<?php echo esc_attr($post_type->name); ?>" <?php selected($settings['check_posts'], $post_type->name); ?>>
                <?php echo esc_html($post_type->label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e('选择要检查网盘链接的文章类型。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 检查频率设置字段
     */
    public function check_frequency_render() {
        $settings = $this->get_settings();
        $frequencies = array(
            'hourly' => __('每小时', 'wp-netdisk-link-checker'),
            'twicedaily' => __('每天两次', 'wp-netdisk-link-checker'),
            'daily' => __('每天', 'wp-netdisk-link-checker'),
            'weekly' => __('每周', 'wp-netdisk-link-checker'),
            'disabled' => __('禁用自动检查', 'wp-netdisk-link-checker'),
        );
        ?>
        <select name="wpnlc_settings[check_frequency]">
            <?php foreach ($frequencies as $value => $label): ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['check_frequency'], $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e('设置自动检查网盘链接状态的频率。频繁检查可能会导致服务器负载增加。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 快速检测间隔设置字段
     */
    public function quick_check_interval_render() {
        $settings = $this->get_settings();
        $intervals = array(
            'disabled' => __('禁用', 'wp-netdisk-link-checker'),
            '30sec' => __('30秒', 'wp-netdisk-link-checker'),
            '1min' => __('1分钟', 'wp-netdisk-link-checker'),
            '3min' => __('3分钟', 'wp-netdisk-link-checker'),
            '5min' => __('5分钟', 'wp-netdisk-link-checker'),
            '10min' => __('10分钟', 'wp-netdisk-link-checker'),
        );
        ?>
        <select name="wpnlc_settings[quick_check_interval]">
            <?php foreach ($intervals as $value => $label): ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['quick_check_interval'], $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e('快速检测队列会按设定间隔检测少量文章，适合大量文章的网站。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 快速检测批次设置字段
     */
    public function quick_check_batch_render() {
        $settings = $this->get_settings();
        ?>
        <input type="number" name="wpnlc_settings[quick_check_batch]" min="1" max="10" value="<?php echo esc_attr($settings['quick_check_batch']); ?>">
        <p class="description"><?php _e('每次快速检测处理的文章数量。建议设置为1-3篇，避免服务器负载过高。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 启用通知设置字段
     */
    public function enable_notifications_render() {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="wpnlc_settings[enable_notifications]" value="yes" <?php checked($settings['enable_notifications'], 'yes'); ?>>
        <label><?php _e('当发现失效链接时发送邮件通知', 'wp-netdisk-link-checker'); ?></label>
        <?php
    }

    /**
     * 通知邮箱设置字段
     */
    public function notification_email_render() {
        $settings = $this->get_settings();
        ?>
        <input type="email" name="wpnlc_settings[notification_email]" value="<?php echo esc_attr($settings['notification_email']); ?>" class="regular-text">
        <p class="description"><?php _e('留空则使用管理员邮箱。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 显示仪表盘小工具设置字段
     */
    public function show_dashboard_widget_render() {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="wpnlc_settings[show_dashboard_widget]" value="yes" <?php checked($settings['show_dashboard_widget'], 'yes'); ?>>
        <label><?php _e('在WordPress仪表盘显示链接状态统计小工具', 'wp-netdisk-link-checker'); ?></label>
        <?php
    }

    /**
     * B2主题下载区域显示设置字段
     */
    public function show_in_download_box_render() {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="wpnlc_settings[show_in_download_box]" value="yes" <?php checked($settings['show_in_download_box'], 'yes'); ?>>
        <label><?php _e('在B2主题的下载区域显示链接状态（需要B2主题支持）', 'wp-netdisk-link-checker'); ?></label>
        <?php
    }


    /**
     * 显示检测时间设置字段
     */
    public function show_check_time_render() {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="wpnlc_settings[show_check_time]" value="yes" <?php checked($settings['show_check_time'], 'yes'); ?>>
        <label><?php _e('在前台显示最后检测时间', 'wp-netdisk-link-checker'); ?></label>
        <?php
    }

    /**
     * 前台手动检测功能设置字段
     */
    public function enable_frontend_manual_check_render() {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="wpnlc_settings[enable_frontend_manual_check]" value="yes" <?php checked($settings['enable_frontend_manual_check'], 'yes'); ?>>
        <label><?php _e('允许用户在前台手动检测网盘链接状态', 'wp-netdisk-link-checker'); ?></label>
        <p class="description"><?php _e('启用后，在网盘状态显示区域会增加"检测"按钮，用户点击可手动检测链接状态。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }
    
    /**
     * 前台检测权限设置字段
     */
    public function frontend_check_permission_render() {
        $settings = $this->get_settings();
        ?>
        <select name="wpnlc_settings[frontend_check_permission]">
            <option value="logged_in" <?php selected($settings['frontend_check_permission'], 'logged_in'); ?>><?php _e('仅登录用户', 'wp-netdisk-link-checker'); ?></option>
            <option value="everyone" <?php selected($settings['frontend_check_permission'], 'everyone'); ?>><?php _e('所有用户（包括游客）', 'wp-netdisk-link-checker'); ?></option>
        </select>
        <p class="description"><?php _e('设置谁可以使用前台手动检测功能。', 'wp-netdisk-link-checker'); ?></p>
        <?php
    }

    /**
     * 设置页面
     */
    public function settings_page() {
        // 获取当前Tab，默认显示链接统计
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'statistics';

        ?>
        <div class="wrap">
            <h1><?php _e('网盘链接检测', 'wp-netdisk-link-checker'); ?></h1>

            <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
            <div class="notice notice-info" style="margin-bottom: 20px;">
                <p><strong>调试模式：</strong>
                <button type="button" class="button wpnlc-toggle-debug">批量检测调试</button>
                </p>
            </div>

            <!-- 调试面板 -->
            <div id="wpnlc-debug-panel" style="display: none; margin-bottom: 20px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                <h3>批量检测调试工具</h3>

                <div style="margin: 15px 0;">
                    <h4>1. 当前统计数据</h4>
                    <button type="button" class="button wpnlc-debug-stats">获取当前统计</button>
                    <div id="debug-current-stats" style="margin-top: 10px;"></div>
                </div>

                <div style="margin: 15px 0;">
                    <h4>2. 数据库状态检查</h4>
                    <button type="button" class="button wpnlc-debug-database">检查数据库</button>
                    <div id="debug-database-status" style="margin-top: 10px;"></div>
                </div>

                <div style="margin: 15px 0;">
                    <h4>3. 检查具体文章</h4>
                    <label>文章ID: <input type="number" id="debug-post-id" value="100700" style="width: 100px;" /></label>
                    <button type="button" class="button wpnlc-debug-single">检查单篇文章</button>
                    <div id="debug-single-result" style="margin-top: 10px;"></div>
                </div>

                <div style="margin: 15px 0;">
                    <h4>4. 数据库迁移</h4>
                    <p style="color: #666; font-size: 12px;">创建新的专用表并清理旧的meta数据</p>
                    <button type="button" class="button button-primary wpnlc-migrate-database">迁移到新表结构</button>
                    <div id="debug-migrate-result" style="margin-top: 10px;"></div>
                </div>

                <div style="margin: 15px 0;">
                    <h4>5. 批量检测测试</h4>
                    <button type="button" class="button wpnlc-debug-batch">执行批量检测</button>
                    <div id="debug-batch-test-result" style="margin-top: 10px;"></div>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" class="button wpnlc-close-debug">关闭调试面板</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tab导航 -->
            <nav class="nav-tab-wrapper">
                <a href="<?php echo admin_url('options-general.php?page=wp-netdisk-link-checker&tab=statistics'); ?>"
                   class="nav-tab <?php echo $current_tab === 'statistics' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php _e('链接统计', 'wp-netdisk-link-checker'); ?>
                </a>
                <a href="<?php echo admin_url('options-general.php?page=wp-netdisk-link-checker&tab=settings'); ?>"
                   class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('插件设置', 'wp-netdisk-link-checker'); ?>
                </a>
                <a href="<?php echo admin_url('options-general.php?page=wp-netdisk-link-checker&tab=tools'); ?>"
                   class="nav-tab <?php echo $current_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('工具', 'wp-netdisk-link-checker'); ?>
                </a>
                <a href="<?php echo admin_url('options-general.php?page=wp-netdisk-link-checker&tab=help'); ?>"
                   class="nav-tab <?php echo $current_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('帮助', 'wp-netdisk-link-checker'); ?>
                </a>
            </nav>

            <div class="wpnlc-tab-content">
                <?php
                switch ($current_tab) {
                    case 'settings':
                        $this->display_settings_tab();
                        break;
                    case 'tools':
                        $this->display_tools_tab();
                        break;
                    case 'help':
                        $this->display_help_tab();
                        break;
                    case 'statistics':
                    default:
                        $this->display_statistics_tab();
                        break;
                }
                ?>
            </div>
        </div>

        <style>
        .wpnlc-tab-content {
            margin-top: 20px;
        }

        .nav-tab .dashicons {
            margin-right: 5px;
            vertical-align: middle;
        }
        </style>
        <?php
    }

    /**
     * 显示设置Tab
     */
    private function display_settings_tab() {
        ?>
        <?php
        // 显示保存成功消息
        if (isset($_GET['settings-updated'])) {
            add_settings_error('wpnlc_messages', 'wpnlc_message', __('设置已保存', 'wp-netdisk-link-checker'), 'updated');
        }
        settings_errors('wpnlc_messages');
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('wpnlc_settings_group');
            do_settings_sections('wp-netdisk-link-checker');
            submit_button();
            ?>
        </form>

        <div class="wpnlc-info-box" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-top: 20px; border-radius: 4px;">
            <h3><?php _e('插件信息', 'wp-netdisk-link-checker'); ?></h3>
            <p><?php _e('版本：', 'wp-netdisk-link-checker'); ?><?php echo WPNLC_VERSION; ?></p>
            <p><?php _e('支持的网盘：百度网盘、蓝奏云、天翼云盘、微云、阿里云盘、夸克网盘、城通网盘、123云盘', 'wp-netdisk-link-checker'); ?></p>

            <h4><?php _e('B2主题支持', 'wp-netdisk-link-checker'); ?></h4>
            <p><?php _e('插件完全支持B2主题的下载字段格式：', 'wp-netdisk-link-checker'); ?></p>
            <code>资源名称|下载地址|提取码,解压码</code>
            <p><?php _e('插件会自动检测B2主题下载字段中的网盘链接，无需额外配置。', 'wp-netdisk-link-checker'); ?></p>

            <h4><?php _e('使用说明', 'wp-netdisk-link-checker'); ?></h4>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li><?php _e('插件会自动检测文章内容和B2主题下载字段中的网盘链接', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('在文章列表页面可以看到每篇文章的链接状态', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('支持手动检测单篇文章或批量检测所有文章', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('可以设置自动检测频率和缓存时间', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('查看"链接统计"页面了解详细的检测数据和统计报告', 'wp-netdisk-link-checker'); ?></li>
            </ul>

            <h4><?php _e('快速检测队列说明', 'wp-netdisk-link-checker'); ?></h4>
            <p><?php _e('快速检测队列是一个后台任务，会按设定的时间间隔检测少量文章。这对于有大量文章的网站特别有用，可以避免一次性检测所有文章导致的服务器负载过高。', 'wp-netdisk-link-checker'); ?></p>

            <h4><?php _e('注意事项', 'wp-netdisk-link-checker'); ?></h4>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li><?php _e('频繁的链接检测可能会增加服务器负载', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('某些网盘可能会限制访问频率', 'wp-netdisk-link-checker'); ?></li>
                <li><?php _e('建议根据网站实际情况调整检测频率', 'wp-netdisk-link-checker'); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * 显示链接统计Tab
     */
    private function display_statistics_tab() {
        // 获取统计数据
        $post_stats = wpnlc_get_post_check_statistics();
        $link_stats = wpnlc_get_link_statistics();
        $database = WPNLC_Core::get_instance()->get_database();
        $cron_status = WPNLC_Core::get_instance()->get_cron()->get_cron_status();

        ?>
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

            <div class="wpnlc-stat-card checked">
                <div class="wpnlc-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="wpnlc-stat-content">
                    <div class="wpnlc-stat-number"><?php echo $post_stats['checked']; ?></div>
                    <div class="wpnlc-stat-label"><?php _e('已检测', 'wp-netdisk-link-checker'); ?></div>
                </div>
            </div>

            <div class="wpnlc-stat-card unchecked">
                <div class="wpnlc-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="wpnlc-stat-content">
                    <div class="wpnlc-stat-number"><?php echo $post_stats['unchecked']; ?></div>
                    <div class="wpnlc-stat-label"><?php _e('未检测', 'wp-netdisk-link-checker'); ?></div>
                </div>
            </div>

            <div class="wpnlc-stat-card valid">
                <div class="wpnlc-stat-icon">
                    <span class="dashicons dashicons-thumbs-up"></span>
                </div>
                <div class="wpnlc-stat-content">
                    <div class="wpnlc-stat-number"><?php echo $link_stats['valid']; ?></div>
                    <div class="wpnlc-stat-label"><?php _e('有效链接', 'wp-netdisk-link-checker'); ?></div>
                </div>
            </div>

            <div class="wpnlc-stat-card invalid">
                <div class="wpnlc-stat-icon">
                    <span class="dashicons dashicons-thumbs-down"></span>
                </div>
                <div class="wpnlc-stat-content">
                    <div class="wpnlc-stat-number"><?php echo $link_stats['invalid']; ?></div>
                    <div class="wpnlc-stat-label"><?php _e('失效链接', 'wp-netdisk-link-checker'); ?></div>
                </div>
            </div>

            <div class="wpnlc-stat-card mixed">
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

                <!-- 快速操作 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('快速操作', 'wp-netdisk-link-checker'); ?></h3>
                    <div class="wpnlc-actions">
                        <button type="button" class="button button-primary wpnlc-batch-check" style="width: 100%; margin-bottom: 10px;">
                            <?php _e('批量检测', 'wp-netdisk-link-checker'); ?>
                        </button>
                        <button type="button" class="button wpnlc-refresh-stats" style="width: 100%; margin-bottom: 10px;">
                            <?php _e('刷新统计', 'wp-netdisk-link-checker'); ?>
                        </button>
                        <a href="<?php echo admin_url('tools.php?page=wpnlc-statistics'); ?>" class="button" style="width: 100%; text-align: center; display: block; text-decoration: none; margin-bottom: 10px;">
                            <?php _e('详细统计', 'wp-netdisk-link-checker'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
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
                            alert('<?php _e('批量检测已开始，请稍后刷新页面查看结果。', 'wp-netdisk-link-checker'); ?>');
                            location.reload();
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
        });
        </script>
        <?php
    }

    /**
     * 显示工具Tab
     */
    private function display_tools_tab() {
        $database = WPNLC_Core::get_instance()->get_database();
        $usage = $database->get_database_usage();

        ?>
        <div class="wpnlc-tools-container">
            <div class="wpnlc-tools-main">
                <!-- 数据管理 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('数据管理', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-tool-section">
                        <h4><?php _e('数据库使用情况', 'wp-netdisk-link-checker'); ?></h4>
                        <div class="wpnlc-database-usage">
                            <?php if (isset($usage['using_new_table']) && $usage['using_new_table']): ?>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('链接记录数', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo number_format($usage['records_count'] ?? 0); ?></span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('表大小', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo $usage['table_size_mb'] ?? 0; ?> MB</span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('数据大小', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo $usage['data_size_mb'] ?? 0; ?> MB</span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('索引大小', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo $usage['index_size_mb'] ?? 0; ?> MB</span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('存储方式', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value" style="color: #46b450; font-weight: bold;"><?php _e('专用表', 'wp-netdisk-link-checker'); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('元数据记录数', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo number_format($usage['meta_records'] ?? 0); ?></span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('数据大小', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value"><?php echo $usage['data_size_mb'] ?? 0; ?> MB</span>
                                </div>
                                <div class="wpnlc-usage-item">
                                    <span class="wpnlc-usage-label"><?php _e('存储方式', 'wp-netdisk-link-checker'); ?></span>
                                    <span class="wpnlc-usage-value" style="color: #f56e28; font-weight: bold;"><?php _e('Meta表', 'wp-netdisk-link-checker'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="wpnlc-tool-actions">
                            <button type="button" class="button wpnlc-cleanup-data">
                                <?php _e('清理过期数据', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <button type="button" class="button button-secondary wpnlc-reset-all-data">
                                <?php _e('重置所有数据', 'wp-netdisk-link-checker'); ?>
                            </button>
                        </div>
                        <p class="description"><?php _e('清理过期数据会删除30天前的检测记录。重置所有数据会删除所有检测结果。', 'wp-netdisk-link-checker'); ?></p>
                    </div>
                </div>

                <!-- 数据导出 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('数据导出', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-tool-section">
                        <h4><?php _e('导出检测数据', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('导出所有文章的网盘链接检测数据，包括链接状态、检测时间等信息。', 'wp-netdisk-link-checker'); ?></p>

                        <div class="wpnlc-tool-actions">
                            <button type="button" class="button button-primary wpnlc-export-csv">
                                <?php _e('导出为CSV', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <button type="button" class="button wpnlc-export-json">
                                <?php _e('导出为JSON', 'wp-netdisk-link-checker'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="wpnlc-tool-section" style="margin-top: 20px;">
                        <h4><?php _e('失效链接管理', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('导出失效的网盘链接，便于批量更新或替换。', 'wp-netdisk-link-checker'); ?></p>

                        <div class="wpnlc-tool-actions">
                            <button type="button" class="button button-secondary wpnlc-export-invalid-links">
                                <?php _e('导出失效链接', 'wp-netdisk-link-checker'); ?>
                            </button>
                            <button type="button" class="button wpnlc-show-replace-form">
                                <?php _e('批量替换链接', 'wp-netdisk-link-checker'); ?>
                            </button>
                        </div>

                        <!-- 批量替换表单 -->
                        <div id="wpnlc-replace-form" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                            <h5><?php _e('批量替换失效链接', 'wp-netdisk-link-checker'); ?></h5>
                            <p class="description"><?php _e('将失效的链接批量替换为新的有效链接。支持正则表达式匹配。', 'wp-netdisk-link-checker'); ?></p>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('查找链接', 'wp-netdisk-link-checker'); ?></th>
                                    <td>
                                        <input type="text" id="wpnlc-find-url" class="regular-text" placeholder="<?php _e('输入要替换的链接或正则表达式', 'wp-netdisk-link-checker'); ?>" />
                                        <p class="description"><?php _e('例如：https://pan.baidu.com/s/old_link 或 /pan\.baidu\.com\/s\/\w+/', 'wp-netdisk-link-checker'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('替换为', 'wp-netdisk-link-checker'); ?></th>
                                    <td>
                                        <input type="text" id="wpnlc-replace-url" class="regular-text" placeholder="<?php _e('输入新的链接', 'wp-netdisk-link-checker'); ?>" />
                                        <p class="description"><?php _e('例如：https://pan.baidu.com/s/new_link', 'wp-netdisk-link-checker'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('匹配模式', 'wp-netdisk-link-checker'); ?></th>
                                    <td>
                                        <label>
                                            <input type="radio" name="wpnlc-match-mode" value="exact" checked />
                                            <?php _e('精确匹配', 'wp-netdisk-link-checker'); ?>
                                        </label>
                                        <label style="margin-left: 20px;">
                                            <input type="radio" name="wpnlc-match-mode" value="regex" />
                                            <?php _e('正则表达式', 'wp-netdisk-link-checker'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('替换范围', 'wp-netdisk-link-checker'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="wpnlc-replace-content" checked />
                                            <?php _e('文章内容', 'wp-netdisk-link-checker'); ?>
                                        </label>
                                        <label style="margin-left: 20px;">
                                            <input type="checkbox" id="wpnlc-replace-b2-fields" checked />
                                            <?php _e('B2主题字段', 'wp-netdisk-link-checker'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>

                            <div class="wpnlc-tool-actions">
                                <button type="button" class="button button-primary wpnlc-preview-replace">
                                    <?php _e('预览替换', 'wp-netdisk-link-checker'); ?>
                                </button>
                                <button type="button" class="button button-secondary wpnlc-cancel-replace">
                                    <?php _e('取消', 'wp-netdisk-link-checker'); ?>
                                </button>
                            </div>

                            <!-- 预览结果 -->
                            <div id="wpnlc-replace-preview" style="display: none; margin-top: 20px;"></div>

                            <!-- 执行替换按钮 -->
                            <div id="wpnlc-replace-execute" style="display: none; margin-top: 20px;">
                                <button type="button" class="button button-primary wpnlc-execute-replace">
                                    <?php _e('执行替换', 'wp-netdisk-link-checker'); ?>
                                </button>
                                <p class="description" style="color: #d63638;"><?php _e('警告：此操作将直接修改文章内容，请确保已备份数据！', 'wp-netdisk-link-checker'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 系统信息 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('系统信息', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-tool-section">
                        <div class="wpnlc-system-info">
                            <div class="wpnlc-info-item">
                                <span class="wpnlc-info-label"><?php _e('插件版本', 'wp-netdisk-link-checker'); ?></span>
                                <span class="wpnlc-info-value"><?php echo WPNLC_VERSION; ?></span>
                            </div>
                            <div class="wpnlc-info-item">
                                <span class="wpnlc-info-label"><?php _e('WordPress版本', 'wp-netdisk-link-checker'); ?></span>
                                <span class="wpnlc-info-value"><?php echo get_bloginfo('version'); ?></span>
                            </div>
                            <div class="wpnlc-info-item">
                                <span class="wpnlc-info-label"><?php _e('PHP版本', 'wp-netdisk-link-checker'); ?></span>
                                <span class="wpnlc-info-value"><?php echo PHP_VERSION; ?></span>
                            </div>
                            <div class="wpnlc-info-item">
                                <span class="wpnlc-info-label"><?php _e('当前主题', 'wp-netdisk-link-checker'); ?></span>
                                <span class="wpnlc-info-value">
                                    <?php
                                    $theme = wp_get_theme();
                                    echo $theme->get('Name') . ' ' . $theme->get('Version');
                                    if (wpnlc_is_b2_theme_active()) {
                                        echo ' <span style="color: #46b450;">(B2主题支持已启用)</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="wpnlc-info-item">
                                <span class="wpnlc-info-label"><?php _e('WP Cron状态', 'wp-netdisk-link-checker'); ?></span>
                                <span class="wpnlc-info-value">
                                    <?php echo defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? __('已禁用', 'wp-netdisk-link-checker') : __('已启用', 'wp-netdisk-link-checker'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 清理过期数据
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

            // 重置所有数据
            $('.wpnlc-reset-all-data').on('click', function() {
                var button = $(this);

                if (!confirm('<?php _e('警告：这将删除所有检测数据！确定要继续吗？', 'wp-netdisk-link-checker'); ?>')) {
                    return;
                }

                if (!confirm('<?php _e('最后确认：所有检测结果将被永久删除，此操作不可恢复！', 'wp-netdisk-link-checker'); ?>')) {
                    return;
                }

                button.prop('disabled', true).text('<?php _e('重置中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_reset_data',
                        nonce: '<?php echo wp_create_nonce('wpnlc_reset_data'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('所有数据已重置', 'wp-netdisk-link-checker'); ?>');
                            location.reload();
                        } else {
                            alert('<?php _e('重置失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('重置失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('重置所有数据', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });

            // 导出CSV
            $('.wpnlc-export-csv').on('click', function() {
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
                        button.prop('disabled', false).text('<?php _e('导出为CSV', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });

            // 导出JSON
            $('.wpnlc-export-json').on('click', function() {
                var button = $(this);

                button.prop('disabled', true).text('<?php _e('导出中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_export_data',
                        format: 'json',
                        nonce: '<?php echo wp_create_nonce('wpnlc_export_data'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // 创建下载链接 - 确保UTF-8编码
                            var blob = new Blob([response.data.data], {
                                type: 'application/json;charset=utf-8'
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
                        button.prop('disabled', false).text('<?php _e('导出为JSON', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });

            // 导出失效链接
            $('.wpnlc-export-invalid-links').on('click', function() {
                var button = $(this);

                button.prop('disabled', true).text('<?php _e('导出中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_export_invalid_links',
                        nonce: '<?php echo wp_create_nonce('wpnlc_export_invalid'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // 创建下载链接
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
                        button.prop('disabled', false).text('<?php _e('导出失效链接', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });

            // 显示批量替换表单
            $('.wpnlc-show-replace-form').on('click', function() {
                $('#wpnlc-replace-form').slideToggle();
                $('#wpnlc-replace-preview').hide();
                $('#wpnlc-replace-execute').hide();
            });

            // 取消替换
            $('.wpnlc-cancel-replace').on('click', function() {
                $('#wpnlc-replace-form').slideUp();
                $('#wpnlc-replace-preview').hide();
                $('#wpnlc-replace-execute').hide();
            });

            // 预览替换
            $('.wpnlc-preview-replace').on('click', function() {
                var button = $(this);
                var findUrl = $('#wpnlc-find-url').val().trim();
                var replaceUrl = $('#wpnlc-replace-url').val().trim();
                var matchMode = $('input[name="wpnlc-match-mode"]:checked').val();
                var replaceContent = $('#wpnlc-replace-content').is(':checked');
                var replaceB2Fields = $('#wpnlc-replace-b2-fields').is(':checked');

                if (!findUrl || !replaceUrl) {
                    alert('<?php _e('请填写查找和替换的链接', 'wp-netdisk-link-checker'); ?>');
                    return;
                }

                button.prop('disabled', true).text('<?php _e('预览中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_preview_replace_links',
                        find_url: findUrl,
                        replace_url: replaceUrl,
                        match_mode: matchMode,
                        replace_content: replaceContent,
                        replace_b2_fields: replaceB2Fields,
                        nonce: '<?php echo wp_create_nonce('wpnlc_replace_links'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<h5><?php _e('预览结果', 'wp-netdisk-link-checker'); ?></h5>';
                            html += '<p><?php _e('找到', 'wp-netdisk-link-checker'); ?> <strong>' + response.data.total_matches + '</strong> <?php _e('个匹配项，涉及', 'wp-netdisk-link-checker'); ?> <strong>' + response.data.affected_posts + '</strong> <?php _e('篇文章', 'wp-netdisk-link-checker'); ?></p>';

                            if (response.data.matches.length > 0) {
                                html += '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: white;">';
                                response.data.matches.forEach(function(match) {
                                    html += '<div style="margin-bottom: 10px; padding: 10px; border-left: 3px solid #007cba; background: #f9f9f9;">';
                                    html += '<strong>' + match.post_title + '</strong> (ID: ' + match.post_id + ')<br>';
                                    html += '<small><?php _e('原链接:', 'wp-netdisk-link-checker'); ?></small> <code>' + match.old_url + '</code><br>';
                                    html += '<small><?php _e('新链接:', 'wp-netdisk-link-checker'); ?></small> <code>' + match.new_url + '</code>';
                                    html += '</div>';
                                });
                                html += '</div>';
                                $('#wpnlc-replace-execute').show();
                            } else {
                                html += '<p><?php _e('没有找到匹配的链接', 'wp-netdisk-link-checker'); ?></p>';
                                $('#wpnlc-replace-execute').hide();
                            }

                            $('#wpnlc-replace-preview').html(html).show();
                        } else {
                            alert('<?php _e('预览失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('预览失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('预览替换', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });

            // 执行替换
            $('.wpnlc-execute-replace').on('click', function() {
                var button = $(this);
                var findUrl = $('#wpnlc-find-url').val().trim();
                var replaceUrl = $('#wpnlc-replace-url').val().trim();
                var matchMode = $('input[name="wpnlc-match-mode"]:checked').val();
                var replaceContent = $('#wpnlc-replace-content').is(':checked');
                var replaceB2Fields = $('#wpnlc-replace-b2-fields').is(':checked');

                if (!confirm('<?php _e('确定要执行批量替换吗？此操作将直接修改文章内容，无法撤销！', 'wp-netdisk-link-checker'); ?>')) {
                    return;
                }

                button.prop('disabled', true).text('<?php _e('替换中...', 'wp-netdisk-link-checker'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpnlc_execute_replace_links',
                        find_url: findUrl,
                        replace_url: replaceUrl,
                        match_mode: matchMode,
                        replace_content: replaceContent,
                        replace_b2_fields: replaceB2Fields,
                        nonce: '<?php echo wp_create_nonce('wpnlc_replace_links'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('替换完成！共替换了', 'wp-netdisk-link-checker'); ?> ' + response.data.replaced_count + ' <?php _e('个链接，涉及', 'wp-netdisk-link-checker'); ?> ' + response.data.affected_posts + ' <?php _e('篇文章', 'wp-netdisk-link-checker'); ?>');
                            $('#wpnlc-replace-form').slideUp();
                            $('#wpnlc-replace-preview').hide();
                            $('#wpnlc-replace-execute').hide();
                            // 清空表单
                            $('#wpnlc-find-url, #wpnlc-replace-url').val('');
                        } else {
                            alert('<?php _e('替换失败: ', 'wp-netdisk-link-checker'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('替换失败，请稍后重试', 'wp-netdisk-link-checker'); ?>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('执行替换', 'wp-netdisk-link-checker'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * 显示帮助Tab
     */
    private function display_help_tab() {
        ?>
        <div class="wpnlc-help-container">
            <div class="wpnlc-help-main">
                <!-- 快速开始 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('快速开始', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-help-section">
                        <h4><?php _e('1. 查看统计', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('首先查看"链接统计"页面了解当前网盘链接的整体状况和检测进度。', 'wp-netdisk-link-checker'); ?></p>

                        <h4><?php _e('2. 基本设置', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('在"插件设置"页面配置检测频率、缓存时间等基本参数。', 'wp-netdisk-link-checker'); ?></p>

                        <h4><?php _e('3. 开始检测', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('插件会自动检测文章中的网盘链接。您也可以在文章编辑页面手动检测单篇文章，或在"链接统计"页面进行批量检测。', 'wp-netdisk-link-checker'); ?></p>

                        <h4><?php _e('4. 查看结果', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('在文章列表页面查看每篇文章的链接状态，或返回"链接统计"页面查看详细的统计数据和趋势。', 'wp-netdisk-link-checker'); ?></p>
                    </div>
                </div>

                <!-- B2主题支持 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('B2主题支持', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-help-section">
                        <h4><?php _e('支持的字段格式', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('插件完全支持B2主题的下载字段格式：', 'wp-netdisk-link-checker'); ?></p>
                        <pre><code>资源名称|下载地址|提取码,解压码</code></pre>

                        <h4><?php _e('示例', 'wp-netdisk-link-checker'); ?></h4>
                        <pre><code>百度网盘|https://pan.baidu.com/s/1pPh630pFuHAUMUgBO2utDQ?pwd=rx5f|tq=rx5f
蓝奏云|https://lanzou.com/xxxx|tq=123,jy=456
天翼云盘|https://cloud.189.cn/xxxx|tq=abc</code></pre>

                        <h4><?php _e('自动检测', 'wp-netdisk-link-checker'); ?></h4>
                        <p><?php _e('插件会自动检测B2主题下载字段中的网盘链接，无需额外配置。检测结果会显示在下载区域旁边。', 'wp-netdisk-link-checker'); ?></p>
                    </div>
                </div>

                <!-- 支持的网盘 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('支持的网盘', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-help-section">
                        <div class="wpnlc-netdisk-list">
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('百度网盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>pan.baidu.com</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('蓝奏云', 'wp-netdisk-link-checker'); ?></strong>
                                <span>lanzou.com, lanzous.com, lanzoux.com</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('天翼云盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>cloud.189.cn</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('微云', 'wp-netdisk-link-checker'); ?></strong>
                                <span>share.weiyun.com</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('阿里云盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>aliyundrive.com</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('夸克网盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>pan.quark.cn</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('城通网盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>ctfile.com</span>
                            </div>
                            <div class="wpnlc-netdisk-item">
                                <strong><?php _e('123云盘', 'wp-netdisk-link-checker'); ?></strong>
                                <span>123pan.com</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 常见问题 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('常见问题', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-help-section">
                        <details class="wpnlc-faq-item">
                            <summary><?php _e('为什么显示"无网盘链接"？', 'wp-netdisk-link-checker'); ?></summary>
                            <div class="wpnlc-faq-content">
                                <p><?php _e('可能的原因：', 'wp-netdisk-link-checker'); ?></p>
                                <ul>
                                    <li><?php _e('文章中确实没有网盘链接', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('链接格式不被支持', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('B2主题的下载字段格式不正确', 'wp-netdisk-link-checker'); ?></li>
                                </ul>
                                <p><?php _e('可以使用文章编辑页面的"调试信息"功能查看详细信息。', 'wp-netdisk-link-checker'); ?></p>
                            </div>
                        </details>

                        <details class="wpnlc-faq-item">
                            <summary><?php _e('检测结果不准确怎么办？', 'wp-netdisk-link-checker'); ?></summary>
                            <div class="wpnlc-faq-content">
                                <p><?php _e('网盘链接检测可能受到以下因素影响：', 'wp-netdisk-link-checker'); ?></p>
                                <ul>
                                    <li><?php _e('网盘服务器的响应速度', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('网络连接状况', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('网盘的访问限制', 'wp-netdisk-link-checker'); ?></li>
                                </ul>
                                <p><?php _e('建议适当增加缓存时间，避免频繁检测。', 'wp-netdisk-link-checker'); ?></p>
                            </div>
                        </details>

                        <details class="wpnlc-faq-item">
                            <summary><?php _e('如何提高检测效率？', 'wp-netdisk-link-checker'); ?></summary>
                            <div class="wpnlc-faq-content">
                                <p><?php _e('优化建议：', 'wp-netdisk-link-checker'); ?></p>
                                <ul>
                                    <li><?php _e('启用快速检测队列，分批处理文章', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('适当增加缓存时间，减少重复检测', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('选择合适的自动检测频率', 'wp-netdisk-link-checker'); ?></li>
                                    <li><?php _e('定期清理过期数据', 'wp-netdisk-link-checker'); ?></li>
                                </ul>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- 技术支持 -->
                <div class="wpnlc-stats-widget">
                    <h3><?php _e('技术支持', 'wp-netdisk-link-checker'); ?></h3>

                    <div class="wpnlc-help-section">
                        <p><?php _e('如果您遇到问题或需要帮助，请：', 'wp-netdisk-link-checker'); ?></p>
                        <ul>
                            <li><?php _e('查看"工具"页面的系统信息', 'wp-netdisk-link-checker'); ?></li>
                            <li><?php _e('使用文章编辑页面的调试功能', 'wp-netdisk-link-checker'); ?></li>
                            <li><?php _e('检查WordPress和PHP版本兼容性', 'wp-netdisk-link-checker'); ?></li>
                        </ul>

                        <div class="wpnlc-support-info">
                            <p><strong><?php _e('插件版本：', 'wp-netdisk-link-checker'); ?></strong> <?php echo WPNLC_VERSION; ?></p>
                            <p><strong><?php _e('最低要求：', 'wp-netdisk-link-checker'); ?></strong> WordPress 5.0+, PHP 7.4+</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .wpnlc-help-container {
            max-width: 800px;
        }

        .wpnlc-help-section h4 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #23282d;
        }

        .wpnlc-netdisk-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }

        .wpnlc-netdisk-item {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #007cba;
        }

        .wpnlc-netdisk-item strong {
            display: block;
            margin-bottom: 5px;
            color: #23282d;
        }

        .wpnlc-netdisk-item span {
            font-size: 12px;
            color: #666;
        }

        .wpnlc-faq-item {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .wpnlc-faq-item summary {
            padding: 15px;
            cursor: pointer;
            font-weight: 500;
            background-color: #f8f9fa;
            border-radius: 4px 4px 0 0;
        }

        .wpnlc-faq-item[open] summary {
            border-bottom: 1px solid #ddd;
            border-radius: 4px 4px 0 0;
        }

        .wpnlc-faq-content {
            padding: 15px;
        }

        .wpnlc-faq-content ul {
            margin-left: 20px;
        }

        .wpnlc-support-info {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007cba;
            margin-top: 15px;
        }

        .wpnlc-support-info p {
            margin: 5px 0;
        }
        </style>
        <?php
    }

    /**
     * 显示最近失效的链接
     */
    private function display_recent_invalid_links() {
        $recent_invalid = wpnlc_get_recent_invalid_links(5);

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
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * 显示网盘类型统计
     */
    private function display_netdisk_type_stats() {
        global $wpdb;

        // 获取所有链接数据
        $links_data = $wpdb->get_results(
            "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_wpnlc_links_data' AND meta_value != ''",
            ARRAY_A
        );

        $type_counts = array();

        foreach ($links_data as $row) {
            $links = maybe_unserialize($row['meta_value']);
            if (is_array($links)) {
                foreach ($links as $link) {
                    if (isset($link['type'])) {
                        $type = $link['type'];
                        if (!isset($type_counts[$type])) {
                            $type_counts[$type] = 0;
                        }
                        $type_counts[$type]++;
                    }
                }
            }
        }

        if (empty($type_counts)) {
            echo '<p>' . __('暂无网盘链接数据', 'wp-netdisk-link-checker') . '</p>';
            return;
        }

        // 排序
        arsort($type_counts);

        echo '<div class="wpnlc-netdisk-types">';
        foreach ($type_counts as $type => $count) {
            $type_name = wpnlc_get_netdisk_type_text(array($type));
            echo '<div class="wpnlc-netdisk-type-item">';
            echo '<span class="wpnlc-netdisk-type-name">' . esc_html(trim($type_name)) . '</span>';
            echo '<span class="wpnlc-netdisk-type-count">' . $count . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
}

// 添加调试面板的JavaScript
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_footer', function() {
        if (isset($_GET['page']) && $_GET['page'] === 'wp-netdisk-link-checker') {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // 调试面板切换
                $('.wpnlc-toggle-debug').on('click', function() {
                    var panel = $('#wpnlc-debug-panel');
                    if (panel.is(':visible')) {
                        panel.hide();
                    } else {
                        panel.show();
                    }
                });

                // 关闭调试面板
                $('.wpnlc-close-debug').on('click', function() {
                    $('#wpnlc-debug-panel').hide();
                });

                // 获取统计数据
                $('.wpnlc-debug-stats').on('click', function() {
                    var button = $(this);
                    button.prop('disabled', true).text('获取中...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpnlc_debug_stats',
                            nonce: '<?php echo wp_create_nonce('wpnlc_debug'); ?>'
                        },
                        success: function(response) {
                            var result = $('#debug-current-stats');
                            if (response.success) {
                                var data = response.data;
                                var html = '<div style="background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; border-radius: 4px;">';
                                html += '<h4>统计数据</h4>';
                                html += '<p>有效: ' + (data.valid || 0) + '</p>';
                                html += '<p>失效: ' + (data.invalid || 0) + '</p>';
                                html += '<p>混合: ' + (data.mixed || 0) + '</p>';
                                html += '<p>无链接: ' + (data.no_links || 0) + '</p>';

                                if (data.raw_data && data.raw_data.length > 0) {
                                    html += '<h5>数据库原始数据</h5>';
                                    html += '<ul>';
                                    data.raw_data.forEach(function(row) {
                                        html += '<li>' + row.status + ': ' + row.count + '</li>';
                                    });
                                    html += '</ul>';
                                } else {
                                    html += '<p style="color: red;">⚠️ 数据库中没有找到任何状态记录！</p>';
                                }
                                html += '</div>';
                                result.html(html);
                            } else {
                                result.html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>获取失败: ' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#debug-current-stats').html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>请求失败</p></div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('获取当前统计');
                        }
                    });
                });

                // 检查数据库
                $('.wpnlc-debug-database').on('click', function() {
                    var button = $(this);
                    button.prop('disabled', true).text('检查中...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpnlc_debug_database',
                            nonce: '<?php echo wp_create_nonce('wpnlc_debug'); ?>'
                        },
                        success: function(response) {
                            var result = $('#debug-database-status');
                            if (response.success) {
                                var data = response.data;
                                var html = '<div style="background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; border-radius: 4px;">';
                                html += '<h4>数据库状态</h4>';
                                html += '<p>总文章数: ' + data.total_posts + '</p>';
                                html += '<p>已检测文章数: ' + data.checked_posts + '</p>';
                                html += '<p>状态记录数: ' + data.status_records + '</p>';
                                html += '<p>链接数据记录数: ' + data.links_records + '</p>';

                                if (data.sample_records && data.sample_records.length > 0) {
                                    html += '<h5>最近检测记录</h5>';
                                    html += '<ul>';
                                    data.sample_records.forEach(function(record) {
                                        html += '<li>文章' + record.post_id + ': ' + record.status + ' (' + record.check_time + ')</li>';
                                    });
                                    html += '</ul>';
                                }
                                html += '</div>';
                                result.html(html);
                            } else {
                                result.html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>检查失败: ' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#debug-database-status').html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>请求失败</p></div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('检查数据库');
                        }
                    });
                });

                // 检查单篇文章
                $('.wpnlc-debug-single').on('click', function() {
                    var button = $(this);
                    var postId = $('#debug-post-id').val();
                    button.prop('disabled', true).text('检查中...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpnlc_debug_single_post',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('wpnlc_debug'); ?>'
                        },
                        success: function(response) {
                            var result = $('#debug-single-result');
                            if (response.success) {
                                var data = response.data;
                                var html = '<div style="background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; border-radius: 4px;">';
                                html += '<h4>文章 ' + data.post_id + ' 检测结果</h4>';
                                html += '<p><strong>标题:</strong> ' + data.post_title + '</p>';
                                html += '<p><strong>状态:</strong> ' + data.status + '</p>';
                                html += '<p><strong>找到链接数:</strong> ' + (data.links ? data.links.length : 0) + '</p>';

                                if (data.links && data.links.length > 0) {
                                    html += '<h5>链接详情</h5>';
                                    html += '<ul>';
                                    data.links.forEach(function(link) {
                                        html += '<li>' + link.url + ' (' + link.type + ') - ' + link.status + ': ' + link.message + '</li>';
                                    });
                                    html += '</ul>';
                                } else {
                                    html += '<p style="color: orange;">⚠️ 没有找到网盘链接</p>';
                                }
                                html += '</div>';
                                result.html(html);
                            } else {
                                result.html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>检查失败: ' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#debug-single-result').html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>请求失败</p></div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('检查单篇文章');
                        }
                    });
                });

                // 数据库迁移
                $('.wpnlc-migrate-database').on('click', function() {
                    var button = $(this);

                    if (!confirm('确定要迁移到新的表结构吗？这将删除所有现有的检测数据！')) {
                        return;
                    }

                    button.prop('disabled', true).text('迁移中...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpnlc_migrate_database',
                            nonce: '<?php echo wp_create_nonce('wpnlc_debug'); ?>'
                        },
                        success: function(response) {
                            var result = $('#debug-migrate-result');
                            if (response.success) {
                                var data = response.data;
                                var html = '<div style="background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; border-radius: 4px;">';
                                html += '<h4>✅ 数据库迁移成功</h4>';
                                html += '<p>新表名: ' + data.new_table + '</p>';
                                html += '<p>删除的meta记录: ' + data.deleted_meta_records + ' 条</p>';
                                html += '<p>现在可以使用新的表结构进行链接检测了！</p>';
                                html += '</div>';
                                result.html(html);
                            } else {
                                result.html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>迁移失败: ' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#debug-migrate-result').html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>请求失败</p></div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('迁移到新表结构');
                        }
                    });
                });

                // 批量检测测试
                $('.wpnlc-debug-batch').on('click', function() {
                    var button = $(this);
                    button.prop('disabled', true).text('检测中...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpnlc_debug_batch_check',
                            count: 5,
                            nonce: '<?php echo wp_create_nonce('wpnlc_debug'); ?>'
                        },
                        success: function(response) {
                            var result = $('#debug-batch-test-result');
                            if (response.success) {
                                var data = response.data;
                                var html = '<div style="background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; border-radius: 4px;">';
                                html += '<h4>批量检测结果</h4>';
                                html += '<p>检测文章数: ' + data.checked_count + '</p>';
                                html += '<p>有效链接: ' + data.valid_count + '</p>';
                                html += '<p>失效链接: ' + data.invalid_count + '</p>';
                                html += '<p>混合状态: ' + data.mixed_count + '</p>';
                                html += '<p>无链接: ' + data.no_links_count + '</p>';
                                html += '</div>';
                                result.html(html);
                            } else {
                                result.html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>检测失败: ' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#debug-batch-test-result').html('<div style="background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 4px;"><p>请求失败</p></div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('执行批量检测');
                        }
                    });
                });
            });
            </script>
            <?php
        }
    });
}
