<?php

namespace XHTheme\AIToolbox;

final class XHCronQueue
{

    private static $instance;
    private $table_name;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'xhaitoolbox_cron';
    }

    public function init()
    {
        $this->create_table();
        add_action('wp_ajax_xhaitoolbox_queueexecute', [$this, 'queueExecute'], 10, 1);
        add_action('wp_ajax_nopriv_xhaitoolbox_queueexecute', [$this, 'queueExecute'], 10, 1);
        add_action('wp_ajax_xhaitoolbox_queuelist', [$this, 'ajaxQueueList']);
        add_action('wp_ajax_xhaitoolbox_queuedelete', [$this, 'ajaxQueueDelete']);
        add_action('wp_ajax_xhaitoolbox_queueretry', [$this, 'ajaxQueueRetry']);
        add_action('wp_ajax_xhaitoolbox_queuebatchretry', [$this, 'ajaxQueueBatchRetry']);

        add_action('admin_footer', [$this, 'cronClean']);
        add_filter('cron_schedules', [$this, 'add_cron_interval']);
        add_action('rest_api_init', [$this, 'add_restinit']);

        if (!wp_next_scheduled('xhaitoolbox_minute_cron')) {
            wp_schedule_event(time(), 'xhai_5minutes', 'xhaitoolbox_minute_cron');
        }
        if (!wp_next_scheduled('xhaitoolbox_twicedaily_cron')) {
            wp_schedule_event(time(), 'twicedaily', 'xhaitoolbox_twicedaily_cron');
        }

        add_action('xhaitoolbox_minute_cron', array($this, 'process_cron_queue'));
        add_action('xhaitoolbox_twicedaily_cron', array($this, 'process_cleanup_tasks'));
    }

    public function cronClean()
    {
        if (get_transient('xhaitoolbox_clear_overduecrons_2')) {
            return;
        }
        xh_clear_overduecrons();
    }

    public function postTasks($postId)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE post_id = %d", $postId);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Custom table requires direct query
        return $wpdb->get_results($query, ARRAY_A);
    }

    public function addcrontask($type, $postId, $data = [])
    {
        if (empty($type) || empty($postId)) {
            return '';
        }

        $subIf = isset($data['subif']) ? (bool) $data['subif'] : true;

        $CronItem = $this->getrow($type . '_' . $postId);
        $taskName = '';
        $apiType = $type;
        $btnColor = '';
        switch ($type) {
            case 'tags':
                $taskName = esc_attr__('AI Extract Tags', 'xhtheme-ai-toolbox');
                $btnColor = '#c23939';
                break;
            case 'comment':
                $taskName = esc_attr__('Generate AI Comments', 'xhtheme-ai-toolbox');
                $btnColor = '#009688';
                break;
            case 'summary':
                $taskName = esc_attr__('Extract AI Summary', 'xhtheme-ai-toolbox');
                $btnColor = '#D87093';
                break;
            case 'threadlist':
                $taskName = esc_attr__('Generate Topics', 'xhtheme-ai-toolbox');
                $btnColor = '#9C27B0';
                if (!$CronItem) {
                    $CronItem = $this->getrow('primary_' . $postId);
                    if ($CronItem) {
                        $itemData = !empty($CronItem['data']) ? json_decode($CronItem['data'], true) : [];
                        if (!isset($itemData['postthread']) || empty($itemData['postthread'])) {
                            $CronItem = false;
                        }
                    } elseif (isset($data['threadPending']) && !empty($data['threadPending']) && is_array($data['threadPending'])) {
                        $threadPending = $data['threadPending'];
                        foreach ($threadPending as $thread) {
                            $CronItem = $this->getrow('thread_' . $thread->ID);
                        }
                    }
                }
                break;
        }

        if ($CronItem && $CronItem['status'] !== 'skip') {
            if ($CronItem['status'] == 'error') {
                return sprintf(
                    '
                    <p style="margin-top:.5rem">
                        <a href="%s" class="queue-btnerrortack">
                            %s
                            <svg viewBox="0 0 24 24" width="12" height="12" style="margin-left:4px;fill:currentColor;">
                                <path d="M9 6l6 6-6 6"/>
                            </svg>
                        </a>
                    </p>
                    ',
                    esc_url(admin_url('admin.php?page=xhtheme-ai-queue')),
                    esc_attr__('Error', 'xhtheme-ai-toolbox')
                );
            } else {
                return sprintf(
                    '
                    <p style="margin-top:.5rem">
                        <span class="queue-processing">
                            %s
                        </span>
                    </p>
                    ',
                    esc_attr__('AI queue processing', 'xhtheme-ai-toolbox')
                );
            }
        } elseif ($subIf) {
            return sprintf(
                '
                <p style="margin-top:.5rem">
                    <button type="button" 
                        class="button button-small queue-btntack" 
                        data-taskclick="add"
                        data-type="%s"
                        data-post-id="%s" 
                        style="color:%s;border-color:%s">
                        %s
                    </button>
                </p>
                ',
                $apiType,
                $postId,
                $btnColor,
                $btnColor,
                $taskName
            );
        }
        return null;
    }

    public function add_restinit()
    {
        // 保留 queueexecute REST API 端点（需要公开访问）
        register_rest_route(XHTHEME_AI_TOOLBOX_RESTNAME, '/queueexecute', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'queueExecute'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * AJAX handler for queue list
     */
    public function ajaxQueueList()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        $hidden_statuses = isset($_POST['hidden_statuses']) ? array_map('sanitize_text_field', (array) $_POST['hidden_statuses']) : [];

        // 获取总数
        $total = $this->getTotalCount($hidden_statuses);
        // 获取所有队列数据
        $queues = $this->getAll($hidden_statuses);

        // 处理队列数据
        foreach ($queues as $key => &$queue) {
            if (!empty($queue['data'])) {
                $queue['data'] = json_decode($queue['data'], true);
            }

            // 添加文章标题信息
            if (!empty($queue['post_id'])) {
                $post_title = get_the_title($queue['post_id']);
                if (!$post_title) {
                    $this->delete($queue['cron_id']);
                    unset($queues[$key]);
                    continue;
                }
                $queue['post_title'] = $post_title;
            }

            switch ($queue['type']) {
                case 'summary':
                    $queue['typeName'] = __('AI Summary', 'xhtheme-ai-toolbox');
                    break;
                case 'comment':
                    $queue['typeName'] = __('AI Comments', 'xhtheme-ai-toolbox');
                    break;
                case 'threadlist':
                    $queue['typeName'] = __('AI Topics', 'xhtheme-ai-toolbox');
                    break;
                case 'thread':
                    $queue['typeName'] = __('Topic Content', 'xhtheme-ai-toolbox');
                    break;
                case 'tags':
                    $queue['typeName'] = __('AI Tags', 'xhtheme-ai-toolbox');
                    break;
                case 'primary':
                    $queue['typeName'] = __('Metadata', 'xhtheme-ai-toolbox');
                    break;
                case 'imageword':
                    $queue['typeName'] = __('Images Word', 'xhtheme-ai-toolbox');
                    break;
                case 'getimage':
                    $queue['typeName'] = __('Query Images', 'xhtheme-ai-toolbox');
                    break;
            }
            if ($queue['status'] == 'loading' && (!empty($queue['message']) || strtotime(current_time('mysql')) - strtotime($queue['setdate']) > 300)) {
                $queue['status'] = 'hold-retry';
            }
        }

        $queues = array_values($queues);
        $retry_count = $this->getRetryableCount($hidden_statuses);

        wp_send_json_success([
            'data' => $queues,
            'total' => $total,
            'limit' => count($queues),
            'retry_count' => $retry_count
        ]);
    }

    /**
     * AJAX handler for queue delete
     */
    public function ajaxQueueDelete()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        $cronIds = isset($_POST['cron_ids']) ? array_map('sanitize_text_field', (array) $_POST['cron_ids']) : [];

        if (empty($cronIds)) {
            wp_send_json_error(['message' => __('Cron ID(s) are required', 'xhtheme-ai-toolbox')]);
        }

        $deleted_count = 0;
        $failed_count = 0;

        foreach ($cronIds as $cronId) {
            if (empty($cronId)) {
                $failed_count++;
                continue;
            }

            $cronItem = $this->getrow($cronId);
            if ($cronItem) {
                switch ($cronItem['type']) {
                    case 'thread':
                        $postId = $cronItem['post_id'];
                        wp_delete_post($postId, true);
                        break;
                    case 'imageword':
                    case 'getimage':
                        $postId = $cronItem['post_id'];
                        delete_post_meta($postId, '_aiimage_status');
                        break;
                }
            }

            if ($this->delete($cronId)) {
                $deleted_count++;
            } else {
                $failed_count++;
            }
        }

        $is_batch = count($cronIds) > 1;

        if ($deleted_count > 0) {
            $message = $is_batch
                ? sprintf(__('Successfully deleted %d items', 'xhtheme-ai-toolbox'), $deleted_count)
                : __('Cron deleted successfully', 'xhtheme-ai-toolbox');

            wp_send_json_success([
                'message' => $message,
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count
            ]);
        } else {
            $message = $is_batch
                ? __('Failed to delete any items', 'xhtheme-ai-toolbox')
                : __('Failed to delete cron', 'xhtheme-ai-toolbox');

            wp_send_json_error([
                'message' => $message,
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count
            ]);
        }
    }

    /**
     * AJAX handler for queue retry
     */
    public function ajaxQueueRetry()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        $cronIds = isset($_POST['cron_ids']) ? array_map('sanitize_text_field', (array) $_POST['cron_ids']) : [];

        if (empty($cronIds)) {
            wp_send_json_error(['message' => __('Cron ID(s) are required', 'xhtheme-ai-toolbox')]);
        }

        $success_count = 0;
        $failed_count = 0;
        $skipped_count = 0;

        foreach ($cronIds as $cronId) {
            if (empty($cronId)) {
                $failed_count++;
                continue;
            }

            $cronItem = $this->getrow($cronId);
            if (!$cronItem) {
                $failed_count++;
                continue;
            }

            $local_timestamp = xh_local_timestamp();
            if ($cronItem['status'] == 'error' || $cronItem['status'] == 'skip' || $cronItem['status'] == 'retry' || ($cronItem['status'] == 'freeze' && $local_timestamp - strtotime($cronItem['setdate']) > 1200) || ($cronItem['status'] == 'loading' && (!empty($cronItem['message']) || $local_timestamp - strtotime($cronItem['setdate']) > 600))) {
                $setData = [
                    'status' => 'hold',
                    'errornum' => (int) $cronItem['errornum'] > 4 ? 0 : (int) $cronItem['errornum'],
                    'message' => ''
                ];

                if ($cronItem['status'] == 'skip') {
                    $cronData = !empty($cronItem['data']) ? json_decode($cronItem['data'], true) : [];
                    if (isset($cronData['config']['categories'])) {
                        if (count($cronData['config']) == 1) {
                            unset($cronData['config']);
                        } else {
                            unset($cronData['config']['categories']);
                        }
                    }
                    if (isset($cronData['type']) && $cronData['type'] == 'primary') {
                        foreach ($cronData as $cronkey => $cron) {
                            if ($cronkey == 'config' || !is_array($cron) || !isset($cron['config'])) {
                                continue;
                            }
                            if (isset($cron['config']['categories'])) {
                                if (count($cron['config']) == 1) {
                                    $cronData[$cronkey] = 1;
                                } else {
                                    unset($cronData[$cronkey]['config']['categories']);
                                }
                            }
                        }
                    }
                    $setData['data'] = $cronData;
                }

                $updateResult = $this->update($cronId, $setData);
                if ($updateResult) {
                    $success_count++;
                } else {
                    $failed_count++;
                }
            } else {
                $skipped_count++;
            }
        }

        $is_batch = count($cronIds) > 1;

        if ($success_count > 0) {
            $message = $is_batch
                ? sprintf(__('Successfully retried %d tasks', 'xhtheme-ai-toolbox'), $success_count)
                : __('Task has been queued for retry', 'xhtheme-ai-toolbox');

            wp_send_json_success([
                'message' => $message,
                'success_count' => $success_count,
                'failed_count' => $failed_count,
                'skipped_count' => $skipped_count
            ]);
        } else {
            $message = $is_batch
                ? __('No tasks were retried', 'xhtheme-ai-toolbox')
                : __('Only failed tasks can be retried', 'xhtheme-ai-toolbox');

            wp_send_json_error([
                'message' => $message,
                'success_count' => $success_count,
                'failed_count' => $failed_count,
                'skipped_count' => $skipped_count
            ]);
        }
    }

    /**
     * AJAX handler for batch retry
     */
    public function ajaxQueueBatchRetry()
    {
        check_ajax_referer('xhtheme_ai_toolbox_nonce', '_ajax_nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'xhtheme-ai-toolbox')]);
        }

        global $wpdb;

        $current_time = current_time('mysql');
        // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $timeout_time = date('Y-m-d H:i:s', strtotime($current_time) - 300);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $updated_count = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->prepare(
                "UPDATE {$this->table_name} 
                SET status = 'hold', 
                    errornum = CASE WHEN errornum > 4 THEN 0 ELSE errornum END,
                    message = '',
                    setdate = %s
                WHERE (status = 'error' OR status = 'retry')
                   OR (status = 'loading' AND setdate < %s)",
                $current_time,
                $timeout_time
            )
        );

        if ($updated_count > 0) {
            wp_send_json_success([
                'message' => sprintf(__('Successfully retried %d tasks', 'xhtheme-ai-toolbox'), $updated_count),
                'retry_count' => $updated_count
            ]);
        } else {
            wp_send_json_error([
                'message' => __('No tasks need to be retried', 'xhtheme-ai-toolbox'),
                'retry_count' => 0
            ]);
        }
    }

    public function queueExecute()
    {
        // 检查是否已经在处理中
        if (get_transient('xhaitoolbox_cron_queue_process')) {
            echo 'hold';
            exit;
        }

        ignore_user_abort(true);
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for long-running cron tasks
        set_time_limit(0);

        // 关闭客户端连接，继续后台执行
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        }

        do_action('xhaitoolbox_twicedaily_cron');
        do_action('xhaitoolbox_minute_cron');
    }

    private function create_table()
    {
        if (get_option('xhtheme_ai_toolbox_addmysql') == XHTHEME_AI_TOOLBOX_VERSION) {
            return;
        }

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_name} (
            id BIGINT NOT NULL AUTO_INCREMENT,
            cron_id VARCHAR(100) NOT NULL,
            post_id INT DEFAULT 0,
            type VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            setdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            errornum INT DEFAULT 0,
            fornumber INT DEFAULT 0,
            priority INT DEFAULT 10,
            data LONGTEXT,
            message TEXT,
            PRIMARY KEY (id),
            UNIQUE KEY cron_id (cron_id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name variable is safe
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Checking table existence
        if ($wpdb->get_var($query) == $this->table_name) {
            update_option('xhtheme_ai_toolbox_addmysql', XHTHEME_AI_TOOLBOX_VERSION);
            return;
        }
    }

    /**
     * 添加自定义的Cron时间间隔
     */
    public function add_cron_interval($schedules)
    {
        $schedules['xhai_5minutes'] = array(
            'interval' => 300,
            'display' => __('Every 5 minutes', 'xhtheme-ai-toolbox')
        );
        return $schedules;
    }

    /**
     * 获取队列总数
     */
    public function getTotalCount($hidden_statuses = [])
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        if (!empty($hidden_statuses)) {
            $placeholders = implode(',', array_fill(0, count($hidden_statuses), '%s'));
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic placeholders, prepare() called below
            $newsql = $sql . " WHERE status NOT IN ($placeholders)";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable contains prepared SQL
            $sql = $wpdb->prepare($newsql, ...$hidden_statuses);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Custom table with dynamic SQL
        return (int) $wpdb->get_var($sql);
    }

    /**
     * 获取待处理队列数量（带缓存，每5秒刷新）
     * 用于菜单栏显示，统计除 skip 外的所有任务（包含 error）
     */
    public function getCachedPendingCount()
    {
        $cache_key = 'xhaitoolbox_pending_count';
        $cache_group = 'xhaitoolbox';
        $count = wp_cache_get($cache_key, $cache_group);

        if (false === $count) {
            global $wpdb;
            // 获取除 skip 状态外的所有任务数量
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Real-time queue count
            $count = (int) $wpdb->get_var(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
                "SELECT COUNT(*) FROM {$this->table_name} WHERE status != 'skip'"
            );
            $cacheTime = 1;
            if ($count > 100) {
                $cacheTime = 10;
            } elseif ($count > 20) {
                $cacheTime = 5;
            }
            wp_cache_set($cache_key, $count, $cache_group, $cacheTime); // 缓存5秒
        }

        return (int) $count;
    }

    /**
     * 获取可重试任务数量（error, retry 状态 + 超过300秒的loading，不包含skip）
     */
    public function getRetryableCount($hidden_statuses = [])
    {
        global $wpdb;

        $current_time = current_time('mysql');
        // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Must use local time to match current_time('mysql') stored in database
        $timeout_time = date('Y-m-d H:i:s', strtotime($current_time) - 300);

        // 构建查询条件
        $where_parts = [];
        $params = [];

        // 检查是否隐藏了 error 状态
        if (!in_array('error', $hidden_statuses)) {
            $where_parts[] = "status = 'error'";
        }

        // 检查是否隐藏了 retry 状态
        if (!in_array('retry', $hidden_statuses)) {
            $where_parts[] = "status = 'retry'";
        }

        // loading 状态超过300秒的（不受hidden_statuses影响，因为这是异常状态）
        $where_parts[] = "(status = 'loading' AND setdate < %s AND message != '')";
        $params[] = $timeout_time;

        if (empty($where_parts)) {
            return 0;
        }

        $where_clause = implode(' OR ', $where_parts);
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE $where_clause";

        if (!empty($params)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic SQL construction with prepare()
            $sql = $wpdb->prepare($sql, $params);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Dynamic SQL with prepare() when needed
        return (int) $wpdb->get_var($sql);
    }

    /**
     * 获取所有定时任务
     */
    public function getAll($hidden_statuses = [])
    {
        global $wpdb;
        $sql = "SELECT * FROM {$this->table_name}";
        if (!empty($hidden_statuses)) {
            $placeholders = implode(',', array_fill(0, count($hidden_statuses), '%s'));
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic placeholders construction
            $newsql = $sql . " WHERE status NOT IN ($placeholders)";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable contains dynamically built SQL
            $sql = $wpdb->prepare($newsql, ...$hidden_statuses);
        }

        // 添加排序和限制
        $sql .= " LIMIT 500";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Dynamic SQL with prepare() when needed
        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }

    public function getrow($cronId, $status = '')
    {
        global $wpdb;
        $cache_key = 'xhaitoolbox_cron_' . md5($cronId);
        $result = wp_cache_get($cache_key);

        if (false === $result) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE cron_id = %s",
                $cronId
            );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared -- Custom table requires direct query
            $result = $wpdb->get_row($query, ARRAY_A);
            wp_cache_set($cache_key, $result, '', 300); // 缓存5分钟
        }
        if ($result && $status) {
            $result = $result['status'] == $status ? $result : false;
        }
        return $result;
    }

    /**
     * 获取一个需要执行的任务
     */
    public function getOne()
    {
        global $wpdb;
        $current_time = strtotime(current_time('mysql'));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE (status = 'hold' AND ( (`type` NOT IN ('getimage','thread') AND setdate < %s) OR (`type` = 'getimage' AND setdate < %s) OR (`type` = 'thread' AND setdate < %s) )) 
            OR (status = 'loading' AND setdate < %s) 
            ORDER BY priority ASC, id ASC
            LIMIT 1",
            // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Must use local time to match current_time('mysql') stored in database
            date('Y-m-d H:i:s', $current_time - 10),
            // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            date('Y-m-d H:i:s', $current_time - 120),
            // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            date('Y-m-d H:i:s', $current_time - 60),
            // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            date('Y-m-d H:i:s', $current_time - 300)
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Real-time queue data, caching not appropriate
        $item = $wpdb->get_row($query, ARRAY_A);
        if (!$item) {
            return false;
        }
        $updateArgs = [];
        if ($item['status'] === 'loading') {
            if (isset($item['errornum']) && (int) $item['errornum'] >= 5) {
                $this->update($item['cron_id'], [
                    'status' => 'error'
                ]);
                return $this->getOne();
            } else {
                $updateArgs['errornum'] = (int) $item['errornum'] + 1;
                $updateArgs['message'] = '';
            }
        }

        // 更新任务状态
        $updateArgs['status'] = 'loading';
        $updateArgs['fornumber'] = (int) $item['fornumber'] + 1;
        // 更新数据库
        if ($this->update($item['cron_id'], $updateArgs)) {
            $item = wp_parse_args($updateArgs, $item);
        }
        return $item;
    }

    public function process_cron_queue()
    {
        if (get_transient('xhaitoolbox_cron_queue_process')) {
            return;
        }

        // 以管理员的身份运行定时任务，避免文章没有更新权限的问题        
        if (!is_user_logged_in()) {
            $admins = get_users(['role' => 'administrator', 'fields' => 'ID']);
            if (!empty($admins)) {
                $admin_id = $admins[0];
                wp_set_current_user($admin_id);
            }
        }
        $cacheDel = false;
        $fornumber = 0;
        $start_time = time();
        $maxtime = apply_filters('xhaitoolbox_max_crontime', 580);
        while ((time() - $start_time) < $maxtime) {
            $cronArr = $this->getOne();
            if (!$cronArr) {
                if ($fornumber == 0 || $fornumber > 2) {
                    break;
                }
                if (!$cacheDel) {
                    set_transient('xhaitoolbox_cron_queue_process', 1, 300);
                    $cacheDel = true;
                }
                $fornumber++;
                sleep(12);
                continue;
            }
            if (!$cacheDel) {
                set_transient('xhaitoolbox_cron_queue_process', 1, 300);
                $cacheDel = true;
            }
            $fornumber = 1;
            $itemType = $cronArr['type'];
            if (!empty($cronArr['data'])) {
                $cronArr['data'] = json_decode($cronArr['data'], true);
            }
            $itemData = apply_filters('xhaitoolbox_cronitem_' . $itemType, $cronArr, $itemType, $cronArr['post_id']);
            if ($itemData['status'] == 'success') {
                $this->delete($itemData['cron_id']);
            } else {
                $this->update($itemData['cron_id'], $itemData);
            }
            usleep(200000);
        }
        if ($cacheDel) {
            delete_transient('xhaitoolbox_cron_queue_process');
        }
    }

    /**
     * 清理和处理长期未执行的任务
     */
    public function process_cleanup_tasks()
    {
        global $wpdb;
        $current_time = strtotime(current_time('mysql'));
        // 删除skip状态的任务
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table cleanup
        $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE status = 'skip' AND setdate < %s",
                // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Must use local time to match current_time('mysql') stored in database
                date('Y-m-d H:i:s', $current_time - 86400)
            )
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table cleanup
        $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE status = 'error' AND setdate < %s",
                // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Must use local time to match current_time('mysql') stored in database
                date('Y-m-d H:i:s', $current_time - 86400 * 5)
            )
        );
        // 处理长期未执行的freeze状态任务        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table cleanup
        $frozen_tasks = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE status = 'freeze' 
                AND setdate < %s",
                // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Must use local time to match current_time('mysql') stored in database
                date('Y-m-d H:i:s', $current_time - 86400 * 2)
            ),
            ARRAY_A
        );
        $skipArgs = []; // 要跳过的文章ID
        if (!empty($frozen_tasks)) {
            foreach ($frozen_tasks as $task) {
                $post_id = $task['post_id'];
                if (in_array($post_id, $skipArgs)) {
                    continue;
                }
                $cron_id = $task['cron_id'];
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table status check
                $status_counts = $wpdb->get_results(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe class property
                    $wpdb->prepare(
                        "SELECT status, COUNT(*) as count FROM {$this->table_name} 
                        WHERE post_id = %d 
                        AND cron_id != %s 
                        AND status IN ('hold', 'loading', 'retry', 'error')
                        GROUP BY status",
                        $post_id,
                        $cron_id
                    ),
                    ARRAY_A
                );
                if ($status_counts && is_array($status_counts)) {
                    $transformedData = [];
                    foreach ($status_counts as $item) {
                        $transformedData[$item['status']] = $item['count'];
                    }
                    if (count($transformedData) == 1 && isset($transformedData['error'])) {
                        $freeze_update = $this->freezeUpdate($post_id, [
                            'status' => 'error',
                            'message' => __('Parent task error, association failed!', 'xhtheme-ai-toolbox')
                        ]);
                        if ($freeze_update) {
                            $skipArgs[] = $post_id;
                        }
                    }
                } else {
                    $freeze_update = $this->freezeUpdate($post_id, [
                        'status' => 'hold',
                        'message' => __('Task timeout, attempting execution!', 'xhtheme-ai-toolbox')
                    ]);
                    if ($freeze_update) {
                        $skipArgs[] = $post_id;
                    }
                }
            }
        }
    }


    /**
     * 更新定时任务
     */
    public function update($cronId, $data)
    {
        global $wpdb;
        $data['setdate'] = current_time('mysql');
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = wp_json_encode($data['data']);
        }
        $existing = $this->getrow($cronId);
        if (!$existing) {
            return false;
        }

        $update_data = [];
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $existing) || $existing[$key] != $value) {
                $update_data[$key] = $value;
            }
        }

        if (empty($update_data)) {
            return true;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table requires direct query
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['cron_id' => $cronId]
        );
        wp_cache_delete('xhaitoolbox_cron_' . md5($cronId));
        return (bool) $result;
    }

    /**
     * 添加新定时任务
     */
    public function insert($postId, $type, $data = [], $priority = 0, $status = 'hold')
    {
        global $wpdb;
        $sqlData = array(
            'type' => $type,
            'status' => $status, // freeze 冻结 hold 等待 loading 执行中 success 成功 error 失败 retry 重试
            'post_id' => $postId,
            'setdate' => current_time('mysql'),
            'data' => wp_json_encode($data, JSON_UNESCAPED_UNICODE),
        );
        if (empty($priority)) {
            switch ($type) {
                case 'tags':
                    $sqlData['priority'] = 6;
                    break;
                case 'summary':
                    $sqlData['priority'] = 8;
                    break;
                case 'primary':
                    $sqlData['priority'] = 3;
                    break;
            }
        } else {
            $sqlData['priority'] = (int) $priority;
        }

        $sqlData['cron_id'] = $type . '_' . $postId;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table requires direct query
        $result = $wpdb->insert(
            $this->table_name,
            $sqlData
        );

        wp_cache_delete('xhaitoolbox_cron_' . md5($type . '_' . $postId));
        return (bool) $result;
    }

    /**
     * 删除定时任务
     */
    public function delete($cronId)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table requires direct query
        $deleteStart = $wpdb->delete(
            $this->table_name,
            [
                'cron_id' => $cronId
            ]
        );
        wp_cache_delete('xhaitoolbox_cron_' . md5($cronId));
        return (bool) $deleteStart;
    }

    /**
     * 冻结任务更新
     */
    public function freezeUpdate($postId, $data)
    {
        global $wpdb;
        if (isset($data['data'])) {
            unset($data['data']);
        }
        if (!$data || !is_array($data)) {
            return false;
        }
        $data['setdate'] = current_time('mysql');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table requires direct query
        $result = $wpdb->update(
            $this->table_name,
            $data,
            [
                'post_id' => $postId,
                'status' => 'freeze'
            ]
        );
        return (bool) $result;
    }
}
