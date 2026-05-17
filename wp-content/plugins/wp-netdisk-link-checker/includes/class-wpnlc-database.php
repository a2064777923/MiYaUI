<?php
/**
 * 数据库操作类
 *
 * @package WP_Netdisk_Link_Checker
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 数据库操作类
 */
class WPNLC_Database {

    /**
     * 构造函数
     */
    public function __construct() {
        // 这个类主要提供数据库操作方法，不需要初始化钩子
    }

    /**
     * 创建新的专用数据表
     */
    public function create_new_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // 创建网盘链接检测表
        $table_name = $wpdb->prefix . 'wpnlc_links';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            post_title text,
            link_url text NOT NULL,
            link_type varchar(50) NOT NULL DEFAULT 'unknown',
            link_source varchar(50) NOT NULL DEFAULT 'content',
            link_status varchar(20) NOT NULL DEFAULT 'pending',
            status_message text,
            response_code int(11) DEFAULT NULL,
            response_time float DEFAULT NULL,
            last_check datetime NOT NULL,
            check_count int(11) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY link_status (link_status),
            KEY link_type (link_type),
            KEY last_check (last_check),
            KEY post_url_index (post_id, link_url(255))
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // 更新数据库版本
        update_option('wpnlc_db_version', '2.0');

        return true;
    }

    /**
     * 删除旧的meta数据
     */
    public function cleanup_old_meta_data() {
        global $wpdb;

        $meta_keys = array(
            '_wpnlc_link_status',
            '_wpnlc_links_data',
            '_wpnlc_last_check',
            '_wpnlc_link_url',
            '_wpnlc_links_check_results'
        );

        $placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));

        $prepare_values = array_merge(array("DELETE FROM $wpdb->postmeta WHERE meta_key IN ($placeholders)"), $meta_keys);
        $deleted = $wpdb->query(call_user_func_array(array($wpdb, 'prepare'), $prepare_values));

        return $deleted;
    }

    /**
     * 获取新表名
     */
    public function get_links_table() {
        global $wpdb;
        return $wpdb->prefix . 'wpnlc_links';
    }

    /**
     * 检查新表是否存在
     */
    public function table_exists() {
        global $wpdb;
        $table_name = $this->get_links_table();

        $result = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));

        return $result === $table_name;
    }

    /**
     * 清理过期的检测数据
     *
     * @param int $days 保留天数
     * @return int 清理的记录数
     */
    public function cleanup_old_data($days = 30) {
        global $wpdb;

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();
            $cutoff_date = date('Y-m-d H:i:s', current_time('timestamp') - ($days * 24 * 3600));

            // 清理过期的检测记录
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE last_check < %s",
                $cutoff_date
            ));
        } else {
            $cutoff_time = current_time('timestamp') - ($days * 24 * 3600);

            // 清理过期的检测记录
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM $wpdb->postmeta
                 WHERE meta_key = '_wpnlc_last_check'
                 AND meta_value < %d",
                $cutoff_time
            ));

            // 清理对应的状态和链接数据
            $wpdb->query(
                "DELETE pm1, pm2 FROM $wpdb->postmeta pm1
                 LEFT JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
                 WHERE pm1.meta_key = '_wpnlc_link_status'
                 AND pm2.meta_key IN ('_wpnlc_links_data', '_wpnlc_link_url')
                 AND pm1.post_id NOT IN (
                     SELECT post_id FROM $wpdb->postmeta
                     WHERE meta_key = '_wpnlc_last_check'
                 )"
            );
        }

        return $deleted;
    }

    /**
     * 重置所有检测数据
     *
     * @return bool 是否成功
     */
    public function reset_all_data() {
        global $wpdb;

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();
            $deleted = $wpdb->query("DELETE FROM $table_name");
        } else {
            $meta_keys = array(
                '_wpnlc_link_status',
                '_wpnlc_links_data',
                '_wpnlc_last_check',
                '_wpnlc_link_url'
            );

            $placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));

            $prepare_values = array_merge(array("DELETE FROM $wpdb->postmeta WHERE meta_key IN ($placeholders)"), $meta_keys);
            $deleted = $wpdb->query(call_user_func_array(array($wpdb, 'prepare'), $prepare_values));
        }

        return $deleted !== false;
    }

    /**
     * 获取检测统计数据
     *
     * @return array 统计数据
     */
    public function get_statistics() {
        global $wpdb;
        
        // 获取各种状态的文章数量
        $status_counts = $wpdb->get_results(
            "SELECT meta_value as status, COUNT(*) as count 
             FROM $wpdb->postmeta 
             WHERE meta_key = '_wpnlc_link_status' 
             GROUP BY meta_value",
            ARRAY_A
        );
        
        $stats = array(
            'valid' => 0,
            'invalid' => 0,
            'mixed' => 0,
            'no_links' => 0,
            'total_checked' => 0
        );
        
        foreach ($status_counts as $row) {
            $stats[$row['status']] = intval($row['count']);
            $stats['total_checked'] += intval($row['count']);
        }
        
        // 获取总文章数
        $settings = wpnlc_get_settings();
        $check_post_type = $settings['check_posts'];
        
        if ($check_post_type === 'all') {
            $post_types = get_post_types(array('public' => true));
            $post_type_condition = "AND post_type IN ('" . implode("','", $post_types) . "')";
        } else {
            $post_type_condition = "AND post_type = '$check_post_type'";
        }
        
        $stats['total_posts'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->posts 
             WHERE post_status = 'publish' $post_type_condition"
        );
        
        $stats['unchecked'] = $stats['total_posts'] - $stats['total_checked'];
        
        return $stats;
    }

    /**
     * 获取失效链接的文章
     *
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return array 文章数组
     */
    public function get_invalid_posts($limit = 10, $offset = 0) {
        global $wpdb;

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();
            $posts = $wpdb->get_results($wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_date, MAX(l.last_check) as last_check
                 FROM $wpdb->posts p
                 JOIN $table_name l ON p.ID = l.post_id
                 WHERE p.post_status = 'publish'
                 AND l.link_status = 'invalid'
                 GROUP BY p.ID, p.post_title, p.post_date
                 ORDER BY last_check DESC
                 LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        } else {
            $posts = $wpdb->get_results($wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_date, pm.meta_value as last_check
                 FROM $wpdb->posts p
                 JOIN $wpdb->postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_wpnlc_link_status' AND pm1.meta_value = 'invalid'
                 LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                 WHERE p.post_status = 'publish'
                 ORDER BY pm.meta_value DESC
                 LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        }

        return $posts;
    }

    /**
     * 获取需要检测的文章ID
     *
     * @param int $limit 限制数量
     * @param int $cache_hours 缓存小时数
     * @return array 文章ID数组
     */
    public function get_posts_need_check($limit = 10, $cache_hours = 6) {
        global $wpdb;
        $settings = wpnlc_get_settings();
        $check_post_type = $settings['check_posts'];

        // 构建文章类型条件
        if ($check_post_type === 'all') {
            $post_types = get_post_types(array('public' => true));
            $post_type_condition = "AND p.post_type IN ('" . implode("','", $post_types) . "')";
        } else {
            $post_type_condition = "AND p.post_type = '$check_post_type'";
        }

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();
            $cache_date = date('Y-m-d H:i:s', current_time('timestamp') - ($cache_hours * 3600));

            $post_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT p.ID
                 FROM $wpdb->posts p
                 LEFT JOIN $table_name l ON p.ID = l.post_id
                 WHERE p.post_status = 'publish'
                 $post_type_condition
                 AND (l.last_check IS NULL OR l.last_check < %s)
                 GROUP BY p.ID
                 ORDER BY p.post_date DESC
                 LIMIT %d",
                $cache_date, $limit
            ));
        } else {
            $cache_timestamp = current_time('timestamp') - ($cache_hours * 3600);

            $post_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT p.ID
                 FROM $wpdb->posts p
                 LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wpnlc_last_check'
                 WHERE p.post_status = 'publish'
                 $post_type_condition
                 AND (pm.meta_value IS NULL OR pm.meta_value < %d)
                 ORDER BY p.post_date DESC
                 LIMIT %d",
                $cache_timestamp, $limit
            ));
        }

        return $post_ids;
    }

    /**
     * 批量更新文章的检测状态
     *
     * @param array $updates 更新数据数组
     * @return bool 是否成功
     */
    public function batch_update_status($updates) {
        global $wpdb;

        if (!$this->table_exists()) {
            // 回退到旧的meta方式
            $wpdb->query('START TRANSACTION');

            try {
                foreach ($updates as $post_id => $data) {
                    // 更新状态
                    update_post_meta($post_id, '_wpnlc_link_status', $data['status']);

                    // 更新链接数据
                    if (isset($data['links'])) {
                        update_post_meta($post_id, '_wpnlc_links_data', $data['links']);
                    }

                    // 更新检测时间
                    update_post_meta($post_id, '_wpnlc_last_check', time());

                    // 更新链接URL（用于显示）
                    if (isset($data['url'])) {
                        update_post_meta($post_id, '_wpnlc_link_url', $data['url']);
                    }
                }

                $wpdb->query('COMMIT');
                return true;

            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                return false;
            }
        }

        // 使用新表的批量更新
        $table_name = $this->get_links_table();
        $wpdb->query('START TRANSACTION');

        try {
            foreach ($updates as $post_id => $data) {
                $post = get_post($post_id);
                $post_title = $post ? $post->post_title : '';
                $current_time = current_time('mysql');

                // 删除该文章的旧记录
                $wpdb->delete(
                    $table_name,
                    array('post_id' => $post_id),
                    array('%d')
                );

                // 插入新的链接记录
                if (isset($data['links']) && is_array($data['links'])) {
                    foreach ($data['links'] as $link) {
                        $wpdb->insert(
                            $table_name,
                            array(
                                'post_id' => $post_id,
                                'post_title' => $post_title,
                                'link_url' => $link['url'],
                                'link_type' => $link['type'],
                                'link_source' => isset($link['source']) ? $link['source'] : 'content',
                                'link_status' => $link['status'],
                                'status_message' => $link['message'],
                                'response_code' => isset($link['response_code']) ? $link['response_code'] : null,
                                'response_time' => isset($link['response_time']) ? $link['response_time'] : null,
                                'last_check' => $current_time,
                                'check_count' => 1
                            ),
                            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%d')
                        );
                    }
                } else {
                    // 如果没有链接，插入no_links记录
                    $wpdb->insert(
                        $table_name,
                        array(
                            'post_id' => $post_id,
                            'post_title' => $post_title,
                            'link_url' => '',
                            'link_type' => 'none',
                            'link_source' => 'content',
                            'link_status' => 'no_links',
                            'status_message' => '未找到网盘链接',
                            'last_check' => $current_time,
                            'check_count' => 1
                        ),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
                    );
                }
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    /**
     * 获取检测历史记录
     *
     * @param int $post_id 文章ID
     * @param int $limit 限制数量
     * @return array 历史记录
     */
    public function get_check_history($post_id, $limit = 10) {
        global $wpdb;
        $history = array();

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();
            $links = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name
                 WHERE post_id = %d
                 ORDER BY last_check DESC
                 LIMIT %d",
                $post_id, $limit
            ), ARRAY_A);

            if (!empty($links)) {
                // 按检测时间分组
                $grouped_by_time = array();
                foreach ($links as $link) {
                    $time_key = $link['last_check'];
                    if (!isset($grouped_by_time[$time_key])) {
                        $grouped_by_time[$time_key] = array();
                    }
                    $grouped_by_time[$time_key][] = $link;
                }

                foreach ($grouped_by_time as $check_time => $time_links) {
                    $history[] = array(
                        'check_time' => $check_time,
                        'links' => $time_links
                    );
                }
            }
        } else {
            // 使用旧的meta方式
            $last_check = get_post_meta($post_id, '_wpnlc_last_check', true);
            $status = get_post_meta($post_id, '_wpnlc_link_status', true);
            $links_data = get_post_meta($post_id, '_wpnlc_links_data', true);

            if ($last_check) {
                $history[] = array(
                    'check_time' => $last_check,
                    'status' => $status,
                    'links' => $links_data
                );
            }
        }

        return $history;
    }

    /**
     * 导出检测数据
     *
     * @param string $format 导出格式 ('csv', 'json')
     * @return string 导出的数据
     */
    public function export_data($format = 'csv') {
        global $wpdb;

        // 检查是否使用新表
        if ($this->table_exists()) {
            $table_name = $this->get_links_table();

            // 从新表获取数据
            $data = $wpdb->get_results(
                "SELECT p.ID, p.post_title, p.post_date, p.post_author,
                        u.display_name as author_name,
                        l.link_url, l.link_type, l.link_status, l.status_message,
                        l.last_check, l.response_code, l.response_time
                 FROM $wpdb->posts p
                 JOIN $table_name l ON p.ID = l.post_id
                 LEFT JOIN $wpdb->users u ON p.post_author = u.ID
                 WHERE p.post_status = 'publish'
                 ORDER BY p.post_date DESC, l.id ASC",
                ARRAY_A
            );
        } else {
            // 使用旧的meta表查询
            $data = $wpdb->get_results(
                "SELECT p.ID, p.post_title, p.post_date, p.post_author,
                        pm1.meta_value as status,
                        pm2.meta_value as last_check,
                        pm3.meta_value as links_data,
                        u.display_name as author_name
                 FROM $wpdb->posts p
                 LEFT JOIN $wpdb->postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_wpnlc_link_status'
                 LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wpnlc_last_check'
                 LEFT JOIN $wpdb->postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_wpnlc_links_data'
                 LEFT JOIN $wpdb->users u ON p.post_author = u.ID
                 WHERE p.post_status = 'publish' AND pm1.meta_value IS NOT NULL
                 ORDER BY p.post_date DESC",
                ARRAY_A
            );
        }
        
        if ($format === 'json') {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            // CSV格式 - 添加BOM头解决中文乱码问题
            $csv = "\xEF\xBB\xBF"; // UTF-8 BOM

            if ($this->table_exists()) {
                // 新表格式的CSV头
                $csv .= "文章ID,文章标题,作者,发布日期,链接URL,网盘类型,链接状态,状态消息,检测时间,响应码,响应时间(秒)\n";

                foreach ($data as $row) {
                    $check_time = $row['last_check'] ? $row['last_check'] : '未检测';
                    $status_text = wpnlc_get_status_text($row['link_status']);
                    $type_text = wpnlc_get_netdisk_type_text($row['link_type']);

                    // 确保所有字段都是UTF-8编码
                    $title = mb_convert_encoding($row['post_title'], 'UTF-8', 'auto');
                    $author = mb_convert_encoding($row['author_name'], 'UTF-8', 'auto');
                    $link_url = $row['link_url'] ?: '';
                    $status_message = $row['status_message'] ?: '';

                    $csv .= sprintf(
                        "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%s,%.3f\n",
                        $row['ID'],
                        str_replace('"', '""', $title),
                        str_replace('"', '""', $author),
                        $row['post_date'],
                        str_replace('"', '""', $link_url),
                        str_replace('"', '""', $type_text),
                        $status_text,
                        str_replace('"', '""', $status_message),
                        $check_time,
                        $row['response_code'] ?: '',
                        $row['response_time'] ?: 0
                    );
                }
            } else {
                // 旧表格式的CSV头
                $csv .= "文章ID,文章标题,作者,发布日期,链接状态,检测时间,网盘类型,链接数量,链接详情\n";

                foreach ($data as $row) {
                    $check_time = $row['last_check'] ? date('Y-m-d H:i:s', $row['last_check']) : '未检测';
                    $status_text = wpnlc_get_status_text($row['status']);

                    // 确保所有字段都是UTF-8编码
                    $title = mb_convert_encoding($row['post_title'], 'UTF-8', 'auto');
                    $author = mb_convert_encoding($row['author_name'], 'UTF-8', 'auto');

                    // 解析链接数据
                    $links_data = maybe_unserialize($row['links_data']);
                    $link_count = is_array($links_data) ? count($links_data) : 0;
                    $netdisk_types = array();
                    $link_details = array();

                    if (is_array($links_data)) {
                        foreach ($links_data as $link) {
                            if (!in_array($link['type'], $netdisk_types)) {
                                $netdisk_types[] = wpnlc_get_netdisk_type_text($link['type']);
                            }
                            $link_details[] = $link['url'];
                        }
                    }

                    $netdisk_type_text = implode(', ', $netdisk_types);
                    $link_details_text = implode('; ', $link_details);

                    $csv .= sprintf(
                        "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,\"%s\"\n",
                        $row['ID'],
                        str_replace('"', '""', $title),
                        str_replace('"', '""', $author),
                        $row['post_date'],
                        $status_text,
                        $check_time,
                        str_replace('"', '""', $netdisk_type_text),
                        $link_count,
                        str_replace('"', '""', $link_details_text)
                    );
                }
            }

            return $csv;
        }
    }

    /**
     * 优化数据库表
     *
     * @return bool 是否成功
     */
    public function optimize_database() {
        global $wpdb;
        
        try {
            // 优化postmeta表
            $wpdb->query("OPTIMIZE TABLE $wpdb->postmeta");
            
            // 清理孤立的meta数据
            $wpdb->query(
                "DELETE pm FROM $wpdb->postmeta pm
                 LEFT JOIN $wpdb->posts p ON pm.post_id = p.ID
                 WHERE p.ID IS NULL
                 AND pm.meta_key LIKE '_wpnlc_%'"
            );
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取数据库使用情况
     *
     * @return array 使用情况统计
     */
    public function get_database_usage() {
        global $wpdb;

        if ($this->table_exists()) {
            $table_name = $this->get_links_table();

            // 获取新表的记录数
            $records_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

            // 获取表大小信息
            $table_status = $wpdb->get_row($wpdb->prepare(
                "SELECT
                    data_length + index_length as table_size,
                    data_length,
                    index_length,
                    table_rows
                 FROM information_schema.tables
                 WHERE table_schema = %s AND table_name = %s",
                DB_NAME, $table_name
            ));

            $table_size = $table_status ? $table_status->table_size : 0;
            $data_size = $table_status ? $table_status->data_length : 0;
            $index_size = $table_status ? $table_status->index_length : 0;

            return array(
                'records_count' => intval($records_count ?: 0),
                'table_size_bytes' => intval($table_size ?: 0),
                'table_size_mb' => round(($table_size ?: 0) / 1024 / 1024, 2),
                'data_size_bytes' => intval($data_size ?: 0),
                'data_size_mb' => round(($data_size ?: 0) / 1024 / 1024, 2),
                'index_size_bytes' => intval($index_size ?: 0),
                'index_size_mb' => round(($index_size ?: 0) / 1024 / 1024, 2),
                'using_new_table' => true
            );
        } else {
            // 获取插件相关的meta记录数
            $meta_count = $wpdb->get_var(
                "SELECT COUNT(*) FROM $wpdb->postmeta
                 WHERE meta_key LIKE '_wpnlc_%'"
            );

            // 估算数据大小
            $data_size = $wpdb->get_var(
                "SELECT SUM(LENGTH(meta_key) + LENGTH(meta_value))
                 FROM $wpdb->postmeta
                 WHERE meta_key LIKE '_wpnlc_%'"
            );

            return array(
                'meta_records' => intval($meta_count ?: 0),
                'data_size_bytes' => intval($data_size ?: 0),
                'data_size_mb' => round(($data_size ?: 0) / 1024 / 1024, 2),
                'using_new_table' => false
            );
        }
    }
}
