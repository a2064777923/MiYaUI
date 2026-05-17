<?php
/**
 * 核心类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 插件核心类
 */
class WPNLC_Core {

    /**
     * 单例实例
     *
     * @var WPNLC_Core
     */
    private static $instance = null;

    /**
     * 设置管理器
     *
     * @var WPNLC_Settings
     */
    public $settings;

    /**
     * 链接检测器
     *
     * @var WPNLC_Checker
     */
    public $checker;

    /**
     * 前台显示
     *
     * @var WPNLC_Frontend
     */
    public $frontend;

    /**
     * 后台管理
     *
     * @var WPNLC_Admin
     */
    public $admin;

    /**
     * 计划任务
     *
     * @var WPNLC_Cron
     */
    public $cron;

    /**
     * 数据库操作
     *
     * @var WPNLC_Database
     */
    public $database;

    /**
     * AJAX处理
     *
     * @var WPNLC_Ajax
     */
    public $ajax;

    /**
     * 统计页面
     *
     * @var WPNLC_Statistics
     */
    public $statistics;

    /**
     * 获取单例实例
     *
     * @return WPNLC_Core
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
        $this->init();
    }

    /**
     * 初始化插件
     */
    private function init() {
        // 加载工具函数
        require_once WPNLC_PLUGIN_DIR . 'includes/functions.php';

        // 初始化各个模块
        $this->settings = new WPNLC_Settings();
        $this->checker = new WPNLC_Checker();
        $this->frontend = new WPNLC_Frontend();
        $this->admin = new WPNLC_Admin();
        $this->cron = new WPNLC_Cron();
        $this->database = new WPNLC_Database();
        $this->ajax = new WPNLC_Ajax();

        // 只在需要时初始化统计页面（保留独立统计页面作为备用）
        if (is_admin() && isset($_GET['page']) && sanitize_text_field($_GET['page']) === 'wpnlc-statistics') {
            $this->statistics = new WPNLC_Statistics();
        }

        // 添加钩子
        $this->add_hooks();
        
        // 确保计划任务已安排（避免设置保存前任务未运行的问题）
        add_action('init', array($this, 'ensure_cron_scheduled'), 20);
        
        // 注册后台任务钩子
        add_action('wpnlc_background_batch_check', array($this->ajax, 'background_batch_check'), 10, 2);
    }

