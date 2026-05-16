<?php
/*
站点数据统计
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

// 获取注册用户数量
function get_date_users($date) {
    global $wpdb;
    $user_count = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->prefix}users WHERE DATE(user_registered) = DATE('$date')");
    return $user_count;
}

// 获取评论数量
function get_date_comments($date) {
    global $wpdb;
    $comments_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE DATE(comment_date) = DATE('$date')");
    return $comments_count;
}

// 获取签到数量
function get_date_sign($date){
    global $wpdb;
    $sign_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}usermeta WHERE meta_key='b2_mission_today' AND DATE(meta_value) = DATE('$date')");
    return $sign_count;
}
// 昨日签到人数
function get_date_sign_old($date){
    global $wpdb;
    $sign_count = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}usermeta WHERE meta_key='b2_mission_old_today' AND DATE(meta_value) = DATE('$date')");
    return $sign_count;
}

// 获取已认证用户数量
function get_verified_users(){
    global $wpdb;
    $verified_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}b2_verify WHERE status = 2");
    return $verified_count;
}

// 获取待审核用户数量
function get_pending_users(){
    global $wpdb;
    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}b2_verify WHERE status = 4");
    return $pending_count;
}

//获取可视化友情链接数
function get_total_links(){
   global $wpdb;
   $link_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}b2_links WHERE link_visible = 'Y'");
   return $link_count ?: 0;
}

//获取待审核链接数
function get_pending_links(){
   global $wpdb;
   $pending_link_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}b2_links WHERE link_visible = 'N'");
   return $pending_link_count ?: 0;
}

//获取总订单数
function get_total_orders(){
   global $wpdb;
   $order_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}zrz_order");
   return $order_count ?: 0;
}

//获取未支付订单数
function get_pending_orders(){
   global $wpdb;
   $pending_order_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}zrz_order WHERE order_state = 'w'");
   return $pending_order_count ?: 0;
}

//积分池
function get_credit_total(){
   global $wpdb;
   $credit_total = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->prefix}usermeta WHERE meta_key = 'zrz_credit_total'");
   return $credit_total ?: 0;
}

//余额池
function get_rmb_total(){
   global $wpdb;
   $rmb_total = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->prefix}usermeta WHERE meta_key = 'zrz_rmb'");
   return $rmb_total ?: 0;
}


function get_admin_dashboard_data()
{
    $today = date('Y-m-d',strtotime("now"));
    $yesterday = date('Y-m-d',strtotime("-1 day"));

    $today_register = get_date_users($today);
    $yesterday_register = get_date_users($yesterday);

    $today_comments = get_date_comments($today);
    $yesterday_comments = get_date_comments($yesterday);

    $today_sign = get_date_sign($today);
    $yesterday_sign = get_date_sign_old($yesterday);
    
    $verified_users = get_verified_users();
    $pending_users = get_pending_users();
    
    $total_links = get_total_links();
    $pending_links = get_pending_links();
    
    $total_orders = get_total_orders();
    $pending_orders = get_pending_orders();
    
    $credit_total = get_credit_total();
    $rmb_total = get_rmb_total();

    $data = array(
        array(
            'top'    => '今日注册用户数量',
            'val'    => $today_register,
            'bottom' => '昨日注册用户数量: ' . $yesterday_register,
        ),
        array(
            'top'    => '今日新增评论',
            'val'    => $today_comments,
            'bottom' => '昨日评论数量: ' . $yesterday_comments,
        ),
        array(
            'top'    => '今日签到',
            'val'    => $today_sign,
            'bottom' => '昨日签到但今日为签到人数: ' . $yesterday_sign,
        ),
        array(
            'top'    => '已认证用户数量',
            'val'    => $verified_users,
            'bottom' => '待审核用户数量: ' . $pending_users,
        ),
        array(
            'top'    => '友情链接总数',
            'val'    => $total_links,
            'bottom' => '待审核友情链接总数: ' . $pending_links,
        ),
        array(
           'top'    => '订单总数',
           'val'    => $total_orders,
           'bottom' => '待支付订单总数: ' . $pending_orders,
        ),
        array(
            'top'    => '总积分',
            'val'    => $credit_total,
            'bottom' => '网站积分池',
        ),
        array(
            'top'    => '总余额',
            'val'    => $rmb_total,
            'bottom' => '网站余额池',
        ),
    );
    return $data;
}

function generate_data_card()
{
    $data = get_admin_dashboard_data();
    $html = '';
    foreach ($data as $v) {
        $html .= '<div class="row-3">
                <div class="box-panel">
                    <span class="count_top">' . $v['top'] . '</span>
                    <div class="count">' . $v['val'] . '</div>
                    <span class="count_bottom">' . $v['bottom'] . '</span>
                </div>
            </div>';
    }
    return $html;
}
function acg_user_reginfo($type)
{
    $cycle        = 'day';
    $time_day     = '30';
    $time_end     = current_time('Y-m-d 23:59:59');
    $time_start   = date('Y-m-d 00:00:00', strtotime("-$time_day day", strtotime($time_end)));
    $filling      = acg_this_get_time_fillings($cycle, array($time_start, $time_end));
    $cycle_format = '%Y-%m-%d';

    global $wpdb;
    $data = $filling['data'];
    $result = $filling['time'];

    switch($type){
        case 'reg': //注册用户
            $db_data = $wpdb->get_results("SELECT COUNT(*) as count, date_format(user_registered, '$cycle_format') as time FROM {$wpdb->prefix}users WHERE user_registered BETWEEN '$time_start' AND '$time_end' group by date_format(user_registered,'$cycle_format')");
            break;
        case 'q': //已支付订单
            $db_data = $wpdb->get_results("SELECT COUNT(*) as count, date_format(order_date, '$cycle_format') as time FROM {$wpdb->prefix}zrz_order WHERE order_state='q' AND order_date BETWEEN '$time_start' AND '$time_end' GROUP BY date_format(order_date,'$cycle_format')");
            break;
        case 'w': //未支付订单
            $db_data = $wpdb->get_results("SELECT COUNT(*) as count, date_format(order_date, '$cycle_format') as time FROM {$wpdb->prefix}zrz_order WHERE order_state='w' AND order_date BETWEEN '$time_start' AND '$time_end' GROUP BY date_format(order_date,'$cycle_format')");
            break;
    }

    array_walk($db_data, function ($value, $key) use ($result, &$data) {
        $value = (array) $value;
        $index = array_search($value['time'], $result);
        $data[$index] = $value['count'];
    });

    $chart_data = [
        'time'  => $result,
        'count' => $data
    ];

    return $chart_data;
}
$reg_data = acg_user_reginfo('reg');
$paid_data = acg_user_reginfo('q');
$unpaid_data = acg_user_reginfo('w');

//获取填充时间
function acg_this_get_time_fillings($cycle, $time)
{
    $cycle_format_array = array(
        'day'   => 'Y-m-d',
        'month' => 'Y-m',
        'year'  => 'Y',
    );
    $count_x = array(
        'day'   => 86400,
        'month' => 259200,
        'year'  => 'Y',
    );

    $new_time   = current_time('mysql');
    $time_start = $time[0];
    $time_end   = !empty($time[1]) ? $time[1] : '';

    if (!$time_end) {
        $time_start = $new_time;
        $time_end   = $time[0];
    }

    if (strtotime($time_end) > strtotime($new_time)) {
        $time_end = $new_time;
    }
    //结束时间不高于当前时间

    if (strtotime($time_end) < strtotime($time_start)) {
        throw new Exception('结束时间不能小于开始时间');
    }

    if ('day' == $cycle) {
        $count = ceil((strtotime($time_end) - strtotime($time_start)) / 86400);
    } elseif ('month' == $cycle) {
        $date1_stamp                     = strtotime($time_end);
        $date2_stamp                     = strtotime($time_start);
        list($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        $count                           = abs($date_1['y'] - $date_2['y']) * 12 + ($date_1['m'] - $date_2['m']) + 1;
    }

    for ($i = $count - 1; 0 <= $i; $i--) {
        $time_end_sum = date($cycle_format_array[$cycle], strtotime($time_end));
        $result[]     = date($cycle_format_array[$cycle], strtotime('-' . $i . ' ' . $cycle, strtotime($time_end_sum)));
        $data[]       = 0;
    }

    $asd = array(
        'time'       => $result,
        'data'       => $data,
        'count'      => $count,
        'cycle'      => $cycle,
        'time_start' => $time_start,
        'time_end'   => $time_end,
    );

    return array(
        'time' => $result,
        'data' => $data,
    );
}

?>

<div class="pay-container">
    <?php echo generate_data_card(); ?>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">注册用户可视化图表</div>
            <div style="margin:0 -30px -20px 0px;"><div id="user_count" style="height:300px"></div></div>
        </div>
    </div>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">成交订单统计</div>
            <div style="margin:0 -30px -20px 0px;"><div id="pay_price" style="height:300px"></div></div>
        </div>
    </div>
    <script type="text/javascript">
(function ($, document) {
    $(document).ready(function ($) {
        var option_1 = {
            legend: {
                data: ['全部数据']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($reg_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部数据',
                data: <?php echo json_encode($reg_data['count']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('user_count'), 'westeros');
        myChart.setOption(option_1);

        var option_2 = {
            legend: {
                data: ['已成交', '未成交']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($paid_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '已成交',
                data: <?php echo json_encode($paid_data['count']); ?>,
                type: 'line',
                smooth: true
            }, {
                name: '已成交',
                data: <?php echo json_encode($unpaid_data['count']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('pay_price'), 'westeros');
        myChart.setOption(option_2);
    });
})(jQuery, document);
    </script>
</div>