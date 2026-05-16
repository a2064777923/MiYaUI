<?php
defined('ABSPATH') || exit;

class JithemeGg
{
    public static function Ji_Gg_index_list_01($cpay_id, $id){
        $top_gg_img_01 = self::Jitheme_capy_list($cpay_id);
        $top_gg_01_html='';
        if (isset($top_gg_img_01['data'])) {
            $cpay = $top_gg_img_01['data'];
            $i = 0;
            // 获取当前时间的时间戳
            $current_timestamp = time();
            foreach ($cpay as $item) {
                // 转换 modified_time 为时间戳
                $modified_timestamp = strtotime($item->modified_time);
        
                // 判断当前日期是否小于 modified_time
                if ($current_timestamp < $modified_timestamp && $i < $id && isset($item)) {
                    $imgUrl = $item->img;
                    $top_gg_01_html .= '<div class="ji-img-2">
                        <a href="'. ($item->links ?? '无链接') .'" target="_blank">
                            <div class="graphicad" style="background-image: url(' . htmlspecialchars($imgUrl) . ');background-repeat: no-repeat;">
                            </div>
                        </a>
                        <a class="label" href="./cpay/' . $cpay_id . '.html" target="_blank"><span class="inco">投放</span> <span data-v-f3038604="">广告</span></a>
                    </div>';
                    $i++;
                }
            }
        
            // 如果数据少于2条，添加默认广告
            while ($i < $id) {
                $top_gg_01_html .= '<div class="ji-img-2">
                    <a href="#" target="_blank">
                        <div class="graphicad" style="background-image: url(https://images.jitheme.com/uploads/2024/10/20241027164611720.webp);background-repeat: no-repeat;">
                        </div>
                    </a>
                    <a class="label" href="./cpay/' . $cpay_id . '.html" target="_blank"><span class="inco">投放</span> <span data-v-f3038604="">广告</span></a>
                </div>';
                $i++;
            }
        }


        return $top_gg_01_html; // Return the generated HTML.
    }
    public static function Ji_Gg_index_list_02($cpay_id, $id){
        $top_gg_test_01 = self::Jitheme_capy_list($cpay_id);
        if (isset($top_gg_test_01['data'])) {
            $cpay = $top_gg_test_01['data'];
            $top_gg_test_01_html = '';
            $current_count = 0;
        
            // 获取当前时间的时间戳
            $current_timestamp = time();
        
            foreach ($cpay as $item) {
                $selected_color = self::Jitheme_gg_color();
                
                // 转换 modified_time 为时间戳
                $modified_timestamp = strtotime($item->modified_time);
        
                // 判断当前日期是否小于 modified_time
                if ($current_timestamp < $modified_timestamp) {
                    if (isset($item)) {
                        $top_gg_test_01_html .= '<div class="ji-txt-6">
                            <a href="'. ($item->links ?? '无链接') .'" class="d-flex align-items-center auto-url-list b2-radius px-2 py-1" target="_blank" rel="external">
                                <div class="auto-ad-name" style="color:' . $selected_color . '">'. unicodeToString(($item->title ?? '无名称')) .'</div>
                            </a>
                        </div>';
                        $current_count++;
                    } else {
                        echo "数据无效" . PHP_EOL;
                    }
                }
            }
        
            // 如果数据少于8条, 添加默认广告
            while ($current_count < $id) {
                $top_gg_test_01_html .= '<div class="ji-txt-6">
                    <a href="./cpay/' . $cpay_id . '.html" class="d-flex align-items-center auto-url-list b2-radius px-2 py-1" target="_blank" rel="external">
                        <div class="auto-ad-name" style="color:var(--key-color)">广告投放</div>
                    </a>
                </div>';
                $current_count++;
            }
        }


        return $top_gg_test_01_html; // Return the generated HTML.
    }
    public static function Jitheme_capy_list($cpay_id) {
        global $wpdb;
        // $tableA = $wpdb->prefix . 'posts';
        $tableB = $wpdb->prefix . 'jitheme_cpay_gg';
        $queryB = "SELECT * FROM $tableB WHERE post_id = %d";
        $resultsB = $wpdb->get_results($wpdb->prepare($queryB, $cpay_id));
        // 检查是否有错误
        if ($wpdb->last_error) {
            return array(
                'status' => 'error',
                'message' => '数据库查询出错: ' . $wpdb->last_error,
                'data' => array(),
            );
        }
        // 解析 JSON 数据并添加到结果数组

        // 调试输出
        return array(
            'allow' => 'Jitheme_gg', // 可以设置为其他值来反映授予情况
            'message' => '成功获取极主题广告列表',
            'data' => $resultsB, // 返回合并后的结果
            );
    }
    public static function Jitheme_gg_color() {
        $colors = [
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color1'),
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color2'),
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color3'),
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color4'),
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color5'),
            b2_get_option('Jitheme_main_tab2', 'index_jiaobiao_color6'),
        ];
        // 随机选择一个颜色
        $random_color = $colors[array_rand($colors)];
    
        return $random_color;
    }
}