    /**
     * 添加WordPress钩子
     */
    private function add_hooks() {
        // 加载文本域
        add_action('init', array($this, 'load_textdomain'));
        
        // 加载资源文件
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * 加载文本域
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-netdisk-link-checker',
            false,
            dirname(plugin_basename(WPNLC_PLUGIN_DIR . 'wp-netdisk-link-checker.php')) . '/languages'
        );
    }

    /**
     * 加载前台脚本和样式
     */
    public function enqueue_frontend_scripts() {
        $settings = wpnlc_get_settings();

        // 总是加载前台样式，确保美化效果生效
        wp_enqueue_style(
            'wpnlc-front-style',
            WPNLC_PLUGIN_URL . 'assets/css/front-style.css',
            array(),
            WPNLC_VERSION . '-' . time() // 添加时间戳防止缓存
        );

        // 前台不加载JavaScript，只显示状态标签

        // 添加内联样式确保CSS变量生效
        wp_add_inline_style('wpnlc-front-style', '
            /* 确保前台CSS变量在所有浏览器中生效 */
            .wpnlc-front-status,
            .wpnlc-status-label,
            .wpnlc-simple-status,
            .wpnlc-status-container,
            .wpnlc-frontend-check {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            }
        ');
    }

    /**
     * 加载后台脚本和样式
     */
    public function enqueue_admin_scripts($hook) {
        // 只在特定页面加载CSS
        $css_allowed_hooks = array(
            'edit.php',                                    // 文章列表页
            'post.php',                                    // 文章编辑页
            'post-new.php',                               // 新建文章页
            'settings_page_wp-netdisk-link-checker',      // 设置页面
            'tools_page_wpnlc-statistics',                // 统计页面
            'index.php',                                  // 仪表盘
        );

        // 检查是否在允许的页面
        $should_load_css = false;
        foreach ($css_allowed_hooks as $allowed_hook) {
            if (strpos($hook, $allowed_hook) !== false || $hook === $allowed_hook) {
                $should_load_css = true;
                break;
            }
        }

        // 如果不在允许的页面，检查是否是文章相关页面
        if (!$should_load_css) {
            global $post_type;
            if ($post_type === 'post' || (isset($_GET['post_type']) && $_GET['post_type'] === 'post')) {
                $should_load_css = true;
            }
        }

        if (!$should_load_css) {
            return;
        }

        // 确保Dashicons加载
        wp_enqueue_style('dashicons');

        wp_enqueue_style(
            'wpnlc-admin-style',
            WPNLC_PLUGIN_URL . 'assets/css/admin-style.css',
            array('dashicons'),
            WPNLC_VERSION . '-' . time() // 添加时间戳防止缓存
        );

        // 🚨 弹窗功能已完全禁用 - 用户反馈自动弹窗且无法关闭
        // 暂时禁用所有弹窗相关JavaScript，直到找到根本原因

        /*
        // 只在文章列表页面加载JavaScript，确保精确的事件绑定
        if ($hook === 'edit.php') {
            global $post_type;
            // 确保是文章列表页面
            if ($post_type === 'post' || !isset($_GET['post_type'])) {
                // 重新创建JavaScript文件
                $this->create_link_details_js();

                // 加载JavaScript文件
                wp_enqueue_script('jquery');
                wp_enqueue_script(
                    'wpnlc-link-details',
                    WPNLC_PLUGIN_URL . 'assets/js/link-details.js',
                    array('jquery'),
                    WPNLC_VERSION . '-' . time(),
                    true
                );

                // 传递AJAX参数
                wp_localize_script('wpnlc-link-details', 'wpnlc_ajax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wpnlc_ajax_nonce')
                ));
            }
        }
        */

        // 添加内联样式确保CSS变量生效
        wp_add_inline_style('wpnlc-admin-style', '
            /* 确保CSS变量在所有浏览器中生效 */
            .wpnlc-metabox,
            .wpnlc-status,
            .wpnlc-button,
            .netdisk-status-valid,
            .netdisk-status-invalid,
            .netdisk-status-mixed,
            .netdisk-status-none,
            .netdisk-status-disabled,
            .netdisk-status-error {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }


        ');
    }

    /**
     * 确保计划任务已安排
     */
    public function ensure_cron_scheduled() {
        $settings = wpnlc_get_settings();
        
        // 检查主任务是否已安排
        if ($settings['check_frequency'] !== 'disabled' && !wp_next_scheduled('wpnlc_check_links_event')) {
            $this->cron->schedule_tasks();
        }
        
        // 检查快速任务是否已安排
        if ($settings['quick_check_interval'] !== 'disabled' && !wp_next_scheduled('wpnlc_quick_check_event')) {
            $this->cron->schedule_tasks();
        }
    }

    /**
     * 插件激活
     */
    public static function activate() {
        // 创建默认设置
        $default_settings = array(
            'cache_hours' => 6,
            'check_posts' => 'all',
            'check_frequency' => 'daily',
            'enable_notifications' => 'no',
            'notification_email' => '',
            'show_dashboard_widget' => 'yes',
            'show_in_download_box' => 'yes',
            'quick_check_interval' => 'disabled',
            'quick_check_batch' => 1,
            'show_check_time' => 'no',
            'enable_frontend_manual_check' => 'no',
            'frontend_check_permission' => 'logged_in',
            // 新增的优化设置
            'use_concurrent_check' => 'yes',
            'max_concurrent_checks' => 10,
            'check_timeout' => 15,
            'max_retries' => 3,
            'auto_check_batch' => 50,
            'enable_smart_intervals' => 'yes',
            'valid_link_recheck_days' => 7,
            'invalid_link_recheck_days' => 1,
        );

        // 如果设置不存在，则创建默认设置
        if (!get_option('wpnlc_settings')) {
            add_option('wpnlc_settings', $default_settings);
        }

        // 设置计划任务 - 使用Cron类来正确安排任务
        $cron = new WPNLC_Cron();
        $cron->schedule_tasks($default_settings);
    }

    /**
     * 插件停用
     */
    public static function deactivate() {
        // 清除计划任务
        $timestamp = wp_next_scheduled('wpnlc_check_links_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wpnlc_check_links_event');
        }

        $quick_timestamp = wp_next_scheduled('wpnlc_quick_check_event');
        if ($quick_timestamp) {
            wp_unschedule_event($quick_timestamp, 'wpnlc_quick_check_event');
        }
    }

    /**
     * 获取设置
     *
     * @return array
     */
    public function get_settings() {
        return $this->settings->get_settings();
    }

    /**
     * 获取链接检测器
     *
     * @return WPNLC_Checker
     */
    public function get_checker() {
        return $this->checker;
    }

    /**
     * 获取前台显示
     *
     * @return WPNLC_Frontend
     */
    public function get_frontend() {
        return $this->frontend;
    }

    /**
     * 获取后台管理
     *
     * @return WPNLC_Admin
     */
    public function get_admin() {
        return $this->admin;
    }

    /**
     * 获取计划任务
     *
     * @return WPNLC_Cron
     */
    public function get_cron() {
        return $this->cron;
    }

    /**
     * 获取数据库操作
     *
     * @return WPNLC_Database
     */
    public function get_database() {
        return $this->database;
    }

    /**
     * 获取AJAX处理
     *
     * @return WPNLC_Ajax
     */
    public function get_ajax() {
        return $this->ajax;
    }

    /**
     * 获取统计页面
     *
     * @return WPNLC_Statistics|null
     */
    public function get_statistics() {
        if (!isset($this->statistics)) {
            $this->statistics = new WPNLC_Statistics();
        }
        return $this->statistics;
    }

    /**
     * 创建链接详情JavaScript文件
     */
    private function create_link_details_js() {
        $js_file = WPNLC_PLUGIN_DIR . 'assets/js/link-details.js';

        // 如果文件已存在，不重复创建
        if (file_exists($js_file)) {
            return;
        }

        // 确保目录存在
        $js_dir = dirname($js_file);
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }

        $js_content = $this->get_link_details_js_content();
        file_put_contents($js_file, $js_content);
    }

    /**
     * 获取链接详情JavaScript内容
     */
    private function get_link_details_js_content() {
        return '(function($) {
    "use strict";

    // 防止重复初始化
    if (window.wpnlcModalInitialized) {
        console.log("WPNLC: 已初始化，跳过");
        return;
    }

    var LinkDetailsModal = {
        initialized: false,

        init: function() {
            if (this.initialized) {
                console.log("WPNLC: LinkDetailsModal已初始化");
                return;
            }

            console.log("WPNLC: 开始初始化LinkDetailsModal");
            this.createModal();
            this.bindEvents();
            this.initialized = true;
            console.log("WPNLC: LinkDetailsModal初始化完成");
        },

        bindEvents: function() {
            // 只绑定文章列表中的状态标签，使用更精确的选择器
            const statusSelectors = [
                "table.wp-list-table .netdisk-status-valid.clickable",
                "table.wp-list-table .netdisk-status-invalid.clickable",
                "table.wp-list-table .netdisk-status-mixed.clickable"
            ].join(", ");

            console.log("WPNLC: 绑定点击事件到选择器:", statusSelectors);

            // 使用事件委托，但添加严格的检查
            $(document).on("click", statusSelectors, function(e) {
                // 严格检查：只响应真正的用户点击
                if (e.isTrigger || e.originalEvent === undefined) {
                    console.log("WPNLC: 忽略程序触发的事件");
                    return false;
                }

                // 检查事件类型
                if (e.type !== "click") {
                    console.log("WPNLC: 忽略非点击事件:", e.type);
                    return false;
                }

                // 检查是否在正确的页面
                if (window.location.href.indexOf("edit.php") === -1) {
                    console.log("WPNLC: 不在文章列表页面，忽略点击");
                    return false;
                }

                e.preventDefault();
                e.stopPropagation();

                const $this = $(this);
                const postId = $this.data("post-id");

                // 添加调试信息
                console.log("WPNLC: 用户点击状态标签", {
                    postId: postId,
                    element: $this[0],
                    event: e.type,
                    originalEvent: !!e.originalEvent
                });

                // 验证文章ID
                if (!postId || postId === "" || postId === "0" || isNaN(postId)) {
                    console.log("WPNLC: 无效的文章ID:", postId);
                    return false;
                }

                // 显示弹窗
                LinkDetailsModal.showModal(postId);
            });

            $(document).on("click", ".wpnlc-modal-overlay", function(e) {
                if (e.target === this) {
                    LinkDetailsModal.hideModal();
                }
            });

            $(document).on("click", ".wpnlc-modal-close", function(e) {
                e.preventDefault();
                LinkDetailsModal.hideModal();
            });

            $(document).on("keydown", function(e) {
                if (e.keyCode === 27 && $("#wpnlc-modal-container").is(":visible")) {
                    LinkDetailsModal.hideModal();
                }
            });
        },

        createModal: function() {
            if ($("#wpnlc-modal-container").length > 0) {
                console.log("WPNLC: 弹窗容器已存在，跳过创建");
                return;
            }

            console.log("WPNLC: 创建弹窗容器");

            const modalHTML = `
                <div id="wpnlc-modal-container" class="wpnlc-modal-overlay" style="display: none;">
                    <div class="wpnlc-modal">
                        <div class="wpnlc-modal-header">
                            <h3 class="wpnlc-modal-title">链接详情</h3>
                            <button class="wpnlc-modal-close" type="button" title="关闭弹窗">
                                <span aria-hidden="true">&times;</span>
                                <span class="screen-reader-text">关闭</span>
                            </button>
                        </div>
                        <div class="wpnlc-modal-body">
                            <div class="wpnlc-modal-loading" style="text-align: center; padding: 2rem;">
                                <span class="spinner is-active"></span>
                                <p>正在加载链接详情...</p>
                            </div>
                            <div class="wpnlc-modal-content" style="display: none; padding: 1rem;"></div>
                        </div>
                    </div>
                </div>
            `;

            $("body").append(modalHTML);
            console.log("WPNLC: 弹窗容器创建完成，默认隐藏状态");
        },

        showModal: function(postId) {
            console.log("WPNLC: 准备显示弹窗，文章ID:", postId);

            // 确保在正确的页面
            if (window.location.href.indexOf("edit.php") === -1) {
                console.warn("WPNLC: 不在文章列表页面，取消显示弹窗");
                return;
            }

            const $modal = $("#wpnlc-modal-container");
            if ($modal.length === 0) {
                console.error("WPNLC: 弹窗容器不存在");
                return;
            }

            console.log("WPNLC: 显示弹窗");
            $modal.show();
            $("body").addClass("wpnlc-modal-open");
            this.loadLinkDetails(postId);
        },

        hideModal: function() {
            console.log("WPNLC: 隐藏弹窗");

            const $modal = $("#wpnlc-modal-container");
            if ($modal.length > 0) {
                $modal.hide();

                // 重置弹窗状态
                $(".wpnlc-modal-loading").show();
                $(".wpnlc-modal-content").hide().empty();

                console.log("WPNLC: 弹窗已隐藏并重置");
            }

            $("body").removeClass("wpnlc-modal-open");
        },

        loadLinkDetails: function(postId) {
            console.log("WPNLC: 开始加载链接详情，文章ID:", postId);

            $.ajax({
                url: wpnlc_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "wpnlc_get_link_details",
                    post_id: postId,
                    nonce: wpnlc_ajax.nonce
                },
                success: function(response) {
                    console.log("WPNLC: AJAX请求成功", response);

                    if (response.success) {
                        LinkDetailsModal.displayLinkDetails(response.data);
                    } else {
                        console.error("WPNLC: 服务器返回错误", response.data);
                        $(".wpnlc-modal-content").html("<p>加载失败: " + (response.data || "未知错误") + "</p>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("WPNLC: AJAX请求失败", {xhr, status, error});
                    $(".wpnlc-modal-content").html("<p>网络错误，请稍后重试</p>");
                },
                complete: function() {
                    console.log("WPNLC: 隐藏加载状态，显示内容");
                    $(".wpnlc-modal-loading").hide();
                    $(".wpnlc-modal-content").show();
                }
            });
        },

        displayLinkDetails: function(data) {
            console.log("WPNLC: 显示链接详情", data);

            let html = `
                <div class="wpnlc-link-details">
                    <div class="wpnlc-post-info">
                        <h4>${data.post_title || "未知文章"}</h4>
                        <p><strong>文章ID:</strong> ${data.post_id || "未知"}</p>
                        <p><strong>最后检测:</strong> ${data.last_check || "未检测"}</p>
                        <p><strong>总体状态:</strong> <span class="wpnlc-status-${data.overall_status || "unknown"}">${data.overall_status_text || "未知"}</span></p>
                    </div>
            `;

            if (data.links && data.links.length > 0) {
                html += `<div class="wpnlc-links-list">`;
                html += `<h5>网盘链接 (${data.links.length}个)</h5>`;

                data.links.forEach(function(link, index) {
                    html += `
                        <div class="wpnlc-link-item wpnlc-status-${link.status || "unknown"}">
                            <div class="wpnlc-link-header">
                                <span class="wpnlc-link-type">${link.type_text || link.type || "未知类型"}</span>
                                <span class="wpnlc-link-status">${link.status_text || link.status || "未知状态"}</span>
                            </div>
                            <div class="wpnlc-link-url">
                                <a href="${link.url || "#"}" target="_blank">${link.url || "无链接"}</a>
                            </div>
                            ${link.message ? `<div class="wpnlc-link-message">${link.message}</div>` : ""}
                            ${link.check_time ? `<div class="wpnlc-link-time">检测时间: ${link.check_time}</div>` : ""}
                        </div>
                    `;
                });

                html += `</div>`;
            } else {
                html += `<p>未找到网盘链接或链接数据为空</p>`;
            }

            html += `</div>`;

            console.log("WPNLC: 生成的HTML内容", html);
            $(".wpnlc-modal-content").html(html);
        }
    };

    $(document).ready(function() {
        // 防止重复初始化
        if (window.wpnlcModalInitialized) {
            console.log("WPNLC: 全局已初始化，跳过");
            return;
        }

        // 严格检查页面类型，只在文章列表页面初始化
        if (window.location.href.indexOf("edit.php") !== -1 &&
            (window.location.href.indexOf("post_type=") === -1 || window.location.href.indexOf("post_type=post") !== -1) &&
            $("table.wp-list-table").length > 0) {

            console.log("WPNLC: 在文章列表页面，准备初始化链接详情功能");

            // 延迟初始化，确保页面完全加载
            setTimeout(function() {
                // 再次检查，确保没有重复初始化
                if (!window.wpnlcModalInitialized) {
                    LinkDetailsModal.init();

                    var statusElements = $(".netdisk-status-valid.clickable, .netdisk-status-invalid.clickable, .netdisk-status-mixed.clickable");
                    console.log("WPNLC: 找到", statusElements.length, "个可点击状态标签");

                    // 设置全局标记
                    window.wpnlcModalInitialized = true;

                    console.log("WPNLC: 初始化完成，等待用户主动点击状态标签");
                } else {
                    console.log("WPNLC: 延迟检查发现已初始化，跳过");
                }
            }, 1000);
        } else {
            console.log("WPNLC: 不在文章列表页面，跳过初始化");
        }
    });

})(jQuery);';
    }
}
