<?php

/**
 * 后台管理类
 * 统一管理后台配置面板相关功能
 * 
 * @package XHTheme\AIToolbox
 */

namespace XHTheme\AIToolbox;

defined('ABSPATH') || exit;

class XHAdmin
{

    /**
     * 单例实例
     */
    private static $instance = null;

    /**
     * 获取单例实例
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取默认设置
     */
    public static function get_default_settings()
    {
        return [
            // 基础配置
            'appId' => '',
            'modelType' => 'auto',
            'queueEnabled' => true,
            'imageRecognition' => true,
            'languageType' => 'auto',

            // 内容优化
            'paragraphEnabled' => true,
            'paragraphCapacity' => ['optimize', 'follow', 'expand', 'shorten'],

            // 摘要配置
            'summaryEnabled' => true,
            'summaryModel' => 'interest',
            'summaryMaxLength' => 150,
            'summaryStyle' => 'simple',
            'summaryTitle' => __('AI Smart Summary', 'xhtheme-ai-toolbox'),
            'summaryDesc' => __('AI-generated summary of the article content', 'xhtheme-ai-toolbox'),
            'summaryCssCode' => '',
            'summaryExportFields' => [],

            // 标签配置
            'tagEnabled' => true,
            'tagMinCount' => 2,
            'tagMaxCount' => 4,
            'tagSeoTitle' => true,

            // 评论配置
            'commentEnabled' => true,
            'commentMinCount' => 10,
            'commentMaxCount' => 50,
            'commentMaxday' => 30,
            'commentGroup' => 3,
            'commentNotice' => true,
            'commentuser' => (xh_themeName() === 'Zibll') ? 'user' : 'guest',
            'commentMixedRatio' => 20,

            // 元数据配置
            'primarySlug' => false,
            'primaryCategory' => false,

            // 话题配置
            'primaryThread' => true,
            'primaryThreadTypes' => ['question', 'definition'],
            'primaryThreadPerspectives' => ['blogger', 'expert'],
            'primaryThreadTitle' => __('Trending Topics', 'xhtheme-ai-toolbox'),
            'primaryThreadNumber' => 3,
            'primaryThreadtype' => 'default',
            'primaryThreadDesc' => '',
            'primaryThreadComment' => true,
            'primaryThreadimage' => false,
            'primaryThreadimageRatio' => 50,
            'primaryThreadTemplate' => (function_exists('wp_is_block_theme') && wp_is_block_theme()) ? 'default' : 's2',
            'primaryThreadPage' => false,

            // 图片配置
            'imageThumb' => false,
            'imagePlatform' => 'default',
            'imageSize' => '1280x768',
            'imageStyle' => 'auto',
            'imageApikey' => '',
            'imageModel' => 'flash',
            'imagePattern' => 'fill',
            'imageMapnum' => '1',
            'imageMark' => false,
            'imageCompress' => true
        ];
    }

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * 初始化钩子
     */
    private function init_hooks()
    {
        // 注册后台菜单
        add_action('admin_menu', [$this, 'add_admin_pages']);

        // 注册后台脚本
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // 注册顶部工具栏链接
        add_action('admin_bar_menu', [$this, 'add_toolbar_queue_link'], 100);

        // 注册插件设置链接
        add_filter('plugin_action_links', [$this, 'add_settings_link'], 10, 2);

        // 注册AJAX处理
        add_action('wp_ajax_save_xhtheme_ai_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_xhaitool_thread_page', [$this, 'toggle_thread_page']);
        add_action('wp_ajax_hide_theme_notice', [$this, 'ajax_hide_theme_notice']);
        add_action('wp_ajax_xhaitool_wizard_save', [$this, 'ajax_wizard_save']);

        // 注册后台通知
        add_action('admin_notices', [$this, 'show_adminnotice']);

        // 注册停用反馈弹窗脚本
        add_action('admin_enqueue_scripts', [$this, 'enqueue_deactivate_feedback']);
    }

