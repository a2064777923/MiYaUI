<?php
if (!defined('ABSPATH')) {
    die;
}

if (class_exists('CSF')) {
    $prefix = 'aas_admin_options';
     
    CSF::createOptions($prefix, array(
        'menu_title' => '防账号共享',
        'menu_slug' => 'anti-account-sharing',
        'framework_title' => '防账号共享设置 v1.0.0',
        'menu_type' => 'add_menu_page',
        'menu_icon' => 'dashicons-shield-alt',
        'menu_position' => 25,
        'theme' => 'light',
        'footer_text' => '防账号共享插件 v1.0.0 - 作者：喵CG'
    ));
    
    // 基础设置
    CSF::createSection($prefix, array(
        'id' => 'basic_settings',
        'title' => '基础设置',
        'icon' => 'fa fa-cog',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => '基础功能配置'
            ),
            array(
                'id' => 'aas_enable_location_check',
                'type' => 'switcher',
                'title' => '启用地理位置检测',
                'desc' => '检测用户登录的地理位置变化',
                'default' => true
            ),
            array(
                'id' => 'aas_enable_device_check',
                'type' => 'switcher', 
                'title' => '启用设备信息检测',
                'desc' => '记录和分析用户设备信息',
                'default' => true
            ),
            array(
                'id' => 'aas_enable_single_session',
                'type' => 'switcher',
                'title' => '强制单一会话',
                'desc' => '新登录时自动踢出其他会话',
                'default' => true
            ),
            array(
                'id' => 'aas_prevent_simultaneous_login',
                'type' => 'switcher',
                'title' => '禁止同时登录',
                'desc' => '启用后用户只能在一个设备或浏览器上保持登录状态，新的登录会自动退出其他位置的登录',
                'default' => true
            ),
            array(
                'id' => 'aas_simultaneous_login_method',
                'type' => 'select',
                'title' => '同时登录处理方式',
                'desc' => '选择检测到同时登录时的处理方式',
                'options' => array(
                    'force_logout' => '强制退出旧会话（推荐）',
                    'block_new' => '阻止新登录',
                    'warn_only' => '仅警告不处理'
                ),
                'default' => 'force_logout',
                'dependency' => array('aas_prevent_simultaneous_login', '==', true)
            ),
            array(
                'id' => 'aas_enable_auto_warning',
                'type' => 'switcher',
                'title' => '自动警告',
                'desc' => '达到警告阈值时自动发送邮件警告',
                'default' => true
            ),
            array(
                'id' => 'aas_enable_auto_ban',
                'type' => 'switcher',
                'title' => '自动封号',
                'desc' => '达到封号阈值时自动封禁账户',
                'default' => true
            ),
            array(
                'type' => 'heading',
                'content' => '管理员通知设置'
            ),
            array(
                'id' => 'aas_enable_admin_notifications',
                'type' => 'switcher',
                'title' => '启用管理员邮件通知',
                'desc' => '检测到异常用户行为时通知管理员',
                'default' => true
            ),
            array(
                'id' => 'aas_admin_notification_email',
                'type' => 'text',
                'title' => '管理员邮箱',
                'desc' => '接收通知的管理员邮箱，留空则使用站点管理员邮箱',
                'placeholder' => '留空使用默认管理员邮箱',
                'dependency' => array('aas_enable_admin_notifications', '==', true)
            ),
            array(
                'id' => 'aas_notification_triggers',
                'type' => 'checkbox',
                'title' => '通知触发条件',
                'desc' => '选择什么情况下向管理员发送通知',
                'options' => array(
                    'auto_ban' => '自动封禁用户时',
                    'high_risk_user' => '检测到高风险用户时',
                    'suspicious_login' => '可疑登录活动时',
                    'simultaneous_login' => '同时登录检测时',
                    'multiple_locations' => '多地登录时',
                    'registration_abuse' => 'IP注册滥用时'
                ),
                'default' => array('auto_ban', 'high_risk_user', 'suspicious_login'),
                'dependency' => array('aas_enable_admin_notifications', '==', true)
            ),
            array(
                'id' => 'aas_notification_frequency',
                'type' => 'select',
                'title' => '通知频率限制',
                'desc' => '同一用户的通知发送频率限制',
                'options' => array(
                    'immediate' => '立即通知（每次都发送）',
                    'hourly' => '每小时最多一次',
                    'daily' => '每天最多一次',
                    'weekly' => '每周最多一次'
                ),
                'default' => 'hourly',
                'dependency' => array('aas_enable_admin_notifications', '==', true)
            )
        )
    ));
    
    // 阈值设置
    CSF::createSection($prefix, array(
        'id' => 'threshold_settings',
        'title' => '阈值设置',
        'icon' => 'fa fa-sliders',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => '风险等级阈值配置'
            ),
            array(
                'id' => 'aas_warning_threshold',
                'type' => 'number',
                'title' => '警告阈值',
                'desc' => '不同地区登录数量达到此值时发出警告',
                'default' => 3,
                'min' => 1,
                'max' => 20
            ),
            array(
                'id' => 'aas_ban_threshold',
                'type' => 'number',
                'title' => '封号阈值',
                'desc' => '不同地区登录数量达到此值时自动封号',
                'default' => 5,
                'min' => 1,
                'max' => 50
            ),
            array(
                'id' => 'aas_registration_ip_limit',
                'type' => 'number',
                'title' => '单IP注册限制',
                'desc' => '同一IP地址24小时内允许注册的账户数量(0为不限制)',
                'default' => 3,
                'min' => 0,
                'max' => 100
            )
        )
    ));
    
    // IP访问控制
    CSF::createSection($prefix, array(
        'id' => 'ip_control',
        'title' => 'IP访问控制',
        'icon' => 'fa fa-globe',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => 'IP地址访问限制'
            ),
            array(
                'id' => 'aas_block_foreign_ip',
                'type' => 'switcher',
                'title' => '禁止国外IP',
                'desc' => '阻止来自国外的IP地址访问',
                'default' => false
            ),
            array(
                'id' => 'aas_block_domestic_ip',
                'type' => 'switcher',
                'title' => '禁止国内IP',
                'desc' => '阻止来自国内的IP地址访问',
                'default' => false
            ),
            array(
                'type' => 'content',
                'content' => '<div class="csf-notice csf-notice-info">
                    <p>注意：IP地理位置识别可能存在误差，建议配合白名单功能使用。</p>
                </div>'
            )
        )
    ));
    
    // 用户管理
    CSF::createSection($prefix, array(
        'id' => 'user_management',
        'title' => '用户管理',
        'icon' => 'fa fa-users'
    ));
    
    CSF::createSection($prefix, array(
        'parent' => 'user_management',
        'title' => '用户分类',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_user_classification_table'
            )
        )
    ));
    
    CSF::createSection($prefix, array(
        'parent' => 'user_management',
        'title' => '白名单管理',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_whitelist_management'
            )
        )
    ));
    
    CSF::createSection($prefix, array(
        'parent' => 'user_management',
        'title' => '封禁管理',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_ban_management'
            )
        )
    ));
    
    CSF::createSection($prefix, array(
        'parent' => 'user_management',
        'title' => '手动封禁',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_manual_ban'
            )
        )
    ));
    
    // 登录日志
    CSF::createSection($prefix, array(
        'id' => 'login_logs',
        'title' => '登录日志',
        'icon' => 'fa fa-list-alt',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_login_logs'
            )
        )
    ));
    
    // 统计报告
    CSF::createSection($prefix, array(
        'id' => 'statistics',
        'title' => '统计报告',
        'icon' => 'fa fa-bar-chart',
        'fields' => array(
            array(
                'type' => 'callback',
                'function' => 'aas_display_statistics_dashboard'
            )
        )
    ));
    
    // 系统工具
    CSF::createSection($prefix, array(
        'id' => 'system_tools',
        'title' => '系统工具',
        'icon' => 'fa fa-wrench',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => '数据维护工具'
            ),
            array(
                'type' => 'callback',
                'function' => 'aas_display_system_tools'
            )
        )
    ));
    
    // 导入导出
    CSF::createSection($prefix, array(
        'title' => '导入导出',
        'icon' => 'fa fa-download',
        'fields' => array(
            array(
                'type' => 'backup'
            )
        )
    ));
}

