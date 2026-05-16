<?php
namespace B2\Modules\Settings;

class Report{

    public static $default_settings = array(
       'report_type'=>'政治有害
不友善
垃圾广告
违法违规
色情低俗
涉嫌侵权
网络暴力
涉未成年
自杀自残
不实信息
引人不适
抄袭
扰乱社区秩序',
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'document_settings'));
    }

    public static function get_default_settings($key){
        
        $arr = array(
            'report_type'=>'政治有害
不友善
垃圾广告
违法违规
色情低俗
涉嫌侵权
网络暴力
涉未成年
自杀自残
不实信息
引人不适
抄袭
扰乱社区秩序',
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function document_settings(){

        //常规设置
        $document = new_cmb2_box( array(
            'id'           => 'b2_report_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_report_main',
            'tab_group'    => 'b2_report_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('举报设置','b2'),
            'menu_title'   => __('举报设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $document->add_field(array(
            'name'    => __( '举报类型', 'b2' ),
            'id'=>'report_type',
            'type'=>'textarea',
            'description'=>__('请输入举报类型，每个类型占一行','b2'),
            'default'=>self::get_default_settings('report_type')
        ));

        $this->document_requests();
    }

    public function document_requests(){
        $request = new_cmb2_box(array(
            'id'           => 'b2_report_options_page',
            'title'   => __('举报列表','b2'), 
            'tab_title'    => __('举报列表','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_report_list',
            'parent_slug'     => '/admin.php?page=b2_report_main',
            'tab_group'    => 'b2_report_options',
            'display_cb'=>array($this,'list_option_page_cb')
        ));
    }

    public function cb_options_page_tabs( $cmb_options ) {
        $tab_group = $cmb_options->cmb->prop( 'tab_group' );
        $tabs      = array();
        foreach ( \CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
            if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
                $tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
                    ? $cmb->prop( 'tab_title' )
                    : $cmb->prop( 'title' );
            }
        }
        return $tabs;
    }

    public function list_option_page_cb($cmb_options){
        $tabs = $this->cb_options_page_tabs( $cmb_options );
        $order_code = new ReportTable();
        $order_code->prepare_items();
        $status = isset($_REQUEST["status"]) ? esc_sql($_REQUEST["status"]) : 'all';
        $ref_url = admin_url('admin.php?'.$_SERVER['QUERY_STRING']);

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
            
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';

            if($order_ids){
                $order_code->delete_coupons($order_ids);
                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
                echo '<script> location.replace("'.$ref_url.'"); </script>';
            }
        }

    ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_REQUEST['page'] ) && $option_key === $_REQUEST['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit'){ ?>
                    <?php 
                        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

                        $update = isset($_REQUEST['report_update']) ? (int)$_REQUEST['report_update'] : 0;
                        

                        global $wpdb;
                        $table_name = $wpdb->prefix . 'b2_report';

                        if($update){
                            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
                            $content = isset($_REQUEST['report_content']) ? $_REQUEST['report_content'] : '';
                            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
                            $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
                            $status = isset($_REQUEST['status']) ? (int)$_REQUEST['status'] : 0;

                            $res = $wpdb->update(
                                $table_name, 
                                array(
                                    'status'=>$status,
                                    'report_content'=>$content,
                                )
                                , array('id'=>$id)
                            );

                            \B2\Modules\Common\Message::update_data([
                                'date'=>current_time('mysql'),
                                'from'=>0,
                                'to'=>$user_id,
                                'post_id'=>0,
                                'msg'=>sprintf(__('您的举报已处理：%s','b2'),$content),
                                'type'=>'report_result',
                                'type_text'=>__('举报处理结果','b2'),
                                'old_row'=>1
                            ]);

                            if($email){
                                self::send_email($email,$content);
                            }

                            b2_settings_error('updated',__('更新成功','b2'));
                        }


                        $res = $wpdb->get_results($wpdb->prepare("
                                SELECT * FROM $table_name
                                WHERE id = %d
                            ",
                            $id
                        ),ARRAY_A);

                        if(empty($res)) {
                            echo __('没有找到此举报','b2').'</div>
                            </div>';
                            return;
                        }

                        $img = $res[0]['img'];
                        $img_url = wp_get_attachment_url($img);

                        $user_id = $res[0]['user_id'];
                        $user_data = get_userdata($user_id);
                        $user_name = $user_data ? $user_data->display_name : __('已删除','b2');

                        $type = $res[0]['post_type'];
                        if($type != 'comment'){
                            $link = '<a href="'.admin_url('/post.php?post='.$res[0]['post_id'].'&action=edit').'" target="_blank">'.get_the_title($res[0]['post_id']).'</a>';
                        }else{
                            $link = '<a href="'.admin_url('/comment.php?action=editcomment&c='.$res[0]['post_id']).'" target="_blank">'.__('查看评论','b2').'</a>';
                        }
                       
                    ?>
                    <div id="profile-page">
                        <form id="order-edit" method="post">
                            <a href="<?php echo remove_query_arg(array('id','action','report_update','submit-update-report'),$ref_url); ?>">返回到举报列表</a>
                            <div class="request-table" role="presentation" style="margin-top:20px;">
                                <div style="margin-bottom:10px;" class="jubao-title"><?php echo __('举报内容：','b2'); ?></div>
                                <div>
                                    <?php echo $res[0]['content']; ?>
                                </div>
                            </div>
                            <?php if($img_url){ ?>
                            <div class="request-table" role="presentation">
                                <div class="jubao-title"><?php echo __('举报图片：','b2'); ?></div>
                                <div>
                                    <a href="<?php echo $img_url; ?>" target="_blank"><img src="<?php echo $img_url; ?>" style="width:100px;height:100px;"/></a>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="request-table" role="presentation" style="margin-top:20px;">
                                <div style="margin-bottom:10px;">
                                    <span class="jubao-title"><?php echo __('举报连接：','b2'); ?></span> <?php echo $link; ?>
                                </div>
                                <div style="margin-bottom:10px;">
                                    <span class="jubao-title"><?php echo __('举报人：','b2'); ?></span> <a href="<?php echo get_author_posts_url($user_id); ?>" target="_blank"><?php echo $user_name; ?></a>
                                </div>
                                <div>
                                    <span class="jubao-title"><?php echo __('邮箱：','b2'); ?></span> <?php echo $res[0]['email']; ?>
                                </div>
                            </div>
                            <div class="request-table" role="presentation" style="margin-top:20px;">
                                <div class="jubao-title" style="margin-bottom:10px;"><?php echo __('处理结果：','b2'); ?></div>
                                <textarea class="small-text code" name="report_content" style="width:400px;max-width:100%;height:100px;"><?php echo $res[0]['report_content']; ?></textarea>
                            </div>
                            <div class="request-table" role="presentation" style="margin-top:20px;">
                                <div class="jubao-title" style="margin-bottom:10px;"><?php echo __('处理状态：','b2'); ?></div>
                                <select name="status">
                                    <option value="0" <?php echo $res[0]['status'] === '0' ? 'selected' : ''; ?>><?php echo __('未处理','b2'); ?></option>
                                    <option value="1" <?php echo $res[0]['status'] === '1' ? 'selected' : ''; ?>><?php echo __('已处理','b2'); ?></option>
                                </select>
                            </div>
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="report_update" value="1">
                            <!-- <input type="hidden" name="status" value="<?php echo $res[0]['status']; ?>"> -->
                            <input type="hidden" name="email" value="<?php echo $res[0]['email']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <p class="submit"><input type="submit" name="submit-update-report" id="submit-cmb" class="button button-primary" value="提交"></p>
                        </form>
                    </div>
                <?php }else{ ?>
                    <div class="filter-row1">
                        <a href="<?php echo remove_query_arg(array('status','s'),$ref_url); ?>" class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('all'); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status',1,$ref_url); ?>" class="<?php echo $status === '1' ? 'current' : ''; ?>"><?php echo __('已处理','b2'); ?><span class="count">（<?php echo $order_code->get_status_count(1); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status',0,$ref_url); ?>" class="<?php echo $status === '0' ? 'current' : ''; ?>"><?php echo __('未处理','b2'); ?><span class="count">（<?php echo $order_code->get_status_count(0); ?>）</span></a>
                    </div>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : ''; ?>">
                        <!-- <?php
                            $order_code->search_box( __('搜索条目','b2'), 'search_id' );
                        ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> -->

                        <?php $order_code->display() ?>
                    </form>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function send_email($email,$content){

        $site_name = B2_BLOG_NAME;
        $subject = '['.$site_name.']'.__('：您的举报已处理','b2');


 
        $message = '<div style="width:700px;background-color:#fff;margin:0 auto;border: 1px solid #ccc;">
            <div style="height:64px;margin:0;padding:0;width:100%;">
                <a href="'.B2_HOME_URI.'" style="display:block;padding: 12px 30px;text-decoration: none;font-size: 24px;letter-spacing: 3px;border-bottom: 1px solid #ccc;" rel="noopener" target="_blank">
                    '.$site_name.'
                </a>
            </div>
            <div style="padding: 30px;margin:0;">
                <p style="font-size:14px;color:#333;">
                    '.__('你的举报已处理：','b2').'
                </p>
                <div style="font-size:16px;color: green;"><pre>'.$content.'</pre></div>
                <p style="font-size:12px;color:#999;border-top:1px dotted #E3E3E3;margin-top:30px;padding-top:30px;">
                    '.__('感谢您的反馈！','b2').'
                </p>
            </div>
        </div>';

        $send = wp_mail( $email, $subject, $message );

        if(!$send){
            return false;
        }

        return true;
    }
}