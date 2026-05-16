<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//工单表格
class ReportTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
                return $item->$column_name;
            case 'user_id':
                $user_data = get_userdata($item->$column_name);
                if($user_data){
                    return '<a href="'.get_author_posts_url($item->$column_name).'" target="_blank">'.$user_data->display_name.'</a>';
                }else{
                    return __('已删除','b2');
                }
            case 'email':
                return esc_attr($item->$column_name);
            case 'type':
                return esc_attr($item->$column_name);
            case 'date':
                return $item->$column_name;
            case 'status':
                return (int)$item->$column_name === 0 ? '<span class="red">'.__('未处理','b2').'</span>' : '<span class="green">'.__('已处理','b2').'</span>';
            case 'link':
                $post_id = $item->post_id;
                if($item->post_type != 'comment'){
                    return '<a href="'.admin_url('/post.php?post='.$post_id.'&action=edit').'" target="_blank">'.get_the_title($item->post_id).'</a>';
                }

                $comment_id = $item->post_id;
                if($item->post_type == 'comment'){
                    return '<a href="'.admin_url('/comment.php?action=editcomment&c='.$comment_id).'" target="_blank">'.__('查看评论','b2').'</a>';
                }
                return '';
            case 'content':
                return esc_attr($item->$column_name);
            case 'img':
                $img_id = $item->$column_name;
                if($img_id){
                    $img_url = wp_get_attachment_url($img_id);
                    return '<a href="'.$img_url.'" target="_blank"><img src="'.$img_url.'" style="width:100px;height:100px;" /></a>';
                }
                return __('未上传图片','b2');
            case 'report_content':
                return esc_attr($item->$column_name);
            default :
            return $item->$column_name;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_report';

        if($status === 'all'){
            $from = $table_name;
        }else{
            $from = " $table_name WHERE `status`=$status";
        }
        
        $query = "SELECT COUNT(*) FROM $from ";

        $rowcount = $wpdb->get_var($query);
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'b2_report';
        
        if(is_array($ids)){
            foreach ($ids as $id) {
                $wpdb->query(
                    $wpdb->prepare( 
                        "DELETE FROM $table_name WHERE `id` = %d",
                        $id
                    )
                );
            }
        }
    }

    function column_id($item){
        $paged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

        $actions = array(
            'delete'    => sprintf('<a onclick="return confirm(\'您确定删除举报吗?\')" href="?page=%s&action=%s&id=%s&paged=%s">'.__('删除','b2').'</a>','b2_report_list','delete',$item->id,$paged),
            'edit'    => sprintf('<a class="green" href="?page=%s&action=%s&id=%s&paged=%s&status=%s">'.__('处理','b2').'</a>','b2_report_list','edit',$item->id,$paged,$status)
        );

        return sprintf('%1$s %2$s',
            $item->id,
            $this->row_actions($actions)
        );
    }

    function column_cb($item){

        return sprintf(
            '<input type="checkbox" name="id[]" value="%1$s" />',
            $item->id
        );
    }

    function get_columns() {
        return $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => __('举报ID','b2'),
            'user_id' => __('来自','b2'),
            'date' => __('时间','b2'),
            'type' => __('举报类型','b2'),
            'link' => __('涉及的连接','b2'),
            'content' => __('举报的内容','b2'),
            'img' => __('举报的图片','b2'),
            'status' => __('处理状态','b2'),
            'report_content' => __('处理说明','b2'),
            'email' => __('举报者邮箱','b2'),
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'status' => array('status',false)
        );
        return $sortable_columns;
    }

    function display_tablenav( $which ) {

        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <?php if ( $this->has_items() ): ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
            <?php endif;
                $this->extra_tablenav( $which );
                $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('删除','b2')
        );
        return $actions;
    }

    function prepare_items($val ='') {

        $this->process_bulk_action();

        global $wpdb; 
        $table_name = $wpdb->prefix . 'b2_report';

        $from = $table_name;

        //状态筛选
        $status = isset($_GET["status"]) ? esc_sql($_GET["status"]) : '';
        if (!empty($status) && $status != 'all') {
            $from = " $table_name WHERE `status`=$status";
        }

        $query = "SELECT * FROM $from ORDER BY `id` DESC";

        $totalitems = $wpdb->query($query);

        $perpage = 20;

        $paged = isset($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $totalpages = ceil($totalitems / $perpage);

        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query.=' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
    }
}