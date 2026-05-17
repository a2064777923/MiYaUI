<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_IP_Location {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get_location_by_ip($ip_address) {
        if (empty($ip_address) || $ip_address === '127.0.0.1' || $ip_address === '::1') {
            return array(
                'country' => 'Local',
                'region' => 'Local',
                'city' => 'Local',
                'is_domestic' => true,
                'display_location' => '本地',
                'country_flag' => '🏠',
                'isp' => '',
                'timezone' => '',
                'coordinates' => array('lat' => 0, 'lon' => 0)
            );
        }
        
        $cached_location = $this->get_cached_location($ip_address);
        if ($cached_location !== false) {
            return $cached_location;
        }
        
        $location = $this->fetch_location_from_api($ip_address);
        
        if ($location) {
            $this->cache_location($ip_address, $location);
        }
        
        return $location;
    }
    
    private function fetch_location_from_api($ip_address) {
        $apis = array(
            'pconline' => 'http://whois.pconline.com.cn/ipJson.jsp?ip=' . $ip_address . '&json=true',
            'ip-api' => 'http://ip-api.com/json/' . $ip_address . '?fields=status,country,countryCode,region,regionName,city,isp,timezone,lat,lon&lang=zh-CN',
            'ipapi' => 'http://ipapi.co/' . $ip_address . '/json/',
            'ipinfo' => 'http://ipinfo.io/' . $ip_address . '/json'
        );
        
        foreach ($apis as $api_name => $api_url) {
            $location = $this->call_api($api_url, $api_name);
            if ($location !== false) {
                return $location;
            }
        }
        
        return array(
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'is_domestic' => false,
            'display_location' => '未知',
            'country_flag' => '❓',
            'isp' => '',
            'timezone' => '',
            'coordinates' => array('lat' => 0, 'lon' => 0)
        );
    }
    
    private function call_api($url, $api_name) {
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'WordPress Anti Account Sharing Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // 处理太平洋IP接口的编码问题
        if ($api_name === 'pconline') {
            // 检测编码并转换为UTF-8
            $encoding = mb_detect_encoding($body, array('UTF-8', 'GBK', 'GB2312'), true);
            if ($encoding && $encoding !== 'UTF-8') {
                $body = mb_convert_encoding($body, 'UTF-8', $encoding);
            }
        }
        
        $data = json_decode($body, true);
        
        if (!$data) {
            return false;
        }
        
        return $this->parse_api_response($data, $api_name);
    }
    
    private function parse_api_response($data, $api_name) {
        $location = array(
            'country' => 'Unknown',
            'region' => 'Unknown', 
            'city' => 'Unknown',
            'is_domestic' => false,
            'display_location' => '未知',
            'country_flag' => '❓',
            'isp' => '',
            'timezone' => '',
            'coordinates' => array('lat' => 0, 'lon' => 0)
        );
        
        switch ($api_name) {
            case 'pconline':
                if (isset($data['pro']) || isset($data['city']) || isset($data['addr'])) {
                    // 太平洋IP查询接口处理
                    $location['country'] = '中国';
                    
                    // 解析省份信息
                    if (isset($data['pro']) && !empty($data['pro'])) {
                        $location['region'] = $data['pro'];
                    } elseif (isset($data['addr'])) {
                        // 从addr字段提取省份信息
                        if (preg_match('/^(.+?省|.+?市|.+?自治区|.+?特别行政区)/', $data['addr'], $matches)) {
                            $location['region'] = $matches[1];
                        } else {
                            $location['region'] = 'Unknown';
                        }
                    } else {
                        $location['region'] = 'Unknown';
                    }
                    
                    // 解析城市信息
                    if (isset($data['city']) && !empty($data['city'])) {
                        $location['city'] = $data['city'];
                    } elseif (isset($data['addr'])) {
                        // 从addr字段提取城市信息
                        if (preg_match('/(.+?市|.+?县|.+?区)/', $data['addr'], $matches)) {
                            $location['city'] = $matches[1];
                        } else {
                            $location['city'] = 'Unknown';
                        }
                    } else {
                        $location['city'] = 'Unknown';
                    }
                    
                    // ISP信息 - 优先使用addr字段的详细信息
                    if (isset($data['addr']) && !empty($data['addr'])) {
                        $location['isp'] = $data['addr'];
                    } else {
                        $location['isp'] = '';
                    }
                    
                    $location['timezone'] = 'Asia/Shanghai'; // 国内默认时区
                    $location['coordinates'] = array('lat' => 0, 'lon' => 0); // 太平洋接口不提供坐标
                    $location['country_flag'] = $this->get_country_flag('CN');
                    $location['is_domestic'] = true;
                    $location['display_location'] = $this->format_display_location($location);
                }
                break;
                
            case 'ip-api':
                if (isset($data['status']) && $data['status'] === 'success') {
                    $location['country'] = isset($data['country']) ? $data['country'] : 'Unknown';
                    $location['region'] = isset($data['regionName']) ? $data['regionName'] : 'Unknown';
                    $location['city'] = isset($data['city']) ? $data['city'] : 'Unknown';
                    $location['isp'] = isset($data['isp']) ? $data['isp'] : '';
                    $location['timezone'] = isset($data['timezone']) ? $data['timezone'] : '';
                    $location['coordinates'] = array(
                        'lat' => isset($data['lat']) ? $data['lat'] : 0,
                        'lon' => isset($data['lon']) ? $data['lon'] : 0
                    );
                    $country_code = isset($data['countryCode']) ? $data['countryCode'] : '';
                    $location['country_flag'] = $this->get_country_flag($country_code);
                    $location['is_domestic'] = $this->is_domestic_country($location['country']);
                    $location['display_location'] = $this->format_display_location($location);
                }
                break;
                
            case 'ipapi':
                if (isset($data['country_name'])) {
                    $location['country'] = $data['country_name'];
                    $location['region'] = isset($data['region']) ? $data['region'] : 'Unknown';
                    $location['city'] = isset($data['city']) ? $data['city'] : 'Unknown';
                    $location['isp'] = isset($data['org']) ? $data['org'] : '';
                    $location['timezone'] = isset($data['timezone']) ? $data['timezone'] : '';
                    $location['coordinates'] = array(
                        'lat' => isset($data['latitude']) ? $data['latitude'] : 0,
                        'lon' => isset($data['longitude']) ? $data['longitude'] : 0
                    );
                    $country_code = isset($data['country_code']) ? $data['country_code'] : '';
                    $location['country_flag'] = $this->get_country_flag($country_code);
                    $location['is_domestic'] = $this->is_domestic_country($location['country']);
                    $location['display_location'] = $this->format_display_location($location);
                }
                break;
                
            case 'ipinfo':
                if (isset($data['country'])) {
                    $country_code = $data['country'];
                    $location['country'] = $this->get_country_name_by_code($country_code);
                    $location['region'] = isset($data['region']) ? $data['region'] : 'Unknown';
                    $location['city'] = isset($data['city']) ? $data['city'] : 'Unknown';
                    $location['isp'] = isset($data['org']) ? $data['org'] : '';
                    $location['timezone'] = isset($data['timezone']) ? $data['timezone'] : '';
                    
                    if (isset($data['loc'])) {
                        $coords = explode(',', $data['loc']);
                        $location['coordinates'] = array(
                            'lat' => isset($coords[0]) ? floatval($coords[0]) : 0,
                            'lon' => isset($coords[1]) ? floatval($coords[1]) : 0
                        );
                    }
                    
                    $location['country_flag'] = $this->get_country_flag($country_code);
                    $location['is_domestic'] = $this->is_domestic_country($location['country']);
                    $location['display_location'] = $this->format_display_location($location);
                }
                break;
        }
        
        return $location;
    }
    
    private function is_domestic_country($country) {
        $domestic_countries = array(
            'China', '中国', 'CN', 'People\'s Republic of China',
            'Hong Kong', '香港', 'HK',
            'Macao', '澳门', 'MO', 'Macau',
            'Taiwan', '台湾', 'TW'
        );
        
        return in_array($country, $domestic_countries);
    }
    
    private function get_country_name_by_code($country_code) {
        $country_codes = array(
            'CN' => '中国',
            'HK' => '香港',
            'MO' => '澳门',
            'TW' => '台湾',
            'US' => '美国',
            'JP' => '日本',
            'KR' => '韩国',
            'SG' => '新加坡',
            'MY' => '马来西亚',
            'TH' => '泰国',
            'VN' => '越南',
            'ID' => '印度尼西亚',
            'PH' => '菲律宾',
            'IN' => '印度',
            'GB' => '英国',
            'DE' => '德国',
            'FR' => '法国',
            'IT' => '意大利',
            'ES' => '西班牙',
            'RU' => '俄罗斯',
            'CA' => '加拿大',
            'AU' => '澳大利亚',
            'BR' => '巴西',
            'MX' => '墨西哥',
            'AR' => '阿根廷'
        );
        
        return isset($country_codes[$country_code]) ? $country_codes[$country_code] : $country_code;
    }
    
    private function get_cached_location($ip_address) {
        $cache_key = 'aas_ip_location_' . md5($ip_address);
        return get_transient($cache_key);
    }
    
    private function cache_location($ip_address, $location) {
        $cache_key = 'aas_ip_location_' . md5($ip_address);
        set_transient($cache_key, $location, 24 * HOUR_IN_SECONDS);
    }
    
    public function get_user_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx代理
            'HTTP_X_FORWARDED_FOR',      // 标准代理头
            'HTTP_X_FORWARDED',          // 代理变体
            'HTTP_X_CLUSTER_CLIENT_IP',  // 集群代理
            'HTTP_CLIENT_IP',            // 客户端IP
            'HTTP_FORWARDED_FOR',        // 转发头
            'HTTP_FORWARDED',            // RFC 7239
            'REMOTE_ADDR'                // 最后备选
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // 基本IP格式验证
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    // 排除明显的无效IP
                    if ($ip !== '0.0.0.0' && $ip !== '127.0.0.1' && $ip !== '::1') {
                        return $ip;
                    }
                }
            }
        }
        
        // 如果以上都没有找到有效IP，返回REMOTE_ADDR
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    public function is_ip_blocked($ip_address) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_ip_restrictions';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE ip_address = %s AND restriction_type = 'blocked'",
            $ip_address
        ));
        
        return !empty($result);
    }
    
    public function block_ip($ip_address, $reason = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_ip_restrictions';
        return $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'restriction_type' => 'blocked',
                'reason' => $reason,
                'created_time' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    public function unblock_ip($ip_address) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_ip_restrictions';
        return $wpdb->delete(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'restriction_type' => 'blocked'
            ),
            array('%s', '%s')
        );
    }
    
    public function get_country_flag($country_code) {
        $flags = array(
            'CN' => '🇨🇳', 'HK' => '🇭🇰', 'MO' => '🇲🇴', 'TW' => '🇹🇼',
            'US' => '🇺🇸', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'SG' => '🇸🇬',
            'MY' => '🇲🇾', 'TH' => '🇹🇭', 'VN' => '🇻🇳', 'ID' => '🇮🇩',
            'PH' => '🇵🇭', 'IN' => '🇮🇳', 'GB' => '🇬🇧', 'DE' => '🇩🇪',
            'FR' => '🇫🇷', 'IT' => '🇮🇹', 'ES' => '🇪🇸', 'RU' => '🇷🇺',
            'CA' => '🇨🇦', 'AU' => '🇦🇺', 'BR' => '🇧🇷', 'MX' => '🇲🇽',
            'AR' => '🇦🇷', 'NL' => '🇳🇱', 'BE' => '🇧🇪', 'CH' => '🇨🇭',
            'AT' => '🇦🇹', 'SE' => '🇸🇪', 'NO' => '🇳🇴', 'DK' => '🇩🇰',
            'FI' => '🇫🇮', 'PL' => '🇵🇱', 'CZ' => '🇨🇿', 'HU' => '🇭🇺',
            'GR' => '🇬🇷', 'PT' => '🇵🇹', 'IE' => '🇮🇪', 'IL' => '🇮🇱',
            'SA' => '🇸🇦', 'AE' => '🇦🇪', 'TR' => '🇹🇷', 'EG' => '🇪🇬',
            'ZA' => '🇿🇦', 'NG' => '🇳🇬', 'KE' => '🇰🇪', 'ET' => '🇪🇹',
            'NZ' => '🇳🇿', 'CL' => '🇨🇱', 'PE' => '🇵🇪', 'CO' => '🇨🇴',
            'VE' => '🇻🇪', 'UY' => '🇺🇾', 'PY' => '🇵🇾', 'BO' => '🇧🇴',
            'EC' => '🇪🇨', 'CR' => '🇨🇷', 'PA' => '🇵🇦', 'GT' => '🇬🇹',
            'NI' => '🇳🇮', 'HN' => '🇭🇳', 'SV' => '🇸🇻', 'BZ' => '🇧🇿',
            'JM' => '🇯🇲', 'TT' => '🇹🇹', 'BB' => '🇧🇧', 'GY' => '🇬🇾',
            'SR' => '🇸🇷', 'UZ' => '🇺🇿', 'KZ' => '🇰🇿', 'KG' => '🇰🇬',
            'TJ' => '🇹🇯', 'TM' => '🇹🇲', 'AF' => '🇦🇫', 'PK' => '🇵🇰',
            'BD' => '🇧🇩', 'LK' => '🇱🇰', 'MV' => '🇲🇻', 'NP' => '🇳🇵',
            'BT' => '🇧🇹', 'MM' => '🇲🇲', 'LA' => '🇱🇦', 'KH' => '🇰🇭',
            'BN' => '🇧🇳', 'TL' => '🇹🇱', 'MN' => '🇲🇳', 'KP' => '🇰🇵'
        );
        
        return isset($flags[strtoupper($country_code)]) ? $flags[strtoupper($country_code)] : '🌍';
    }
    
    public function format_display_location($location) {
        $parts = array();
        
        if (!empty($location['city']) && $location['city'] !== 'Unknown') {
            $parts[] = $location['city'];
        }
        
        if (!empty($location['region']) && $location['region'] !== 'Unknown' && $location['region'] !== $location['city']) {
            $parts[] = $location['region'];
        }
        
        if (!empty($location['country']) && $location['country'] !== 'Unknown') {
            $parts[] = $location['country'];
        }
        
        $display = implode(', ', $parts);
        
        if (empty($display) || $display === 'Unknown' || $display === 'Unknown, Unknown') {
            return '未知位置';
        }
        
        return $display;
    }
    
    public function get_location_with_formatting($ip_address) {
        $location = $this->get_location_by_ip($ip_address);
        
        if (!$location) {
            return array(
                'html' => '<span class="aas-location unknown">❓ 未知位置</span>',
                'text' => '未知位置',
                'data' => null
            );
        }
        
        $flag = $location['country_flag'];
        $display = $location['display_location'];
        $is_domestic = $location['is_domestic'];
        $isp = !empty($location['isp']) ? $location['isp'] : '';
        
        $class = $is_domestic ? 'domestic' : 'foreign';
        $title_parts = array();
        
        if (!empty($isp)) {
            $title_parts[] = "ISP: {$isp}";
        }
        
        if (!empty($location['timezone'])) {
            $title_parts[] = "时区: {$location['timezone']}";
        }
        
        if ($location['coordinates']['lat'] != 0 || $location['coordinates']['lon'] != 0) {
            $title_parts[] = sprintf("坐标: %.4f, %.4f", $location['coordinates']['lat'], $location['coordinates']['lon']);
        }
        
        $title = !empty($title_parts) ? implode(' | ', $title_parts) : $display;
        
        $html = sprintf(
            '<span class="aas-location %s" title="%s">%s %s</span>',
            $class,
            esc_attr($title),
            $flag,
            esc_html($display)
        );
        
        return array(
            'html' => $html,
            'text' => $display,
            'data' => $location
        );
    }
    
    public function get_ip_info_popup($ip_address) {
        $location = $this->get_location_by_ip($ip_address);
        
        if (!$location) {
            return '<div class="aas-ip-info">无法获取IP信息</div>';
        }
        
        $html = '<div class="aas-ip-info">';
        $html .= '<div class="aas-ip-header">';
        $html .= '<strong>' . $location['country_flag'] . ' ' . esc_html($ip_address) . '</strong>';
        $html .= '</div>';
        
        $html .= '<div class="aas-ip-details">';
        $html .= '<div class="aas-detail-row">';
        $html .= '<span class="label">位置:</span>';
        $html .= '<span class="value">' . esc_html($location['display_location']) . '</span>';
        $html .= '</div>';
        
        if (!empty($location['isp'])) {
            $html .= '<div class="aas-detail-row">';
            $html .= '<span class="label">ISP:</span>';
            $html .= '<span class="value">' . esc_html($location['isp']) . '</span>';
            $html .= '</div>';
        }
        
        if (!empty($location['timezone'])) {
            $html .= '<div class="aas-detail-row">';
            $html .= '<span class="label">时区:</span>';
            $html .= '<span class="value">' . esc_html($location['timezone']) . '</span>';
            $html .= '</div>';
        }
        
        if ($location['coordinates']['lat'] != 0 || $location['coordinates']['lon'] != 0) {
            $html .= '<div class="aas-detail-row">';
            $html .= '<span class="label">坐标:</span>';
            $html .= '<span class="value">' . sprintf('%.4f, %.4f', $location['coordinates']['lat'], $location['coordinates']['lon']) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '<div class="aas-detail-row">';
        $html .= '<span class="label">地区:</span>';
        $html .= '<span class="value ' . ($location['is_domestic'] ? 'domestic' : 'foreign') . '">';
        $html .= $location['is_domestic'] ? '国内' : '国外';
        $html .= '</span>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // 添加关闭按钮
        $html .= '<div style="text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">';
        $html .= '<button type="button" class="button" onclick="jQuery(\'#ip-details-modal\').hide()">关闭</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
}