    /**
     * 添加后台菜单页面
     */
    public function add_admin_pages()
    {
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" width="48" height="48">
                <path d="M512 97.52381c228.912762 0 414.47619 185.563429 414.47619 414.47619s-185.563429 414.47619-414.47619 414.47619S97.52381 740.912762 97.52381 512 283.087238 97.52381 512 97.52381z m-12.190476 170.666666c-19.309714 0-36.035048 34.669714-45.031619 51.395048l-23.942095 57.197714a146.285714 146.285714 0 0 1-78.433524 78.433524l-57.197715 23.942095C278.479238 488.17981 243.809524 504.880762 243.809524 524.190476s34.669714 36.035048 51.395047 45.031619l57.197715 23.942095a146.285714 146.285714 0 0 1 78.433524 78.433524l23.942095 57.197715c9.020952 16.725333 25.721905 51.395048 45.031619 51.395047s36.035048-34.669714 45.031619-51.395047l23.942095-57.197715a146.285714 146.285714 0 0 1 78.433524-78.433524l57.197714-23.942095C721.13981 560.201143 755.809524 543.50019 755.809524 524.190476s-34.669714-36.035048-51.395048-45.031619l-57.197714-23.942095a146.285714 146.285714 0 0 1-78.433524-78.433524l-23.942095-57.197714C535.82019 302.86019 519.119238 268.190476 499.809524 268.190476z" fill="#ffffff"></path>
            </svg>'
        );

        // 主菜单
        add_menu_page(
            __('AI Toolbox', 'xhtheme-ai-toolbox'),
            __('AI Toolbox', 'xhtheme-ai-toolbox'),
            'manage_options',
            'xhtheme-ai-toolbox',
            [$this, 'render_settings_page'],
            $icon_svg
        );

        // 功能设置子菜单
        add_submenu_page(
            'xhtheme-ai-toolbox',
            __('Feature Settings', 'xhtheme-ai-toolbox'),
            __('Feature Settings', 'xhtheme-ai-toolbox'),
            'manage_options',
            'xhtheme-ai-toolbox',
            [$this, 'render_settings_page']
        );

        // 任务队列子菜单
        $queue_instance = XHCronQueue::getInstance();
        $pending_count = $queue_instance->getCachedPendingCount();
        $queue_menu_title = __('Task Queue', 'xhtheme-ai-toolbox');
        if ($pending_count > 0) {
            $queue_menu_title .= sprintf(
                ' <span class="awaiting-mod count-%d"><span class="pending-count">%s</span></span>',
                $pending_count,
                number_format_i18n($pending_count)
            );
        }
        add_submenu_page(
            'xhtheme-ai-toolbox',
            __('Task Queue', 'xhtheme-ai-toolbox'),
            $queue_menu_title,
            'manage_options',
            'xhtheme-ai-queue',
            [$this, 'render_queue_page']
        );

        // 自动化设置子菜单
        add_submenu_page(
            'xhtheme-ai-toolbox',
            __('Automation Settings', 'xhtheme-ai-toolbox'),
            __('Automation Settings', 'xhtheme-ai-toolbox'),
            'manage_options',
            'xhtheme-ai-automate',
            [$this, 'render_automate_page']
        );

