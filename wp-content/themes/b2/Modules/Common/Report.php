<?php

namespace B2\Modules\Common;

class Report
{

    public function init()
    {

    }

    public static function submit_report($request)
    {
        $type = ($request['type'] ?? 0) ? apply_filters('b2_sanitize_data',$request['type']) : '';
        $post_id = ($request['post_id'] ?? 0) ? (int) $request['post_id'] : 0;
        $content = ($request['content'] ?? '') ? apply_filters('b2_sanitize_data', $request['content']) : '';
        $img = ($request['img'] ?? 0) ? (int) $request['img'] : 0;
        $post_type = ($request['post_type'] ?? '') ? apply_filters('b2_sanitize_data', $request['post_type']) : '';
        $email = ($request['email'] ?? '') ? sanitize_email($request['email']) : '';

        if (empty($type) || empty($content) || empty($post_id) || empty($post_type)) {
            return array('error' => __('请输入举报类型和内容', 'b2'));
        }

        $user_id = b2_get_current_user_id();
        if (!$user_id) {
            return array('error' => __('请先登录', 'b2'));
        }

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if (isset($public_count['error']))
            return $public_count;

        $data = array(
            'type' => $type,
            'user_id' => $user_id,
            'post_id' => $post_id,
            'content' => $content,
            'img' => $img,
            'date' => current_time('mysql'),
            'post_type' => $post_type,
            'email' => $email,
        );

        $format = array(
            'type' => '%s',
            'user_id' => '%d',
            'post_id' => '%d',
            'content' => '%s',
            'img' => '%d',
            'date' => '%s',
            'post_type' => '%s',
            'email' => '%s',
        );

        $res = self::insert_report($data, $format);

        if ($res) {
            return 'success';
        } else {
            return array('error' => __('举报失败', 'b2'));
        }
    }

    public static function insert_report($data, $format)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_report';

        $wpdb->insert($table_name, $data, $format);

        return $wpdb->insert_id;
    }

    //更新处理状态和处理内容
    public static function update_report_status($id, $status, $report_content)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_report';

        $res = $wpdb->update($table_name, array('status' => $status, 'report_content' => $report_content), array('id' => $id));

        return $res;
    }
}