// 回调函数用于显示自定义内容
function aas_display_user_classification_table() {
    if (!class_exists('AAS_User_Classification')) {
        return '<p>用户分类系统未加载</p>';
    }
    
    $user_classification = AAS_User_Classification::instance();
    $stats = $user_classification->get_classification_statistics();
    
    echo '<div class="aas-dashboard">';
    echo '<h3>用户风险等级分布</h3>';
    echo '<div class="aas-stats-grid">';
    
    $classifications = array(
        'safe' => array('label' => '安全用户', 'color' => '#28a745'),
        'low_risk' => array('label' => '低风险', 'color' => '#ffc107'),
        'medium_risk' => array('label' => '中风险', 'color' => '#fd7e14'),
        'high_risk' => array('label' => '高风险', 'color' => '#dc3545')
    );
    
    foreach ($classifications as $type => $info) {
        $count = isset($stats[$type]) ? $stats[$type] : 0;
        echo '<div class="aas-stat-card" style="border-left: 4px solid ' . $info['color'] . ';">';
        echo '<h4>' . $info['label'] . '</h4>';
        echo '<div class="aas-stat-number">' . $count . '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '<p>总用户数: ' . $stats['total'] . '</p>';
    echo '</div>';
    
    echo '<style>
    .aas-dashboard { margin: 20px 0; }
    .aas-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .aas-stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .aas-stat-card h4 { margin: 0 0 10px 0; color: #333; }
    .aas-stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
    </style>';
}

function aas_display_whitelist_management() {
    // 白名单管理页面 - 现在使用AJAX处理，无需PHP表单处理
    
    echo '<div class="aas-whitelist-management">';
    echo '<h3>白名单用户管理</h3>';
    echo '<p>白名单用户不受自动警告和封号限制。</p>';
    
    // 显示当前白名单
    global $wpdb;
    $table_name = $wpdb->prefix . 'aas_whitelist';
    $users_table = $wpdb->users;
    
    $results = $wpdb->get_results(
        "SELECT w.user_id, w.created_time, w.reason, u.user_login, u.user_email, u.display_name
         FROM $table_name w
         LEFT JOIN $users_table u ON w.user_id = u.ID
         WHERE w.is_active = 1
         ORDER BY w.created_time DESC
         LIMIT 50"
    );
    
    echo '<form method="post" action="">';
    echo '<table id="whitelist-table" class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>用户ID</th><th>用户名</th><th>邮箱</th><th>原因</th><th>添加时间</th><th>操作</th></tr></thead>';
    echo '<tbody id="whitelist-tbody">';
    
    if (empty($results)) {
        echo '<tr><td colspan="6">暂无白名单用户</td></tr>';
    } else {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->user_id) . '</td>';
            echo '<td>' . esc_html($row->user_login ?: '用户不存在') . '</td>';
            echo '<td>' . esc_html($row->user_email ?: '-') . '</td>';
            echo '<td>' . esc_html($row->reason ?: '-') . '</td>';
            echo '<td>' . esc_html($row->created_time) . '</td>';
            echo '<td>';
            echo '<button type="button" class="button button-small remove-whitelist-btn" data-user-id="' . $row->user_id . '" data-username="' . esc_attr($row->user_login) . '">移除</button>';
            echo '</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo wp_nonce_field('aas_whitelist_action', 'aas_whitelist_nonce');
    echo '</form>';
    
    // 添加用户表单
    echo '<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
    echo '<h4>添加用户到白名单</h4>';
    echo '<form method="post" action="" id="add-whitelist-form" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">';
    echo '<div>';
    echo '<label for="user_id"><strong>用户ID:</strong></label><br>';
    echo '<input type="number" id="user_id" name="user_id" min="1" required style="width: 100px;">';
    echo '</div>';
    echo '<div>';
    echo '<label for="reason"><strong>原因:</strong></label><br>';
    echo '<input type="text" id="reason" name="reason" placeholder="可选" style="width: 200px;">';
    echo '</div>';
    echo '<div>';
    echo '<br>';
    echo '<input type="submit" name="add_whitelist" value="添加到白名单" class="button button-primary">';
    echo '</div>';
    echo wp_nonce_field('aas_whitelist_action', 'aas_whitelist_nonce');
    echo '</form>';
    
    // 添加用户查找功能
    echo '<div style="margin-top: 15px;">';
    echo '<p><strong>如何找到用户ID：</strong></p>';
    echo '<ul>';
    echo '<li>方法1：在WordPress后台 → 用户 → 所有用户，鼠标悬停在用户名上查看URL中的user_id</li>';
    echo '<li>方法2：在用户编辑页面的URL中查看user_id参数</li>';
    echo '<li>方法3：使用下面的搜索功能</li>';
    echo '</ul>';
    
    echo '<div style="margin-top: 10px;">';
    echo '<label for="user_search"><strong>按用户名或邮箱搜索：</strong></label><br>';
    echo '<input type="text" id="user_search" placeholder="输入用户名或邮箱" style="width: 250px;">';
    echo '<button type="button" id="search_user" class="button">搜索</button>';
    echo '<div id="search_results" style="margin-top: 10px;"></div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // 添加JavaScript功能
    echo '<script>
    jQuery(document).ready(function($) {
        
        // 刷新白名单表格函数
        function refreshWhitelistTable() {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_get_whitelist",
                    nonce: "' . wp_create_nonce('aas_get_whitelist') . '"
                },
                success: function(response) {
                    if (response.success) {
                        $("#whitelist-tbody").html(response.data.html);
                    }
                },
                error: function() {
                    console.log("刷新白名单表格失败");
                }
            });
        }
        
        // 将函数暴露到全局，以便其他地方调用
        window.refreshWhitelistTable = refreshWhitelistTable;
        
        // 显示临时消息的函数
        function showMessage(message, type) {
            type = type || "success";
            var noticeClass = "notice notice-" + type + " is-dismissible";
            var messageHtml = "<div class=\"" + noticeClass + "\"><p>" + message + "</p></div>";
            
            // 移除旧消息
            $(".aas-whitelist-management .notice").remove();
            
            // 添加新消息
            $(".aas-whitelist-management").prepend(messageHtml);
            
            // 3秒后自动移除消息
            setTimeout(function() {
                $(".aas-whitelist-management .notice").fadeOut(500, function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // 将函数暴露到全局
        window.showMessage = showMessage;
        // 搜索用户功能
        $("#search_user").click(function() {
            var search_term = $("#user_search").val();
            if (!search_term) {
                alert("请输入搜索词");
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_search_user",
                    search_term: search_term,
                    nonce: "' . wp_create_nonce('aas_search_user') . '"
                },
                success: function(response) {
                    if (response.success) {
                        var html = "<div style=\"background: #fff; border: 1px solid #ddd; padding: 10px; max-height: 200px; overflow-y: auto;\">";
                        if (response.data.length > 0) {
                            html += "<strong>搜索结果：</strong><br>";
                            $.each(response.data, function(index, user) {
                                html += "<div style=\"padding: 5px; border-bottom: 1px solid #eee;\">";
                                html += "<strong>ID: " + user.ID + "</strong> - " + user.user_login + " (" + user.user_email + ")";
                                html += " <button type=\"button\" class=\"button button-small\" onclick=\"$(\'#user_id\').val(\'" + user.ID + "\')\">选择</button>";
                                html += "</div>";
                            });
                        } else {
                            html += "未找到匹配的用户";
                        }
                        html += "</div>";
                        $("#search_results").html(html);
                    } else {
                        $("#search_results").html("<div style=\"color: red;\">搜索失败：" + response.data + "</div>");
                    }
                },
                error: function() {
                    $("#search_results").html("<div style=\"color: red;\">搜索请求失败</div>");
                }
            });
        });
        
        $("#user_search").keypress(function(e) {
            if (e.which == 13) {
                $("#search_user").click();
            }
        });
        
        // 使用AJAX提交白名单表单，避免页面跳转问题
        $("#add-whitelist-form").submit(function(e) {
            e.preventDefault(); // 阻止默认表单提交
            
            var user_id = $("#user_id").val();
            var reason = $("#reason").val();
            var nonce = $("input[name=aas_whitelist_nonce]").val();
            
            if (!user_id) {
                alert("请输入用户ID");
                return;
            }
            
            // 显示加载状态
            var submitBtn = $(this).find("input[type=submit]");
            var originalText = submitBtn.val();
            submitBtn.val("添加中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_add_whitelist",
                    user_id: user_id,
                    reason: reason,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // 显示成功消息
                        showMessage(response.data.message, "success");
                        // 清空表单
                        $("#user_id").val("");
                        $("#reason").val("");
                        // 刷新白名单表格而不重载页面
                        refreshWhitelistTable();
                    } else {
                        // 显示错误消息
                        showMessage(response.data, "error");
                    }
                },
                error: function() {
                    showMessage("请求失败，请稍后重试", "error");
                },
                complete: function() {
                    // 恢复按钮状态
                    submitBtn.val(originalText).prop("disabled", false);
                }
            });
        });
        
        // 移除白名单AJAX处理
        $(document).on("click", ".remove-whitelist-btn", function() {
            var user_id = $(this).data("user-id");
            var username = $(this).data("username");
            var btn = $(this);
            
            if (!confirm("确定要从白名单移除用户 " + username + " 吗？")) {
                return;
            }
            
            // 显示加载状态
            var originalText = btn.text();
            btn.text("移除中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_remove_whitelist", 
                    user_id: user_id,
                    nonce: $("input[name=aas_whitelist_nonce]").val()
                },
                success: function(response) {
                    if (response.success) {
                        // 显示成功消息
                        showMessage(response.data.message, "success");
                        // 刷新白名单表格而不重载页面
                        refreshWhitelistTable();
                    } else {
                        // 显示错误消息
                        showMessage(response.data, "error");
                    }
                },
                error: function() {
                    showMessage("请求失败，请稍后重试", "error");
                },
                complete: function() {
                    // 恢复按钮状态
                    btn.text(originalText).prop("disabled", false);
                }
            });
        });
    });
    </script>';
}

