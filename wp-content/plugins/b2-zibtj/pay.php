<?php
/*
商城数据统计
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}
//获取站点今日的订单统计
function acg_get_order_statistics_totime($time_type = 'today') {
    $error = array(
        'count' => 0,
        'sum' => 0,
        'ids' => '',
    );

    if(!$time_type) {
        return $error;
    }
    static $this_data = null;
    if(isset($this_data[$time_type])) {
        return $this_data[$time_type];
    }

    global $wpdb;
    switch($time_type){
        case 'today':
            $like_time = current_time('Y-m-d');
            break;
        case 'yester':
            $todaytime = current_time('Y-m-d');
            $like_time = date("Y-m-d", strtotime("$todaytime -1 day"));
            break;
        case 'thismonth':
            $like_time = current_time('Y-m');
            break;
        case 'lastmonth':
            $thismonth_time = current_time('Y-m');
            $like_time = date('Y-m', strtotime("$thismonth_time -1 month"));
            break;
        case 'thisyear':
            $like_time = current_time('Y');
            break;
        case 'all':
            $like_time = '';
            break;
        default:
            $like_time = current_time('Y-m-d');
    }

    $table_name = $wpdb->prefix . 'zrz_order'; // Please replace 'zrz_order' with your actual table name

    $data = $wpdb->get_row("SELECT COUNT(*) as count, SUM(order_price) as sum FROM $table_name WHERE order_date LIKE '%$like_time%' and order_state='q' and order_price > 0 and pay_type != 'balance'");


    if($wpdb->last_error !== '') {
        error_log("SQL query failed: ".$wpdb->last_error);
        return $error;
    }

    $data = (array) $data;

    if (!isset($data['count'])){
        $this_data[$time_type] = $error;
    } else {
        $this_data[$time_type] = array(
            'count' => $data['count'] ? intval($data['count']) : 0,
            'sum' => $data['sum'] ? floatval($data['sum']) : 0,
            'ids' => '',
        );
    }
    return $this_data[$time_type];
}

function acg_get_admin_dashboard_data()
{
    global $wpdb;
    $thismonth_time = current_time('Y-m');
    $today          = acg_get_order_statistics_totime('today');
    $yester         = acg_get_order_statistics_totime('yester');
    $thismonth      = acg_get_order_statistics_totime('thismonth');
    $lastmonth      = acg_get_order_statistics_totime('lastmonth');
    $all            = acg_get_order_statistics_totime('all');
    $thisyear       = acg_get_order_statistics_totime('thisyear');

    $_all       = (array) $wpdb->get_row("SELECT SUM(order_price) as sum FROM $wpdb->zrz_order WHERE  `order_state` = 'q' AND `pay_type` != 'balance'");
    $_thismonth = (array) $wpdb->get_row("SELECT SUM(order_price) as sum FROM $wpdb->zrz_order WHERE  `order_state` = 'q' AND `pay_type` != 'balance' AND order_date LIKE '%$thismonth_time%'");

    $order_price = array(
        'all'       => isset($_all['sum']) ? floatval($_all['sum']) : 0,
        'thismonth' => isset($_thismonth['sum']) ? floatval($_thismonth['sum']) : 0,
    );

    $_order_price_1 = $wpdb->get_var("SELECT SUM(order_price) FROM $wpdb->zrz_order WHERE  `order_state` = 'q' AND `pay_type` != 'balance' AND `order_price` = 1");

    //有效
    $order_price['effective'] = $order_price['all'] - $_order_price_1;
  
    $data = array(
        array(
            'top'    => '今日订单',
            'val'    => $today['count'],
            'bottom' => '昨日订单：' . $yester['count'],
        ),
        array(
            'top'    => '今日收款',
            'val'    => ($today['sum'] > 1000) ? (int) $today['sum'] : $today['sum'],
            'bottom' => '昨日收款：' . $yester['sum'],
        ),
        array(
            'top'    => '本月订单',
            'val'    => $thismonth['count'],
            'bottom' => '上月订单：' . $lastmonth['count'],
        ),
        array(
            'top'    => '本月收款',
            'val'    => ($thismonth['sum'] > 10000) ? (int) $thismonth['sum'] : $thismonth['sum'],
            'bottom' => '上月收款：' . $lastmonth['sum'],
        ),
        array(
            'top'    => '有效单量',
            'val'    => $all['count'],
            'bottom' => '今年订单：' . $thisyear['count'],
        ),
        array(
            'top'    => '有效收款',
            'val'    => ($all['sum'] > 10000) ? (int) $all['sum'] : $all['sum'],
            'bottom' => '今年收款：' . $thisyear['sum'],
        ),
    );
    return $data;
}

function acg_this_card()
{

    $data =acg_get_admin_dashboard_data();
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

function acg_this_charts_data($order_type = 0)
{
    $cycle        = 'day';
    $time_day     = '30';
    $time_end     = current_time('Y-m-d 23:59:59');
    $time_start   = date('Y-m-d 00:00:00', strtotime("-$time_day day", strtotime($time_end)));
    $filling      = acg_this_get_time_filling($cycle, array($time_start, $time_end));
    $cycle_format = '%Y-%m-%d';

    global $wpdb;
    $order_type_where = $order_type ? " and order_type='$order_type'" : "";
    $db_data = $wpdb->get_results("SELECT COUNT(*) as count,SUM(order_price) as price,date_format(order_date, '$cycle_format') as time FROM {$wpdb->prefix}zrz_order WHERE `order_state` = 'q' AND `pay_type` != 'balance' AND order_price > 0 AND order_date BETWEEN '$time_start' AND '$time_end' $order_type_where group by date_format(order_date,'$cycle_format')");



    $nums   = $filling['data'];
    $total  = $filling['data'];
    $result = $filling['time'];
    array_walk($db_data, function ($value, $key) use ($result, &$nums, &$total) {
        $value         = (array) $value;
        $index         = array_search($value['time'], $result);
        $nums[$index]  = $value['count'];
        $total[$index] = floatval($value['price']);
    });
    $chart_data = [
        'time'  => $result,
        'count' => $nums,
        'price' => $total,
    ];
    return $chart_data;
}

$charts_data     = acg_this_charts_data();
$vip_charts_data = acg_this_charts_data('vip');

//获取填充时间
function acg_this_get_time_filling($cycle, $time)
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
<?php echo acg_this_card(); ?>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">有效收款单量</div>
            <div style="margin:0 -30px -20px 0px;"><div id="highcharts_count" style="height:300px"></div></div>
        </div>
    </div>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">有效收款金额</div>
            <div style="margin:0 -30px -20px 0px;"><div id="highcharts_price" style="height:300px"></div></div>
        </div>
    </div>
    <script type="text/javascript">
(function ($, document) {
    $(document).ready(function ($) {
        var option_1 = {
            legend: {
                data: ['全部订单',  '购买会员']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($charts_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部订单',
                data: <?php echo json_encode($charts_data['count']); ?>,
                type: 'line',
                smooth: true
            }, {
                name: '购买会员',
                data: <?php echo json_encode($vip_charts_data['count']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('highcharts_count'), 'westeros');
        myChart.setOption(option_1);

        var option_2 = {
            legend: {
                data: ['全部订单', '购买会员']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($charts_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部订单',
                data: <?php echo json_encode($charts_data['price']); ?>,
                type: 'line',
                smooth: true
            }, {
                name: '购买会员',
                data: <?php echo json_encode($vip_charts_data['price']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('highcharts_price'), 'westeros');
        myChart.setOption(option_2);
    });
})(jQuery, document);
    </script>
</div>