<?php
if (!defined('ABSPATH')) {
    exit;
}

class AAS_User_Classification {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function update_user_classification($user_id) {
        $location_count = $this->get_user_location_count($user_id);
        $classification = $this->determine_classification($location_count);
        
        $this->save_user_classification($user_id, $classification, $location_count);
        
        return $classification;
    }
    
    public function get_user_classification($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return $result;
    }
    
    public function get_user_location_count($user_id, $days = 7) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(country, '-', region, '-', city)) 
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s",
            $user_id,
            $date_limit
        ));
        
        return intval($result);
    }
    
    public function determine_classification($location_count) {
        if ($location_count <= 1) {
            return 'safe';
        } elseif ($location_count <= 2) {
            return 'low_risk';
        } elseif ($location_count <= 4) {
            return 'medium_risk';
        } else {
            return 'high_risk';
        }
    }
    
    public function get_classification_label($classification) {
        $labels = array(
            'safe' => __('Safe', 'anti-account-sharing'),
            'low_risk' => __('Low Risk', 'anti-account-sharing'),
            'medium_risk' => __('Medium Risk', 'anti-account-sharing'),
            'high_risk' => __('High Risk', 'anti-account-sharing')
        );
        
        return isset($labels[$classification]) ? $labels[$classification] : __('Unknown', 'anti-account-sharing');
    }
    
    public function get_classification_color($classification) {
        $colors = array(
            'safe' => '#28a745',
            'low_risk' => '#ffc107',
            'medium_risk' => '#fd7e14',
            'high_risk' => '#dc3545'
        );
        
        return isset($colors[$classification]) ? $colors[$classification] : '#6c757d';
    }
    
    private function save_user_classification($user_id, $classification, $location_count) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        // 获取之前的分类以检测变化
        $previous = $wpdb->get_row($wpdb->prepare(
            "SELECT classification FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if ($previous) {
            $wpdb->update(
                $table_name,
                array(
                    'classification' => $classification,
                    'location_count' => $location_count,
                    'last_updated' => current_time('mysql')
                ),
                array('user_id' => $user_id),
                array('%s', '%d', '%s'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'classification' => $classification,
                    'location_count' => $location_count,
                    'last_updated' => current_time('mysql'),
                    'warning_count' => 0,
                    'is_banned' => 0
                ),
                array('%d', '%s', '%d', '%s', '%d', '%d')
            );
        }
        
        // 检测是否新升级为高风险用户，触发管理员通知
        if ($classification === 'high_risk' && (!$previous || $previous->classification !== 'high_risk')) {
            $current_classification_data = $this->get_user_classification($user_id);
            do_action('aas_high_risk_user_detected', $user_id, array(
                'classification' => $classification,
                'location_count' => $location_count,
                'previous_classification' => $previous ? $previous->classification : 'none',
                'warning_count' => $current_classification_data ? $current_classification_data->warning_count : 0
            ));
        }
        
        // 检测多地登录触发管理员通知
        if ($location_count >= 3) {
            $recent_locations = $this->get_recent_login_locations($user_id, 5);
            if (count($recent_locations) >= 3) {
                do_action('aas_multiple_locations_detected', $user_id, $recent_locations, '7天内');
            }
        }
    }
    
    public function get_users_by_classification($classification, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        $users_table = $wpdb->users;
        
        $sql = $wpdb->prepare(
            "SELECT u.ID, u.user_login, u.user_email, u.display_name, 
                    c.classification, c.location_count, c.warning_count, 
                    c.is_banned, c.last_updated
             FROM $users_table u
             INNER JOIN $table_name c ON u.ID = c.user_id
             WHERE c.classification = %s
             ORDER BY c.last_updated DESC
             LIMIT %d OFFSET %d",
            $classification,
            $limit,
            $offset
        );
        
        return $wpdb->get_results($sql);
    }
    
    public function get_classification_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $results = $wpdb->get_results(
            "SELECT classification, COUNT(*) as count 
             FROM $table_name 
             GROUP BY classification"
        );
        
        $stats = array(
            'safe' => 0,
            'low_risk' => 0,
            'medium_risk' => 0,
            'high_risk' => 0,
            'total' => 0
        );
        
        foreach ($results as $result) {
            $stats[$result->classification] = intval($result->count);
            $stats['total'] += intval($result->count);
        }
        
        return $stats;
    }
    
    public function get_recent_login_locations($user_id, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT country, region, city, login_time, ip_address
             FROM $table_name 
             WHERE user_id = %d 
             ORDER BY login_time DESC 
             LIMIT %d",
            $user_id,
            $limit
        ));
        
        return $results;
    }
    
    public function get_user_login_pattern($user_id, $days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(login_time) as login_date,
                COUNT(*) as login_count,
                COUNT(DISTINCT CONCAT(country, '-', region, '-', city)) as location_count,
                COUNT(DISTINCT ip_address) as ip_count
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s
             GROUP BY DATE(login_time)
             ORDER BY login_date DESC",
            $user_id,
            $date_limit
        ));
        
        return $results;
    }
    
    public function is_suspicious_activity($user_id) {
        $classification = $this->get_user_classification($user_id);
        
        if (!$classification) {
            return false;
        }
        
        if ($classification->classification === 'high_risk') {
            return true;
        }
        
        if ($classification->location_count >= get_option('aas_warning_threshold', 3)) {
            return true;
        }
        
        $recent_logins = $this->get_recent_simultaneous_logins($user_id);
        if ($recent_logins > 1) {
            return true;
        }
        
        return false;
    }
    
    private function get_recent_simultaneous_logins($user_id, $minutes = 5) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_login_logs';
        $time_limit = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT ip_address) 
             FROM $table_name 
             WHERE user_id = %d AND login_time >= %s AND status = 'active'",
            $user_id,
            $time_limit
        ));
        
        return intval($result);
    }
    
    public function ban_user($user_id, $reason = '', $duration_days = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        // 计算封禁过期时间
        $ban_expires = null;
        if ($duration_days !== null && is_numeric($duration_days) && $duration_days > 0) {
            $ban_expires = date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
        }
        
        // 准备更新数据
        $update_data = array(
            'is_banned' => 1,
            'ban_reason' => $reason,
            'ban_time' => current_time('mysql')
        );
        $update_format = array('%d', '%s', '%s');
        
        if ($ban_expires) {
            $update_data['ban_expires'] = $ban_expires;
            $update_format[] = '%s';
        }
        
        $wpdb->update(
            $table_name,
            $update_data,
            array('user_id' => $user_id),
            $update_format,
            array('%d')
        );
        
        // 销毁所有会话
        $sessions = WP_Session_Tokens::get_instance($user_id);
        $sessions->destroy_all();
        
        do_action('aas_user_banned', $user_id, $reason, $duration_days);
    }
    
    public function unban_user($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        $wpdb->update(
            $table_name,
            array(
                'is_banned' => 0,
                'ban_reason' => '',
                'ban_time' => null,
                'ban_expires' => null
            ),
            array('user_id' => $user_id),
            array('%d', '%s', '%s', '%s'),
            array('%d')
        );
        
        do_action('aas_user_unbanned', $user_id);
    }
    
    public function add_user_to_whitelist($user_id, $added_by, $reason = '', $expires_time = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_whitelist';
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
        
        if (!$existing) {
            $data = array(
                'user_id' => $user_id,
                'added_by' => $added_by,
                'reason' => $reason,
                'created_time' => current_time('mysql'),
                'is_active' => 1
            );
            $format = array('%d', '%d', '%s', '%s', '%d');
            
            if ($expires_time) {
                $data['expires_time'] = $expires_time;
                $format[] = '%s';
            }
            
            $result = $wpdb->insert($table_name, $data, $format);
            
            if ($result !== false) {
                // 更新用户信任分数
                $this->update_user_trust_score($user_id, 20);
                do_action('aas_user_whitelisted', $user_id, $added_by, $reason);
                return true;
            } else {
                error_log('AAS: Failed to add user to whitelist. User ID: ' . $user_id . ', Error: ' . $wpdb->last_error);
                return false;
            }
        }
        
        return false;
    }
    
    public function remove_user_from_whitelist($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $result = $wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('user_id' => $user_id),
            array('%d'),
            array('%d')
        );
        
        if ($result !== false) {
            // 减少用户信任分数
            $this->update_user_trust_score($user_id, -10);
            do_action('aas_user_removed_from_whitelist', $user_id);
            return true;
        } else {
            error_log('AAS: Failed to remove user from whitelist. User ID: ' . $user_id . ', Error: ' . $wpdb->last_error);
            return false;
        }
    }
    
    public function is_user_whitelisted($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_whitelist';
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND is_active = 1 
             AND (expires_time IS NULL OR expires_time > %s)",
            $user_id,
            current_time('mysql')
        ));
        
        return !empty($result);
    }
    
    public function update_user_trust_score($user_id, $change) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aas_user_classification';
        
        // 获取当前分数
        $current = $wpdb->get_var($wpdb->prepare(
            "SELECT trust_score FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        $new_score = max(0, min(100, ($current ?? 50) + $change));
        
        // 更新或插入记录
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            $wpdb->update(
                $table_name,
                array('trust_score' => $new_score),
                array('user_id' => $user_id),
                array('%d'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'classification' => 'safe',
                    'trust_score' => $new_score,
                    'location_count' => 0,
                    'warning_count' => 0,
                    'is_banned' => 0
                ),
                array('%d', '%s', '%d', '%d', '%d', '%d')
            );
        }
        
        return $new_score;
    }
    
    public function calculate_risk_score($user_id) {
        $classification = $this->get_user_classification($user_id);
        if (!$classification) {
            return 0;
        }
        
        $risk_score = 0;
        
        // 基于分类的风险分数
        switch ($classification->classification) {
            case 'high_risk':
                $risk_score += 40;
                break;
            case 'medium_risk':
                $risk_score += 25;
                break;
            case 'low_risk':
                $risk_score += 10;
                break;
        }
        
        // 基于地理位置数量
        $risk_score += min(30, $classification->location_count * 5);
        
        // 基于警告次数
        $risk_score += min(20, $classification->warning_count * 5);
        
        // 基于信任分数（反向）
        $trust_factor = (100 - ($classification->trust_score ?? 50)) / 100;
        $risk_score += $trust_factor * 10;
        
        // 检查近期登录模式
        $recent_locations = $this->get_recent_login_locations($user_id, 5);
        if (count($recent_locations) >= 3) {
            $risk_score += 15;
        }
        
        return min(100, max(0, $risk_score));
    }
    
    public function get_intelligent_recommendations($user_id) {
        $classification = $this->get_user_classification($user_id);
        $risk_score = $this->calculate_risk_score($user_id);
        $is_whitelisted = $this->is_user_whitelisted($user_id);
        
        $recommendations = array();
        
        if ($is_whitelisted) {
            $recommendations[] = array(
                'type' => 'info',
                'message' => '用户已在白名单中，享受更宽松的安全策略'
            );
        }
        
        if ($risk_score > 70) {
            $recommendations[] = array(
                'type' => 'danger',
                'message' => '高风险用户，建议立即审查账户活动'
            );
        } elseif ($risk_score > 40) {
            $recommendations[] = array(
                'type' => 'warning',
                'message' => '中等风险用户，建议加强监控'
            );
        }
        
        if ($classification && $classification->location_count > 5) {
            $recommendations[] = array(
                'type' => 'warning',
                'message' => '用户在多个地区登录，建议验证身份'
            );
        }
        
        if ($classification && $classification->warning_count > 2) {
            $recommendations[] = array(
                'type' => 'danger',
                'message' => '多次触发安全警告，建议采取限制措施'
            );
        }
        
        return $recommendations;
    }
}