function aas_display_ban_management() {
    echo '<div class="aas-ban-management">';
    echo '<h3>封禁用户管理</h3>';
    echo '<p>管理已被封禁的用户，可以解封或查看封禁详情。</p>';
    
    // 批量解封功能
    echo '<div style="margin-bottom: 20px; padding: 15px; background: #e7f7ff; border: 1px solid #0073aa; border-radius: 4px;">';
    echo '<h4>批量解封操作</h4>';
    echo '<form id="batch-unban-form" style="display: flex; align-items: center; gap: 15px;">';
    echo '<div>';
    echo '<label for="batch_unban_ids"><strong>用户ID列表:</strong></label><br>';
    echo '<textarea id="batch_unban_ids" placeholder="输入要解封的用户ID，每行一个或用逗号分隔" rows="2" style="width: 300px;"></textarea>';
    echo '</div>';
    echo '<div>';
    echo '<input type="submit" value="批量解封" class="button button-primary">';
    echo '</div>';
    echo wp_nonce_field('aas_batch_unban_action', 'aas_batch_unban_nonce', true, false);
    echo '</form>';
    echo '</div>';
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aas_user_classification';
    $users_table = $wpdb->users;
    
    $results = $wpdb->get_results(
        "SELECT c.user_id, c.ban_reason, c.ban_time, c.ban_expires, c.last_updated, u.user_login, u.user_email, u.display_name
         FROM $table_name c
         LEFT JOIN $users_table u ON c.user_id = u.ID
         WHERE c.is_banned = 1
         ORDER BY c.last_updated DESC"
    );
    
    echo '<div id="banned-users-table">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>用户ID</th><th>用户名</th><th>邮箱</th><th>封禁原因</th><th>封禁时间</th><th>过期时间</th><th>操作</th></tr></thead>';
    echo '<tbody id="banned-users-tbody">';
    
    if (empty($results)) {
        echo '<tr><td colspan="7">暂无封禁用户</td></tr>';
    } else {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->user_id) . '</td>';
            echo '<td>' . esc_html($row->user_login ?: '用户不存在') . '</td>';
            echo '<td>' . esc_html($row->user_email ?: '-') . '</td>';
            echo '<td title="' . esc_attr($row->ban_reason) . '">' . esc_html(mb_substr($row->ban_reason ?: '无原因', 0, 30) . (mb_strlen($row->ban_reason ?: '') > 30 ? '...' : '')) . '</td>';
            echo '<td>' . esc_html($row->ban_time ?: $row->last_updated) . '</td>';
            
            // 过期时间处理
            $expires_display = '永久';
            if (!empty($row->ban_expires)) {
                $expires_time = strtotime($row->ban_expires);
                $current_time = time();
                if ($expires_time > $current_time) {
                    $expires_display = esc_html($row->ban_expires);
                } else {
                    $expires_display = '<span style="color: #d63384;">已过期</span>';
                }
            }
            echo '<td>' . $expires_display . '</td>';
            
            echo '<td>';
            echo '<button type="button" class="button button-small unban-user-btn" data-user-id="' . $row->user_id . '" data-username="' . esc_attr($row->user_login) . '">解封</button>';
            echo ' <button type="button" class="button button-small view-ban-details" data-user-id="' . $row->user_id . '">详情</button>';
            echo '</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo '</div>';
    echo '</div>';
    
    // 封禁详情弹窗
    echo '<div id="ban-details-modal" style="display: none;">';
    echo '<div class="ban-details-content"></div>';
    echo '</div>';
    
    // CSS样式
    echo '<style>
    #ban-details-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .ban-details-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }
    
    .ban-info {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .ban-info-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        font-size: 16px;
        font-weight: 600;
    }
    
    .ban-info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .ban-info-row:last-child {
        border-bottom: none;
    }
    
    .ban-info-row .label {
        font-weight: 600;
        color: #555;
        min-width: 80px;
    }
    
    .ban-info-row .value {
        color: #333;
        text-align: right;
        flex: 1;
        word-break: break-word;
    }
    </style>';
    
    // JavaScript处理
    echo '<script>
    jQuery(document).ready(function($) {
        // 单个解封
        $(document).on("click", ".unban-user-btn", function() {
            var user_id = $(this).data("user-id");
            var username = $(this).data("username");
            var btn = $(this);
            
            if (!confirm("确定要解封用户 " + username + " (ID: " + user_id + ") 吗？")) {
                return;
            }
            
            var originalText = btn.text();
            btn.text("解封中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_unban_user",
                    user_id: user_id,
                    nonce: "' . wp_create_nonce('aas_unban_action') . '"
                },
                success: function(response) {
                    if (response.success) {
                        alert("解封成功：" + response.data.message);
                        refreshBannedUsersTable();
                    } else {
                        alert("解封失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                },
                complete: function() {
                    btn.text(originalText).prop("disabled", false);
                }
            });
        });
        
        // 批量解封
        $("#batch-unban-form").submit(function(e) {
            e.preventDefault();
            
            var user_ids = $("#batch_unban_ids").val();
            var nonce = $("#batch-unban-form input[name=aas_batch_unban_nonce]").val();
            
            if (!user_ids) {
                alert("请输入要解封的用户ID");
                return;
            }
            
            var ids = user_ids.split(/[,\\n]/).map(function(id) {
                return id.trim();
            }).filter(function(id) {
                return id && /^\\d+$/.test(id);
            });
            
            if (ids.length === 0) {
                alert("请输入有效的用户ID");
                return;
            }
            
            if (!confirm("确定要批量解封 " + ids.length + " 个用户吗？")) {
                return;
            }
            
            var submitBtn = $(this).find("input[type=submit]");
            var originalText = submitBtn.val();
            submitBtn.val("批量解封中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_batch_unban_users",
                    user_ids: ids.join(","),
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert("批量解封完成：" + response.data.message);
                        $("#batch_unban_ids").val("");
                        refreshBannedUsersTable();
                    } else {
                        alert("批量解封失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                },
                complete: function() {
                    submitBtn.val(originalText).prop("disabled", false);
                }
            });
        });
        
        // 查看封禁详情
        $(document).on("click", ".view-ban-details", function() {
            var user_id = $(this).data("user-id");
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_get_ban_details",
                    user_id: user_id,
                    nonce: "' . wp_create_nonce('aas_ban_details') . '"
                },
                success: function(response) {
                    if (response.success) {
                        $("#ban-details-modal .ban-details-content").html(response.data.html);
                        $("#ban-details-modal").show();
                    } else {
                        alert("获取详情失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                }
            });
        });
        
        // 关闭弹窗
        $("#ban-details-modal").click(function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
        
        // ESC键关闭弹窗
        $(document).keyup(function(e) {
            if (e.keyCode === 27) {
                $("#ban-details-modal").hide();
            }
        });
        
        // 刷新封禁用户表格
        function refreshBannedUsersTable() {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_get_banned_users",
                    nonce: "' . wp_create_nonce('aas_get_banned_users') . '"
                },
                success: function(response) {
                    if (response.success) {
                        $("#banned-users-tbody").html(response.data.html);
                    }
                }
            });
        }
    });
    </script>';
    
    // 处理旧式表单提交（兼容性）
    if (isset($_POST['unban_user']) && wp_verify_nonce($_POST['aas_ban_nonce'], 'aas_ban_action')) {
        $user_id = intval($_POST['unban_user_id']);
        
        if ($user_id) {
            $user_classification = AAS_User_Classification::instance();
            $user_classification->unban_user($user_id);
            echo '<div class="notice notice-success"><p>用户已解封</p></div>';
        }
    }
}