        // 配置引导子菜单 - 5天后自动关闭入口
        $option_json = get_option('xhtheme_ai_toolbox_automate_rules');
        $rules_json = get_option('xhtheme_ai_toolbox_automate_rules');
        if ($option_json && $rules_json) {
            return;
        }
        add_submenu_page(
            'xhtheme-ai-toolbox',
            __('Setup Wizard', 'xhtheme-ai-toolbox'),
            __('Setup Wizard', 'xhtheme-ai-toolbox'),
            'manage_options',
            'xhtheme-ai-wizard',
            [$this, 'render_wizard_page']
        );
    }

    /**
     * 渲染设置页面
     */
    public function render_settings_page()
    {
        echo '<div id="xhtheme-ai-toolbox-wrapper"></div>';
    }

    /**
     * 渲染队列页面
     */
    public function render_queue_page()
    {
        echo '<div id="xhtheme-ai-toolbox-queues-wrapper"></div>';
    }

    /**
     * 渲染自动化页面
     */
    public function render_automate_page()
    {
        echo '<div id="xhtheme-ai-toolbox-automate-wrapper"></div>';
    }

    /**
     * 渲染配置引导页面
     */
    public function render_wizard_page()
    {
        echo '<div id="xhtheme-ai-toolbox-wizard-wrapper" class="xhtheme-wizard-fullpage" style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:99999;background-color:#f3f4f6;display:flex;align-items:center;justify-content:center;">
            <div style="text-align:center;color:#94a3b8;">
                <svg style="width:48px;height:48px;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
            </div>
        </div>';
    }

    /**
     * 加载后台脚本和样式
     */
    public function enqueue_admin_scripts()
    {
        $screen = get_current_screen();
        $enqueue = false;
        $pages = [
            'page_xhtheme-ai-toolbox',
            'page_xhtheme-ai-queue',
            'page_xhtheme-ai-automate',
            'page_xhtheme-ai-wizard'
        ];

        foreach ($pages as $page) {
            if (strpos($screen->id, $page) !== false) {
                $enqueue = true;
                break;
            }
        }

        if (!$enqueue) {
            return;
        }

        $dir = untrailingslashit(plugin_dir_path(dirname(__FILE__)));
        $url = untrailingslashit(plugin_dir_url(dirname(__FILE__)));

        // 判断是否是向导页面
        $is_wizard_page = strpos($screen->id, 'page_xhtheme-ai-wizard') !== false;

        if ($is_wizard_page) {
            // 加载向导脚本
            if (!file_exists("{$dir}/build/wizard.asset.php")) {
                return;
            }
            $asset = include "{$dir}/build/wizard.asset.php";
            wp_enqueue_script(
                'xhtheme-ai-toolbox-wizard',
                "{$url}/build/wizard.js",
                $asset['dependencies'],
                $asset['version'],
                true
            );
            wp_set_script_translations(
                'xhtheme-ai-toolbox-wizard',
                'xhtheme-ai-toolbox',
                plugin_dir_path(dirname(__FILE__)) . 'languages'
            );
            $script_handle = 'xhtheme-ai-toolbox-wizard';
        } else {
            // 加载设置脚本
            if (!file_exists("{$dir}/build/settings.asset.php")) {
                return;
            }
            $asset = include "{$dir}/build/settings.asset.php";
            wp_enqueue_script(
                'xhtheme-ai-toolbox-settings',
                "{$url}/build/settings.js",
                $asset['dependencies'],
                $asset['version'],
                true
            );
            wp_set_script_translations(
                'xhtheme-ai-toolbox-settings',
                'xhtheme-ai-toolbox',
                plugin_dir_path(dirname(__FILE__)) . 'languages'
            );
            $script_handle = 'xhtheme-ai-toolbox-settings';
        }

        // 获取保存的设置，与默认值合并
        $saved_settings = get_option('xhtheme_ai_toolbox_settings', []);
        $settings = wp_parse_args($saved_settings, self::get_default_settings());

        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $ai_instance = XHThemeAi::getInstance();
        $thread_instance = XHThread::getInstance();
        $userQuota = $ai_instance->userData('quota');
        $apiupdate = true;
        if ($userQuota) {
            $dateUtc = gmdate('Y-m-d');
            if (isset($userQuota['date_utc']) && $userQuota['date_utc'] == $dateUtc) {
                $apiupdate = false;
            }
        }
        if ($apiupdate) {
            delete_transient('xhtheme_aitoolbox_apicheck');
            $ai_instance->fetchRemoteModelList();
            $userQuota = $ai_instance->userData('quota');
        }

        if (isset($userQuota['date'])) {
            $timestamp = strtotime($userQuota['date']);
            $locale = get_locale();
            if (strpos($locale, 'zh_') === 0 || strpos($locale, 'zh-') === 0) {
                $userQuota['date'] = date('Y.m.d', $timestamp);
            } else {
                $userQuota['date'] = date('M j', $timestamp);
            }
        }

        wp_localize_script(
            $script_handle,
            'xhthemeAiToolbox',
            [
                'nonce' => wp_create_nonce('xhtheme_ai_toolbox_nonce'),
                'settings' => $settings,
                'modelList' => $ai_instance->chatLists('localize'),
                'modelVersion' => $ai_instance->getVersion(),
                'updateTime' => $ai_instance->getModelTime(),
                'classicEditorActive' => is_plugin_active('classic-editor/classic-editor.php'),
                'gutenbergActive' => function_exists('register_block_type'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'pluginVersion' => XHTHEME_AI_TOOLBOX_VERSION,
                'version' => $ai_instance->getVersion(),
                'apiurl' => $ai_instance->apiUrl,
                'apitoken' => $ai_instance->option('appId', ''),
                'userAppiderror' => $ai_instance->userData('error'),
                'userType' => $ai_instance->userData('type'),
                'userName' => $ai_instance->userData('name'),
                'userQuota' => $userQuota,
                'userTypename' => $ai_instance->userTypeName($ai_instance->userData('type')),
                'queueTasks' => xh_active_post_types(),
                'themeFitter' => xh_themefitter_notice(),
                'themeName' => xh_themeName(),
                'blockTheme' => function_exists('wp_is_block_theme') && wp_is_block_theme(),
                'threadPageEditUrl' => $thread_instance->get_pageedit_url(),
                'settingsUrl' => admin_url('admin.php?page=xhtheme-ai-toolbox')
            ]
        );
    }

    /**
     * 添加顶部工具栏链接
     */
    public function add_toolbar_queue_link($wp_admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $icon_svg = '<svg style="width:20px;height:20px;margin-top:2px;vertical-align:middle;fill:currentColor;opacity:.75;margin-right:3px" viewBox="0 0 1024 1024"><path d="M240.832 250.24v549.76a38.4 38.4 0 0 0 76.8 0v-155.968q48.512-15.104 92.544-15.104 53.568 0 101.44 22.528 63.36 29.824 134.208 29.824 69.248 0 144.576-28.672a38.4 38.4 0 0 0 24.704-35.84V250.112a38.4 38.4 0 0 0-52.096-35.84q-62.08 23.68-117.184 23.68-53.632 0-101.504-22.592Q480.96 185.6 410.24 185.6q-69.248 0-144.64 28.672a38.4 38.4 0 0 0-24.704 35.904zM410.24 552.064q-44.992 0-92.544 12.16V277.44Q366.144 262.4 410.24 262.4q53.568 0 101.44 22.528 63.36 29.824 134.208 29.824 44.928 0 92.48-12.096v286.72q-48.512 15.104-92.48 15.104-53.632 0-101.504-22.528-63.36-29.824-134.144-29.824z"></path></svg>';

        // 获取队列数量
        $queue_instance = XHCronQueue::getInstance();
        $pending_count = $queue_instance->getCachedPendingCount();
        $count_badge = '';
        if ($pending_count > 0) {
            $count_badge = '｜<span style="opacity:0.8">' . number_format_i18n($pending_count) . '</span>';
        }

        $wp_admin_bar->add_node([
            'id' => 'xhtheme-ai-queue',
            'title' => $icon_svg . ' <span style="vertical-align:middle">' . __('AI Queue', 'xhtheme-ai-toolbox') . '</span>' . $count_badge,
            'href' => admin_url('admin.php?page=xhtheme-ai-queue'),
            'meta' => [
                'title' => __('Manage AI Task Queue', 'xhtheme-ai-toolbox'),
                'html' => '<style>#wp-admin-bar-xhtheme-ai-queue .ab-item{display:flex !important;align-items:center !important;}</style>'
            ]
        ]);
    }


    /**
     * 添加插件设置链接
     */
    public function add_settings_link($links, $file)
    {
        $plugin_basename = plugin_basename(dirname(dirname(__FILE__)) . '/aitoolbox.php');

        if ($file === $plugin_basename && current_user_can('manage_options')) {
            $url = admin_url('admin.php?page=xhtheme-ai-toolbox');
            $links = (array) $links;
            $links[] = sprintf('<a href="%s">%s</a>', $url, __('Settings', 'xhtheme-ai-toolbox'));
            $links[] = sprintf(
                '<a href="%s" target="_blank" style="color:#04b3a2">%s</a>',
                'https://www.xhtheme.com/docs/aitoolbox',
                __('Document', 'xhtheme-ai-toolbox')
            );
        }

        return $links;
    }

    /**
     * AJAX: 保存设置
     */
    public function ajax_save_settings()
    {
        // 验证nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'xhtheme_ai_toolbox_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'xhtheme-ai-toolbox')]);
            return;
        }

        // 获取设置数据 - JSON 字符串需要先 unslash，消毒在 json_decode 后的 validate_settings 中完成
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string sanitized after decode in validate_settings()
        $settings = isset($_POST['settings']) ? sanitize_text_field(wp_unslash($_POST['settings'])) : '';
        if (!$settings) {
            wp_send_json_error(['message' => __('No settings data received', 'xhtheme-ai-toolbox')]);
            return;
        }

        $settings = json_decode($settings, true);
        if (!is_array($settings)) {
            wp_send_json_error(['message' => __('Settings data format error', 'xhtheme-ai-toolbox')]);
            return;
        }

        $refresh = false;
        $validated_settings = $this->validate_settings($settings);

        // 如果未启用评论功能，强制关闭话题自动评论
        if (empty($validated_settings['commentEnabled'])) {
            $validated_settings['primaryThreadComment'] = false;
        }

        $ai_instance = XHThemeAi::getInstance();
        $userappId = isset($validated_settings['appId']) ? $validated_settings['appId'] : '';
        $oldappId = xh_option('appId', '');

        // 保存设置到数据库
        update_option('xhtheme_ai_toolbox_settings', $validated_settings);

        if ($oldappId !== $userappId) {
            $refresh = true;
            delete_transient('xhtheme_aitoolbox_users_error');
            delete_transient('xhtheme_aitoolbox_users');
            delete_option('xhtheme_aitoolbox_users');
            if ($userappId) {
                delete_transient('xhtheme_aitoolbox_apicheck');
                $ai_instance->fetchRemoteModelList(false);
            }
        }

        if (isset($validated_settings['primaryThread'])) {
            if (!empty($validated_settings['primaryThread'])) {
                if (get_option('xhtheme_ai_toolbox_thread_rewrite') !== 'pass') {
                    update_option('xhtheme_ai_toolbox_thread_rewrite', 'load');
                }
            } else {
                delete_option('xhtheme_ai_toolbox_thread_rewrite');
            }
        }

        wp_send_json_success([
            'message' => __('Settings saved', 'xhtheme-ai-toolbox'),
            'refresh' => $refresh
        ]);
    }

    /**
     * 验证设置数据
     */
    private function validate_settings($settings)
    {
        $validated_settings = [];

        foreach ($settings as $key => $value) {
            switch ($key) {
                case 'tagEnabled':
                case 'tagSeoTitle':
                case 'summaryEnabled':
                case 'commentEnabled':
                case 'commentPlanned':
                case 'primarySlug':
                case 'primaryCategory':
                case 'queueEnabled':
                case 'commentNotice':
                case 'primaryThread':
                case 'primaryThreadComment':
                case 'primaryThreadimage':
                case 'imageThumb':
                case 'imageMark':
                case 'imageCompress':
                case 'imageRecognition':
                case 'primaryThreadPage':
                    $validated_settings[$key] = (bool) $value;
                    break;

                case 'tagMinCount':
                case 'tagMaxCount':
                case 'summaryMaxLength':
                case 'commentMinCount':
                case 'commentMaxCount':
                case 'commentMaxday':
                case 'commentGroup':
                case 'commentMixedRatio':
                case 'primaryThreadNumber':
                case 'primaryThreadimageRatio':
                    $validated_settings[$key] = absint($value);
                    break;

                case 'primaryThreadTypes':
                case 'primaryThreadPerspectives':
                case 'paragraphCapacity':
                    if (is_array($value)) {
                        $validated_settings[$key] = array_map(function ($item) {
                            return sanitize_text_field(trim($item));
                        }, $value);

                        if ($key === 'paragraphCapacity') {
                            if (
                                in_array('fyauto', $validated_settings[$key]) &&
                                isset($validated_settings['languageType']) &&
                                $validated_settings['languageType'] === 'webauto'
                            ) {
                                $validated_settings[$key] = array_filter($validated_settings[$key], function ($item) {
                                    return $item !== 'fyauto';
                                });
                            }
                        }
                    } else {
                        $validated_settings[$key] = [];
                    }
                    break;

                case 'summaryExportFields':
                    if (is_array($value)) {
                        $validated_settings[$key] = array_map(function ($item) {
                            return sanitize_text_field(trim($item));
                        }, $value);
                    } else {
                        $validated_settings[$key] = [];
                    }
                    break;

                case 'summaryCssCode':
                    if (function_exists('wp_strip_all_tags')) {
                        $css = wp_strip_all_tags($value);
                        $disallowed = ['javascript', 'expression', 'behavior', 'vbscript', 'mocha', 'livescript', 'data:', 'javascript:', 'vbscript:'];
                        foreach ($disallowed as $d) {
                            $css = preg_replace('/' . preg_quote($d, '/') . '/i', '', $css);
                        }
                        $css = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $css);
                        $css = preg_replace('/on\w+\s*=/i', '', $css);
                        $css = preg_replace('/[^a-zA-Z0-9\{\}\(\)\[\]\-\+\*\/\,\.\;\:\s\#\@\%\!\=\>\<\~\|\&\"\'\_\\\r\n\t]/', '', $css);

                        if (substr_count($css, '{') !== substr_count($css, '}')) {
                            $open_count = substr_count($css, '{');
                            $close_count = substr_count($css, '}');
                            if ($open_count > $close_count) {
                                $css .= str_repeat('}', $open_count - $close_count);
                            } elseif ($close_count > $open_count) {
                                $css = preg_replace('/\}/', '', $css, $close_count - $open_count);
                            }
                        }
                        $validated_settings[$key] = trim($css);
                    } else {
                        $validated_settings[$key] = '';
                    }
                    break;

                default:
                    $validated_settings[$key] = sanitize_text_field($value);
                    break;
            }
        }

        return $validated_settings;
    }

    /**
     * AJAX: 切换话题聚合页
     */
    public function toggle_thread_page()
    {
        // 验证nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'xhtheme_ai_toolbox_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'xhtheme-ai-toolbox')]);
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'xhtheme-ai-toolbox')]);
        }

        // 获取启用状态
        $is_enable = isset($_POST['enable']) && !empty($_POST['enable']);

        $thread_instance = XHThread::getInstance();
        $threadPage = (array) $thread_instance->toggle_thread_page($is_enable);
        if (!$threadPage) {
            wp_send_json_error(['message' => __('Processing failed, please try again later!', 'xhtheme-ai-toolbox')]);
        }
        if ($threadPage['success']) {
            wp_send_json_success(['message' => $threadPage['message']]);
        }
        // 处理失败
        wp_send_json_error(['message' => $threadPage['message']]);
    }

    /**
     * AJAX: 隐藏主题通知
     */
    public function ajax_hide_theme_notice()
    {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'xhtheme_ai_toolbox_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'xhtheme-ai-toolbox')]);
        }

        $theme_key = isset($_POST['theme']) ? sanitize_text_field(wp_unslash($_POST['theme'])) : '';
        if (empty($theme_key)) {
            wp_send_json_error(['message' => __('Theme key is required', 'xhtheme-ai-toolbox')]);
        }

        $hidden_notices = get_option('xhaitool_theme_notices', []);
        if (!is_array($hidden_notices)) {
            $hidden_notices = [];
        }
        $hidden_notices[] = $theme_key;
        $hidden_notices = array_unique($hidden_notices);
        update_option('xhaitool_theme_notices', $hidden_notices);

        wp_send_json_success(['message' => __('Notice hidden successfully', 'xhtheme-ai-toolbox')]);
    }

    /**
     * 显示配置提醒
     */
    public function show_adminnotice()
    {
        // 仅管理员可见
        if (!current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only for admin notice display, no data modification
        $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

        $appId = xh_option('appId', '');
        if (empty($appId)) {
            // 排除插件设置页面（避免重复提醒）            
            if ($current_page === 'xhtheme-ai-toolbox') {
                return;
            }
?>
            <div class="notice notice-warning" style="padding: 4px 10px; font-size: 12px;margin-top:20px;">
                <p style="margin: 0.3em 0;">
                    <strong><?php esc_html_e('XHTheme AI Toolbox', 'xhtheme-ai-toolbox'); ?>:</strong>
                    <?php esc_html_e('Please complete the AppID configuration to start using the plugin features.', 'xhtheme-ai-toolbox'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=xhtheme-ai-toolbox')); ?>"
                        style="margin-left: 6px; color: #2271b1; text-decoration: underline;">
                        <?php esc_html_e('Go to Settings', 'xhtheme-ai-toolbox'); ?> →
                    </a>
                </p>
            </div>
        <?php
            return;
        }

        // AppID 已配置，检查自动化规则
        $rules_json = get_option('xhtheme_ai_toolbox_automate_rules', '[]');
        $rules = json_decode($rules_json, true);

        if (empty($rules) || !is_array($rules) || count($rules) === 0) {
            if ($current_page === 'xhtheme-ai-automate') {
                return;
            }
        ?>
            <div class="notice notice-info" style="padding: 4px 10px; font-size: 12px;margin-top:20px;">
                <p style="margin: 0.3em 0;">
                    <strong><?php esc_html_e('XHTheme AI Toolbox', 'xhtheme-ai-toolbox'); ?>:</strong>
                    <?php esc_html_e('Configure automation rules to automatically apply AI capabilities to your posts and save time.', 'xhtheme-ai-toolbox'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=xhtheme-ai-automate')); ?>"
                        style="margin-left: 6px; color: #2271b1; text-decoration: underline;">
                        <?php esc_html_e('Configure Automation', 'xhtheme-ai-toolbox'); ?> →
                    </a>
                </p>
            </div>
<?php
            return;
        }
    }

    /**
     * AJAX: Wizard 保存配置
     */
    public function ajax_wizard_save()
    {
        // 验证nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'xhtheme_ai_toolbox_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'xhtheme-ai-toolbox')]);
            return;
        }

        // 检查权限
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'xhtheme-ai-toolbox')]);
            return;
        }

        // 获取数据
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data sanitized after decode
        $features_raw = isset($_POST['features']) ? wp_unslash($_POST['features']) : '[]';
        $config_raw = isset($_POST['config']) ? wp_unslash($_POST['config']) : '{}';

        $features = json_decode($features_raw, true);
        $config = json_decode($config_raw, true);

        if (!is_array($features)) {
            $features = [];
        }
        if (!is_array($config)) {
            $config = [];
        }

        // 创建模拟请求对象来复用 wizardSave 逻辑
        $params = [
            'features' => $features,
            'config' => $config
        ];

        // 调用保存逻辑
        $result = $this->wizardSaveInternal($params);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Wizard 保存配置内部逻辑
     * 供 REST API 和 AJAX 共用
     */
    private function wizardSaveInternal($params)
    {
        $oldAppId = xh_option('appId', '');
        $oldPrimaryThread = xh_option('primaryThread', false);
        $oldPrimaryThreadPage = xh_option('primaryThreadPage', false);
        $features = isset($params['features']) && is_array($params['features']) ? $params['features'] : [];
        $config = isset($params['config']) && is_array($params['config']) ? $params['config'] : [];
        $appId = isset($config['appId']) ? sanitize_text_field($config['appId']) : '';
        if (empty($appId) || strlen($appId) < 15 || strlen($appId) > 30) {
            return [
                'success' => false,
                'message' => __('Invalid AppID, please check your input', 'xhtheme-ai-toolbox')
            ];
        }
        $postTypeAutomation = [];
        if (isset($config['postTypeAutomation']) && is_array($config['postTypeAutomation'])) {
            $postTypeAutomation = $config['postTypeAutomation'];
            unset($config['postTypeAutomation']);
        }
        $featureConfigMap = [
            'summary' => 'summaryEnabled',
            'tags' => 'tagEnabled',
            'comments' => 'commentEnabled',
            'slug' => 'primarySlug',
            'category' => 'primaryCategory',
            'thread' => 'primaryThread',
            'image' => 'imageThumb',
        ];
        foreach ($featureConfigMap as $feature => $configKey) {
            $config[$configKey] = in_array($feature, $features);
        }
        $validated_settings = $this->validate_settings($config);

        // 如果未启用评论功能，强制关闭话题自动评论
        if (!in_array('comments', $features)) {
            $validated_settings['primaryThreadComment'] = false;
        }

        $existing_settings = get_option('xhtheme_ai_toolbox_settings', []);
        $settings = wp_parse_args($validated_settings, is_array($existing_settings) ? $existing_settings : []);
        update_option('xhtheme_ai_toolbox_settings', $settings);

        if (!empty($postTypeAutomation)) {
            $rules = [];
            $queueTasks = xh_active_post_types();
            $taskIdMapping = [
                'image' => 'aiimage'
            ];

            foreach ($postTypeAutomation as $postType => $postTypeConfig) {
                if (!isset($queueTasks[$postType])) {
                    continue;
                }
                $tasks = [];
                $publishOnStatuses = [];

                if (is_array($postTypeConfig)) {
                    if (isset($postTypeConfig['tasks']) && is_array($postTypeConfig['tasks'])) {
                        $tasks = $postTypeConfig['tasks'];
                        $publishOnStatuses = isset($postTypeConfig['publishOnStatuses']) && is_array($postTypeConfig['publishOnStatuses'])
                            ? $postTypeConfig['publishOnStatuses']
                            : [];
                    } else {
                        $tasks = $postTypeConfig;
                    }
                }

                if (empty($tasks)) {
                    continue;
                }

                $typeData = $queueTasks[$postType];
                $validTasks = array_filter($tasks, function ($task) use ($typeData, $features, $taskIdMapping) {
                    $backendTaskId = isset($taskIdMapping[$task]) ? $taskIdMapping[$task] : $task;
                    return isset($typeData['tasks'])
                        && in_array($backendTaskId, $typeData['tasks'])
                        && in_array($task, $features);
                });

                if (!empty($validTasks)) {
                    $tasksObject = [];
                    foreach ($validTasks as $task) {
                        $backendTaskId = isset($taskIdMapping[$task]) ? $taskIdMapping[$task] : $task;
                        $tasksObject[sanitize_text_field($backendTaskId)] = [
                            'enabled' => true,
                            'categories' => ['type' => 'all', 'list' => []]
                        ];
                    }

                    $validStatuses = ['pending', 'private'];
                    $sanitizedStatuses = array_filter(
                        array_map('sanitize_text_field', $publishOnStatuses),
                        function ($status) use ($validStatuses) {
                            return in_array($status, $validStatuses);
                        }
                    );

                    $rules[] = [
                        'id' => $postType . '_' . time(),
                        'name' => $typeData['name'] ?? $postType,
                        'postType' => sanitize_text_field($postType),
                        'enabled' => true,
                        'defaultTrigger' => 'auto',
                        'publishOnStatuses' => array_values($sanitizedStatuses),
                        'tasks' => $tasksObject,
                        'createdAt' => current_time('Y-m-d H:i:s')
                    ];
                }
            }

            if (!empty($rules)) {
                update_option('xhtheme_ai_toolbox_automate_rules', wp_json_encode($rules));
            }
        }

        $newAppId = isset($settings['appId']) ? $settings['appId'] : '';
        $refresh = false;
        if ($oldAppId !== $newAppId) {
            $refresh = true;
            delete_transient('xhtheme_aitoolbox_users_error');
            delete_transient('xhtheme_aitoolbox_users');
            delete_option('xhtheme_aitoolbox_users');
            if ($newAppId) {
                delete_transient('xhtheme_aitoolbox_apicheck');
                $ai_instance = XHThemeAi::getInstance();
                $ai_instance->fetchRemoteModelList(false);
            }
        }

        $newPrimaryThread = isset($settings['primaryThread']) ? $settings['primaryThread'] : false;
        if ($newPrimaryThread !== $oldPrimaryThread) {
            if (!empty($newPrimaryThread)) {
                if (get_option('xhtheme_ai_toolbox_thread_rewrite') !== 'pass') {
                    update_option('xhtheme_ai_toolbox_thread_rewrite', 'load');
                }
            } else {
                delete_option('xhtheme_ai_toolbox_thread_rewrite');
            }
        }

        $newPrimaryThreadPage = isset($settings['primaryThreadPage']) ? $settings['primaryThreadPage'] : false;
        if ($newPrimaryThreadPage !== $oldPrimaryThreadPage) {
            $thread_instance = XHThread::getInstance();
            $thread_instance->toggle_thread_page($newPrimaryThreadPage);
        }

        return [
            'success' => true,
            'message' => __('Configuration saved successfully', 'xhtheme-ai-toolbox'),
            'refresh' => $refresh
        ];
    }

    /**
     * 插件停用反馈弹窗脚本
     * 仅在 plugins.php 页面加载
     */
    public function enqueue_deactivate_feedback($hook)
    {
        if ($hook !== 'plugins.php') {
            return;
        }

        $url = untrailingslashit(plugin_dir_url(dirname(__FILE__)));

        wp_enqueue_script(
            'xhtheme-deactivate-feedback',
            "{$url}/assets/js/deactivate-feedback.min.js",
            [],
            XHTHEME_AI_TOOLBOX_VERSION,
            true
        );

        $ai_instance = XHThemeAi::getInstance();

        wp_localize_script('xhtheme-deactivate-feedback', 'xhDeactivateFeedback', [
            'pluginSlug' => XHTHEME_AI_TOOLBOX_SLUG,
            'apiUrl' => $ai_instance->apiUrl,
            'apiToken' => xh_option('appId', ''),
            'pluginsVer' => XHTHEME_AI_TOOLBOX_VERSION,
            'wpVersion' => get_bloginfo('version'),
            'phpVersion' => PHP_VERSION,
            'reasons' => [
                'missing_feature' => __('Missing features I need', 'xhtheme-ai-toolbox'),
                'bug_or_error' => __('Found a bug or error', 'xhtheme-ai-toolbox'),
                'switch_plugin' => __('Switching to another plugin', 'xhtheme-ai-toolbox'),
                'temporary' => __('Temporary, will re-enable later', 'xhtheme-ai-toolbox'),
                'other' => __('Other', 'xhtheme-ai-toolbox'),
            ],
            'feedbackPrompts' => [
                'missing_feature' => __('What feature do you need? 💡', 'xhtheme-ai-toolbox'),
                'missing_feature_placeholder' => __('Your ideas matter, share your suggestions...', 'xhtheme-ai-toolbox'),
                'bug_or_error' => __('What issue did you encounter? 🔧', 'xhtheme-ai-toolbox'),
                'bug_or_error_placeholder' => __('Describe the problem briefly...', 'xhtheme-ai-toolbox'),
                'switch_plugin' => __('What attracted you to switch?', 'xhtheme-ai-toolbox'),
                'switch_plugin_placeholder' => __('Your feedback helps us improve...', 'xhtheme-ai-toolbox'),
                'other' => __('Mind sharing more?', 'xhtheme-ai-toolbox'),
                'other_placeholder' => __('We are happy to listen...', 'xhtheme-ai-toolbox'),
            ],
            'i18n' => [
                'title' => __('Leaving?', 'xhtheme-ai-toolbox'),
                'subtitle' => __('Your feedback helps us improve', 'xhtheme-ai-toolbox'),
                'feedbackLabel' => __('Feedback or suggestions (optional)', 'xhtheme-ai-toolbox'),
                'feedbackPlaceholder' => __('How can we improve...', 'xhtheme-ai-toolbox'),
                'submit' => __('Submit & Deactivate', 'xhtheme-ai-toolbox'),
                'skip' => __('Skip & Deactivate', 'xhtheme-ai-toolbox'),
                'footerHint' => __('We read every feedback carefully ❤️', 'xhtheme-ai-toolbox'),
            ]
        ]);
    }
}