function aas_display_login_logs() {
    echo '<div class="aas-login-logs">';
    echo '<h3>最近登录日志</h3>';
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aas_login_logs';
    $users_table = $wpdb->users;
    
    $results = $wpdb->get_results(
        "SELECT l.*, u.user_login
         FROM $table_name l
         LEFT JOIN $users_table u ON l.user_id = u.ID
         ORDER BY l.login_time DESC
         LIMIT 50"
    );
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>用户</th><th>IP地址</th><th>归属地</th><th>设备/浏览器</th><th>登录时间</th><th>状态</th><th>详情</th></tr></thead>';
    echo '<tbody>';
    
    if (empty($results)) {
        echo '<tr><td colspan="7">暂无登录日志</td></tr>';
    } else {
        // 获取IP地理位置类实例
        $ip_location = class_exists('AAS_IP_Location') ? AAS_IP_Location::instance() : null;
        
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->user_login ?: 'ID:' . $row->user_id) . '</td>';
            echo '<td><code>' . esc_html($row->ip_address) . '</code></td>';
            
            // 显示格式化的地理位置信息
            if ($ip_location) {
                $location_info = $ip_location->get_location_with_formatting($row->ip_address);
                echo '<td>' . $location_info['html'] . '</td>';
            } else {
                // 回退到原始显示方式
                $location_parts = array_filter(array($row->country, $row->region, $row->city), function($part) {
                    return !empty($part) && $part !== 'Unknown';
                });
                $location_display = !empty($location_parts) ? implode(', ', $location_parts) : '未知';
                echo '<td>' . esc_html($location_display) . '</td>';
            }
            
            // 设备和浏览器信息
            $device_info = array();
            if (!empty($row->browser) && $row->browser !== 'Unknown') {
                $device_info[] = $row->browser;
            }
            if (!empty($row->os) && $row->os !== 'Unknown') {
                $device_info[] = $row->os;
            }
            $device_display = !empty($device_info) ? implode(' / ', $device_info) : '未知设备';
            echo '<td>' . esc_html($device_display) . '</td>';
            
            // 登录时间
            echo '<td>' . esc_html($row->login_time) . '</td>';
            
            // 状态
            $status_labels = array(
                'active' => '活跃',
                'logged_out' => '已退出',
                'forced_logout' => '强制退出',
                'forced_logout_simultaneous' => '同时登录强制退出',
                'whitelisted_user' => '白名单用户',
                'banned_attempt' => '封禁用户尝试登录',
                'expired' => '会话过期'
            );
            $status_label = isset($status_labels[$row->status]) ? $status_labels[$row->status] : $row->status;
            echo '<td><span class="aas-status-' . esc_attr($row->status) . '">' . esc_html($status_label) . '</span></td>';
            
            // 详情按钮
            echo '<td>';
            echo '<button type="button" class="button button-small view-ip-details" data-ip="' . esc_attr($row->ip_address) . '">详情</button>';
            echo '</td>';
            
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    echo '</div>';
    
    // IP详情弹窗
    echo '<div id="ip-details-modal" style="display: none;">';
    echo '<div class="ip-details-content"></div>';
    echo '</div>';
    
    echo '<style>
    .aas-status-active { color: #28a745; font-weight: bold; }
    .aas-status-logged_out { color: #6c757d; }
    .aas-status-forced_logout { color: #dc3545; }
    .aas-status-forced_logout_simultaneous { color: #fd7e14; font-weight: bold; }
    .aas-status-whitelisted_user { color: #17a2b8; }
    .aas-status-banned_attempt { color: #dc3545; font-weight: bold; }
    .aas-status-expired { color: #6c757d; font-style: italic; }
    
    .aas-location {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .aas-location.domestic {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .aas-location.foreign {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .aas-location.unknown {
        background-color: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    #ip-details-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .ip-details-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }
    
    .aas-ip-info {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .aas-ip-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        font-size: 16px;
    }
    
    .aas-detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .aas-detail-row:last-child {
        border-bottom: none;
    }
    
    .aas-detail-row .label {
        font-weight: 600;
        color: #555;
        min-width: 60px;
    }
    
    .aas-detail-row .value {
        color: #333;
        text-align: right;
        flex: 1;
    }
    
    .aas-detail-row .value.domestic {
        color: #28a745;
        font-weight: 600;
    }
    
    .aas-detail-row .value.foreign {
        color: #dc3545;
        font-weight: 600;
    }
    </style>';
    
    // JavaScript for IP details popup
    echo '<script>
    jQuery(document).ready(function($) {
        $(".view-ip-details").click(function() {
            var ip = $(this).data("ip");
            var btn = $(this);
            var originalText = btn.text();
            
            // 显示加载状态
            btn.text("加载中...").prop("disabled", true);
            
            // 通过AJAX获取详细的IP信息
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_get_ip_details",
                    ip_address: ip,
                    nonce: "' . wp_create_nonce('aas_get_ip_details') . '"
                },
                success: function(response) {
                    if (response.success) {
                        $("#ip-details-modal .ip-details-content").html(response.data.html);
                        $("#ip-details-modal").show();
                    } else {
                        alert("获取IP详情失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                },
                complete: function() {
                    // 恢复按钮状态
                    btn.text(originalText).prop("disabled", false);
                }
            });
        });
        
        // 点击背景关闭弹窗
        $("#ip-details-modal").click(function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
        
        // ESC键关闭弹窗
        $(document).keyup(function(e) {
            if (e.keyCode === 27) {
                $("#ip-details-modal").hide();
            }
        });
    });
    </script>';
}

function aas_display_statistics_dashboard() {
    echo '<div class="aas-statistics-dashboard">';
    echo '<h3>统计概览</h3>';
    
    if (class_exists('AAS_Access_Control')) {
        $access_control = new AAS_Access_Control();
        $stats = $access_control->get_access_statistics();
        
        echo '<div class="aas-stats-grid">';
        echo '<div class="aas-stat-card">';
        echo '<h4>今日登录</h4>';
        echo '<div class="aas-stat-number">' . $stats['logins_today'] . '</div>';
        echo '</div>';
        
        echo '<div class="aas-stat-card">';
        echo '<h4>被封IP</h4>';
        echo '<div class="aas-stat-number">' . $stats['blocked_ips'] . '</div>';
        echo '</div>';
        
        echo '<div class="aas-stat-card">';
        echo '<h4>封禁用户</h4>';
        echo '<div class="aas-stat-number">' . $stats['banned_users'] . '</div>';
        echo '</div>';
        
        echo '<div class="aas-stat-card">';
        echo '<h4>高风险用户</h4>';
        echo '<div class="aas-stat-number">' . $stats['high_risk_users'] . '</div>';
        echo '</div>';
        echo '</div>';
        
        // 添加同时登录统计
        if (get_option('aas_prevent_simultaneous_login', true)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'aas_login_logs';
            
            // 统计今日同时登录强制退出次数
            $simultaneous_logouts_today = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name 
                 WHERE status = 'forced_logout_simultaneous' 
                 AND DATE(login_time) = %s",
                current_time('Y-m-d')
            ));
            
            // 统计当前活跃会话数
            $active_sessions = $wpdb->get_var(
                "SELECT COUNT(*) FROM $table_name WHERE status = 'active'"
            );
            
            echo '<div style="margin-top: 20px;">';
            echo '<h4>同时登录防护统计</h4>';
            echo '<div class="aas-stats-grid">';
            echo '<div class="aas-stat-card" style="border-left: 4px solid #fd7e14;">';
            echo '<h4>今日强制退出</h4>';
            echo '<div class="aas-stat-number">' . $simultaneous_logouts_today . '</div>';
            echo '<small>因同时登录被强制退出的会话数</small>';
            echo '</div>';
            
            echo '<div class="aas-stat-card" style="border-left: 4px solid #28a745;">';
            echo '<h4>当前活跃会话</h4>';
            echo '<div class="aas-stat-number">' . $active_sessions . '</div>';
            echo '<small>正在活跃的登录会话总数</small>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    echo '</div>';
}

function aas_display_system_tools() {
    echo '<div class="aas-system-tools">';
    echo '<h3>系统维护工具</h3>';
    
    // 数据库状态检查
    echo '<div class="aas-tool-section" style="background: #f0f8ff; padding: 15px; margin-bottom: 20px; border: 1px solid #0073aa;">';
    echo '<h4>数据库状态检查</h4>';
    global $wpdb;
    
    $tables = array(
        'aas_login_logs' => '登录日志表',
        'aas_user_classification' => '用户分类表', 
        'aas_ip_restrictions' => 'IP限制表',
        'aas_whitelist' => '白名单表'
    );
    
    echo '<table class="wp-list-table widefat">';
    echo '<thead><tr><th>表名</th><th>描述</th><th>状态</th><th>记录数</th></tr></thead><tbody>';
    
    foreach ($tables as $table_suffix => $description) {
        $table_name = $wpdb->prefix . $table_suffix;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        $record_count = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
        
        echo '<tr>';
        echo '<td><code>' . $table_name . '</code></td>';
        echo '<td>' . $description . '</td>';
        echo '<td>' . ($table_exists ? '<span style="color: green;">✓ 存在</span>' : '<span style="color: red;">✗ 不存在</span>') . '</td>';
        echo '<td>' . $record_count . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    
    // 重建数据库表按钮
    echo '<form method="post" action="" style="margin-top: 10px;">';
    echo '<input type="submit" name="rebuild_tables" value="重建数据库表" class="button button-secondary" onclick="return confirm(\'确定要重建数据库表吗？这将清空所有数据！\')">';
    echo wp_nonce_field('aas_rebuild_action', 'aas_rebuild_nonce');
    echo '</form>';
    echo '</div>';
    
    echo '<div class="aas-tool-section">';
    echo '<h4>数据清理</h4>';
    echo '<p>清理超过指定天数的登录日志数据。</p>';
    echo '<form method="post" action="">';
    echo '<p>';
    echo '<label>保留天数: <input type="number" name="keep_days" value="90" min="1" max="365"></label> ';
    echo '<input type="submit" name="cleanup_logs" value="清理日志" class="button" onclick="return confirm(\'确定要清理旧的登录日志吗？此操作不可恢复。\')">';
    echo '</p>';
    echo wp_nonce_field('aas_cleanup_action', 'aas_cleanup_nonce');
    echo '</form>';
    echo '</div>';
    
    
    
    echo '<div class="aas-tool-section">';
    echo '<h4>重置统计</h4>';
    echo '<p>重置所有用户的风险等级分类。</p>';
    echo '<form method="post" action="">';
    echo '<input type="submit" name="reset_classifications" value="重置分类" class="button" onclick="return confirm(\'确定要重置所有用户分类吗？此操作不可恢复。\')">';
    echo wp_nonce_field('aas_reset_action', 'aas_reset_nonce');
    echo '</form>';
    echo '</div>';
    
    echo '</div>';
    
    // 处理工具操作
    if (isset($_POST['rebuild_tables']) && wp_verify_nonce($_POST['aas_rebuild_nonce'], 'aas_rebuild_action')) {
        // 重建数据库表
        $aas_instance = AntiAccountSharing::instance();
        $aas_instance->init_database();
        echo '<div class="notice notice-success"><p>数据库表已重建</p></div>';
    }
    
    if (isset($_POST['cleanup_logs']) && wp_verify_nonce($_POST['aas_cleanup_nonce'], 'aas_cleanup_action')) {
        $keep_days = intval($_POST['keep_days']);
        if ($keep_days > 0) {
            $table_name = $wpdb->prefix . 'aas_login_logs';
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$keep_days} days"));
            
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE login_time < %s",
                $cutoff_date
            ));
            
            echo '<div class="notice notice-success"><p>已清理 ' . $deleted . ' 条旧日志记录</p></div>';
        }
    }
    
    if (isset($_POST['reset_classifications']) && wp_verify_nonce($_POST['aas_reset_nonce'], 'aas_reset_action')) {
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $wpdb->query("TRUNCATE TABLE $table_name");
        echo '<div class="notice notice-success"><p>用户分类数据已重置</p></div>';
    }
    
    // JavaScript for system tools
    echo '<script>
    jQuery(document).ready(function($) {
        // 其他功能的JavaScript代码...
    });
    </script>';
}

function aas_display_manual_ban() {
    echo '<div class="aas-manual-ban">';
    echo '<h3>手动封禁用户</h3>';
    echo '<p>手动封禁指定用户，封禁后用户将无法登录。</p>';
    
    // 手动封禁表单
    echo '<div style="margin-top: 20px; padding: 15px; background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 4px;">';
    echo '<h4>封禁用户</h4>';
    echo '<form id="manual-ban-form" style="display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap;">';
    echo '<div>';
    echo '<label for="ban_user_id"><strong>用户ID:</strong></label><br>';
    echo '<input type="number" id="ban_user_id" name="ban_user_id" min="1" required style="width: 100px;">';
    echo '</div>';
    echo '<div>';
    echo '<label for="ban_reason"><strong>封禁原因:</strong></label><br>';
    echo '<input type="text" id="ban_reason" name="ban_reason" placeholder="请输入封禁原因" required style="width: 250px;">';
    echo '</div>';
    echo '<div>';
    echo '<label for="ban_duration"><strong>封禁时长:</strong></label><br>';
    echo '<select id="ban_duration" name="ban_duration" style="width: 150px;">';
    echo '<option value="permanent">永久封禁</option>';
    echo '<option value="1">1天</option>';
    echo '<option value="3">3天</option>';
    echo '<option value="7">7天</option>';
    echo '<option value="30">30天</option>';
    echo '<option value="90">90天</option>';
    echo '</select>';
    echo '</div>';
    echo '<div>';
    echo '<input type="submit" value="封禁用户" class="button button-primary" style="background-color: #dc3545; border-color: #dc3545;">';
    echo '</div>';
    echo wp_nonce_field('aas_manual_ban_action', 'aas_manual_ban_nonce', true, false);
    echo '</form>';
    
    // 用户搜索功能
    echo '<div style="margin-top: 15px;">';
    echo '<label for="ban_user_search"><strong>搜索用户:</strong></label><br>';
    echo '<input type="text" id="ban_user_search" placeholder="输入用户名或邮箱" style="width: 250px;">';
    echo '<button type="button" id="search_ban_user" class="button">搜索</button>';
    echo '<div id="ban_search_results" style="margin-top: 10px;"></div>';
    echo '</div>';
    echo '</div>';
    
    // 批量操作
    echo '<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
    echo '<h4>批量操作</h4>';
    echo '<form id="batch-ban-form">';
    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="batch_user_ids"><strong>用户ID列表:</strong></label><br>';
    echo '<textarea id="batch_user_ids" name="batch_user_ids" placeholder="输入用户ID，每行一个或用逗号分隔" rows="3" style="width: 300px;"></textarea>';
    echo '</div>';
    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="batch_ban_reason"><strong>批量封禁原因:</strong></label><br>';
    echo '<input type="text" id="batch_ban_reason" name="batch_ban_reason" placeholder="请输入批量封禁原因" required style="width: 300px;">';
    echo '</div>';
    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="batch_ban_duration"><strong>封禁时长:</strong></label><br>';
    echo '<select id="batch_ban_duration" name="batch_ban_duration" style="width: 150px;">';
    echo '<option value="permanent">永久封禁</option>';
    echo '<option value="1">1天</option>';
    echo '<option value="3">3天</option>';
    echo '<option value="7">7天</option>';
    echo '<option value="30">30天</option>';
    echo '<option value="90">90天</option>';
    echo '</select>';
    echo '</div>';
    echo '<input type="submit" value="批量封禁" class="button button-secondary" style="background-color: #dc3545; border-color: #dc3545; color: white;">';
    echo wp_nonce_field('aas_batch_ban_action', 'aas_batch_ban_nonce', true, false);
    echo '</form>';
    echo '</div>';
    
    // 快速封禁预设
    echo '<div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">';
    echo '<h4>快速封禁模板</h4>';
    echo '<p>点击使用预设的封禁原因：</p>';
    echo '<div style="display: flex; gap: 10px; flex-wrap: wrap;">';
    
    $quick_ban_reasons = array(
        '账号共享' => '检测到账号共享行为',
        '异常登录' => '异常登录活动',
        '违规操作' => '违反网站使用规定',
        '恶意行为' => '恶意使用网站功能',
        '安全风险' => '账户存在安全风险',
        '多设备登录' => '同时使用过多设备登录'
    );
    
    foreach ($quick_ban_reasons as $label => $reason) {
        echo '<button type="button" class="button button-small quick-ban-reason" data-reason="' . esc_attr($reason) . '">' . esc_html($label) . '</button>';
    }
    
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    // JavaScript处理
    echo '<script>
    jQuery(document).ready(function($) {
        // 快速封禁原因按钮
        $(".quick-ban-reason").click(function() {
            var reason = $(this).data("reason");
            $("#ban_reason").val(reason);
            $("#batch_ban_reason").val(reason);
        });
        
        // 搜索用户功能
        $("#search_ban_user").click(function() {
            var search_term = $("#ban_user_search").val();
            if (!search_term) {
                alert("请输入搜索词");
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_search_user",
                    search_term: search_term,
                    nonce: "' . wp_create_nonce('aas_search_user') . '"
                },
                success: function(response) {
                    if (response.success) {
                        var html = "<div style=\"background: #fff; border: 1px solid #ddd; padding: 10px; max-height: 200px; overflow-y: auto;\">";
                        if (response.data.length > 0) {
                            html += "<strong>搜索结果：</strong><br>";
                            $.each(response.data, function(index, user) {
                                html += "<div style=\"padding: 5px; border-bottom: 1px solid #eee;\">";
                                html += "<strong>ID: " + user.ID + "</strong> - " + user.user_login + " (" + user.user_email + ")";
                                html += " <button type=\"button\" class=\"button button-small\" onclick=\"$(\'#ban_user_id\').val(\'" + user.ID + "\')\">选择</button>";
                                html += "</div>";
                            });
                        } else {
                            html += "未找到匹配的用户";
                        }
                        html += "</div>";
                        $("#ban_search_results").html(html);
                    }
                }
            });
        });
        
        $("#ban_user_search").keypress(function(e) {
            if (e.which == 13) {
                $("#search_ban_user").click();
            }
        });
        
        // 手动封禁表单提交
        $("#manual-ban-form").submit(function(e) {
            e.preventDefault();
            
            var user_id = $("#ban_user_id").val();
            var reason = $("#ban_reason").val();
            var duration = $("#ban_duration").val();
            var nonce = $("#manual-ban-form input[name=aas_manual_ban_nonce]").val();
            
            console.log("Nonce value:", nonce); // 调试信息
            
            if (!user_id || !reason) {
                alert("请填写完整信息");
                return;
            }
            
            if (!confirm("确定要封禁用户ID " + user_id + " 吗？\\n原因：" + reason + "\\n时长：" + (duration === "permanent" ? "永久" : duration + "天"))) {
                return;
            }
            
            var submitBtn = $(this).find("input[type=submit]");
            var originalText = submitBtn.val();
            submitBtn.val("封禁中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_manual_ban_user",
                    user_id: user_id,
                    reason: reason,
                    duration: duration,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert("封禁成功：" + response.data.message);
                        $("#ban_user_id").val("");
                        $("#ban_reason").val("");
                        $("#ban_duration").val("permanent");
                    } else {
                        alert("封禁失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                },
                complete: function() {
                    submitBtn.val(originalText).prop("disabled", false);
                }
            });
        });
        
        // 批量封禁表单提交
        $("#batch-ban-form").submit(function(e) {
            e.preventDefault();
            
            var user_ids = $("#batch_user_ids").val();
            var reason = $("#batch_ban_reason").val();
            var duration = $("#batch_ban_duration").val();
            var nonce = $("#batch-ban-form input[name=aas_batch_ban_nonce]").val();
            
            if (!user_ids || !reason) {
                alert("请填写完整信息");
                return;
            }
            
            // 解析用户ID列表
            var ids = user_ids.split(/[,\\n]/).map(function(id) {
                return id.trim();
            }).filter(function(id) {
                return id && /^\\d+$/.test(id);
            });
            
            if (ids.length === 0) {
                alert("请输入有效的用户ID");
                return;
            }
            
            if (!confirm("确定要批量封禁 " + ids.length + " 个用户吗？\\n原因：" + reason + "\\n时长：" + (duration === "permanent" ? "永久" : duration + "天"))) {
                return;
            }
            
            var submitBtn = $(this).find("input[type=submit]");
            var originalText = submitBtn.val();
            submitBtn.val("批量封禁中...").prop("disabled", true);
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aas_batch_ban_users",
                    user_ids: ids.join(","),
                    reason: reason,
                    duration: duration,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert("批量封禁完成：" + response.data.message);
                        $("#batch_user_ids").val("");
                        $("#batch_ban_reason").val("");
                        $("#batch_ban_duration").val("permanent");
                    } else {
                        alert("批量封禁失败：" + response.data);
                    }
                },
                error: function() {
                    alert("请求失败，请稍后重试");
                },
                complete: function() {
                    submitBtn.val(originalText).prop("disabled", false);
                }
            });
        });
    });
    </script>';
}